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
		$this->register_frontend();
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
		$event     = new KH_Event();
		$venue     = new KH_Venue();
		$organizer = new KH_Organizer();

		add_action( 'init', array( $event, 'register' ) );
		add_action( 'init', array( $venue, 'register' ) );
		add_action( 'init', array( $organizer, 'register' ) );
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
		$event_meta     = new KH_Event_Meta();
		$venue_meta     = new KH_Venue_Meta();
		$organizer_meta = new KH_Organizer_Meta();

		add_action( 'add_meta_boxes', array( $event_meta, 'add_meta_boxes' ) );
		add_action( 'save_post_kh_event', array( $event_meta, 'save' ), 10, 2 );

		add_action( 'add_meta_boxes', array( $venue_meta, 'add_meta_boxes' ) );
		add_action( 'save_post_kh_venue', array( $venue_meta, 'save' ), 10, 2 );

		add_action( 'add_meta_boxes', array( $organizer_meta, 'add_meta_boxes' ) );
		add_action( 'save_post_kh_organizer', array( $organizer_meta, 'save' ), 10, 2 );
	}

	/**
	 * Admin-Bereich registrieren.
	 */
	private function register_admin(): void {
		if ( ! is_admin() ) {
			return;
		}

		$admin_columns = new KH_Admin_Columns();
		$admin_page    = new KH_Admin_Page();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		$admin_columns->register();
		$admin_page->register();
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
		if ( ! is_singular( 'kh_event' ) && ! is_post_type_archive( 'kh_event' ) ) {
			return;
		}

		wp_enqueue_style(
			'kh-frontend',
			KH_EVENTS_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			KH_EVENTS_VERSION
		);

		wp_enqueue_script(
			'kh-frontend',
			KH_EVENTS_PLUGIN_URL . 'assets/js/frontend.js',
			array(),
			KH_EVENTS_VERSION,
			true
		);
	}
}
