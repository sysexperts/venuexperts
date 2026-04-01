<?php
/**
 * Custom Post Type: Veranstaltung (kh_event).
 *
 * @package KulturhausEvents
 * @since   1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Event
 *
 * Registriert den Custom Post Type für Veranstaltungen.
 */
class KH_Event {

	/**
	 * Post Type Slug.
	 */
	public const POST_TYPE = 'kh_event';

	/**
	 * Custom Post Type registrieren.
	 */
	public function register(): void {
		$labels = array(
			'name'                  => __( 'Veranstaltungen', 'kulturhaus-events' ),
			'singular_name'         => __( 'Veranstaltung', 'kulturhaus-events' ),
			'menu_name'             => __( 'Veranstaltungen', 'kulturhaus-events' ),
			'name_admin_bar'        => __( 'Veranstaltung', 'kulturhaus-events' ),
			'add_new'               => __( 'Neue Veranstaltung', 'kulturhaus-events' ),
			'add_new_item'          => __( 'Neue Veranstaltung erstellen', 'kulturhaus-events' ),
			'new_item'              => __( 'Neue Veranstaltung', 'kulturhaus-events' ),
			'edit_item'             => __( 'Veranstaltung bearbeiten', 'kulturhaus-events' ),
			'view_item'             => __( 'Veranstaltung ansehen', 'kulturhaus-events' ),
			'all_items'             => __( 'Alle Veranstaltungen', 'kulturhaus-events' ),
			'search_items'          => __( 'Veranstaltungen suchen', 'kulturhaus-events' ),
			'not_found'             => __( 'Keine Veranstaltungen gefunden.', 'kulturhaus-events' ),
			'not_found_in_trash'    => __( 'Keine Veranstaltungen im Papierkorb.', 'kulturhaus-events' ),
			'featured_image'        => __( 'Veranstaltungsbild', 'kulturhaus-events' ),
			'set_featured_image'    => __( 'Veranstaltungsbild festlegen', 'kulturhaus-events' ),
			'remove_featured_image' => __( 'Veranstaltungsbild entfernen', 'kulturhaus-events' ),
			'archives'              => __( 'Veranstaltungsarchiv', 'kulturhaus-events' ),
			'filter_items_list'     => __( 'Veranstaltungsliste filtern', 'kulturhaus-events' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_rest'        => true,
			'rest_base'           => 'events',
			'query_var'           => true,
			'rewrite'             => array(
				'slug'       => 'veranstaltungen',
				'with_front' => false,
			),
			'capability_type'     => 'post',
			'has_archive'         => true,
			'hierarchical'        => false,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-calendar-alt',
			'supports'            => array(
				'title',
				'editor',
				'thumbnail',
				'excerpt',
				'revisions',
				'custom-fields',
			),
			'taxonomies'          => array( 'kh_event_cat', 'kh_event_tag' ),
		);

		register_post_type( self::POST_TYPE, $args );
	}
}
