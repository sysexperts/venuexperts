<?php
/**
 * Query-Filter für Event-Archive.
 *
 * @package KulturhausEvents
 * @since   1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Query_Filters
 *
 * Modifiziert Event-Queries basierend auf Filter-Parametern.
 */
class KH_Query_Filters {

	/**
	 * Hooks registrieren.
	 */
	public function register(): void {
		add_action( 'pre_get_posts', array( $this, 'filter_events_archive' ) );
	}

	/**
	 * Event-Archive basierend auf GET-Parametern filtern.
	 *
	 * @param \WP_Query $query Die Query.
	 */
	public function filter_events_archive( \WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( ! is_post_type_archive( KH_Event::POST_TYPE ) && ! is_tax( array( KH_Event_Category::TAXONOMY, KH_Event_Tag::TAXONOMY ) ) ) {
			return;
		}

		$meta_query = $query->get( 'meta_query' ) ?: array();
		$tax_query  = $query->get( 'tax_query' ) ?: array();

		// Datumsfilter.
		if ( isset( $_GET['kh_date'] ) && ! empty( $_GET['kh_date'] ) ) {
			$date_filter = sanitize_text_field( wp_unslash( $_GET['kh_date'] ) );
			$date_range  = $this->get_date_range( $date_filter );

			if ( $date_range ) {
				$meta_query[] = array(
					'key'     => '_kh_event_start_date',
					'value'   => $date_range,
					'compare' => 'BETWEEN',
					'type'    => 'DATETIME',
				);
			}
		}

		// Kategorie-Filter.
		if ( isset( $_GET['kh_cat'] ) && ! empty( $_GET['kh_cat'] ) ) {
			$tax_query[] = array(
				'taxonomy' => KH_Event_Category::TAXONOMY,
				'field'    => 'slug',
				'terms'    => sanitize_text_field( wp_unslash( $_GET['kh_cat'] ) ),
			);
		}

		// Tag-Filter.
		if ( isset( $_GET['kh_tag'] ) && ! empty( $_GET['kh_tag'] ) ) {
			$tax_query[] = array(
				'taxonomy' => KH_Event_Tag::TAXONOMY,
				'field'    => 'slug',
				'terms'    => sanitize_text_field( wp_unslash( $_GET['kh_tag'] ) ),
			);
		}

		// Venue-Filter.
		if ( isset( $_GET['kh_venue'] ) && ! empty( $_GET['kh_venue'] ) ) {
			$meta_query[] = array(
				'key'   => '_kh_event_venue_id',
				'value' => absint( $_GET['kh_venue'] ),
			);
		}

		// Organizer-Filter.
		if ( isset( $_GET['kh_organizer'] ) && ! empty( $_GET['kh_organizer'] ) ) {
			$meta_query[] = array(
				'key'   => '_kh_event_organizer_id',
				'value' => absint( $_GET['kh_organizer'] ),
			);
		}

		if ( ! empty( $meta_query ) ) {
			$query->set( 'meta_query', $meta_query );
		}

		if ( ! empty( $tax_query ) ) {
			$query->set( 'tax_query', $tax_query );
		}

		// Standard: Nach Startdatum sortieren.
		if ( ! $query->get( 'orderby' ) ) {
			$query->set( 'meta_key', '_kh_event_start_date' );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'order', 'ASC' );
		}
	}

	/**
	 * Datumsbereich für Filter ermitteln.
	 *
	 * @param string $filter Der Filter-Wert.
	 * @return array<string>|null Array mit Start- und Enddatum oder null.
	 */
	private function get_date_range( string $filter ): ?array {
		$now = current_time( 'timestamp' );

		switch ( $filter ) {
			case 'today':
				$start = gmdate( 'Y-m-d 00:00:00', $now );
				$end   = gmdate( 'Y-m-d 23:59:59', $now );
				break;

			case 'tomorrow':
				$tomorrow = strtotime( '+1 day', $now );
				$start    = gmdate( 'Y-m-d 00:00:00', $tomorrow );
				$end      = gmdate( 'Y-m-d 23:59:59', $tomorrow );
				break;

			case 'week':
				$start = gmdate( 'Y-m-d 00:00:00', strtotime( 'monday this week', $now ) );
				$end   = gmdate( 'Y-m-d 23:59:59', strtotime( 'sunday this week', $now ) );
				break;

			case 'month':
				$start = gmdate( 'Y-m-01 00:00:00', $now );
				$end   = gmdate( 'Y-m-t 23:59:59', $now );
				break;

			case 'next_month':
				$next_month = strtotime( 'first day of next month', $now );
				$start      = gmdate( 'Y-m-01 00:00:00', $next_month );
				$end        = gmdate( 'Y-m-t 23:59:59', $next_month );
				break;

			default:
				return null;
		}

		return array( $start, $end );
	}
}
