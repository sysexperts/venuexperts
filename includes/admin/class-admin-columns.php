<?php
/**
 * Custom Admin-Spalten für die Event-Übersicht.
 *
 * @package KulturhausEvents
 * @since   1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Admin_Columns
 *
 * Fügt benutzerdefinierte Spalten in der Event-Listenansicht hinzu
 * und ermöglicht Sortierung nach Datum.
 */
class KH_Admin_Columns {

	/**
	 * Hooks registrieren.
	 */
	public function register(): void {
		add_filter( 'manage_kh_event_posts_columns', array( $this, 'add_columns' ) );
		add_action( 'manage_kh_event_posts_custom_column', array( $this, 'render_columns' ), 10, 2 );
		add_filter( 'manage_edit-kh_event_sortable_columns', array( $this, 'sortable_columns' ) );
		add_action( 'pre_get_posts', array( $this, 'orderby_event_date' ) );
	}

	/**
	 * Spalten zur Event-Übersicht hinzufügen.
	 *
	 * @param array<string, string> $columns Bestehende Spalten.
	 * @return array<string, string> Aktualisierte Spalten.
	 */
	public function add_columns( array $columns ): array {
		$new_columns = array();

		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;

			// Nach dem Titel die Event-Spalten einfügen.
			if ( 'title' === $key ) {
				$new_columns['kh_event_date']   = __( 'Datum', 'kulturhaus-events' );
				$new_columns['kh_event_venue']   = __( 'Ort', 'kulturhaus-events' );
				$new_columns['kh_event_status']  = __( 'Status', 'kulturhaus-events' );
			}
		}

		return $new_columns;
	}

	/**
	 * Spalteninhalte rendern.
	 *
	 * @param string $column  Der Spaltenname.
	 * @param int    $post_id Die Post-ID.
	 */
	public function render_columns( string $column, int $post_id ): void {
		switch ( $column ) {
			case 'kh_event_date':
				$start = get_post_meta( $post_id, '_kh_event_start_date', true );
				$end   = get_post_meta( $post_id, '_kh_event_end_date', true );

				if ( $start ) {
					$date_format = get_option( 'kh_date_format', 'd.m.Y' );
					$time_format = get_option( 'kh_time_format', 'H:i' );

					echo esc_html( wp_date( $date_format, strtotime( $start ) ) );
					echo '<br><small>' . esc_html( wp_date( $time_format, strtotime( $start ) ) );

					if ( $end ) {
						echo ' – ' . esc_html( wp_date( $time_format, strtotime( $end ) ) );
					}

					echo '</small>';
				} else {
					echo '—';
				}
				break;

			case 'kh_event_venue':
				$venue_id = get_post_meta( $post_id, '_kh_event_venue_id', true );
				if ( $venue_id ) {
					$venue = get_post( (int) $venue_id );
					if ( $venue ) {
						printf(
							'<a href="%s">%s</a>',
							esc_url( get_edit_post_link( $venue->ID ) ),
							esc_html( $venue->post_title )
						);
					}
				} else {
					echo '—';
				}
				break;

			case 'kh_event_status':
				$status  = get_post_meta( $post_id, '_kh_event_status', true );
				$statuses = array(
					'scheduled' => array( __( 'Geplant', 'kulturhaus-events' ), '#2271b1' ),
					'cancelled' => array( __( 'Abgesagt', 'kulturhaus-events' ), '#d63638' ),
					'postponed' => array( __( 'Verschoben', 'kulturhaus-events' ), '#dba617' ),
					'soldout'   => array( __( 'Ausverkauft', 'kulturhaus-events' ), '#8c8f94' ),
				);

				if ( $status && isset( $statuses[ $status ] ) ) {
					printf(
						'<span style="color:%s;font-weight:600;">%s</span>',
						esc_attr( $statuses[ $status ][1] ),
						esc_html( $statuses[ $status ][0] )
					);
				} else {
					echo esc_html__( 'Geplant', 'kulturhaus-events' );
				}
				break;
		}
	}

	/**
	 * Sortierbare Spalten definieren.
	 *
	 * @param array<string, string> $columns Bestehende sortierbare Spalten.
	 * @return array<string, string> Aktualisierte sortierbare Spalten.
	 */
	public function sortable_columns( array $columns ): array {
		$columns['kh_event_date'] = 'kh_event_date';
		return $columns;
	}

	/**
	 * Sortierung nach Veranstaltungsdatum ermöglichen.
	 *
	 * @param \WP_Query $query Die aktuelle Query.
	 */
	public function orderby_event_date( \WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( 'kh_event' !== $query->get( 'post_type' ) ) {
			return;
		}

		$orderby = $query->get( 'orderby' );

		if ( 'kh_event_date' === $orderby || '' === $orderby ) {
			$query->set( 'meta_key', '_kh_event_start_date' );
			$query->set( 'orderby', 'meta_value' );

			if ( '' === $query->get( 'order' ) ) {
				$query->set( 'order', 'ASC' );
			}
		}
	}
}
