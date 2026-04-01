<?php
/**
 * Shortcode-Handler für Event-Listen.
 *
 * @package KulturhausEvents
 * @since   1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Shortcodes
 *
 * Registriert und verarbeitet Shortcodes für die Ausgabe von Events.
 */
class KH_Shortcodes {

	/**
	 * Shortcodes registrieren.
	 */
	public function register(): void {
		add_shortcode( 'kh_events', array( $this, 'events_list' ) );
		add_shortcode( 'kh_upcoming_events', array( $this, 'upcoming_events' ) );
		add_shortcode( 'kh_event_highlights', array( $this, 'event_highlights' ) );
		add_shortcode( 'kh_event_program', array( $this, 'event_program' ) );
	}

	/**
	 * Shortcode: [kh_events]
	 *
	 * Zeigt eine Liste von Veranstaltungen an.
	 *
	 * Attribute:
	 * - limit: Anzahl der Events (Standard: 10)
	 * - category: Kategorie-Slug
	 * - tag: Tag-Slug
	 * - venue: Venue-ID
	 * - organizer: Organizer-ID
	 * - order: ASC oder DESC (Standard: ASC)
	 * - show_past: true/false - Vergangene Events anzeigen (Standard: false)
	 *
	 * @param array<string, mixed> $atts Shortcode-Attribute.
	 * @return string HTML-Ausgabe.
	 */
	public function events_list( $atts ): string {
		$atts = shortcode_atts(
			array(
				'limit'      => 10,
				'category'   => '',
				'tag'        => '',
				'venue'      => '',
				'organizer'  => '',
				'order'      => 'ASC',
				'show_past'  => false,
			),
			$atts,
			'kh_events'
		);

		$args = array(
			'post_type'      => KH_Event::POST_TYPE,
			'posts_per_page' => (int) $atts['limit'],
			'post_status'    => 'publish',
			'meta_key'       => '_kh_event_start_date',
			'orderby'        => 'meta_value',
			'order'          => strtoupper( $atts['order'] ) === 'DESC' ? 'DESC' : 'ASC',
		);

		// Vergangene Events ausschließen (Standard).
		if ( ! $atts['show_past'] ) {
			$args['meta_query'] = array(
				array(
					'key'     => '_kh_event_start_date',
					'value'   => current_time( 'mysql' ),
					'compare' => '>=',
					'type'    => 'DATETIME',
				),
			);
		}

		// Kategorie-Filter.
		if ( ! empty( $atts['category'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => KH_Event_Category::TAXONOMY,
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $atts['category'] ),
			);
		}

		// Tag-Filter.
		if ( ! empty( $atts['tag'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => KH_Event_Tag::TAXONOMY,
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $atts['tag'] ),
			);
		}

		// Venue-Filter.
		if ( ! empty( $atts['venue'] ) ) {
			$args['meta_query'][] = array(
				'key'   => '_kh_event_venue_id',
				'value' => absint( $atts['venue'] ),
			);
		}

		// Organizer-Filter.
		if ( ! empty( $atts['organizer'] ) ) {
			$args['meta_query'][] = array(
				'key'   => '_kh_event_organizer_id',
				'value' => absint( $atts['organizer'] ),
			);
		}

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return '<p class="kh-events-empty">' . esc_html__( 'Keine Veranstaltungen gefunden.', 'kulturhaus-events' ) . '</p>';
		}

		ob_start();
		?>
		<div class="kh-events-shortcode">
			<?php while ( $query->have_posts() ) : $query->the_post(); ?>
				<?php $this->render_event_card( get_the_ID() ); ?>
			<?php endwhile; ?>
		</div>
		<?php
		wp_reset_postdata();

		return ob_get_clean();
	}

	/**
	 * Shortcode: [kh_upcoming_events]
	 *
	 * Zeigt kommende Veranstaltungen in kompakter Form.
	 *
	 * Attribute:
	 * - limit: Anzahl (Standard: 5)
	 * - category: Kategorie-Slug
	 *
	 * @param array<string, mixed> $atts Shortcode-Attribute.
	 * @return string HTML-Ausgabe.
	 */
	public function upcoming_events( $atts ): string {
		$atts = shortcode_atts(
			array(
				'limit'    => 5,
				'category' => '',
			),
			$atts,
			'kh_upcoming_events'
		);

		$args = array(
			'post_type'      => KH_Event::POST_TYPE,
			'posts_per_page' => (int) $atts['limit'],
			'post_status'    => 'publish',
			'meta_key'       => '_kh_event_start_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'     => '_kh_event_start_date',
					'value'   => current_time( 'mysql' ),
					'compare' => '>=',
					'type'    => 'DATETIME',
				),
			),
		);

		if ( ! empty( $atts['category'] ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => KH_Event_Category::TAXONOMY,
					'field'    => 'slug',
					'terms'    => sanitize_text_field( $atts['category'] ),
				),
			);
		}

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return '<p class="kh-upcoming-empty">' . esc_html__( 'Keine kommenden Veranstaltungen.', 'kulturhaus-events' ) . '</p>';
		}

		ob_start();
		?>
		<ul class="kh-upcoming-events">
			<?php while ( $query->have_posts() ) : $query->the_post(); ?>
				<?php $this->render_upcoming_item( get_the_ID() ); ?>
			<?php endwhile; ?>
		</ul>
		<?php
		wp_reset_postdata();

		return ob_get_clean();
	}

	/**
	 * Shortcode: [kh_event_highlights]
	 *
	 * Zeigt Event-Highlights mit Text links und 3 Event-Karten rechts.
	 * Bevorzugt hervorgehobene Events, sonst kommende Events.
	 *
	 * Attribute:
	 * - limit: Anzahl der Events (Standard: 3)
	 * - category: Kategorie-Slug
	 * - title: Überschrift (Standard: "HIGH-LIGHTS")
	 * - subtitle: Untertitel (Standard: "DAS KULTURHAUS OSTERFELD")
	 * - text: Beschreibungstext
	 * - button_text: Button-Text (Standard: "UNSER AKTUELLES PROGRAMM")
	 * - button_link: Button-Link
	 *
	 * @param array<string, mixed> $atts Shortcode-Attribute.
	 * @return string HTML-Ausgabe.
	 */
	public function event_highlights( $atts ): string {
		$atts = shortcode_atts(
			array(
				'limit'        => 3,
				'category'     => '',
				'title'        => 'HIGH-LIGHTS',
				'subtitle'     => 'DAS KULTURHAUS OSTERFELD',
				'text'         => 'Das „Osterfeld" ist mit jährlich bis zu 150.000 Besuchern und Nutzern das größte Kultur- und Kommunikationszentrum in der Region Pforzheim. Hier findet die freie Kunst-, Kultur- und Theaterszene ihren Spielraum. Auf dem Programm stehen u.a. künstlerische und kulturelle Projekte, Comedy, Kabarett sowie Theaterproduktionen.',
				'button_text'  => 'UNSER AKTUELLES PROGRAMM',
				'button_link'  => '/veranstaltungen/',
				'accent_color' => '#dc143c',
			),
			$atts,
			'kh_event_highlights'
		);

		// Erst Featured Events holen
		$featured_args = array(
			'post_type'      => KH_Event::POST_TYPE,
			'posts_per_page' => (int) $atts['limit'],
			'post_status'    => 'publish',
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => '_kh_event_featured',
					'value'   => '1',
					'compare' => '=',
				),
				array(
					'key'     => '_kh_event_start_date',
					'value'   => current_time( 'mysql' ),
					'compare' => '>=',
					'type'    => 'DATETIME',
				),
			),
			'meta_key'       => '_kh_event_start_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
		);

		if ( ! empty( $atts['category'] ) ) {
			$featured_args['tax_query'] = array(
				array(
					'taxonomy' => KH_Event_Category::TAXONOMY,
					'field'    => 'slug',
					'terms'    => sanitize_text_field( $atts['category'] ),
				),
			);
		}

		$featured_query = new WP_Query( $featured_args );
		$events = array();

		// Featured Events sammeln
		while ( $featured_query->have_posts() ) {
			$featured_query->the_post();
			$events[] = get_the_ID();
		}
		wp_reset_postdata();

		// Wenn nicht genug Featured Events, mit kommenden Events auffüllen
		if ( count( $events ) < (int) $atts['limit'] ) {
			$remaining = (int) $atts['limit'] - count( $events );
			
			$upcoming_args = array(
				'post_type'      => KH_Event::POST_TYPE,
				'posts_per_page' => $remaining,
				'post_status'    => 'publish',
				'post__not_in'   => $events,
				'meta_key'       => '_kh_event_start_date',
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				'meta_query'     => array(
					array(
						'key'     => '_kh_event_start_date',
						'value'   => current_time( 'mysql' ),
						'compare' => '>=',
						'type'    => 'DATETIME',
					),
				),
			);

			if ( ! empty( $atts['category'] ) ) {
				$upcoming_args['tax_query'] = array(
					array(
						'taxonomy' => KH_Event_Category::TAXONOMY,
						'field'    => 'slug',
						'terms'    => sanitize_text_field( $atts['category'] ),
					),
				);
			}

			$upcoming_query = new WP_Query( $upcoming_args );
			
			while ( $upcoming_query->have_posts() ) {
				$upcoming_query->the_post();
				$events[] = get_the_ID();
			}
			wp_reset_postdata();
		}

		if ( empty( $events ) ) {
			return '<p class="kh-highlights-empty">' . esc_html__( 'Keine Highlights verfügbar.', 'kulturhaus-events' ) . '</p>';
		}

		ob_start();
		?>
		<section class="kh-event-highlights">
			<div class="kh-highlights-container">
				<!-- Text-Block Links -->
				<div class="kh-highlights-text">
					<h2 class="kh-highlights-text__title"><?php echo esc_html( $atts['title'] ); ?></h2>
					<h3 class="kh-highlights-text__subtitle"><?php echo esc_html( $atts['subtitle'] ); ?></h3>
					<p class="kh-highlights-text__description"><?php echo esc_html( $atts['text'] ); ?></p>
					<?php if ( ! empty( $atts['button_link'] ) ) : ?>
						<a href="<?php echo esc_url( $atts['button_link'] ); ?>" class="kh-highlights-text__button">
							<?php echo esc_html( $atts['button_text'] ); ?>
							<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</a>
					<?php endif; ?>
				</div>

				<!-- Event-Karten Rechts -->
				<div class="kh-highlights-cards">
					<?php foreach ( $events as $event_id ) : ?>
						<?php $this->render_highlight_card( $event_id ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php

		return ob_get_clean();
	}

	/**
	 * Event-Karte rendern (für Shortcode).
	 *
	 * @param int $post_id Die Post-ID.
	 */
	private function render_event_card( int $post_id ): void {
		$start_date = get_post_meta( $post_id, '_kh_event_start_date', true );
		$venue_id   = get_post_meta( $post_id, '_kh_event_venue_id', true );
		$status     = get_post_meta( $post_id, '_kh_event_status', true );

		$date_format = get_option( 'kh_date_format', 'd.m.Y' );
		$time_format = get_option( 'kh_time_format', 'H:i' );

		$status_labels = array(
			'cancelled' => __( 'Abgesagt', 'kulturhaus-events' ),
			'postponed' => __( 'Verschoben', 'kulturhaus-events' ),
			'soldout'   => __( 'Ausverkauft', 'kulturhaus-events' ),
		);
		?>
		<article class="kh-event-card kh-event-card--shortcode">
			<div class="kh-event-card__content">
				<?php if ( $start_date ) : ?>
					<time class="kh-event-card__date" datetime="<?php echo esc_attr( gmdate( 'c', strtotime( $start_date ) ) ); ?>">
						<span class="kh-event-card__date-day"><?php echo esc_html( wp_date( 'd', strtotime( $start_date ) ) ); ?></span>
						<span class="kh-event-card__date-month"><?php echo esc_html( wp_date( 'M', strtotime( $start_date ) ) ); ?></span>
					</time>
				<?php endif; ?>

				<div class="kh-event-card__details">
					<h3 class="kh-event-card__title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h3>

					<?php if ( $status && 'scheduled' !== $status && isset( $status_labels[ $status ] ) ) : ?>
						<span class="kh-event-card__status kh-event-card__status--<?php echo esc_attr( $status ); ?>">
							<?php echo esc_html( $status_labels[ $status ] ); ?>
						</span>
					<?php endif; ?>

					<div class="kh-event-card__meta">
						<?php if ( $start_date ) : ?>
							<span class="kh-event-card__time">
								<?php echo esc_html( wp_date( $date_format, strtotime( $start_date ) ) ); ?>,
								<?php echo esc_html( wp_date( $time_format, strtotime( $start_date ) ) ); ?>
								<?php esc_html_e( 'Uhr', 'kulturhaus-events' ); ?>
							</span>
						<?php endif; ?>

						<?php
						if ( $venue_id ) :
							$venue = get_post( (int) $venue_id );
							if ( $venue ) :
								?>
								<span class="kh-event-card__venue">
									<?php echo esc_html( $venue->post_title ); ?>
								</span>
							<?php endif; ?>
						<?php endif; ?>
					</div>

					<?php if ( has_excerpt() ) : ?>
						<p class="kh-event-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</article>
		<?php
	}

	/**
	 * Kompaktes Event-Item rendern (für Upcoming-Shortcode).
	 *
	 * @param int $post_id Die Post-ID.
	 */
	private function render_upcoming_item( int $post_id ): void {
		$start_date  = get_post_meta( $post_id, '_kh_event_start_date', true );
		$date_format = get_option( 'kh_date_format', 'd.m.Y' );
		$time_format = get_option( 'kh_time_format', 'H:i' );
		?>
		<li class="kh-upcoming-event">
			<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
				<span class="kh-upcoming-event__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></span>
				<?php if ( $start_date ) : ?>
					<time class="kh-upcoming-event__date" datetime="<?php echo esc_attr( gmdate( 'c', strtotime( $start_date ) ) ); ?>">
						<?php echo esc_html( wp_date( $date_format, strtotime( $start_date ) ) ); ?>
						<?php echo esc_html( wp_date( $time_format, strtotime( $start_date ) ) ); ?>
					</time>
				<?php endif; ?>
			</a>
		</li>
		<?php
	}

	/**
	 * Highlight-Karte rendern (für Highlights-Shortcode).
	 *
	 * @param int $post_id Die Post-ID.
	 */
	private function render_highlight_card( int $post_id ): void {
		$start_date  = get_post_meta( $post_id, '_kh_event_start_date', true );
		$date_format = get_option( 'kh_date_format', 'd.m.Y' );
		?>
		<article class="kh-highlight-card">
			<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="kh-highlight-card__link">
				<?php if ( has_post_thumbnail( $post_id ) ) : ?>
					<div class="kh-highlight-card__image">
						<?php echo get_the_post_thumbnail( $post_id, 'medium_large' ); ?>
					</div>
				<?php endif; ?>
				
				<div class="kh-highlight-card__content">
					<?php if ( $start_date ) : ?>
						<time class="kh-highlight-card__date" datetime="<?php echo esc_attr( gmdate( 'c', strtotime( $start_date ) ) ); ?>">
							<?php echo esc_html( strtoupper( wp_date( $date_format, strtotime( $start_date ) ) ) ); ?>
						</time>
					<?php endif; ?>
					
					<h3 class="kh-highlight-card__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
				</div>
			</a>
		</article>
		<?php
	}

	/**
	 * Shortcode: [kh_event_program]
	 *
	 * Zeigt eine monatliche Programm-Liste im Kulturhaus-Design.
	 *
	 * Attribute:
	 * - month: Monat (1-12, Standard: aktueller Monat)
	 * - year: Jahr (Standard: aktuelles Jahr)
	 * - category: Kategorie-Slug
	 * - show_navigation: true/false - Monatsnavigation anzeigen (Standard: true)
	 *
	 * @param array<string, mixed> $atts Shortcode-Attribute.
	 * @return string HTML-Ausgabe.
	 */
	public function event_program( $atts ): string {
		$atts = shortcode_atts(
			array(
				'month'           => '',
				'year'            => '',
				'category'        => '',
				'show_navigation' => 'false',
				'limit'           => 50,
			),
			$atts,
			'kh_event_program'
		);

		$show_nav = $atts['show_navigation'] === 'true';
		$limit = absint( $atts['limit'] );

		// Wenn Monat/Jahr angegeben: Monatliche Ansicht
		if ( ! empty( $atts['month'] ) && ! empty( $atts['year'] ) ) {
			$month = absint( $atts['month'] );
			$year  = absint( $atts['year'] );
			
			$start_date = sprintf( '%04d-%02d-01 00:00:00', $year, $month );
			$end_date   = date( 'Y-m-t 23:59:59', strtotime( $start_date ) );
			$month_title = strtoupper( wp_date( 'F', strtotime( $start_date ) ) );
			
			$args = array(
				'post_type'      => KH_Event::POST_TYPE,
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				'meta_key'       => '_kh_event_start_date',
				'meta_query'     => array(
					array(
						'key'     => '_kh_event_start_date',
						'value'   => array( $start_date, $end_date ),
						'compare' => 'BETWEEN',
						'type'    => 'DATETIME',
					),
				),
			);
		} else {
			// Standard: Kommende Events ab jetzt
			$start_date = current_time( 'mysql' );
			$month_title = '';
			
			$args = array(
				'post_type'      => KH_Event::POST_TYPE,
				'posts_per_page' => $limit,
				'post_status'    => 'publish',
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				'meta_key'       => '_kh_event_start_date',
				'meta_query'     => array(
					array(
						'key'     => '_kh_event_start_date',
						'value'   => $start_date,
						'compare' => '>=',
						'type'    => 'DATETIME',
					),
				),
			);
		}

		// Kategorie-Filter
		if ( ! empty( $atts['category'] ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => KH_Event_Category::TAXONOMY,
					'field'    => 'slug',
					'terms'    => sanitize_text_field( $atts['category'] ),
				),
			);
		}

		$query = new WP_Query( $args );

		ob_start();
		?>
		<div class="kh-event-program">
			
			<?php if ( $show_nav ) : ?>
				<div class="kh-program-month-nav">
					<button class="kh-program-month-nav__button" onclick="khNavigateMonth(-1)">‹</button>
					<div class="kh-program-month-nav__current">
						<?php echo esc_html( wp_date( 'F Y', strtotime( $start_date ) ) ); ?>
					</div>
					<button class="kh-program-month-nav__button" onclick="khNavigateMonth(1)">›</button>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $month_title ) ) : ?>
				<h2 class="kh-program-month-title">
					<?php echo esc_html( $month_title ); ?>
				</h2>
			<?php endif; ?>

			<?php if ( $query->have_posts() ) : ?>
				<div class="kh-program-list">
					<?php while ( $query->have_posts() ) : $query->the_post(); ?>
						<?php $this->render_program_item( get_the_ID() ); ?>
					<?php endwhile; ?>
				</div>
			<?php else : ?>
				<div class="kh-program-empty">
					<p><?php esc_html_e( 'Keine Veranstaltungen in diesem Monat.', 'kulturhaus-events' ); ?></p>
				</div>
			<?php endif; ?>

		</div>
		<?php
		wp_reset_postdata();

		return ob_get_clean();
	}

	/**
	 * Einzelnes Programm-Item rendern.
	 *
	 * @param int $event_id Event-ID.
	 */
	private function render_program_item( int $event_id ): void {
		$start_date = get_post_meta( $event_id, '_kh_event_start_date', true );
		$price_regular = get_post_meta( $event_id, '_kh_event_price_regular', true );
		$price_reduced = get_post_meta( $event_id, '_kh_event_price_reduced', true );
		$cost_free = get_post_meta( $event_id, '_kh_event_cost_free', true );
		$ticket_url = get_post_meta( $event_id, '_kh_event_url', true );
		
		// Kategorien für Untertitel
		$categories = wp_get_post_terms( $event_id, KH_Event_Category::TAXONOMY, array( 'fields' => 'names' ) );
		$subtitle = ! empty( $categories ) ? implode( ', ', $categories ) : '';

		// Datum formatieren
		$date_formatted = wp_date( 'D, d.m.Y, H:i', strtotime( $start_date ) ) . ' Uhr';

		// Preis
		$price_display = '';
		if ( $cost_free ) {
			$price_display = '<span class="kh-program-item__free">' . esc_html__( 'Eintritt frei', 'kulturhaus-events' ) . '</span>';
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
					<?php esc_html_e( 'Infos & Tickets', 'kulturhaus-events' ); ?>
				</span>
			</div>
		</a>
		<?php
	}
}
