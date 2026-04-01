<?php
/**
 * Plugin-Einstellungsseite.
 *
 * @package KulturhausEvents
 * @since   1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Admin_Page
 *
 * Registriert die Einstellungsseite unter dem Veranstaltungen-Menü.
 */
class KH_Admin_Page {

	/**
	 * Hooks registrieren.
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Einstellungsseite im Admin-Menü hinzufügen.
	 */
	public function add_settings_page(): void {
		add_submenu_page(
			'edit.php?post_type=kh_event',
			__( 'Einstellungen', 'kulturhaus-events' ),
			__( 'Einstellungen', 'kulturhaus-events' ),
			'manage_options',
			'kh-events-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Rendert die Design-Sektion.
	 */
	public function render_design_section(): void {
		echo '<p>' . esc_html__( 'Passen Sie das Erscheinungsbild der Event-Seiten an.', 'kulturhaus-events' ) . '</p>';
	}

	/**
	 * Rendert die Anzeige-Sektion.
	 */
	public function render_display_section(): void {
		echo '<p>' . esc_html__( 'Konfigurieren Sie die Darstellung der Veranstaltungen.', 'kulturhaus-events' ) . '</p>';
	}

	/**
	 * Registriert die Einstellungs-Sektionen.
	 */
	public function register_sections(): void {
		add_settings_section(
			'kh_events_design_section',
			__( 'Design-Einstellungen', 'kulturhaus-events' ),
			array( $this, 'render_design_section' ),
			'kh-events-settings'
		);

		add_settings_section(
			'kh_events_display_section',
			__( 'Anzeige-Einstellungen', 'kulturhaus-events' ),
			array( $this, 'render_display_section' ),
			'kh-events-settings'
		);
	}

	/**
	 * Einstellungen registrieren.
	 */
	public function register_settings(): void {
		// Sektion: Allgemein.
		add_settings_section(
			'kh_general_section',
			__( 'Allgemeine Einstellungen', 'kulturhaus-events' ),
			'__return_false',
			'kh-events-settings'
		);

		// Events pro Seite.
		register_setting( 'kh_events_settings', 'kh_events_per_page', array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 12,
		) );

		add_settings_field(
			'kh_events_per_page',
			__( 'Veranstaltungen pro Seite', 'kulturhaus-events' ),
			array( $this, 'render_number_field' ),
			'kh-events-settings',
			'kh_general_section',
			array(
				'name'    => 'kh_events_per_page',
				'default' => 12,
				'min'     => 1,
				'max'     => 100,
			)
		);

		// Standard-Ansicht.
		register_setting( 'kh_events_settings', 'kh_default_view', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 'list',
		) );

		add_settings_field(
			'kh_default_view',
			__( 'Standard-Ansicht', 'kulturhaus-events' ),
			array( $this, 'render_select_field' ),
			'kh-events-settings',
			'kh_general_section',
			array(
				'name'    => 'kh_default_view',
				'default' => 'list',
				'options' => array(
					'list'  => __( 'Liste', 'kulturhaus-events' ),
					'month' => __( 'Monatskalender', 'kulturhaus-events' ),
					'day'   => __( 'Tagesansicht', 'kulturhaus-events' ),
				),
			)
		);

		// Sektion: Datumsformat.
		add_settings_section(
			'kh_format_section',
			__( 'Formatierung', 'kulturhaus-events' ),
			'__return_false',
			'kh-events-settings'
		);

		register_setting( 'kh_events_settings', 'kh_date_format', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 'd.m.Y',
		) );

		add_settings_field(
			'kh_date_format',
			__( 'Datumsformat', 'kulturhaus-events' ),
			array( $this, 'render_text_field' ),
			'kh-events-settings',
			'kh_format_section',
			array(
				'name'        => 'kh_date_format',
				'default'     => 'd.m.Y',
				'description' => __( 'PHP-Datumsformat (z.B. d.m.Y für 01.04.2026)', 'kulturhaus-events' ),
			)
		);

		register_setting( 'kh_events_settings', 'kh_time_format', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 'H:i',
		) );

		add_settings_field(
			'kh_time_format',
			__( 'Zeitformat', 'kulturhaus-events' ),
			array( $this, 'render_text_field' ),
			'kh-events-settings',
			'kh_format_section',
			array(
				'name'        => 'kh_time_format',
				'default'     => 'H:i',
				'description' => __( 'PHP-Zeitformat (z.B. H:i für 14:30)', 'kulturhaus-events' ),
			)
		);
	}

	/**
	 * Einstellungsseite rendern.
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
			<div class="kh-events-settings-header">
				<h2><?php esc_html_e( 'Kulturhaus Events Konfiguration', 'kulturhaus-events' ); ?></h2>
				<p><?php esc_html_e( 'Passen Sie das Erscheinungsbild und Verhalten Ihrer Veranstaltungen an.', 'kulturhaus-events' ); ?></p>
			</div>

			<?php if ( isset( $_GET['settings-updated'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Einstellungen erfolgreich gespeichert!', 'kulturhaus-events' ); ?></p>
				</div>
			<?php endif; ?>

			<form action="options.php" method="post">
				<?php
				settings_fields( 'kh_events_settings' );
				do_settings_sections( 'kh-events-settings' );
				submit_button( __( 'Einstellungen speichern', 'kulturhaus-events' ) );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Zahlenfeld rendern.
	 *
	 * @param array<string, mixed> $args Feld-Argumente.
	 */
	public function render_number_field( array $args ): void {
		$value = get_option( $args['name'], $args['default'] );
		printf(
			'<input type="number" id="%1$s" name="%1$s" value="%2$s" min="%3$d" max="%4$d" class="small-text" />',
			esc_attr( $args['name'] ),
			esc_attr( (string) $value ),
			(int) $args['min'],
			(int) $args['max']
		);
	}

	/**
	 * Textfeld rendern.
	 *
	 * @param array<string, mixed> $args Feld-Argumente.
	 */
	public function render_text_field( array $args ): void {
		$value = get_option( $args['name'], $args['default'] );
		?>
		<input
			type="text"
			id="<?php echo esc_attr( $args['name'] ); ?>"
			name="<?php echo esc_attr( $args['name'] ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			class="regular-text"
		/>
		<?php
		if ( isset( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
	}

	/**
	 * Farbwähler-Feld rendern.
	 *
	 * @param array<string, mixed> $args Feld-Argumente.
	 */
	public function render_color_field( array $args ): void {
		$defaults = array(
			'kh_events_accent_color' => '#ffc107',
			'kh_events_hover_color'  => '#ffb300',
		);
		$default = $defaults[ $args['label_for'] ] ?? '#ffc107';
		$option  = get_option( $args['label_for'], $default );
		?>
		<input
			type="text"
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="<?php echo esc_attr( $args['label_for'] ); ?>"
			value="<?php echo esc_attr( $option ); ?>"
			class="kh-color-picker"
			data-default-color="<?php echo esc_attr( $default ); ?>"
		/>
		<?php if ( isset( $args['description'] ) ) : ?>
			<p class="description">
				<?php echo esc_html( $args['description'] ); ?>
			</p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Select-Feld rendern.
	 *
	 * @param array<string, mixed> $args Feld-Argumente.
	 */
	public function render_select_field( array $args ): void {
		$value = get_option( $args['name'], $args['default'] );

		printf( '<select id="%1$s" name="%1$s">', esc_attr( $args['name'] ) );

		foreach ( $args['options'] as $opt_value => $opt_label ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $opt_value ),
				selected( $value, $opt_value, false ),
				esc_html( $opt_label )
			);
		}

		echo '</select>';
	}

	/**
	 * Lädt den WordPress Color Picker und Admin-CSS.
	 *
	 * @param string $hook Die aktuelle Admin-Seite.
	 */
	public function enqueue_color_picker( string $hook ): void {
		if ( 'kh_event_page_kh-events-settings' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		wp_add_inline_style(
			'wp-color-picker',
			'
			.kh-events-settings-header {
				background: #fff;
				border-left: 4px solid var(--kh-accent-color, #ffc107);
				padding: 20px;
				margin: 20px 0;
				box-shadow: 0 1px 3px rgba(0,0,0,0.1);
			}
			.kh-events-settings-header h2 {
				margin: 0 0 10px;
				color: #1d2327;
			}
			.kh-events-settings-header p {
				margin: 0;
				color: #646970;
			}
			.form-table th {
				width: 220px;
				font-weight: 600;
			}
			.kh-events-row .wp-picker-container {
				margin-top: 5px;
			}
			.kh-events-row .description {
				margin-top: 10px;
				font-style: italic;
			}
			'
		);

		wp_add_inline_script(
			'wp-color-picker',
			'jQuery(document).ready(function($) {
				$(".kh-color-picker").wpColorPicker({
					change: function(event, ui) {
						var color = ui.color.toString();
						var fieldId = $(this).attr("id");
						
						if (fieldId === "kh_events_accent_color") {
							$(":root").css("--kh-accent-color", color);
						} else if (fieldId === "kh_events_hover_color") {
							$(":root").css("--kh-hover-color", color);
						}
					}
				});
			});'
		);
	}

	/**
	 * Gibt Custom CSS mit den gewählten Farben aus.
	 */
	public function output_custom_css(): void {
		$accent_color = get_option( 'kh_events_accent_color', '#ffc107' );
		$hover_color  = get_option( 'kh_events_hover_color', '#ffb300' );
		
		if ( ! $accent_color ) {
			$accent_color = '#ffc107';
		}
		if ( ! $hover_color ) {
			$hover_color = '#ffb300';
		}

		?>
		<style id="kh-events-custom-colors">
			:root {
				--kh-accent-color: <?php echo esc_attr( $accent_color ); ?> !important;
				--kh-hover-color: <?php echo esc_attr( $hover_color ); ?> !important;
				--kh-color-accent: <?php echo esc_attr( $accent_color ); ?> !important;
			}
		</style>
		<?php
	}
}
