<?php
/**
 * AJAX Handler für Program Filter
 *
 * @package KulturhausEvents
 * @since   1.20.2
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX Handler für Event Program Filter.
 */
class KH_Ajax_Program_Filter {

	/**
	 * AJAX-Action registrieren.
	 */
	public function register(): void {
		add_action( 'wp_ajax_kh_filter_program', array( $this, 'filter_program' ) );
		add_action( 'wp_ajax_nopriv_kh_filter_program', array( $this, 'filter_program' ) );
	}

	/**
	 * Program-Filter AJAX Handler.
	 */
	public function filter_program(): void {
		// Nonce prüfen
		check_ajax_referer( 'kh_program_nonce', 'nonce' );

		$search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
		$category = isset( $_POST['category'] ) ? sanitize_text_field( wp_unslash( $_POST['category'] ) ) : '';
		$timeframe = isset( $_POST['timeframe'] ) ? sanitize_text_field( wp_unslash( $_POST['timeframe'] ) ) : 'upcoming';
		$limit = isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 5;
		$show_all_link = isset( $_POST['show_all_link'] ) && $_POST['show_all_link'] === 'true';
		$all_link_url = isset( $_POST['all_link_url'] ) ? esc_url_raw( wp_unslash( $_POST['all_link_url'] ) ) : '/veranstaltungen/';

		// Query Args
		$args = array(
			'post_type'      => 'event',
			'posts_per_page' => $limit,
			'post_status'    => 'publish',
			'meta_key'       => '_kh_event_start_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
		);

		$meta_query = array();

		// Zeitraum-Filter
		$now = current_time( 'mysql' );
		
		switch ( $timeframe ) {
			case 'today':
				$start = date( 'Y-m-d 00:00:00' );
				$end = date( 'Y-m-d 23:59:59' );
				$meta_query[] = array(
					'key'     => '_kh_event_start_date',
					'value'   => array( $start, $end ),
					'compare' => 'BETWEEN',
					'type'    => 'DATETIME',
				);
				break;

			case 'this-week':
				$start = date( 'Y-m-d 00:00:00', strtotime( 'monday this week' ) );
				$end = date( 'Y-m-d 23:59:59', strtotime( 'sunday this week' ) );
				$meta_query[] = array(
					'key'     => '_kh_event_start_date',
					'value'   => array( $start, $end ),
					'compare' => 'BETWEEN',
					'type'    => 'DATETIME',
				);
				break;

			case 'this-month':
				$start = date( 'Y-m-01 00:00:00' );
				$end = date( 'Y-m-t 23:59:59' );
				$meta_query[] = array(
					'key'     => '_kh_event_start_date',
					'value'   => array( $start, $end ),
					'compare' => 'BETWEEN',
					'type'    => 'DATETIME',
				);
				break;

			case 'next-month':
				$next_month = strtotime( '+1 month' );
				$start = date( 'Y-m-01 00:00:00', $next_month );
				$end = date( 'Y-m-t 23:59:59', $next_month );
				$meta_query[] = array(
					'key'     => '_kh_event_start_date',
					'value'   => array( $start, $end ),
					'compare' => 'BETWEEN',
					'type'    => 'DATETIME',
				);
				break;

			case 'this-year':
				$start = date( 'Y-01-01 00:00:00' );
				$end = date( 'Y-12-31 23:59:59' );
				$meta_query[] = array(
					'key'     => '_kh_event_start_date',
					'value'   => array( $start, $end ),
					'compare' => 'BETWEEN',
					'type'    => 'DATETIME',
				);
				break;

			case 'upcoming':
			default:
				$meta_query[] = array(
					'key'     => '_kh_event_start_date',
					'value'   => $now,
					'compare' => '>=',
					'type'    => 'DATETIME',
				);
				break;
		}

		if ( ! empty( $meta_query ) ) {
			$args['meta_query'] = $meta_query;
		}

		// Kategorie-Filter
		if ( ! empty( $category ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => KH_Event_Category::TAXONOMY,
					'field'    => 'slug',
					'terms'    => $category,
				),
			);
		}

		// Suche
		if ( ! empty( $search ) ) {
			$args['s'] = $search;
		}

		// Query ausführen
		$query = new WP_Query( $args );

		ob_start();

		if ( $query->have_posts() ) :
			?>
			<div class="kh-program-list">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					$this->render_program_item( get_the_ID() );
				endwhile;
				wp_reset_postdata();
				?>
			</div>

			<?php if ( $show_all_link ) : ?>
				<div class="kh-program-all-link">
					<a href="<?php echo esc_url( $all_link_url ); ?>" class="kh-program-all-link__button">
						Alle Veranstaltungen ansehen
					</a>
				</div>
			<?php endif; ?>
			<?php
		else :
			?>
			<div class="kh-program-empty">
				<p>Keine Veranstaltungen für die gewählten Filter gefunden.</p>
			</div>
			<?php
		endif;

		$html = ob_get_clean();

		wp_send_json_success(
			array(
				'html'  => $html,
				'count' => $query->found_posts,
			)
		);
	}

	/**
	 * Rendert ein einzelnes Event-Item.
	 *
	 * @param int $event_id Event ID.
	 */
	private function render_program_item( int $event_id ): void {
		$start_date = get_post_meta( $event_id, '_kh_event_start_date', true );
		$price_regular = get_post_meta( $event_id, '_kh_event_price_regular', true );
		$cost_free = get_post_meta( $event_id, '_kh_event_cost_free', true );

		// Kategorien
		$categories = wp_get_post_terms( $event_id, KH_Event_Category::TAXONOMY, array( 'fields' => 'names' ) );
		$subtitle = ! empty( $categories ) ? implode( ', ', $categories ) : '';

		// Datum formatieren
		$date_formatted = wp_date( 'D, d.m.Y, H:i', strtotime( $start_date ) ) . ' Uhr';

		// Preis
		$price_display = '';
		if ( $cost_free ) {
			$price_display = '<span class="kh-program-item__free">Eintritt frei</span>';
		} elseif ( $price_regular ) {
			$price_display = '<span class="kh-program-item__price">€ ' . esc_html( $price_regular ) . '</span>';
		}

		$permalink = get_permalink( $event_id );
		?>
		<a href="<?php echo esc_url( $permalink ); ?>" class="kh-program-item">
			<div class="kh-program-item__image">
				<?php if ( has_post_thumbnail( $event_id ) ) : ?>
					<?php echo get_the_post_thumbnail( $event_id, 'medium' ); ?>
				<?php endif; ?>
			</div>
			
			<div class="kh-program-item__content">
				<div class="kh-program-item__meta">
					<span class="kh-program-item__date"><?php echo esc_html( $date_formatted ); ?></span>
					<?php echo wp_kses_post( $price_display ); ?>
				</div>

				<h3 class="kh-program-item__title"><?php echo esc_html( get_the_title( $event_id ) ); ?></h3>

				<?php if ( $subtitle ) : ?>
					<div class="kh-program-item__subtitle"><?php echo esc_html( strtoupper( $subtitle ) ); ?></div>
				<?php endif; ?>

				<div class="kh-program-item__description">
					<?php echo wp_trim_words( get_the_excerpt( $event_id ), 20 ); ?>
				</div>

				<span class="kh-program-item__button">
					Infos & Tickets
				</span>
			</div>
		</a>
		<?php
	}
}
