<?php
/**
 * Haupt-Plugin-Klasse.
 *
 * Initialisiert alle Komponenten des Plugins.
 *
 * @package KulturhausEvents
 * @since   1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Plugin
 *
 * Bootstrap-Klasse, die alle Module registriert und startet.
 */
class KH_Plugin {

	/**
	 * Plugin starten und alle Hooks registrieren.
	 */
	public function run(): void {
		$this->load_textdomain();
		$this->register_post_types();
		$this->register_taxonomies();
		$this->register_meta();
		$this->register_admin();
		$this->register_customizer();
		$this->register_duplicate_event();
		$this->register_frontend();
		$this->register_shortcodes();
		$this->register_calendar();
		$this->register_widgets();
		$this->register_ical_export();
		$this->register_import_export();
		$this->register_query_filters();
	}

	/**
	 * Übersetzungen laden.
	 */
	private function load_textdomain(): void {
		add_action( 'init', function (): void {
			load_plugin_textdomain(
				'kulturhaus-events',
				false,
				dirname( KH_EVENTS_PLUGIN_BASENAME ) . '/languages'
			);
		} );
	}

	/**
	 * Custom Post Types registrieren.
	 */
	private function register_post_types(): void {
		$event         = new KH_Event();
		$venue         = new KH_Venue();
		$organizer     = new KH_Organizer();
		$pricing_model = new KH_Pricing_Model();

		add_action( 'init', array( $event, 'register' ) );
		add_action( 'init', array( $venue, 'register' ) );
		add_action( 'init', array( $organizer, 'register' ) );
		add_action( 'init', array( $pricing_model, 'register' ) );
	}

	/**
	 * Taxonomien registrieren.
	 */
	private function register_taxonomies(): void {
		$category = new KH_Event_Category();
		$tag      = new KH_Event_Tag();

		add_action( 'init', array( $category, 'register' ) );
		add_action( 'init', array( $tag, 'register' ) );
	}

	/**
	 * Meta-Boxen und Meta-Felder registrieren.
	 */
	private function register_meta(): void {
		$event_meta          = new KH_Event_Meta();
		$venue_meta          = new KH_Venue_Meta();
		$organizer_meta      = new KH_Organizer_Meta();
		$pricing_model_meta  = new KH_Pricing_Model_Meta();

		add_action( 'add_meta_boxes', array( $event_meta, 'add_meta_boxes' ) );
		add_action( 'save_post_' . KH_Event::POST_TYPE, array( $event_meta, 'save_meta' ), 10, 2 );

		add_action( 'add_meta_boxes', array( $venue_meta, 'add_meta_boxes' ) );
		add_action( 'save_post_' . KH_Venue::POST_TYPE, array( $venue_meta, 'save_meta' ), 10, 2 );

		add_action( 'add_meta_boxes', array( $organizer_meta, 'add_meta_boxes' ) );
		add_action( 'save_post_' . KH_Organizer::POST_TYPE, array( $organizer_meta, 'save_meta' ), 10, 2 );

		$pricing_model_meta->register();
	}

	/**
	 * Admin-Bereich registrieren.
	 */
	private function register_admin(): void {
		if ( ! is_admin() ) {
			return;
		}

		$admin_columns   = new KH_Admin_Columns();
		$admin_page      = new KH_Admin_Page();
		$shortcode_help  = new KH_Shortcode_Help();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		$admin_columns->register();
		$admin_page->register();
		$shortcode_help->register();
	}

	/**
	 * Customizer registrieren.
	 */
	private function register_customizer(): void {
		$customizer = new KH_Customizer();
		$customizer->register();
	}

	/**
	 * Event-Duplizierung registrieren.
	 */
	private function register_duplicate_event(): void {
		if ( ! is_admin() ) {
			return;
		}

		$duplicate_event = new KH_Duplicate_Event();
		$duplicate_event->register();
	}

	/**
	 * Frontend registrieren.
	 */
	private function register_frontend(): void {
		$template_loader = new KH_Template_Loader();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_filter( 'single_template', array( $template_loader, 'load_single_template' ) );
		add_filter( 'archive_template', array( $template_loader, 'load_archive_template' ) );
	}

	/**
	 * Admin-Assets (CSS/JS) einbinden.
	 *
	 * @param string $hook_suffix Die aktuelle Admin-Seite.
	 */
	public function enqueue_admin_assets( string $hook_suffix ): void {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return;
		}

		$allowed_types = array( 'kh_event', 'kh_venue', 'kh_organizer' );

		if ( ! in_array( $screen->post_type, $allowed_types, true ) ) {
			return;
		}

		wp_enqueue_style(
			'kh-admin',
			KH_EVENTS_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			KH_EVENTS_VERSION
		);

		wp_enqueue_script(
			'kh-admin',
			KH_EVENTS_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			KH_EVENTS_VERSION,
			true
		);
	}

	/**
	 * Frontend-Assets (CSS/JS) einbinden.
	 */
	public function enqueue_frontend_assets(): void {
		// Highlights CSS immer laden (für Shortcodes auf allen Seiten)
		wp_enqueue_style(
			'kh-events-highlights',
			KH_EVENTS_PLUGIN_URL . 'assets/css/highlights.css',
			array(),
			KH_EVENTS_VERSION,
			'all'
		);

		// Calendar CSS & JS immer laden (für Shortcodes auf allen Seiten)
		wp_enqueue_style(
			'kh-events-calendar',
			KH_EVENTS_PLUGIN_URL . 'assets/css/calendar.css',
			array(),
			KH_EVENTS_VERSION,
			'all'
		);

		wp_enqueue_script(
			'kh-events-calendar',
			KH_EVENTS_PLUGIN_URL . 'assets/js/calendar.js',
			array( 'jquery' ),
			KH_EVENTS_VERSION,
			true
		);

		wp_localize_script(
			'kh-events-calendar',
			'khCalendar',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'kh_calendar_nonce' ),
			)
		);

		// Program CSS immer laden (für Shortcodes auf allen Seiten)
		wp_enqueue_style(
			'kh-events-program',
			KH_EVENTS_PLUGIN_URL . 'assets/css/program.css',
			array(),
			KH_EVENTS_VERSION,
			'all'
		);

		// Program JS für Filter
		wp_enqueue_script(
			'kh-events-program',
			KH_EVENTS_PLUGIN_URL . 'assets/js/program.js',
			array( 'jquery' ),
			KH_EVENTS_VERSION,
			true
		);

		// AJAX-Daten für Program JS
		wp_localize_script(
			'kh-events-program',
			'khProgramData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'kh_program_nonce' ),
			)
		);

		// Calendar CSS immer laden (für Kalender-Shortcode)
		wp_enqueue_style(
			'kh-events-calendar',
			KH_EVENTS_PLUGIN_URL . 'assets/css/calendar.css',
			array(),
			KH_EVENTS_VERSION,
			'all'
		);

		// Search CSS immer laden (für Such-Shortcode)
		wp_enqueue_style(
			'kh-events-search',
			KH_EVENTS_PLUGIN_URL . 'assets/css/search.css',
			array(),
			KH_EVENTS_VERSION,
			'all'
		);

		// Search JS immer laden
		wp_enqueue_script(
			'kh-events-search',
			KH_EVENTS_PLUGIN_URL . 'assets/js/search.js',
			array( 'jquery' ),
			KH_EVENTS_VERSION,
			true
		);

		// AJAX-Daten für Search JS
		wp_localize_script(
			'kh-events-search',
			'khSearchData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'kh_search_nonce' ),
			)
		);

		// Archive CSS auf Event-Archiv-Seiten
		if ( is_post_type_archive( 'kh_event' ) ) {
			wp_enqueue_style(
				'kh-events-archive',
				KH_EVENTS_PLUGIN_URL . 'assets/css/archive.css',
				array(),
				KH_EVENTS_VERSION,
				'all'
			);
		}

		// Andere CSS nur auf Event-Seiten
		if ( ! is_singular( 'kh_event' ) && ! is_post_type_archive( 'kh_event' ) ) {
			return;
		}

		wp_enqueue_style(
			'kh-events-frontend',
			KH_EVENTS_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			KH_EVENTS_VERSION,
			'all'
		);

		wp_enqueue_style(
			'kh-events-design',
			KH_EVENTS_PLUGIN_URL . 'assets/css/event-design.css',
			array(),
			KH_EVENTS_VERSION,
			'all'
		);

		wp_enqueue_script(
			'kh-frontend',
			KH_EVENTS_PLUGIN_URL . 'assets/js/frontend.js',
			array(),
			KH_EVENTS_VERSION,
			true
		);

		wp_enqueue_script(
			'kh-slider',
			KH_EVENTS_PLUGIN_URL . 'assets/js/slider.js',
			array(),
			KH_EVENTS_VERSION,
			true
		);
	}

	/**
	 * Shortcodes registrieren.
	 */
	private function register_shortcodes(): void {
		$shortcodes = new KH_Shortcodes();
		$shortcodes->register();
	}

	/**
	 * Kalender registrieren.
	 */
	private function register_calendar(): void {
		$calendar = new KH_Calendar();
		$calendar->register();
	}

	/**
	 * Widgets registrieren.
	 */
	private function register_widgets(): void {
		add_action( 'widgets_init', function (): void {
			register_widget( 'KH_Event_Filter_Widget' );
			register_widget( 'KH_Upcoming_Events_Widget' );
		} );
	}

	/**
	 * iCal-Export registrieren.
	 */
	private function register_ical_export(): void {
		$ical_export = new KH_ICal_Export();
		$ical_export->register();
	}

	/**
	 * Import/Export registrieren.
	 */
	private function register_import_export(): void {
		$import_export = new KH_Import_Export();
		$import_export->register();
	}

	/**
	 * Query-Filter registrieren.
	 */
	private function register_query_filters(): void {
		$filters = new KH_Query_Filters();
		$filters->register();
	}
}
