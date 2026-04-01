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
		printf(
			'<input type="text" id="%1$s" name="%1$s" value="%2$s" class="regular-text" />',
			esc_attr( $args['name'] ),
			esc_attr( (string) $value )
		);

		if ( isset( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
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
}
