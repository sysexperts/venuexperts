<?php
/**
 * Plugin Name:       Kulturhaus Events
 * Plugin URI:        https://github.com/sysexperts/venuexperts
 * Description:       Professionelles Veranstaltungsmanagement für behördliche und kulturelle Einrichtungen. DSGVO-konform, barrierefrei (BITV 2.0 / WCAG 2.1 AA).
 * Version:           1.20.3
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            SysExperts
 * Author URI:        https://github.com/sysexperts
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       kulturhaus-events
 * Domain Path:       /languages
 *
 * @package KulturhausEvents
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin-Konstanten.
 */
define( 'KH_EVENTS_VERSION', '1.20.3' );
define( 'KH_EVENTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'KH_EVENTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'KH_EVENTS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Autoloader für Plugin-Klassen.
 *
 * Lädt Klassen aus dem includes/-Verzeichnis basierend auf dem Klassennamen.
 * Beispiel: KH_Event_Meta → includes/meta/class-event-meta.php
 *
 * @param string $class_name Der vollqualifizierte Klassenname.
 */
spl_autoload_register( function ( string $class_name ): void {
	$prefix = 'KH_';

	if ( strpos( $class_name, $prefix ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class_name, strlen( $prefix ) );

	$subdirectories = array(
		'post-types',
		'taxonomies',
		'meta',
		'admin',
		'frontend',
		'api',
		'widgets',
		'export',
		'helpers',
	);

	$file_name = 'class-' . strtolower( str_replace( '_', '-', $relative_class ) ) . '.php';

	// Zuerst im includes/-Hauptverzeichnis suchen.
	$file = KH_EVENTS_PLUGIN_DIR . 'includes/' . $file_name;
	if ( file_exists( $file ) ) {
		require_once $file;
		return;
	}

	// Dann in Unterverzeichnissen suchen.
	foreach ( $subdirectories as $subdir ) {
		$file = KH_EVENTS_PLUGIN_DIR . 'includes/' . $subdir . '/' . $file_name;
		if ( file_exists( $file ) ) {
			require_once $file;
			return;
		}
	}
} );

/**
 * Plugin-Aktivierung.
 */
register_activation_hook( __FILE__, function (): void {
	require_once KH_EVENTS_PLUGIN_DIR . 'includes/class-activator.php';
	require_once KH_EVENTS_PLUGIN_DIR . 'includes/post-types/class-event.php';
	require_once KH_EVENTS_PLUGIN_DIR . 'includes/post-types/class-venue.php';
	require_once KH_EVENTS_PLUGIN_DIR . 'includes/post-types/class-organizer.php';
	require_once KH_EVENTS_PLUGIN_DIR . 'includes/post-types/class-pricing-model.php';
	require_once KH_EVENTS_PLUGIN_DIR . 'includes/taxonomies/class-event-category.php';
	require_once KH_EVENTS_PLUGIN_DIR . 'includes/taxonomies/class-event-tag.php';
	KH_Activator::activate();
} );

/**
 * Plugin-Deaktivierung.
 */
register_deactivation_hook( __FILE__, function (): void {
	require_once KH_EVENTS_PLUGIN_DIR . 'includes/class-deactivator.php';
	KH_Deactivator::deactivate();
} );

/**
 * Plugin initialisieren.
 */
function kh_events_init(): void {
	$plugin = new KH_Plugin();
	$plugin->run();
	
	// AJAX Handler
	$ajax = new KH_Ajax();
	$ajax->register();

	// Program Filter AJAX Handler
	$program_filter = new KH_Ajax_Program_Filter();
	$program_filter->register();
}

add_action( 'plugins_loaded', 'kh_events_init' );
