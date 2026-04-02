<?php
/**
 * Backup Manager for Kulturhaus Events
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
 * Verwaltet das Erstellen und Wiederherstellen von Backups.
 */
class KH_Backup_Manager {

	/**
	 * Backup-Typen.
	 */
	const BACKUP_EVENTS = 'events';
	const BACKUP_SETTINGS = 'settings';
	const BACKUP_FULL = 'full';

	/**
	 * Erstellt ein Backup.
	 */
	public static function create_backup( string $type = self::BACKUP_FULL ): array {
		$settings = KH_Backup_Settings::get_settings();
		
		if ( ! $settings['enabled'] ) {
			return array(
				'success' => false,
				'message' => __( 'Backup-Funktion ist nicht aktiviert.', 'kulturhaus-events' ),
			);
		}

		$backup_data = array();
		$timestamp = current_time( 'Y-m-d_H-i-s' );
		$filename = "kh_events_backup_{$type}_{$timestamp}.json";

		switch ( $type ) {
			case self::BACKUP_EVENTS:
				$backup_data = self::backup_events();
				break;
			case self::BACKUP_SETTINGS:
				$backup_data = self::backup_settings();
				break;
			case self::BACKUP_FULL:
			default:
				$backup_data = array_merge(
					self::backup_events(),
					self::backup_settings()
				);
				break;
		}

		$backup_data['meta'] = array(
			'version' => KH_EVENTS_VERSION,
			'type' => $type,
			'created' => current_time( 'mysql' ),
			'wordpress_version' => get_bloginfo( 'version' ),
			'php_version' => PHP_VERSION,
		);

		$backup_dir = self::get_backup_directory( $settings );
		$backup_path = $backup_dir . '/' . $filename;

		// Backup-Datei erstellen
		$result = file_put_contents( $backup_path, wp_json_encode( $backup_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );

		if ( $result === false ) {
			return array(
				'success' => false,
				'message' => __( 'Backup-Datei konnte nicht erstellt werden.', 'kulturhaus-events' ),
			);
		}

		// Alte Backups aufräumen
		self::cleanup_old_backups( $backup_dir, $settings['keep_backups'] );

		// Medien-Dateien hinzufügen, wenn aktiviert
		if ( $settings['include_media'] ) {
			self::backup_media_files( $backup_dir, $timestamp );
		}

		// Benachrichtigung senden
		if ( ! empty( $settings['notification_email'] ) ) {
			self::send_backup_notification( $settings['notification_email'], $filename, $type );
		}

		// Letztes Backup aktualisieren
		$updated_settings = $settings;
		$updated_settings['last_backup'] = current_time( 'mysql' );
		KH_Backup_Settings::save_settings( $updated_settings );

		return array(
			'success' => true,
			'message' => sprintf(
				__( 'Backup "%s" wurde erfolgreich erstellt.', 'kulturhaus-events' ),
				$filename
			),
			'filename' => $filename,
		);
	}

	/**
	 * Backup aller Events erstellen.
	 */
	private static function backup_events(): array {
		$events = array();
		$query = new WP_Query( array(
			'post_type' => 'kh_event',
			'post_status' => 'any',
			'posts_per_page' => -1,
		) );

		while ( $query->have_posts() ) {
			$query->the_post();
			$event_id = get_the_ID();
			
			$event_data = array(
				'ID' => $event_id,
				'post_title' => get_the_title(),
				'post_content' => get_the_content(),
				'post_excerpt' => get_the_excerpt(),
				'post_status' => get_post_status(),
				'post_date' => get_the_date( 'Y-m-d H:i:s' ),
				'post_modified' => get_the_modified_date( 'Y-m-d H:i:s' ),
				'meta' => get_post_meta( $event_id ),
				'terms' => array(
					'kh_event_category' => wp_get_post_terms( $event_id, 'kh_event_category', array( 'fields' => 'names' ) ),
					'kh_event_tag' => wp_get_post_terms( $event_id, 'kh_event_tag', array( 'fields' => 'names' ) ),
				),
				'featured_image' => get_post_thumbnail_id( $event_id ),
			);

			$events[] = $event_data;
		}

		wp_reset_postdata();

		return array( 'events' => $events );
	}

	/**
	 * Backup aller Einstellungen erstellen.
	 */
	private static function backup_settings(): array {
		return array(
			'settings' => array(
				'kh_events_design_settings' => get_option( 'kh_events_design_settings', array() ),
				'kh_events_backup_settings' => get_option( 'kh_events_backup_settings', array() ),
				'kh_events_widget_settings' => get_option( 'kh_events_widget_settings', array() ),
			),
			'options' => array(
				'kh_events_version' => KH_EVENTS_VERSION,
			),
		);
	}

	/**
	 * Backup-Verzeichnis abrufen.
	 */
	private static function get_backup_directory( array $settings ): string {
		if ( $settings['backup_location'] === 'custom' && ! empty( $settings['custom_path'] ) ) {
			$backup_dir = $settings['custom_path'];
		} else {
			$backup_dir = WP_CONTENT_DIR . '/backups';
		}

		if ( ! file_exists( $backup_dir ) ) {
			wp_mkdir_p( $backup_dir );
		}

		return $backup_dir;
	}

	/**
	 * Alte Backups aufräumen.
	 */
	private static function cleanup_old_backups( string $backup_dir, int $keep_count ): void {
		$backup_files = glob( $backup_dir . '/kh_events_backup_*.json' );
		
		if ( ! $backup_files || count( $backup_files ) <= $keep_count ) {
			return;
		}

		// Sortieren nach Erstellungsdatum
		usort( $backup_files, function( $a, $b ) {
			return filemtime( $b ) - filemtime( $a );
		} );

		// Älteste Backups löschen
		$to_delete = array_slice( $backup_files, $keep_count );
		foreach ( $to_delete as $file ) {
			unlink( $file );
		}
	}

	/**
	 * Medien-Dateien backupen.
	 */
	private static function backup_media_files( string $backup_dir, string $timestamp ): void {
		$media_dir = $backup_dir . "/media_{$timestamp}";
		wp_mkdir_p( $media_dir );

		$events_query = new WP_Query( array(
			'post_type' => 'kh_event',
			'post_status' => 'any',
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => '_thumbnail_id',
					'compare' => 'EXISTS',
				),
			),
		) );

		while ( $events_query->have_posts() ) {
			$events_query->the_post();
			$thumbnail_id = get_post_thumbnail_id();
			
			if ( $thumbnail_id ) {
				$image_path = get_attached_file( $thumbnail_id );
				if ( $image_path && file_exists( $image_path ) ) {
					$filename = basename( $image_path );
					copy( $image_path, $media_dir . '/' . $filename );
				}
			}
		}

		wp_reset_postdata();
	}

	/**
	 * Backup-Benachrichtigung senden.
	 */
	private static function send_backup_notification( string $email, string $filename, string $type ): void {
		$subject = sprintf(
			__( '[Kulturhaus Events] Backup erstellt: %s', 'kulturhaus-events' ),
			$filename
		);

		$message = sprintf(
			__( "Ein neues Backup wurde erstellt:\n\nDatei: %s\nTyp: %s\nDatum: %s\n\nDas Backup wurde erfolgreich im Backup-Verzeichnis gespeichert.", 'kulturhaus-events' ),
			$filename,
			$type,
			current_time( 'd.m.Y H:i' )
		);

		wp_mail( $email, $subject, $message );
	}

	/**
	 * AJAX-Handler für manuelles Backup.
	 */
	public static function handle_manual_backup(): void {
		// Debug: Log AJAX-Request
		error_log('KH Backup: AJAX-Request erhalten');
		
		check_ajax_referer( 'kh_backup_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			error_log('KH Backup: Keine Berechtigung');
			wp_send_json_error( array(
				'message' => __( 'Keine Berechtigung.', 'kulturhaus-events' ),
			) );
		}

		error_log('KH Backup: Erstelle Backup...');
		$result = self::create_backup( self::BACKUP_FULL );
		error_log('KH Backup: Ergebnis: ' . print_r($result, true));
		
		wp_send_json( $result );
	}

	/**
	 * Cron-Job für automatische Backups.
	 */
	public static function schedule_automatic_backup(): void {
		$settings = KH_Backup_Settings::get_settings();

		if ( ! $settings['enabled'] || ! $settings['auto_backup'] ) {
			return;
		}

		$interval = $settings['backup_interval'];
		$hook = 'kh_events_automatic_backup';

		// Cron-Job registrieren
		if ( ! wp_next_scheduled( $hook ) ) {
			$schedule_time = strtotime( 'today 2:00 AM' ); // 2 Uhr nachts
			
			switch ( $interval ) {
				case 'daily':
					wp_schedule_event( $schedule_time, 'daily', $hook );
					break;
				case 'weekly':
					wp_schedule_event( $schedule_time, 'weekly', $hook );
					break;
				case 'monthly':
					wp_schedule_event( $schedule_time, 'monthly', $hook );
					break;
			}
		}
	}

	/**
	 * Automatisches Backup ausführen.
	 */
	public static function execute_automatic_backup(): void {
		$settings = KH_Backup_Settings::get_settings();

		if ( ! $settings['enabled'] || ! $settings['auto_backup'] ) {
			return;
		}

		self::create_backup( self::BACKUP_FULL );
	}

	/**
	 * Hooks registrieren.
	 */
	public static function register(): void {
		add_action( 'wp_ajax_kh_create_manual_backup', array( __CLASS__, 'handle_manual_backup' ) );
		add_action( 'kh_events_automatic_backup', array( __CLASS__, 'execute_automatic_backup' ) );
		add_action( 'admin_init', array( __CLASS__, 'schedule_automatic_backup' ) );
	}
}
