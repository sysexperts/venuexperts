<?php
/**
 * Template-Loader.
 *
 * Ermöglicht das Überschreiben von Plugin-Templates im aktiven Theme.
 *
 * @package KulturhausEvents
 * @since   1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Template_Loader
 *
 * Sucht Templates zuerst im Theme-Verzeichnis (kulturhaus-events/),
 * dann im Plugin-Verzeichnis (templates/).
 */
class KH_Template_Loader {

	/**
	 * Single-Template für Events laden.
	 *
	 * @param string $template Der Standard-Template-Pfad.
	 * @return string Der angepasste Template-Pfad.
	 */
	public function load_single_template( string $template ): string {
		if ( is_singular( KH_Event::POST_TYPE ) ) {
			$template = $this->locate_template( 'single-event.php', $template );
		}

		return $template;
	}

	/**
	 * Archiv-Template für Events laden.
	 *
	 * @param string $template Der Standard-Template-Pfad.
	 * @return string Der angepasste Template-Pfad.
	 */
	public function load_archive_template( string $template ): string {
		if ( is_post_type_archive( KH_Event::POST_TYPE ) ) {
			$template = $this->locate_template( 'archive-event.php', $template );
		}

		return $template;
	}

	/**
	 * Template im Theme oder Plugin suchen.
	 *
	 * Reihenfolge:
	 * 1. Theme: kulturhaus-events/{template}
	 * 2. Plugin: templates/{template}
	 *
	 * @param string $template_name Der Template-Dateiname.
	 * @param string $fallback      Fallback-Template.
	 * @return string Der Template-Pfad.
	 */
	public function locate_template( string $template_name, string $fallback = '' ): string {
		// Zuerst im Theme suchen.
		$theme_template = locate_template( 'kulturhaus-events/' . $template_name );

		if ( $theme_template ) {
			return $theme_template;
		}

		// Dann im Plugin.
		$plugin_template = KH_EVENTS_PLUGIN_DIR . 'templates/' . $template_name;

		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}

		return $fallback;
	}

	/**
	 * Template-Part laden (für Partials).
	 *
	 * @param string               $template_name Der Template-Name (ohne .php).
	 * @param array<string, mixed> $args          Variablen für das Template.
	 */
	public static function get_template_part( string $template_name, array $args = array() ): void {
		$loader   = new self();
		$template = $loader->locate_template( $template_name . '.php' );

		if ( ! $template ) {
			return;
		}

		if ( $args ) {
			// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Template-Variablen.
			extract( $args );
		}

		include $template;
	}
}
