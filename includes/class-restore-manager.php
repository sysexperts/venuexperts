<?php
/**
 * Restore Manager für Kulturhaus Events.
 *
 * Importiert ZIP-Backups und stellt Daten wieder her.
 * Nutzt MERGE-Strategie: Duplikate werden ignoriert, neue Posts erstellt.
 *
 * @package KulturhausEvents
 * @since   1.21.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Restore_Manager
 *
 * Verwaltet den Import von ZIP-Backups.
 */
class KH_Restore_Manager {

	/**
	 * Temporärer Extraktions-Verzeichnis.
	 *
	 * @var string
	 */
	private string $temp_dir;

	/**
	 * ID-Mapping für Post Types (backup_id => new_id).
	 *
	 * @var array<string, array<int, int>>
	 */
	private array $id_mapping = array(
		'kh_event'         => array(),
		'kh_venue'         => array(),
		'kh_organizer'     => array(),
		'kh_pricing_model' => array(),
	);

	/**
	 * Attachment ID-Mapping.
	 *
	 * @var array<int, int>
	 */
	private array $attachment_mapping = array();

	/**
	 * Restore-Statistik.
	 *
	 * @var array<string, int>
	 */
	private array $stats = array(
		'events_imported'      => 0,
		'events_skipped'       => 0,
		'venues_imported'      => 0,
		'organizers_imported'  => 0,
		'pricing_imported'     => 0,
		'attachments_imported' => 0,
	);

	/**
	 * Log-Meldungen.
	 *
	 * @var array<string>
	 */
	private array $logs = array();

	/**
	 * Backup von ZIP-Datei wiederherstellen.
	 *
	 * @param string $file_path Pfad zur Backup-ZIP-Datei.
	 * @return array<string, mixed> Statistik und Logs.
	 */
	public function restore_backup( string $file_path ): array {
		// Sicherheitschecks
		if ( ! current_user_can( 'manage_options' ) ) {
			return array(
				'success' => false,
				'message' => __( 'Unberechtigt', 'kulturhaus-events' ),
			);
		}

		if ( ! file_exists( $file_path ) ) {
			return array(
				'success' => false,
				'message' => __( 'Backup-Datei nicht gefunden', 'kulturhaus-events' ),
			);
		}

		// ZIP extrahieren
		$this->temp_dir = wp_upload_dir()['basedir'] . '/.kh-restore-temp';
		wp_mkdir_p( $this->temp_dir );

		if ( ! $this->extract_backup( $file_path ) ) {
			return array(
				'success' => false,
				'message' => __( 'Backup konnte nicht entpackt werden', 'kulturhaus-events' ),
			);
		}

		// Manifest validieren
		$manifest = $this->load_manifest();
		if ( ! $manifest ) {
			return array(
				'success' => false,
				'message' => __( 'Backup-Manifest ungültig', 'kulturhaus-events' ),
			);
		}

		// Checksums validieren
		if ( ! $this->validate_checksums( $manifest ) ) {
			return array(
				'success' => false,
				'message' => __( 'Backup-Integrität überprüft: Fehlerhafte Dateien gefunden', 'kulturhaus-events' ),
			);
		}

		// Import starten (mit Transaktions-ähnlicher Logik)
		try {
			// Reihenfolge: Venues → Organizers → Pricing Models → Events → Taxonomies → Attachments
			$this->import_posts( 'kh_venue' );
			$this->import_posts( 'kh_organizer' );
			$this->import_posts( 'kh_pricing_model' );
			$this->import_posts( 'kh_event' );
			$this->import_taxonomies();
			$this->import_attachments();

			$this->add_log( __( '✅ Restore erfolgreich abgeschlossen', 'kulturhaus-events' ) );

			return array(
				'success' => true,
				'message' => __( 'Backup erfolgreich wiederhergestellt', 'kulturhaus-events' ),
				'stats'   => $this->stats,
				'logs'    => $this->logs,
			);
		} catch ( \Exception $e ) {
			$this->add_log( '❌ Fehler: ' . $e->getMessage() );

			return array(
				'success' => false,
				'message' => $e->getMessage(),
				'logs'    => $this->logs,
			);
		} finally {
			$this->cleanup_temp_dir();
		}
	}

	/**
	 * ZIP extrahieren.
	 *
	 * @param string $file_path ZIP-Dateipfad.
	 * @return bool Erfolg.
	 */
	private function extract_backup( string $file_path ): bool {
		$zip = new \ZipArchive();

		if ( $zip->open( $file_path ) !== true ) {
			return false;
		}

		$zip->extractTo( $this->temp_dir );
		$zip->close();

		return true;
	}

	/**
	 * Manifest laden.
	 *
	 * @return array<string, mixed>|null Manifest oder null.
	 */
	private function load_manifest(): ?array {
		$manifest_path = $this->temp_dir . '/manifest.json';

		if ( ! file_exists( $manifest_path ) ) {
			return null;
		}

		$content = file_get_contents( $manifest_path );
		if ( ! $content ) {
			return null;
		}

		return json_decode( $content, true );
	}

	/**
	 * Checksums validieren.
	 *
	 * @param array<string, mixed> $manifest Manifest.
	 * @return bool Gültig.
	 */
	private function validate_checksums( array $manifest ): bool {
		if ( empty( $manifest['checksums'] ) ) {
			return true; // Keine Checksums vorhanden, überspringen
		}

		foreach ( $manifest['checksums'] as $file_path => $expected_hash ) {
			if ( 'manifest.json' === $file_path ) {
				continue; // Manifest selbst nicht prüfen
			}

			$full_path = $this->temp_dir . '/' . $file_path;

			if ( ! file_exists( $full_path ) ) {
				$this->add_log( "⚠️ Datei fehlt: $file_path" );
				continue;
			}

			$actual_hash = md5_file( $full_path );
			if ( $actual_hash !== $expected_hash ) {
				$this->add_log( "❌ Checksumm-Fehler: $file_path" );
				return false;
			}
		}

		$this->add_log( __( '✅ Checksums validiert', 'kulturhaus-events' ) );
		return true;
	}

	/**
	 * Posts importieren (MERGE-Strategie).
	 *
	 * @param string $post_type Der zu importierende Post Type.
	 */
	private function import_posts( string $post_type ): void {
		$json_file = $this->temp_dir . '/database/' . $post_type . '.json';

		if ( ! file_exists( $json_file ) ) {
			return;
		}

		$content = file_get_contents( $json_file );
		if ( ! $content ) {
			return;
		}

		$posts = json_decode( $content, true );
		if ( ! is_array( $posts ) ) {
			return;
		}

		foreach ( $posts as $post_data ) {
			// MERGE-Strategie: Duplikate ignorieren (nach Titel)
			$existing = get_page_by_title( $post_data['title'], OBJECT, $post_type );

			if ( $existing ) {
				$this->add_log( "⏭️  {$post_type}: '{$post_data['title']}' existiert bereits, übersprungen" );
				$this->stats[ $this->get_stat_key( $post_type, 'skipped' ) ]++;
				continue; // Nicht importieren
			}

			// Neue Post erstellen
			$new_post_id = wp_insert_post(
				array(
					'post_type'    => $post_type,
					'post_title'   => $post_data['title'],
					'post_content' => $post_data['content'],
					'post_excerpt' => $post_data['excerpt'] ?? '',
					'post_status'  => $post_data['status'] ?? 'publish',
					'post_author'  => $post_data['author'] ?? get_current_user_id(),
				)
			);

			if ( is_wp_error( $new_post_id ) ) {
				$this->add_log( "❌ {$post_type}: '{$post_data['title']}' konnte nicht importiert werden" );
				continue;
			}

			// ID-Mapping speichern
			$this->id_mapping[ $post_type ][ (int) $post_data['id'] ] = (int) $new_post_id;

			// Meta-Felder importieren
			if ( ! empty( $post_data['meta'] ) ) {
				foreach ( $post_data['meta'] as $key => $value ) {
					// Feld-Typ bestimmen für Sanitization
					$sanitized_value = $this->sanitize_meta_value( $key, $value );
					update_post_meta( $new_post_id, $key, $sanitized_value );
				}
			}

			// Featured Image
			if ( ! empty( $post_data['featured_image_id'] ) ) {
				$old_thumb_id = (int) $post_data['featured_image_id'];
				$new_thumb_id = $this->attachment_mapping[ $old_thumb_id ] ?? $old_thumb_id;
				set_post_thumbnail( $new_post_id, $new_thumb_id );
			}

			// Statistik
			$this->stats[ $this->get_stat_key( $post_type, 'imported' ) ]++;
			$this->add_log( "✅ {$post_type}: '{$post_data['title']}' importiert (neue ID: {$new_post_id})" );
		}
	}

	/**
	 * Taxonomien importieren.
	 */
	private function import_taxonomies(): void {
		// Kategorien
		$cat_file = $this->temp_dir . '/taxonomy/categories.json';
		if ( file_exists( $cat_file ) ) {
			$content = file_get_contents( $cat_file );
			$categories = json_decode( $content, true );

			if ( is_array( $categories ) ) {
				// Parents zuerst (parent_id = 0)
				foreach ( $categories as $cat ) {
					if ( empty( $cat['parent_id'] ) ) {
						$this->import_category( $cat );
					}
				}

				// Dann Children
				foreach ( $categories as $cat ) {
					if ( ! empty( $cat['parent_id'] ) ) {
						$this->import_category( $cat );
					}
				}
			}
		}

		// Tags
		$tag_file = $this->temp_dir . '/taxonomy/tags.json';
		if ( file_exists( $tag_file ) ) {
			$content = file_get_contents( $tag_file );
			$tags    = json_decode( $content, true );

			if ( is_array( $tags ) ) {
				foreach ( $tags as $tag ) {
					wp_insert_term(
						$tag['name'],
						'kh_event_tag',
						array(
							'slug'        => $tag['slug'],
							'description' => $tag['description'] ?? '',
						)
					);
				}
			}
		}
	}

	/**
	 * Einzelne Kategorie importieren (mit Parent-Handling).
	 *
	 * @param array<string, mixed> $cat Kategorie-Daten.
	 */
	private function import_category( array $cat ): void {
		$parent_id = 0;

		// Parent by slug lookup
		if ( ! empty( $cat['parent_slug'] ) ) {
			$parent_term = get_term_by( 'slug', $cat['parent_slug'], 'kh_event_cat' );
			if ( $parent_term ) {
				$parent_id = (int) $parent_term->term_id;
			}
		}

		$result = wp_insert_term(
			$cat['name'],
			'kh_event_cat',
			array(
				'slug'        => $cat['slug'],
				'description' => $cat['description'] ?? '',
				'parent'      => $parent_id,
			)
		);

		if ( ! is_wp_error( $result ) ) {
			$this->add_log( "✅ Kategorie: '{$cat['name']}' importiert" );
		}
	}

	/**
	 * Attachments importieren.
	 */
	private function import_attachments(): void {
		$manifest_file = $this->temp_dir . '/attachments/manifest.json';

		if ( ! file_exists( $manifest_file ) ) {
			return;
		}

		$content = file_get_contents( $manifest_file );
		$manifest = json_decode( $content, true );

		if ( ! is_array( $manifest ) ) {
			return;
		}

		foreach ( $manifest as $attachment_data ) {
			$old_id      = (int) $attachment_data['id'];
			$filename    = $attachment_data['filename'];
			$alt_text    = $attachment_data['alt_text'] ?? '';
			$source_path = $this->temp_dir . '/attachments/' . $old_id . '/' . $filename;

			if ( ! file_exists( $source_path ) ) {
				$this->add_log( "⚠️  Attachment fehlt: {$filename}" );
				continue;
			}

			// Datei hochladen
			$upload_result = wp_upload_bits( $filename, null, file_get_contents( $source_path ) );

			if ( isset( $upload_result['error'] ) && $upload_result['error'] ) {
				$this->add_log( "❌ Attachment-Upload fehlgeschlagen: {$filename}" );
				continue;
			}

			// MIME-Type bestimmen
			$file_info = wp_check_filetype( $filename );
			$mime_type = $file_info['type'] ?: 'application/octet-stream';

			// Attachment-Post erstellen
			$attachment_post = array(
				'post_mime_type' => $mime_type,
				'post_title'     => $attachment_data['title'],
				'post_content'   => '',
				'post_status'    => 'inherit',
			);

			$new_attachment_id = wp_insert_attachment( $attachment_post, $upload_result['file'] );

			if ( is_wp_error( $new_attachment_id ) ) {
				$this->add_log( "❌ Attachment-Post konnte nicht erstellt werden: {$filename}" );
				continue;
			}

			// ID-Mapping speichern
			$this->attachment_mapping[ $old_id ] = (int) $new_attachment_id;

			// Alt-Text setzen
			if ( $alt_text ) {
				update_post_meta( $new_attachment_id, '_wp_attachment_image_alt', $alt_text );
			}

			// Attachment-Metadata generieren
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$attach_data = wp_generate_attachment_metadata( $new_attachment_id, $upload_result['file'] );
			wp_update_attachment_metadata( $new_attachment_id, $attach_data );

			$this->stats['attachments_imported']++;
			$this->add_log( "✅ Attachment: {$filename} importiert (neue ID: {$new_attachment_id})" );
		}

		// Jetzt Event-Meta-Felder mit neuen Attachment-IDs updaten
		$this->update_event_attachment_references();
	}

	/**
	 * Event-Meta mit neuen Attachment-IDs updaten.
	 */
	private function update_event_attachment_references(): void {
		$events = get_posts(
			array(
				'post_type'   => 'kh_event',
				'numberposts' => -1,
			)
		);

		foreach ( $events as $event ) {
			// Featured Image (könnte ein altes Attachment sein)
			$thumb_id = get_post_thumbnail_id( $event->ID );
			if ( $thumb_id && isset( $this->attachment_mapping[ $thumb_id ] ) ) {
				set_post_thumbnail( $event->ID, $this->attachment_mapping[ $thumb_id ] );
			}

			// Gallery-IDs aktualisieren
			$gallery_ids = get_post_meta( $event->ID, '_kh_event_gallery', true );
			if ( $gallery_ids ) {
				$ids       = explode( ',', $gallery_ids );
				$new_ids   = array();
				$updated   = false;

				foreach ( $ids as $id ) {
					$id = (int) trim( $id );
					if ( isset( $this->attachment_mapping[ $id ] ) ) {
						$new_ids[] = $this->attachment_mapping[ $id ];
						$updated   = true;
					} else {
						$new_ids[] = $id;
					}
				}

				if ( $updated ) {
					update_post_meta( $event->ID, '_kh_event_gallery', implode( ',', $new_ids ) );
				}
			}
		}
	}

	/**
	 * Meta-Wert sanitizen basierend auf Feldtyp.
	 *
	 * @param string $key Der Meta-Key.
	 * @param mixed  $value Der Wert.
	 * @return mixed Sanitierter Wert.
	 */
	private function sanitize_meta_value( string $key, $value ) {
		// Spezielle Felder
		if ( '_kh_event_all_day' === $key || '_kh_event_featured' === $key || '_kh_event_cost_free' === $key ) {
			return (int) $value;
		}

		if ( '_kh_event_venue_id' === $key || '_kh_event_organizer_id' === $key || '_kh_event_pricing_model_id' === $key ) {
			// ID-Mapping anwenden
			$old_id     = (int) $value;
			$post_type  = match ( $key ) {
				'_kh_event_venue_id' => 'kh_venue',
				'_kh_event_organizer_id' => 'kh_organizer',
				'_kh_event_pricing_model_id' => 'kh_pricing_model',
			};

			return $this->id_mapping[ $post_type ][ $old_id ] ?? $old_id;
		}

		if ( '_kh_event_url' === $key ) {
			return esc_url_raw( $value );
		}

		if ( '_kh_event_prices' === $key && is_array( $value ) ) {
			return $value; // Array bleibt wie es ist
		}

		// Standard: Text-Sanitization
		return sanitize_text_field( $value );
	}

	/**
	 * Log-Meldung hinzufügen.
	 *
	 * @param string $message Die Meldung.
	 */
	private function add_log( string $message ): void {
		$this->logs[] = $message;
	}

	/**
	 * Stat-Key generieren.
	 *
	 * @param string $post_type Post Type.
	 * @param string $action 'imported' oder 'skipped'.
	 * @return string Der Stat-Key.
	 */
	private function get_stat_key( string $post_type, string $action ): string {
		$mapping = array(
			'kh_event'         => 'events',
			'kh_venue'         => 'venues',
			'kh_organizer'     => 'organizers',
			'kh_pricing_model' => 'pricing',
		);

		$type = $mapping[ $post_type ] ?? $post_type;
		return $type . '_' . $action;
	}

	/**
	 * Temp-Verzeichnis aufräumen.
	 */
	private function cleanup_temp_dir(): void {
		if ( is_dir( $this->temp_dir ) ) {
			$this->remove_directory( $this->temp_dir );
		}
	}

	/**
	 * Verzeichnis rekursiv löschen.
	 *
	 * @param string $dir Verzeichnispfad.
	 */
	private function remove_directory( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}

		$files = @scandir( $dir );
		if ( ! $files ) {
			return;
		}

		$files = array_diff( $files, array( '.', '..' ) );

		foreach ( $files as $file ) {
			$path = $dir . '/' . $file;
			if ( is_dir( $path ) ) {
				$this->remove_directory( $path );
			} else {
				@unlink( $path );
			}
		}

		@rmdir( $dir );
	}
}
