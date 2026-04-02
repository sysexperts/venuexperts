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
		add_shortcode( 'kh_event_calendar', array( $this, 'event_calendar' ) );
		add_shortcode( 'kh_event_search', array( $this, 'event_search' ) );
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
				'limit'           => 5,
				'show_all_link'   => 'true',
				'all_link_url'    => '/veranstaltungen/',
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

		$show_all_link = $atts['show_all_link'] === 'true';
		$all_link_url = esc_url( $atts['all_link_url'] );

		// Kategorien für Filter abrufen
		$categories = get_terms(
			array(
				'taxonomy'   => KH_Event_Category::TAXONOMY,
				'hide_empty' => true,
			)
		);

		ob_start();
		?>
		<div class="kh-event-program" data-limit="<?php echo esc_attr( $limit ); ?>" data-show-all-link="<?php echo esc_attr( $show_all_link ? '1' : '0' ); ?>" data-all-link-url="<?php echo esc_attr( $all_link_url ); ?>">
			
			<!-- Filter Top Bar -->
			<div class="kh-program-filter-bar">
				<div class="kh-program-filter-bar__inner">
					
					<!-- Suchfeld -->
					<div class="kh-program-filter-field kh-program-filter-field--search">
						<label for="kh-program-search">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<circle cx="11" cy="11" r="8"></circle>
								<path d="m21 21-4.35-4.35"></path>
							</svg>
							Suche
						</label>
						<input 
							type="text" 
							id="kh-program-search" 
							class="kh-program-filter-input" 
							placeholder="Event-Name suchen..."
							autocomplete="off"
						>
					</div>

					<!-- Kategorie Filter -->
					<div class="kh-program-filter-field">
						<label for="kh-program-category">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M4 4h7l2 2h7a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"></path>
							</svg>
							Kategorie
						</label>
						<select id="kh-program-category" class="kh-program-filter-select">
							<option value="">Alle Kategorien</option>
							<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
								<?php foreach ( $categories as $category ) : ?>
									<option value="<?php echo esc_attr( $category->slug ); ?>">
										<?php echo esc_html( $category->name ); ?>
									</option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</div>

					<!-- Zeitraum Filter -->
					<div class="kh-program-filter-field">
						<label for="kh-program-timeframe">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
								<line x1="16" y1="2" x2="16" y2="6"></line>
								<line x1="8" y1="2" x2="8" y2="6"></line>
								<line x1="3" y1="10" x2="21" y2="10"></line>
							</svg>
							Zeitraum
						</label>
						<select id="kh-program-timeframe" class="kh-program-filter-select">
							<option value="upcoming">Kommende Events</option>
							<option value="today">Heute</option>
							<option value="this-week">Diese Woche</option>
							<option value="this-month">Dieser Monat</option>
							<option value="next-month">Nächster Monat</option>
							<option value="this-year">Dieses Jahr</option>
						</select>
					</div>

					<!-- Reset & Results Count -->
					<div class="kh-program-filter-actions">
						<button type="button" class="kh-program-filter-reset" id="kh-program-filter-reset">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<polyline points="1 4 1 10 7 10"></polyline>
								<path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
							</svg>
							Zurücksetzen
						</button>
						<div class="kh-program-filter-count">
							<strong id="kh-program-results-count"><?php echo esc_html( $query->found_posts ); ?></strong> 
							<span>Veranstaltungen</span>
						</div>
					</div>

				</div>
			</div>

			<div class="kh-program-container">
				
				<!-- Events Links (60%) -->
				<div class="kh-program-events" id="kh-program-events-list">
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

						<?php if ( $show_all_link ) : ?>
							<div class="kh-program-all-link">
								<a href="<?php echo esc_url( $all_link_url ); ?>" class="kh-program-all-link__button">
									<?php esc_html_e( 'Alle Veranstaltungen', 'kulturhaus-events' ); ?> →
								</a>
							</div>
						<?php endif; ?>
					<?php else : ?>
						<div class="kh-program-empty">
							<p><?php esc_html_e( 'Keine Veranstaltungen gefunden.', 'kulturhaus-events' ); ?></p>
						</div>
					<?php endif; ?>
				</div>

				<!-- News Rechts (40%) -->
				<div class="kh-program-news">
					<h2 class="kh-program-news__title"><?php esc_html_e( 'News', 'kulturhaus-events' ); ?></h2>
					<?php
					// Neueste Beiträge abfragen
					$news_query = new WP_Query(
						array(
							'post_type'      => 'post',
							'posts_per_page' => 5,
							'post_status'    => 'publish',
							'orderby'        => 'date',
							'order'          => 'DESC',
						)
					);
					?>

					<?php if ( $news_query->have_posts() ) : ?>
						<div class="kh-program-news__list">
							<?php while ( $news_query->have_posts() ) : $news_query->the_post(); ?>
								<article class="kh-news-item">
									<?php if ( has_post_thumbnail() ) : ?>
										<div class="kh-news-item__image">
											<a href="<?php the_permalink(); ?>">
												<?php the_post_thumbnail( 'thumbnail' ); ?>
											</a>
										</div>
									<?php endif; ?>
									
									<div class="kh-news-item__content">
										<time class="kh-news-item__date">
											<?php echo get_the_date( 'd.m.Y' ); ?>
										</time>
										<h3 class="kh-news-item__title">
											<a href="<?php the_permalink(); ?>">
												<?php the_title(); ?>
											</a>
										</h3>
										<div class="kh-news-item__excerpt">
											<?php echo wp_trim_words( get_the_excerpt(), 15 ); ?>
										</div>
										<a href="<?php the_permalink(); ?>" class="kh-news-item__link">
											<?php esc_html_e( 'Weiterlesen', 'kulturhaus-events' ); ?> →
										</a>
									</div>
								</article>
							<?php endwhile; ?>
						</div>
					<?php else : ?>
						<div class="kh-program-news__content">
							<p class="kh-program-news__placeholder">
								<?php esc_html_e( 'Noch keine News vorhanden. Erstellen Sie Beiträge unter "Beiträge" → "Erstellen".', 'kulturhaus-events' ); ?>
							</p>
						</div>
					<?php endif; ?>
					<?php wp_reset_postdata(); ?>
				</div>

			</div>
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

	/**
	 * Shortcode: [kh_event_calendar]
	 *
	 * Zeigt einen Monatskalender mit Events an.
	 *
	 * Attribute:
	 * - month: Monat (1-12, Standard: aktueller Monat)
	 * - year: Jahr (Standard: aktuelles Jahr)
	 * - category: Kategorie-Slug
	 *
	 * @param array<string, mixed> $atts Shortcode-Attribute.
	 * @return string HTML-Ausgabe.
	 */
	public function event_calendar( $atts ): string {
		$atts = shortcode_atts(
			array(
				'month'    => (int) current_time( 'n' ),
				'year'     => (int) current_time( 'Y' ),
				'category' => '',
			),
			$atts,
			'kh_event_calendar'
		);

		$month = (int) $atts['month'];
		$year  = (int) $atts['year'];
		$category = sanitize_text_field( $atts['category'] );

		// Kalender-Daten berechnen
		$first_day = mktime( 0, 0, 0, $month, 1, $year );
		$days_in_month = (int) date( 't', $first_day );
		$day_of_week = (int) date( 'N', $first_day ); // 1 (Mo) bis 7 (So)
		
		// Events für diesen Monat abrufen
		$start_date = date( 'Y-m-d', $first_day );
		$end_date = date( 'Y-m-d', mktime( 0, 0, 0, $month, $days_in_month, $year ) );
		
		$args = array(
			'post_type'      => 'event',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_key'       => '_kh_event_start_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'     => '_kh_event_start_date',
					'value'   => array( $start_date, $end_date ),
					'compare' => 'BETWEEN',
					'type'    => 'DATE',
				),
			),
		);

		if ( ! empty( $category ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => KH_Event_Category::TAXONOMY,
					'field'    => 'slug',
					'terms'    => $category,
				),
			);
		}

		$events_query = new WP_Query( $args );
		
		// Events nach Tag gruppieren
		$events_by_day = array();
		if ( $events_query->have_posts() ) {
			while ( $events_query->have_posts() ) {
				$events_query->the_post();
				$event_id = get_the_ID();
				$event_start = get_post_meta( $event_id, '_kh_event_start_date', true );
				$day = (int) date( 'j', strtotime( $event_start ) );
				
				if ( ! isset( $events_by_day[ $day ] ) ) {
					$events_by_day[ $day ] = array();
				}
				
				$events_by_day[ $day ][] = array(
					'id'    => $event_id,
					'title' => get_the_title(),
					'url'   => get_permalink(),
					'time'  => date( 'H:i', strtotime( $event_start ) ),
				);
			}
			wp_reset_postdata();
		}

		// Navigation URLs
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

		$current_url = get_permalink();
		$prev_url = add_query_arg( array( 'month' => $prev_month, 'year' => $prev_year ), $current_url );
		$next_url = add_query_arg( array( 'month' => $next_month, 'year' => $next_year ), $current_url );

		// Monatsnamen
		$month_names = array(
			1 => 'Januar', 2 => 'Februar', 3 => 'März', 4 => 'April',
			5 => 'Mai', 6 => 'Juni', 7 => 'Juli', 8 => 'August',
			9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Dezember'
		);

		$today = (int) current_time( 'j' );
		$current_month = (int) current_time( 'n' );
		$current_year = (int) current_time( 'Y' );

		ob_start();
		?>
		<div class="kh-calendar">
			<!-- Header mit Navigation -->
			<div class="kh-calendar-header">
				<a href="<?php echo esc_url( $prev_url ); ?>" class="kh-calendar-nav kh-calendar-nav--prev" aria-label="Vorheriger Monat">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M15 18l-6-6 6-6"/>
					</svg>
				</a>
				
				<h2 class="kh-calendar-title">
					<?php echo esc_html( $month_names[ $month ] . ' ' . $year ); ?>
				</h2>
				
				<a href="<?php echo esc_url( $next_url ); ?>" class="kh-calendar-nav kh-calendar-nav--next" aria-label="Nächster Monat">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M9 18l6-6-6-6"/>
					</svg>
				</a>
			</div>

			<!-- Kalender Grid -->
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
				// Leere Tage vor dem 1. des Monats
				for ( $i = 1; $i < $day_of_week; $i++ ) {
					echo '<div class="kh-calendar-day kh-calendar-day--empty"></div>';
				}

				// Tage des Monats
				for ( $day = 1; $day <= $days_in_month; $day++ ) {
					$has_events = isset( $events_by_day[ $day ] );
					$is_today = ( $day === $today && $month === $current_month && $year === $current_year );
					
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
								<?php foreach ( $events_by_day[ $day ] as $event ) : ?>
									<a href="<?php echo esc_url( $event['url'] ); ?>" class="kh-calendar-event" title="<?php echo esc_attr( $event['title'] ); ?>">
										<span class="kh-calendar-event__dot"></span>
										<span class="kh-calendar-event__title"><?php echo esc_html( $event['title'] ); ?></span>
									</a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
					<?php
				}

				// Leere Tage nach dem letzten Tag des Monats
				$last_day_of_week = (int) date( 'N', mktime( 0, 0, 0, $month, $days_in_month, $year ) );
				for ( $i = $last_day_of_week; $i < 7; $i++ ) {
					echo '<div class="kh-calendar-day kh-calendar-day--empty"></div>';
				}
				?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Shortcode: [kh_event_search]
	 *
	 * Zeigt Suchfeld und Filter für Events an.
	 *
	 * Attribute:
	 * - show_filters: true/false (Standard: true)
	 * - results_layout: grid/list (Standard: grid)
	 *
	 * @param array<string, mixed> $atts Shortcode-Attribute.
	 * @return string HTML-Ausgabe.
	 */
	public function event_search( $atts ): string {
		$atts = shortcode_atts(
			array(
				'show_filters'   => 'true',
				'results_layout' => 'grid',
			),
			$atts,
			'kh_event_search'
		);

		$show_filters = $atts['show_filters'] === 'true';

		// Kategorien abrufen
		$categories = get_terms(
			array(
				'taxonomy'   => KH_Event_Category::TAXONOMY,
				'hide_empty' => true,
			)
		);

		// Veranstaltungsorte abrufen
		$venues = get_posts(
			array(
				'post_type'      => 'venue',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		ob_start();
		?>
		<div class="kh-event-search">
			<!-- Search Form -->
			<form class="kh-search-form" id="kh-search-form">
				<div class="kh-search-header">
					<h2 class="kh-search-title">Veranstaltungen suchen</h2>
					<button type="button" class="kh-search-reset">
						Filter zurücksetzen
					</button>
				</div>

				<div class="kh-search-fields">
					<!-- Suchfeld -->
					<div class="kh-search-field">
						<label for="kh-search-query">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<circle cx="11" cy="11" r="8"></circle>
								<path d="m21 21-4.35-4.35"></path>
							</svg>
							Suche
						</label>
						<input 
							type="text" 
							id="kh-search-query" 
							name="query" 
							placeholder="Event-Name oder Stichwort..."
							autocomplete="off"
						>
					</div>

					<?php if ( $show_filters ) : ?>
						<!-- Kategorie Filter -->
						<div class="kh-search-field">
							<label for="kh-search-category">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M4 4h7l2 2h7a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"></path>
								</svg>
								Kategorie
							</label>
							<select id="kh-search-category" name="category">
								<option value="">Alle Kategorien</option>
								<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
									<?php foreach ( $categories as $category ) : ?>
										<option value="<?php echo esc_attr( $category->slug ); ?>">
											<?php echo esc_html( $category->name ); ?>
										</option>
									<?php endforeach; ?>
								<?php endif; ?>
							</select>
						</div>

						<!-- Monat Filter -->
						<div class="kh-search-field">
							<label for="kh-search-month">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
									<line x1="16" y1="2" x2="16" y2="6"></line>
									<line x1="8" y1="2" x2="8" y2="6"></line>
									<line x1="3" y1="10" x2="21" y2="10"></line>
								</svg>
								Monat
							</label>
							<select id="kh-search-month" name="month">
								<option value="">Alle Monate</option>
								<?php
								$current_month = (int) current_time( 'n' );
								$current_year = (int) current_time( 'Y' );
								for ( $i = 0; $i < 12; $i++ ) {
									$month = (int) ( ( $current_month + $i - 1 ) % 12 + 1 );
									$year = (int) ( $current_year + floor( ( $current_month + $i - 1 ) / 12 ) );
									$month_name = date_i18n( 'F Y', mktime( 0, 0, 0, $month, 1, $year ) );
									$value = $year . '-' . str_pad( (string) $month, 2, '0', STR_PAD_LEFT );
									?>
									<option value="<?php echo esc_attr( $value ); ?>">
										<?php echo esc_html( $month_name ); ?>
									</option>
									<?php
								}
								?>
							</select>
						</div>

						<!-- Ort Filter -->
						<div class="kh-search-field">
							<label for="kh-search-venue">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
									<circle cx="12" cy="10" r="3"></circle>
								</svg>
								Ort
							</label>
							<select id="kh-search-venue" name="venue">
								<option value="">Alle Orte</option>
								<?php if ( ! empty( $venues ) ) : ?>
									<?php foreach ( $venues as $venue ) : ?>
										<option value="<?php echo esc_attr( $venue->ID ); ?>">
											<?php echo esc_html( $venue->post_title ); ?>
										</option>
									<?php endforeach; ?>
								<?php endif; ?>
							</select>
						</div>
					<?php endif; ?>
				</div>

				<button type="submit" class="kh-search-submit">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<circle cx="11" cy="11" r="8"></circle>
						<path d="m21 21-4.35-4.35"></path>
					</svg>
					Suchen
				</button>
			</form>

			<!-- Loading -->
			<div class="kh-search-loading">
				<div class="kh-search-spinner"></div>
				<p>Suche läuft...</p>
			</div>

			<!-- Results -->
			<div class="kh-search-results">
				<?php
				// Initial results: alle kommenden Events
				$initial_events = new WP_Query(
					array(
						'post_type'      => 'event',
						'posts_per_page' => 12,
						'post_status'    => 'publish',
						'meta_key'       => '_kh_event_start_date',
						'orderby'        => 'meta_value',
						'order'          => 'ASC',
						'meta_query'     => array(
							array(
								'key'     => '_kh_event_start_date',
								'value'   => current_time( 'Y-m-d' ),
								'compare' => '>=',
								'type'    => 'DATE',
							),
						),
					)
				);

				if ( $initial_events->have_posts() ) :
					?>
					<div class="kh-search-results-header">
						<div class="kh-search-results-count">
							<strong><?php echo esc_html( $initial_events->found_posts ); ?></strong> Veranstaltungen gefunden
						</div>
					</div>

					<div class="kh-search-results-grid">
						<?php
						while ( $initial_events->have_posts() ) :
							$initial_events->the_post();
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
				?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
