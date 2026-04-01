<?php
/**
 * Event Calendar
 *
 * @package KulturhausEvents
 * @since   1.12.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Calendar
 *
 * Generiert eine Kalender-Ansicht für Events.
 */
class KH_Calendar {

	/**
	 * Shortcode registrieren.
	 */
	public function register(): void {
		add_shortcode( 'kh_event_calendar', array( $this, 'render_calendar' ) );
	}

	/**
	 * Shortcode: [kh_event_calendar]
	 *
	 * Zeigt einen Monatskalender mit Events.
	 *
	 * Attribute:
	 * - month: Monat (1-12, Standard: aktueller Monat)
	 * - year: Jahr (Standard: aktuelles Jahr)
	 * - category: Kategorie-Slug (optional)
	 *
	 * @param array<string, mixed> $atts Shortcode-Attribute.
	 * @return string HTML-Ausgabe.
	 */
	public function render_calendar( $atts ): string {
		$atts = shortcode_atts(
			array(
				'month'    => (int) current_time( 'n' ),
				'year'     => (int) current_time( 'Y' ),
				'category' => '',
			),
			$atts,
			'kh_event_calendar'
		);

		$month = absint( $atts['month'] );
		$year  = absint( $atts['year'] );

		// Validierung
		if ( $month < 1 || $month > 12 ) {
			$month = (int) current_time( 'n' );
		}
		if ( $year < 2000 || $year > 2100 ) {
			$year = (int) current_time( 'Y' );
		}

		// Events für diesen Monat holen
		$events = $this->get_events_for_month( $month, $year, $atts['category'] );

		// Kalender rendern
		ob_start();
		$this->render_calendar_html( $month, $year, $events, $atts['category'] );
		return ob_get_clean();
	}

	/**
	 * Events für einen bestimmten Monat abrufen.
	 *
	 * @param int    $month    Monat (1-12).
	 * @param int    $year     Jahr.
	 * @param string $category Kategorie-Slug (optional).
	 * @return array<int, array<int, WP_Post>> Events gruppiert nach Tag.
	 */
	private function get_events_for_month( int $month, int $year, string $category = '' ): array {
		$start_date = sprintf( '%04d-%02d-01 00:00:00', $year, $month );
		$end_date   = date( 'Y-m-t 23:59:59', strtotime( $start_date ) );

		$args = array(
			'post_type'      => KH_Event::POST_TYPE,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => '_kh_event_start_date',
					'value'   => array( $start_date, $end_date ),
					'compare' => 'BETWEEN',
					'type'    => 'DATETIME',
				),
				array(
					'key'     => '_kh_event_end_date',
					'value'   => array( $start_date, $end_date ),
					'compare' => 'BETWEEN',
					'type'    => 'DATETIME',
				),
			),
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_key'       => '_kh_event_start_date',
		);

		if ( ! empty( $category ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => KH_Event_Category::TAXONOMY,
					'field'    => 'slug',
					'terms'    => sanitize_text_field( $category ),
				),
			);
		}

		$query = new WP_Query( $args );

		// Events nach Tag gruppieren
		$events_by_day = array();
		while ( $query->have_posts() ) {
			$query->the_post();
			$start_date = get_post_meta( get_the_ID(), '_kh_event_start_date', true );
			if ( $start_date ) {
				$day = (int) date( 'j', strtotime( $start_date ) );
				if ( ! isset( $events_by_day[ $day ] ) ) {
					$events_by_day[ $day ] = array();
				}
				$events_by_day[ $day ][] = get_post();
			}
		}
		wp_reset_postdata();

		return $events_by_day;
	}

	/**
	 * Kalender HTML rendern.
	 *
	 * @param int                                $month    Monat.
	 * @param int                                $year     Jahr.
	 * @param array<int, array<int, WP_Post>>   $events   Events gruppiert nach Tag.
	 * @param string                             $category Kategorie-Slug.
	 */
	private function render_calendar_html( int $month, int $year, array $events, string $category ): void {
		$first_day = mktime( 0, 0, 0, $month, 1, $year );
		$days_in_month = (int) date( 't', $first_day );
		$day_of_week = (int) date( 'N', $first_day ); // 1 (Montag) bis 7 (Sonntag)
		
		$month_name = date_i18n( 'F Y', $first_day );
		
		$prev_month = $month - 1;
		$prev_year = $year;
		if ( $prev_month < 1 ) {
			$prev_month = 12;
			$prev_year--;
		}
		
		$next_month = $month + 1;
		$next_year = $year;
		if ( $next_month > 12 ) {
			$next_month = 1;
			$next_year++;
		}
		
		$category_param = ! empty( $category ) ? '&category=' . esc_attr( $category ) : '';
		?>
		<div class="kh-calendar">
			<div class="kh-calendar-header">
				<button class="kh-calendar-nav kh-calendar-nav--prev" 
				        data-month="<?php echo esc_attr( $prev_month ); ?>" 
				        data-year="<?php echo esc_attr( $prev_year ); ?>"
				        data-category="<?php echo esc_attr( $category ); ?>">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
						<path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
				
				<h2 class="kh-calendar-title"><?php echo esc_html( $month_name ); ?></h2>
				
				<button class="kh-calendar-nav kh-calendar-nav--next" 
				        data-month="<?php echo esc_attr( $next_month ); ?>" 
				        data-year="<?php echo esc_attr( $next_year ); ?>"
				        data-category="<?php echo esc_attr( $category ); ?>">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
						<path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
			</div>

			<div class="kh-calendar-grid">
				<!-- Wochentage -->
				<div class="kh-calendar-weekday">Mo</div>
				<div class="kh-calendar-weekday">Di</div>
				<div class="kh-calendar-weekday">Mi</div>
				<div class="kh-calendar-weekday">Do</div>
				<div class="kh-calendar-weekday">Fr</div>
				<div class="kh-calendar-weekday">Sa</div>
				<div class="kh-calendar-weekday">So</div>

				<?php
				// Leere Zellen vor dem ersten Tag
				for ( $i = 1; $i < $day_of_week; $i++ ) {
					echo '<div class="kh-calendar-day kh-calendar-day--empty"></div>';
				}

				// Tage des Monats
				for ( $day = 1; $day <= $days_in_month; $day++ ) {
					$has_events = isset( $events[ $day ] );
					$is_today = ( $day === (int) current_time( 'j' ) && $month === (int) current_time( 'n' ) && $year === (int) current_time( 'Y' ) );
					
					$classes = array( 'kh-calendar-day' );
					if ( $has_events ) {
						$classes[] = 'kh-calendar-day--has-events';
					}
					if ( $is_today ) {
						$classes[] = 'kh-calendar-day--today';
					}
					?>
					<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
						<span class="kh-calendar-day__number"><?php echo esc_html( $day ); ?></span>
						<?php if ( $has_events ) : ?>
							<div class="kh-calendar-day__events">
								<?php foreach ( $events[ $day ] as $event ) : ?>
									<a href="<?php echo esc_url( get_permalink( $event->ID ) ); ?>" 
									   class="kh-calendar-event"
									   title="<?php echo esc_attr( get_the_title( $event->ID ) ); ?>">
										<span class="kh-calendar-event__dot"></span>
										<span class="kh-calendar-event__title"><?php echo esc_html( get_the_title( $event->ID ) ); ?></span>
									</a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
					<?php
				}

				// Leere Zellen nach dem letzten Tag
				$remaining_cells = 7 - ( ( $day_of_week + $days_in_month - 1 ) % 7 );
				if ( $remaining_cells < 7 ) {
					for ( $i = 0; $i < $remaining_cells; $i++ ) {
						echo '<div class="kh-calendar-day kh-calendar-day--empty"></div>';
					}
				}
				?>
			</div>
		</div>
		<?php
	}
}
