<?php
/**
 * Taxonomie: Veranstaltungskategorie (kh_event_cat).
 *
 * @package KulturhausEvents
 * @since   1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Event_Category
 */
class KH_Event_Category {

	/**
	 * Taxonomie Slug.
	 */
	public const TAXONOMY = 'kh_event_cat';

	/**
	 * Taxonomie registrieren.
	 */
	public function register(): void {
		$labels = array(
			'name'              => __( 'Veranstaltungskategorien', 'kulturhaus-events' ),
			'singular_name'     => __( 'Kategorie', 'kulturhaus-events' ),
			'search_items'      => __( 'Kategorien suchen', 'kulturhaus-events' ),
			'all_items'         => __( 'Alle Kategorien', 'kulturhaus-events' ),
			'parent_item'       => __( 'Übergeordnete Kategorie', 'kulturhaus-events' ),
			'parent_item_colon' => __( 'Übergeordnete Kategorie:', 'kulturhaus-events' ),
			'edit_item'         => __( 'Kategorie bearbeiten', 'kulturhaus-events' ),
			'update_item'       => __( 'Kategorie aktualisieren', 'kulturhaus-events' ),
			'add_new_item'      => __( 'Neue Kategorie erstellen', 'kulturhaus-events' ),
			'new_item_name'     => __( 'Neuer Kategoriename', 'kulturhaus-events' ),
			'menu_name'         => __( 'Kategorien', 'kulturhaus-events' ),
			'not_found'         => __( 'Keine Kategorien gefunden.', 'kulturhaus-events' ),
		);

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_in_rest'      => true,
			'rest_base'         => 'event-categories',
			'rewrite'           => array(
				'slug'         => 'veranstaltungen/kategorie',
				'with_front'   => false,
				'hierarchical' => true,
			),
		);

		register_taxonomy( self::TAXONOMY, KH_Event::POST_TYPE, $args );
	}
}
