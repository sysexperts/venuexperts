<?php
/**
 * Plugin-Deaktivierung.
 *
 * @package KulturhausEvents
 * @since   1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Deactivator
 */
class KH_Deactivator {

	/**
	 * Aktionen bei Plugin-Deaktivierung.
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
