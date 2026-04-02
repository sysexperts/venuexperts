<?php
/**
 * AJAX Handler für Event-Suche
 *
 * @package KulturhausEvents
 * @since   1.20.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Ajax
 *
 * Verarbeitet AJAX-Anfragen für Event-Suche und Filter.
 */
class KH_Ajax {

	/**
	 * AJAX-Actions registrieren.
	 */
	public function register(): void {
		add_action( 'wp_ajax_kh_search_events', array( $this, 'search_events' ) );
		add_action( 'wp_ajax_nopriv_kh_search_events', array( $this, 'search_events' ) );
	}

	/**
	 * Event-Suche AJAX Handler.
	 */
	public function search_events(): void {
		// Nonce prüfen
		check_ajax_referer( 'kh_search_nonce', 'nonce' );

		$query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';
		$category = isset( $_POST['category'] ) ? sanitize_text_field( wp_unslash( $_POST['category'] ) ) : '';
		$month = isset( $_POST['month'] ) ? sanitize_text_field( wp_unslash( $_POST['month'] ) ) : '';
		$venue = isset( $_POST['venue'] ) ? absint( $_POST['venue'] ) : 0;

		// Query Args
		$args = array(
			'post_type'      => 'event',
			'posts_per_page' => 12,
			'post_status'    => 'publish',
			'meta_key'       => '_kh_event_start_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
		);

		// Suchbegriff
		if ( ! empty( $query ) ) {
			$args['s'] = $query;
		}

		// Meta Query für Datum
		$meta_query = array();

		// Nur kommende Events (Standard)
		if ( empty( $month ) ) {
			$meta_query[] = array(
				'key'     => '_kh_event_start_date',
				'value'   => current_time( 'Y-m-d' ),
				'compare' => '>=',
				'type'    => 'DATE',
			);
		} else {
			// Spezifischer Monat
			$year_month = explode( '-', $month );
			if ( count( $year_month ) === 2 ) {
				$year = (int) $year_month[0];
				$month_num = (int) $year_month[1];
				$start_date = date( 'Y-m-d', mktime( 0, 0, 0, $month_num, 1, $year ) );
				$end_date = date( 'Y-m-d', mktime( 0, 0, 0, $month_num + 1, 0, $year ) );
				
				$meta_query[] = array(
					'key'     => '_kh_event_start_date',
					'value'   => array( $start_date, $end_date ),
					'compare' => 'BETWEEN',
					'type'    => 'DATE',
				);
			}
		}

		// Venue Filter
		if ( $venue > 0 ) {
			$meta_query[] = array(
				'key'     => '_kh_event_venue_id',
				'value'   => $venue,
				'compare' => '=',
			);
		}

		if ( ! empty( $meta_query ) ) {
			$args['meta_query'] = $meta_query;
		}

		// Kategorie Filter
		if ( ! empty( $category ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => KH_Event_Category::TAXONOMY,
					'field'    => 'slug',
					'terms'    => $category,
				),
			);
		}

		// Query ausführen
		$events_query = new WP_Query( $args );

		ob_start();

		if ( $events_query->have_posts() ) :
			?>
			<div class="kh-search-results-header">
				<div class="kh-search-results-count">
					<strong><?php echo esc_html( $events_query->found_posts ); ?></strong> Veranstaltungen gefunden
				</div>
			</div>

			<div class="kh-search-results-grid">
				<?php
				while ( $events_query->have_posts() ) :
					$events_query->the_post();
					$event_id = get_the_ID();
					$start_date = get_post_meta( $event_id, '_kh_event_start_date', true );
					$venue_id = get_post_meta( $event_id, '_kh_event_venue_id', true );
					
					$venue_name = '';
					if ( $venue_id ) {
						$venue = get_post( (int) $venue_id );
						if ( $venue ) {
							$venue_name = $venue->post_title;
						}
					}
					?>
					<a href="<?php the_permalink(); ?>" class="kh-highlight-card">
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="kh-highlight-image">
								<?php the_post_thumbnail( 'medium' ); ?>
							</div>
						<?php endif; ?>
						
						<div class="kh-highlight-content">
							<div class="kh-highlight-date">
								<?php echo esc_html( wp_date( 'd.m.Y, H:i', strtotime( $start_date ) ) ); ?> Uhr
							</div>
							
							<h3 class="kh-highlight-title"><?php the_title(); ?></h3>
							
							<?php if ( $venue_name ) : ?>
								<div class="kh-highlight-venue">
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
										<circle cx="12" cy="10" r="3"></circle>
									</svg>
									<?php echo esc_html( $venue_name ); ?>
								</div>
							<?php endif; ?>
						</div>
					</a>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
			<?php
		else :
			?>
			<div class="kh-search-no-results">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<circle cx="11" cy="11" r="8"></circle>
					<path d="m21 21-4.35-4.35"></path>
				</svg>
				<h3>Keine Veranstaltungen gefunden</h3>
				<p>Versuchen Sie es mit anderen Suchkriterien.</p>
			</div>
			<?php
		endif;

		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}
}
