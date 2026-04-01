<?php
/**
 * Widget: Kommende Veranstaltungen.
 *
 * @package KulturhausEvents
 * @since   1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Upcoming_Events_Widget
 *
 * Widget zur Anzeige kommender Veranstaltungen.
 */
class KH_Upcoming_Events_Widget extends WP_Widget {

	/**
	 * Konstruktor.
	 */
	public function __construct() {
		parent::__construct(
			'kh_upcoming_events',
			__( 'Kommende Veranstaltungen', 'kulturhaus-events' ),
			array(
				'description' => __( 'Zeigt eine Liste kommender Veranstaltungen an', 'kulturhaus-events' ),
			)
		);
	}

	/**
	 * Widget-Ausgabe im Frontend.
	 *
	 * @param array<string, mixed> $args     Widget-Argumente.
	 * @param array<string, mixed> $instance Widget-Instanz.
	 */
	public function widget( $args, $instance ): void {
		$title    = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Kommende Veranstaltungen', 'kulturhaus-events' );
		$title    = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$limit    = ! empty( $instance['limit'] ) ? absint( $instance['limit'] ) : 5;
		$category = ! empty( $instance['category'] ) ? $instance['category'] : '';

		$query_args = array(
			'post_type'      => KH_Event::POST_TYPE,
			'posts_per_page' => $limit,
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

		if ( ! empty( $category ) ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => KH_Event_Category::TAXONOMY,
					'field'    => 'slug',
					'terms'    => $category,
				),
			);
		}

		$query = new WP_Query( $query_args );

		if ( ! $query->have_posts() ) {
			return;
		}

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}

		$date_format = get_option( 'kh_date_format', 'd.m.Y' );
		$time_format = get_option( 'kh_time_format', 'H:i' );

		echo '<ul class="kh-widget-events">';

		while ( $query->have_posts() ) {
			$query->the_post();
			$start_date = get_post_meta( get_the_ID(), '_kh_event_start_date', true );
			?>
			<li class="kh-widget-event">
				<a href="<?php the_permalink(); ?>">
					<span class="kh-widget-event__title"><?php the_title(); ?></span>
					<?php if ( $start_date ) : ?>
						<time class="kh-widget-event__date" datetime="<?php echo esc_attr( gmdate( 'c', strtotime( $start_date ) ) ); ?>">
							<?php echo esc_html( wp_date( $date_format, strtotime( $start_date ) ) ); ?>
							<span class="kh-widget-event__time">
								<?php echo esc_html( wp_date( $time_format, strtotime( $start_date ) ) ); ?>
							</span>
						</time>
					<?php endif; ?>
				</a>
			</li>
			<?php
		}

		echo '</ul>';

		$archive_link = get_post_type_archive_link( KH_Event::POST_TYPE );
		if ( $archive_link ) {
			printf(
				'<p class="kh-widget-events__more"><a href="%s">%s</a></p>',
				esc_url( $archive_link ),
				esc_html__( 'Alle Veranstaltungen ansehen', 'kulturhaus-events' )
			);
		}

		wp_reset_postdata();

		echo $args['after_widget'];
	}

	/**
	 * Widget-Formular im Backend.
	 *
	 * @param array<string, mixed> $instance Widget-Instanz.
	 * @return string
	 */
	public function form( $instance ): string {
		$title    = isset( $instance['title'] ) ? $instance['title'] : __( 'Kommende Veranstaltungen', 'kulturhaus-events' );
		$limit    = isset( $instance['limit'] ) ? absint( $instance['limit'] ) : 5;
		$category = isset( $instance['category'] ) ? $instance['category'] : '';

		$categories = get_terms(
			array(
				'taxonomy'   => KH_Event_Category::TAXONOMY,
				'hide_empty' => false,
			)
		);
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Titel:', 'kulturhaus-events' ); ?>
			</label>
			<input 
				class="widefat" 
				id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
				type="text" 
				value="<?php echo esc_attr( $title ); ?>"
			>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>">
				<?php esc_html_e( 'Anzahl:', 'kulturhaus-events' ); ?>
			</label>
			<input 
				class="tiny-text" 
				id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" 
				name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" 
				type="number" 
				min="1" 
				max="20" 
				value="<?php echo esc_attr( (string) $limit ); ?>"
			>
		</p>

		<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>">
					<?php esc_html_e( 'Kategorie:', 'kulturhaus-events' ); ?>
				</label>
				<select 
					class="widefat" 
					id="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>" 
					name="<?php echo esc_attr( $this->get_field_name( 'category' ) ); ?>"
				>
					<option value=""><?php esc_html_e( 'Alle Kategorien', 'kulturhaus-events' ); ?></option>
					<?php foreach ( $categories as $cat ) : ?>
						<option value="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $category, $cat->slug ); ?>>
							<?php echo esc_html( $cat->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</p>
		<?php endif; ?>
		<?php
		return '';
	}

	/**
	 * Widget-Instanz aktualisieren.
	 *
	 * @param array<string, mixed> $new_instance Neue Instanz.
	 * @param array<string, mixed> $old_instance Alte Instanz.
	 * @return array<string, mixed>
	 */
	public function update( $new_instance, $old_instance ): array {
		$instance             = array();
		$instance['title']    = ! empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['limit']    = ! empty( $new_instance['limit'] ) ? absint( $new_instance['limit'] ) : 5;
		$instance['category'] = ! empty( $new_instance['category'] ) ? sanitize_text_field( $new_instance['category'] ) : '';

		return $instance;
	}
}
