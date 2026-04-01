<?php
/**
 * WordPress Customizer Integration
 *
 * @package KulturhausEvents
 * @since   1.9.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Customizer
 *
 * Registriert Customizer-Einstellungen für Design-Anpassungen.
 */
class KH_Customizer {

	/**
	 * Hooks registrieren.
	 */
	public function register(): void {
		add_action( 'customize_register', array( $this, 'register_settings' ) );
		add_action( 'wp_head', array( $this, 'output_custom_css' ), 99 );
	}

	/**
	 * Customizer-Einstellungen registrieren.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer-Manager.
	 */
	public function register_settings( $wp_customize ): void {
		// Panel für Kulturhaus Events
		$wp_customize->add_panel(
			'kh_events_panel',
			array(
				'title'       => __( 'Kulturhaus Events Design', 'kulturhaus-events' ),
				'description' => __( 'Passen Sie das Erscheinungsbild der Veranstaltungen an.', 'kulturhaus-events' ),
				'priority'    => 160,
			)
		);

		// === FARBEN ===
		$this->register_colors_section( $wp_customize );

		// === TYPOGRAFIE ===
		$this->register_typography_section( $wp_customize );

		// === LAYOUT ===
		$this->register_layout_section( $wp_customize );
	}

	/**
	 * Farben-Sektion registrieren.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer-Manager.
	 */
	private function register_colors_section( $wp_customize ): void {
		$wp_customize->add_section(
			'kh_events_colors',
			array(
				'title'    => __( 'Farben', 'kulturhaus-events' ),
				'panel'    => 'kh_events_panel',
				'priority' => 10,
			)
		);

		// Akzentfarbe
		$wp_customize->add_setting(
			'kh_accent_color',
			array(
				'default'           => '#dc143c',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'kh_accent_color',
				array(
					'label'       => __( 'Akzentfarbe', 'kulturhaus-events' ),
					'description' => __( 'Hauptfarbe für Buttons, Icons und Highlights', 'kulturhaus-events' ),
					'section'     => 'kh_events_colors',
					'settings'    => 'kh_accent_color',
				)
			)
		);

		// Hover-Farbe
		$wp_customize->add_setting(
			'kh_hover_color',
			array(
				'default'           => '#b91c1c',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'kh_hover_color',
				array(
					'label'       => __( 'Hover-Farbe', 'kulturhaus-events' ),
					'description' => __( 'Farbe beim Überfahren von interaktiven Elementen', 'kulturhaus-events' ),
					'section'     => 'kh_events_colors',
					'settings'    => 'kh_hover_color',
				)
			)
		);

		// Textfarbe
		$wp_customize->add_setting(
			'kh_text_color',
			array(
				'default'           => '#333333',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'kh_text_color',
				array(
					'label'       => __( 'Textfarbe', 'kulturhaus-events' ),
					'description' => __( 'Haupttextfarbe für Event-Inhalte', 'kulturhaus-events' ),
					'section'     => 'kh_events_colors',
					'settings'    => 'kh_text_color',
				)
			)
		);

		// Hintergrundfarbe
		$wp_customize->add_setting(
			'kh_bg_color',
			array(
				'default'           => '#ffffff',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'kh_bg_color',
				array(
					'label'       => __( 'Hintergrundfarbe', 'kulturhaus-events' ),
					'description' => __( 'Hintergrund für Event-Karten', 'kulturhaus-events' ),
					'section'     => 'kh_events_colors',
					'settings'    => 'kh_bg_color',
				)
			)
		);
	}

	/**
	 * Typografie-Sektion registrieren.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer-Manager.
	 */
	private function register_typography_section( $wp_customize ): void {
		$wp_customize->add_section(
			'kh_events_typography',
			array(
				'title'    => __( 'Typografie', 'kulturhaus-events' ),
				'panel'    => 'kh_events_panel',
				'priority' => 20,
			)
		);

		// Schriftart für Überschriften
		$wp_customize->add_setting(
			'kh_heading_font',
			array(
				'default'           => 'inherit',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'kh_heading_font',
			array(
				'label'       => __( 'Schriftart Überschriften', 'kulturhaus-events' ),
				'description' => __( 'Schriftart für Event-Titel', 'kulturhaus-events' ),
				'section'     => 'kh_events_typography',
				'type'        => 'select',
				'choices'     => array(
					'inherit'                   => __( 'Theme-Standard', 'kulturhaus-events' ),
					'Arial, sans-serif'         => 'Arial',
					'Helvetica, sans-serif'     => 'Helvetica',
					'Georgia, serif'            => 'Georgia',
					'Times New Roman, serif'    => 'Times New Roman',
					'Verdana, sans-serif'       => 'Verdana',
					'Courier New, monospace'    => 'Courier New',
					'-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif' => 'System Font',
				),
			)
		);

		// Schriftart für Text
		$wp_customize->add_setting(
			'kh_body_font',
			array(
				'default'           => 'inherit',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'kh_body_font',
			array(
				'label'       => __( 'Schriftart Text', 'kulturhaus-events' ),
				'description' => __( 'Schriftart für Event-Beschreibungen', 'kulturhaus-events' ),
				'section'     => 'kh_events_typography',
				'type'        => 'select',
				'choices'     => array(
					'inherit'                   => __( 'Theme-Standard', 'kulturhaus-events' ),
					'Arial, sans-serif'         => 'Arial',
					'Helvetica, sans-serif'     => 'Helvetica',
					'Georgia, serif'            => 'Georgia',
					'Times New Roman, serif'    => 'Times New Roman',
					'Verdana, sans-serif'       => 'Verdana',
					'Courier New, monospace'    => 'Courier New',
					'-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif' => 'System Font',
				),
			)
		);

		// Schriftgröße
		$wp_customize->add_setting(
			'kh_font_size',
			array(
				'default'           => '16',
				'sanitize_callback' => 'absint',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'kh_font_size',
			array(
				'label'       => __( 'Schriftgröße (px)', 'kulturhaus-events' ),
				'description' => __( 'Basis-Schriftgröße für Event-Texte', 'kulturhaus-events' ),
				'section'     => 'kh_events_typography',
				'type'        => 'number',
				'input_attrs' => array(
					'min'  => 12,
					'max'  => 24,
					'step' => 1,
				),
			)
		);
	}

	/**
	 * Layout-Sektion registrieren.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer-Manager.
	 */
	private function register_layout_section( $wp_customize ): void {
		$wp_customize->add_section(
			'kh_events_layout',
			array(
				'title'    => __( 'Layout', 'kulturhaus-events' ),
				'panel'    => 'kh_events_panel',
				'priority' => 30,
			)
		);

		// Border Radius
		$wp_customize->add_setting(
			'kh_border_radius',
			array(
				'default'           => '8',
				'sanitize_callback' => 'absint',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'kh_border_radius',
			array(
				'label'       => __( 'Ecken-Rundung (px)', 'kulturhaus-events' ),
				'description' => __( 'Rundung für Buttons und Karten', 'kulturhaus-events' ),
				'section'     => 'kh_events_layout',
				'type'        => 'number',
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 50,
					'step' => 1,
				),
			)
		);

		// Spacing
		$wp_customize->add_setting(
			'kh_spacing',
			array(
				'default'           => '20',
				'sanitize_callback' => 'absint',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'kh_spacing',
			array(
				'label'       => __( 'Abstände (px)', 'kulturhaus-events' ),
				'description' => __( 'Basis-Abstand zwischen Elementen', 'kulturhaus-events' ),
				'section'     => 'kh_events_layout',
				'type'        => 'number',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 50,
					'step' => 5,
				),
			)
		);
	}

	/**
	 * Custom CSS basierend auf Customizer-Einstellungen ausgeben.
	 */
	public function output_custom_css(): void {
		$accent_color   = get_theme_mod( 'kh_accent_color', '#dc143c' );
		$hover_color    = get_theme_mod( 'kh_hover_color', '#b91c1c' );
		$text_color     = get_theme_mod( 'kh_text_color', '#333333' );
		$bg_color       = get_theme_mod( 'kh_bg_color', '#ffffff' );
		$heading_font   = get_theme_mod( 'kh_heading_font', 'inherit' );
		$body_font      = get_theme_mod( 'kh_body_font', 'inherit' );
		$font_size      = get_theme_mod( 'kh_font_size', 16 );
		$border_radius  = get_theme_mod( 'kh_border_radius', 8 );
		$spacing        = get_theme_mod( 'kh_spacing', 20 );

		?>
		<style id="kh-events-customizer-styles">
			:root {
				--kh-accent-color: <?php echo esc_attr( $accent_color ); ?>;
				--kh-hover-color: <?php echo esc_attr( $hover_color ); ?>;
				--kh-text-color: <?php echo esc_attr( $text_color ); ?>;
				--kh-bg-color: <?php echo esc_attr( $bg_color ); ?>;
				--kh-font-size-base: <?php echo esc_attr( $font_size ); ?>px;
				--kh-border-radius: <?php echo esc_attr( $border_radius ); ?>px;
				--kh-spacing: <?php echo esc_attr( $spacing ); ?>px;
			}

			<?php if ( 'inherit' !== $heading_font ) : ?>
			.kh-event-card__title,
			.kh-event__title,
			.kh-events-archive__title {
				font-family: <?php echo esc_attr( $heading_font ); ?>;
			}
			<?php endif; ?>

			<?php if ( 'inherit' !== $body_font ) : ?>
			.kh-event-card,
			.kh-event__content,
			.kh-event-card__excerpt {
				font-family: <?php echo esc_attr( $body_font ); ?>;
			}
			<?php endif; ?>

			.kh-event-card,
			.kh-event__content {
				color: <?php echo esc_attr( $text_color ); ?>;
				background-color: <?php echo esc_attr( $bg_color ); ?>;
				font-size: <?php echo esc_attr( $font_size ); ?>px;
			}

			.kh-btn-tickets,
			.kh-button--primary,
			.kh-event-card__image {
				border-radius: <?php echo esc_attr( $border_radius ); ?>px;
			}
		</style>
		<?php
	}
}
