<?php
/**
 * Plugin-Aktivierung.
 *
 * @package KulturhausEvents
 * @since   1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Activator
 */
class KH_Activator {

	/**
	 * Aktionen bei Plugin-Aktivierung.
	 */
	public static function activate(): void {
		// Custom Post Types registrieren, damit Rewrite-Rules korrekt erstellt werden.
		$event     = new KH_Event();
		$venue     = new KH_Venue();
		$organizer = new KH_Organizer();

		$event->register();
		$venue->register();
		$organizer->register();

		$category = new KH_Event_Category();
		$tag      = new KH_Event_Tag();

		$category->register();
		$tag->register();

		// Rewrite-Rules aktualisieren.
		flush_rewrite_rules();

		// Plugin-Version in der Datenbank speichern.
		update_option( 'kh_events_version', KH_EVENTS_VERSION );

		// Standard-Optionen setzen.
		$defaults = array(
			'kh_events_per_page'    => 12,
			'kh_default_view'       => 'list',
			'kh_date_format'        => 'd.m.Y',
			'kh_time_format'        => 'H:i',
			'kh_map_provider'       => 'openstreetmap',
			'kh_enable_ical_export' => true,
		);

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( $key ) ) {
				add_option( $key, $value );
			}
		}
	}
}
