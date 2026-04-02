<?php
/**
 * Backup Manager für Kulturhaus Events.
 *
 * Erstellt ZIP-Backups aller Events, Venues, Organizers, Pricing Models
 * und Attachments mit vollständiger Datenintegrität.
 *
 * @package KulturhausEvents
 * @since   1.21.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Backup_Manager
 *
 * Verwaltet die Erstellung von ZIP-Backups.
 */
class KH_Backup_Manager {

	/**
	 * Temporärer Backup-Verzeichnis.
	 *
	 * @var string
	 */
	private string $temp_dir;

	/**
	 * ZIP-Archiv-Datei.
	 *
	 * @var \ZipArchive
	 */
	private \ZipArchive $zip;

	/**
	 * Checksums für Integrität.
	 *
	 * @var array<string, string>
	 */
	private array $checksums = array();

	/**
	 * ID-Mapping für Attachments (old_id => new_id).
	 *
	 * @var array<int, int>
	 */
	private array $attachment_mapping = array();

	/**
	 * Statistik über exportierte Daten.
	 *
	 * @var array<string, int>
	 */
	private array $counts = array(
		'events'          => 0,
		'venues'          => 0,
		'organizers'      => 0,
		'pricing_models'  => 0,
		'categories'      => 0,
		'tags'            => 0,
		'attachments'     => 0,
	);

	/**
	 * Backup erstellen und als ZIP herunterladen.
	 */
	public function create_backup(): void {
		// Sicherheitschecks
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unberechtigt', 'kulturhaus-events' ) );
		}

		// Temp-Verzeichnis vorbereiten
		$this->temp_dir = wp_upload_dir()['basedir'] . '/.kh-backup-temp';
		wp_mkdir_p( $this->temp_dir );

		// ZIP initialisieren
		$this->zip = new \ZipArchive();
		$zip_path  = $this->get_backup_filename();

		if ( $this->zip->open( $zip_path, \ZipArchive::CREATE ) !== true ) {
			wp_die( esc_html__( 'ZIP-Datei konnte nicht erstellt werden', 'kulturhaus-events' ) );
		}

		// Daten exportieren
		$this->export_posts( 'kh_event' );
		$this->export_posts( 'kh_venue' );
		$this->export_posts( 'kh_organizer' );
		$this->export_posts( 'kh_pricing_model' );
		$this->export_taxonomies();
		$this->export_attachments();

		// Manifest & Checksums
		$this->generate_manifest();

		// ZIP schließen
		$this->zip->close();

		// Download starten
		$this->download_backup( $zip_path );

		// Cleanup
		$this->cleanup_temp_dir();
	}

	/**
	 * Posts eines bestimmten Types exportieren.
	 *
	 * @param string $post_type Der Post Type (kh_event, kh_venue, etc.).
	 */
	private function export_posts( string $post_type ): void {
		global $wpdb;

		// Daten aus Datenbank abrufen
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'",
				$post_type
			)
		);

		if ( empty( $posts ) ) {
			return;
		}

		$data = array();

		foreach ( $posts as $post_obj ) {
			$post_id = (int) $post_obj->ID;
			$post    = get_post( $post_id );

			if ( ! $post ) {
				continue;
			}

			$exported_post = array(
				'id'      => $post_id,
				'title'   => $post->post_title,
				'content' => $post->post_content,
				'excerpt' => $post->post_excerpt,
				'status'  => $post->post_status,
				'author'  => $post->post_author,
				'date'    => $post->post_date,
				'slug'    => $post->post_name,
			);

			// Meta-Felder exportieren
			$meta_data = get_post_meta( $post_id );
			if ( ! empty( $meta_data ) ) {
				$exported_post['meta'] = array();
				foreach ( $meta_data as $key => $values ) {
					// Nur KH_-Prefixed Meta-Felder
					if ( strpos( $key, '_kh_' ) === 0 ) {
						// Meta-Wert (ist ein Array, nehme den ersten)
						$value = isset( $values[0] ) ? maybe_unserialize( $values[0] ) : '';
						$exported_post['meta'][ $key ] = $value;
					}
				}
			}

			// Featured Image
			$thumb_id = get_post_thumbnail_id( $post_id );
			if ( $thumb_id ) {
				$exported_post['featured_image_id'] = (int) $thumb_id;
			}

			// Taxonomien (nur für Events)
			if ( 'kh_event' === $post_type ) {
				$categories = wp_get_post_terms( $post_id, 'kh_event_cat', array( 'fields' => 'names' ) );
				if ( $categories && ! is_wp_error( $categories ) ) {
					$exported_post['categories'] = $categories;
				}

				$tags = wp_get_post_terms( $post_id, 'kh_event_tag', array( 'fields' => 'names' ) );
				if ( $tags && ! is_wp_error( $tags ) ) {
					$exported_post['tags'] = $tags;
				}
			}

			$data[] = $exported_post;
		}

		// Zu ZIP hinzufügen
		$filename = 'database/' . $post_type . '.json';
		$json     = wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
		$this->zip->addFromString( $filename, $json );
		$this->checksums[ $filename ] = md5( $json );

		// Statistik aktualisieren
		$count_key = $this->get_count_key_for_post_type( $post_type );
		$this->counts[ $count_key ] = count( $data );
	}

	/**
	 * Taxonomien exportieren (Kategorien & Tags).
	 */
	private function export_taxonomies(): void {
		global $wpdb;

		// Event-Kategorien (hierarchisch)
		$categories = get_terms(
			array(
				'taxonomy'   => 'kh_event_cat',
				'hide_empty' => false,
			)
		);

		if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
			$cat_data = array();
			foreach ( $categories as $term ) {
				$cat_data[] = array(
					'id'          => (int) $term->term_id,
					'name'        => $term->name,
					'slug'        => $term->slug,
					'description' => $term->description,
					'parent_id'   => (int) $term->parent,
					'parent_slug' => $term->parent ? get_term_field( 'slug', $term->parent, 'kh_event_cat' ) : null,
				);
			}

			$filename = 'taxonomy/categories.json';
			$json     = wp_json_encode( $cat_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
			$this->zip->addFromString( $filename, $json );
			$this->checksums[ $filename ] = md5( $json );
			$this->counts['categories']     = count( $cat_data );
		}

		// Event-Tags (flach)
		$tags = get_terms(
			array(
				'taxonomy'   => 'kh_event_tag',
				'hide_empty' => false,
			)
		);

		if ( ! is_wp_error( $tags ) && ! empty( $tags ) ) {
			$tag_data = array();
			foreach ( $tags as $term ) {
				$tag_data[] = array(
					'id'          => (int) $term->term_id,
					'name'        => $term->name,
					'slug'        => $term->slug,
					'description' => $term->description,
				);
			}

			$filename = 'taxonomy/tags.json';
			$json     = wp_json_encode( $tag_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
			$this->zip->addFromString( $filename, $json );
			$this->checksums[ $filename ] = md5( $json );
			$this->counts['tags']           = count( $tag_data );
		}
	}

	/**
	 * Attachments (Bilder) exportieren.
	 */
	private function export_attachments(): void {
		global $wpdb;

		// Attachment IDs sammeln (featured images von Events, Venues, Organizers)
		$attachment_ids = array();

		// Featured Images
		$featured_ids = $wpdb->get_col(
			"SELECT meta_value FROM {$wpdb->postmeta}
			WHERE meta_key = '_thumbnail_id'
			AND post_id IN (
				SELECT ID FROM {$wpdb->posts}
				WHERE post_type IN ('kh_event', 'kh_venue', 'kh_organizer')
				AND post_status = 'publish'
			)"
		);

		$attachment_ids = array_merge( $attachment_ids, array_map( 'intval', $featured_ids ) );

		// Gallery-Bilder aus Event-Meta
		$gallery_ids = $wpdb->get_col(
			"SELECT meta_value FROM {$wpdb->postmeta}
			WHERE meta_key = '_kh_event_gallery'
			AND post_id IN (
				SELECT ID FROM {$wpdb->posts}
				WHERE post_type = 'kh_event'
				AND post_status = 'publish'
			)"
		);

		foreach ( $gallery_ids as $gallery_csv ) {
			if ( $gallery_csv ) {
				$ids = explode( ',', $gallery_csv );
				$attachment_ids = array_merge( $attachment_ids, array_map( 'intval', $ids ) );
			}
		}

		// Duplikate entfernen
		$attachment_ids = array_unique( array_filter( $attachment_ids ) );

		if ( empty( $attachment_ids ) ) {
			return;
		}

		$attachment_manifest = array();

		foreach ( $attachment_ids as $attachment_id ) {
			$attachment = get_post( $attachment_id );

			if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
				continue;
			}

			// Dateipath abrufen
			$file_path = get_attached_file( $attachment_id );
			if ( ! $file_path || ! file_exists( $file_path ) ) {
				continue;
			}

			// Datei zu ZIP hinzufügen
			$file_info  = pathinfo( $file_path );
			$zip_path   = 'attachments/' . $attachment_id . '/' . $file_info['basename'];
			$file_hash  = md5_file( $file_path );

			$this->zip->addFile( $file_path, $zip_path );
			$this->checksums[ $zip_path ] = $file_hash;

			// Attachment-Meta speichern
			$attachment_meta = array(
				'id'        => (int) $attachment_id,
				'title'     => $attachment->post_title,
				'filename'  => $file_info['basename'],
				'alt_text'  => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
				'file_hash' => $file_hash,
			);

			$attachment_manifest[] = $attachment_meta;

			$this->counts['attachments']++;
		}

		// Attachment-Manifest speichern
		if ( ! empty( $attachment_manifest ) ) {
			$filename = 'attachments/manifest.json';
			$json     = wp_json_encode( $attachment_manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
			$this->zip->addFromString( $filename, $json );
			$this->checksums[ $filename ] = md5( $json );
		}
	}

	/**
	 * Manifest mit Checksums und Metadata erstellen.
	 */
	private function generate_manifest(): void {
		$manifest = array(
			'version'           => '1.0',
			'plugin_version'    => KH_EVENTS_VERSION,
			'wordpress_version' => get_bloginfo( 'version' ),
			'backup_date'       => wp_date( 'c' ),
			'backup_creator'    => wp_get_current_user()->user_login,
			'site_url'          => get_site_url(),
			'counts'            => $this->counts,
			'checksums'         => $this->checksums,
		);

		$filename = 'manifest.json';
		$json     = wp_json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
		$this->zip->addFromString( $filename, $json );
	}

	/**
	 * Backup-Dateiname generieren.
	 *
	 * @return string ZIP-Dateipfad.
	 */
	private function get_backup_filename(): string {
		$upload_dir = wp_upload_dir();
		$timestamp  = gmdate( 'Y-m-d-His' );
		return $upload_dir['basedir'] . '/kulturhaus-events-backup-' . $timestamp . '.zip';
	}

	/**
	 * Mapping für Count-Keys von Post Types.
	 *
	 * @param string $post_type Der Post Type.
	 * @return string Der Count-Key.
	 */
	private function get_count_key_for_post_type( string $post_type ): string {
		$mapping = array(
			'kh_event'          => 'events',
			'kh_venue'          => 'venues',
			'kh_organizer'      => 'organizers',
			'kh_pricing_model'  => 'pricing_models',
		);

		return $mapping[ $post_type ] ?? $post_type;
	}

	/**
	 * Backup herunterladen.
	 *
	 * @param string $file_path Pfad zur ZIP-Datei.
	 */
	private function download_backup( string $file_path ): void {
		if ( ! file_exists( $file_path ) ) {
			wp_die( esc_html__( 'Backup-Datei nicht gefunden', 'kulturhaus-events' ) );
		}

		// Header setzen
		header( 'Content-Type: application/zip' );
		header( 'Content-Disposition: attachment; filename="' . basename( $file_path ) . '"' );
		header( 'Content-Length: ' . filesize( $file_path ) );
		header( 'Cache-Control: no-cache, must-revalidate' );

		// Datei ausgeben
		readfile( $file_path );

		// Cleanup nach Download
		@unlink( $file_path );
		exit;
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

		$files = array_diff( scandir( $dir ), array( '.', '..' ) );

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
