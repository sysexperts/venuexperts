<?php
/**
 * Widget: Event-Filter.
 *
 * @package KulturhausEvents
 * @since   1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Event_Filter_Widget
 *
 * Widget zum Filtern von Events nach Datum und Kategorie.
 */
class KH_Event_Filter_Widget extends WP_Widget {

	/**
	 * Konstruktor.
	 */
	public function __construct() {
		parent::__construct(
			'kh_event_filter',
			__( 'Veranstaltungsfilter', 'kulturhaus-events' ),
			array(
				'description' => __( 'Filter für Veranstaltungen nach Datum und Kategorie', 'kulturhaus-events' ),
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
		$title        = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Veranstaltungen filtern', 'kulturhaus-events' );
		$title        = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$accent_color = ! empty( $instance['accent_color'] ) ? $instance['accent_color'] : '#ffc107';
		$button_text  = ! empty( $instance['button_text'] ) ? $instance['button_text'] : __( 'Filtern', 'kulturhaus-events' );

		echo $args['before_widget'];

		// Custom Styling
		$widget_id = $args['widget_id'];
		?>
		<style>
			#<?php echo esc_attr( $widget_id ); ?> .kh-filter-widget__submit {
				background: <?php echo esc_attr( $accent_color ); ?>;
			}
			#<?php echo esc_attr( $widget_id ); ?> .kh-filter-widget__submit:hover {
				opacity: 0.8;
			}
		</style>
		<?php

		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}

		$this->render_filter_form();

		echo $args['after_widget'];
	}

	/**
	 * Filter-Formular rendern.
	 */
	private function render_filter_form(): void {
		$current_cat  = isset( $_GET['kh_cat'] ) ? sanitize_text_field( wp_unslash( $_GET['kh_cat'] ) ) : '';
		$current_tag  = isset( $_GET['kh_tag'] ) ? sanitize_text_field( wp_unslash( $_GET['kh_tag'] ) ) : '';
		$current_date = isset( $_GET['kh_date'] ) ? sanitize_text_field( wp_unslash( $_GET['kh_date'] ) ) : '';

		$archive_url = get_post_type_archive_link( KH_Event::POST_TYPE );
		?>
		<form method="get" action="<?php echo esc_url( $archive_url ); ?>" class="kh-filter-form">
			
			<div class="kh-filter-group">
				<label for="kh-filter-date"><?php esc_html_e( 'Zeitraum', 'kulturhaus-events' ); ?></label>
				<select name="kh_date" id="kh-filter-date" class="kh-filter-select">
					<option value=""><?php esc_html_e( 'Alle', 'kulturhaus-events' ); ?></option>
					<option value="today" <?php selected( $current_date, 'today' ); ?>><?php esc_html_e( 'Heute', 'kulturhaus-events' ); ?></option>
					<option value="tomorrow" <?php selected( $current_date, 'tomorrow' ); ?>><?php esc_html_e( 'Morgen', 'kulturhaus-events' ); ?></option>
					<option value="week" <?php selected( $current_date, 'week' ); ?>><?php esc_html_e( 'Diese Woche', 'kulturhaus-events' ); ?></option>
					<option value="month" <?php selected( $current_date, 'month' ); ?>><?php esc_html_e( 'Dieser Monat', 'kulturhaus-events' ); ?></option>
					<option value="next_month" <?php selected( $current_date, 'next_month' ); ?>><?php esc_html_e( 'Nächster Monat', 'kulturhaus-events' ); ?></option>
				</select>
			</div>

			<?php
			$categories = get_terms(
				array(
					'taxonomy'   => KH_Event_Category::TAXONOMY,
					'hide_empty' => true,
				)
			);

			if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) :
				?>
				<div class="kh-filter-group">
					<label for="kh-filter-category"><?php esc_html_e( 'Kategorie', 'kulturhaus-events' ); ?></label>
					<select name="kh_cat" id="kh-filter-category" class="kh-filter-select">
						<option value=""><?php esc_html_e( 'Alle Kategorien', 'kulturhaus-events' ); ?></option>
						<?php foreach ( $categories as $category ) : ?>
							<option value="<?php echo esc_attr( $category->slug ); ?>" <?php selected( $current_cat, $category->slug ); ?>>
								<?php echo esc_html( $category->name ); ?> (<?php echo esc_html( (string) $category->count ); ?>)
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>

			<?php
			$tags = get_terms(
				array(
					'taxonomy'   => KH_Event_Tag::TAXONOMY,
					'hide_empty' => true,
				)
			);

			if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) :
				?>
				<div class="kh-filter-group">
					<label for="kh-filter-tag"><?php esc_html_e( 'Schlagwort', 'kulturhaus-events' ); ?></label>
					<select name="kh_tag" id="kh-filter-tag" class="kh-filter-select">
						<option value=""><?php esc_html_e( 'Alle Schlagwörter', 'kulturhaus-events' ); ?></option>
						<?php foreach ( $tags as $tag ) : ?>
							<option value="<?php echo esc_attr( $tag->slug ); ?>" <?php selected( $current_tag, $tag->slug ); ?>>
								<?php echo esc_html( $tag->name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>

			<div class="kh-filter-actions">
				<button type="submit" class="kh-filter-widget__submit">
				<?php echo esc_html( $button_text ); ?>
			</button>
				<a href="<?php echo esc_url( $archive_url ); ?>" class="kh-filter-reset"><?php esc_html_e( 'Zurücksetzen', 'kulturhaus-events' ); ?></a>
			</div>
		</form>
		<?php
	}

	/**
	 * Widget-Formular im Backend.
	 *
	 * @param array<string, mixed> $instance Widget-Instanz.
	 * @return string
	 */
	public function form( $instance ): string {
		$title        = isset( $instance['title'] ) ? $instance['title'] : __( 'Veranstaltungen filtern', 'kulturhaus-events' );
		$accent_color = isset( $instance['accent_color'] ) ? $instance['accent_color'] : '#ffc107';
		$button_text  = isset( $instance['button_text'] ) ? $instance['button_text'] : __( 'Filtern', 'kulturhaus-events' );
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

		<hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">
		<p><strong><?php esc_html_e( 'Design-Einstellungen', 'kulturhaus-events' ); ?></strong></p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'accent_color' ) ); ?>">
				<?php esc_html_e( 'Button-Farbe:', 'kulturhaus-events' ); ?>
			</label>
			<input 
				class="widefat" 
				id="<?php echo esc_attr( $this->get_field_id( 'accent_color' ) ); ?>" 
				name="<?php echo esc_attr( $this->get_field_name( 'accent_color' ) ); ?>" 
				type="color" 
				value="<?php echo esc_attr( $accent_color ); ?>"
			>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'button_text' ) ); ?>">
				<?php esc_html_e( 'Button-Text:', 'kulturhaus-events' ); ?>
			</label>
			<input 
				class="widefat" 
				id="<?php echo esc_attr( $this->get_field_id( 'button_text' ) ); ?>" 
				name="<?php echo esc_attr( $this->get_field_name( 'button_text' ) ); ?>" 
				type="text" 
				value="<?php echo esc_attr( $button_text ); ?>"
			>
		</p>
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
		$instance                 = array();
		$instance['title']        = ! empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['accent_color'] = ! empty( $new_instance['accent_color'] ) ? sanitize_hex_color( $new_instance['accent_color'] ) : '#ffc107';
		$instance['button_text']  = ! empty( $new_instance['button_text'] ) ? sanitize_text_field( $new_instance['button_text'] ) : __( 'Filtern', 'kulturhaus-events' );

		return $instance;
	}
}
