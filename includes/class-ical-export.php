<?php
/**
 * iCal/ICS Export-Funktionalität.
 *
 * @package KulturhausEvents
 * @since   1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_ICal_Export
 *
 * Generiert iCal/ICS-Dateien für Events.
 */
class KH_ICal_Export {

	/**
	 * Hooks registrieren.
	 */
	public function register(): void {
		add_action( 'template_redirect', array( $this, 'handle_ical_download' ) );
		add_filter( 'the_content', array( $this, 'add_ical_button' ), 20 );
	}

	/**
	 * iCal-Download verarbeiten.
	 */
	public function handle_ical_download(): void {
		if ( ! isset( $_GET['kh_ical'] ) || ! isset( $_GET['event_id'] ) ) {
			return;
		}

		$event_id = absint( $_GET['event_id'] );

		if ( ! $event_id || get_post_type( $event_id ) !== KH_Event::POST_TYPE ) {
			return;
		}

		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'kh_ical_' . $event_id ) ) {
			wp_die( esc_html__( 'Sicherheitsüberprüfung fehlgeschlagen.', 'kulturhaus-events' ) );
		}

		$this->generate_ical( $event_id );
	}

	/**
	 * iCal-Datei generieren und zum Download anbieten.
	 *
	 * @param int $event_id Die Event-ID.
	 */
	private function generate_ical( int $event_id ): void {
		$event = get_post( $event_id );

		if ( ! $event ) {
			return;
		}

		$start_date = get_post_meta( $event_id, '_kh_event_start_date', true );
		$end_date   = get_post_meta( $event_id, '_kh_event_end_date', true );
		$all_day    = get_post_meta( $event_id, '_kh_event_all_day', true );
		$venue_id   = get_post_meta( $event_id, '_kh_event_venue_id', true );
		$url        = get_post_meta( $event_id, '_kh_event_url', true );

		if ( ! $start_date ) {
			return;
		}

		$location = '';
		if ( $venue_id ) {
			$venue   = get_post( (int) $venue_id );
			$address = get_post_meta( $venue_id, '_kh_venue_address', true );
			$zip     = get_post_meta( $venue_id, '_kh_venue_zip', true );
			$city    = get_post_meta( $venue_id, '_kh_venue_city', true );

			$location = $venue ? $venue->post_title : '';
			if ( $address || $city ) {
				$location .= ', ' . trim( $address . ' ' . $zip . ' ' . $city );
			}
		}

		$dtstart = $this->format_ical_date( $start_date, (bool) $all_day );
		$dtend   = $end_date ? $this->format_ical_date( $end_date, (bool) $all_day ) : $dtstart;

		$description = wp_strip_all_tags( $event->post_content );
		$description = $this->escape_ical_text( $description );

		$uid       = 'event-' . $event_id . '@' . wp_parse_url( home_url(), PHP_URL_HOST );
		$dtstamp   = gmdate( 'Ymd\THis\Z' );
		$summary   = $this->escape_ical_text( $event->post_title );
		$location  = $this->escape_ical_text( $location );
		$url       = $url ? $this->escape_ical_text( $url ) : get_permalink( $event_id );

		$ical  = "BEGIN:VCALENDAR\r\n";
		$ical .= "VERSION:2.0\r\n";
		$ical .= "PRODID:-//Kulturhaus Events//WordPress Plugin//DE\r\n";
		$ical .= "CALSCALE:GREGORIAN\r\n";
		$ical .= "METHOD:PUBLISH\r\n";
		$ical .= "BEGIN:VEVENT\r\n";
		$ical .= "UID:{$uid}\r\n";
		$ical .= "DTSTAMP:{$dtstamp}\r\n";
		$ical .= "DTSTART{$dtstart}\r\n";
		$ical .= "DTEND{$dtend}\r\n";
		$ical .= "SUMMARY:{$summary}\r\n";

		if ( $description ) {
			$ical .= "DESCRIPTION:{$description}\r\n";
		}

		if ( $location ) {
			$ical .= "LOCATION:{$location}\r\n";
		}

		if ( $url ) {
			$ical .= "URL:{$url}\r\n";
		}

		$ical .= "STATUS:CONFIRMED\r\n";
		$ical .= "SEQUENCE:0\r\n";
		$ical .= "END:VEVENT\r\n";
		$ical .= "END:VCALENDAR\r\n";

		$filename = sanitize_file_name( $event->post_name ) . '.ics';

		header( 'Content-Type: text/calendar; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );

		echo $ical;
		exit;
	}

	/**
	 * Datum für iCal formatieren.
	 *
	 * @param string $date    Das Datum.
	 * @param bool   $all_day Ganztägiges Event.
	 * @return string Formatiertes Datum.
	 */
	private function format_ical_date( string $date, bool $all_day ): string {
		$timestamp = strtotime( $date );

		if ( $all_day ) {
			return ';VALUE=DATE:' . gmdate( 'Ymd', $timestamp );
		}

		return ':' . gmdate( 'Ymd\THis\Z', $timestamp );
	}

	/**
	 * Text für iCal escapen.
	 *
	 * @param string $text Der Text.
	 * @return string Escapeter Text.
	 */
	private function escape_ical_text( string $text ): string {
		$text = str_replace( array( "\r\n", "\n", "\r" ), ' ', $text );
		$text = str_replace( array( '\\', ',', ';' ), array( '\\\\', '\\,', '\\;' ), $text );
		$text = substr( $text, 0, 1000 );

		return $text;
	}

	/**
	 * iCal-Download-Button zum Event-Content hinzufügen.
	 * 
	 * Hinweis: Der Button wird jetzt direkt im Template angezeigt.
	 * Diese Methode bleibt für Rückwärtskompatibilität erhalten.
	 *
	 * @param string $content Der Content.
	 * @return string Unmodifizierter Content.
	 */
	public function add_ical_button( string $content ): string {
		return $content;
	}

	/**
	 * iCal-Download-Link generieren (für Templates).
	 *
	 * @param int $event_id Die Event-ID.
	 * @return string Die Download-URL.
	 */
	public static function get_ical_url( int $event_id ): string {
		return add_query_arg(
			array(
				'kh_ical'  => '1',
				'event_id' => $event_id,
				'_wpnonce' => wp_create_nonce( 'kh_ical_' . $event_id ),
			),
			get_permalink( $event_id )
		);
	}
}
