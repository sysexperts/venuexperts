<?php
/**
 * Custom Post Type: Veranstaltungsort (kh_venue).
 *
 * @package KulturhausEvents
 * @since   1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Venue
 *
 * Registriert den Custom Post Type für Veranstaltungsorte.
 */
class KH_Venue {

	/**
	 * Post Type Slug.
	 */
	public const POST_TYPE = 'kh_venue';

	/**
	 * Custom Post Type registrieren.
	 */
	public function register(): void {
		$labels = array(
			'name'               => __( 'Veranstaltungsorte', 'kulturhaus-events' ),
			'singular_name'      => __( 'Veranstaltungsort', 'kulturhaus-events' ),
			'menu_name'          => __( 'Veranstaltungsorte', 'kulturhaus-events' ),
			'add_new'            => __( 'Neuer Ort', 'kulturhaus-events' ),
			'add_new_item'       => __( 'Neuen Veranstaltungsort erstellen', 'kulturhaus-events' ),
			'edit_item'          => __( 'Veranstaltungsort bearbeiten', 'kulturhaus-events' ),
			'view_item'          => __( 'Veranstaltungsort ansehen', 'kulturhaus-events' ),
			'all_items'          => __( 'Alle Orte', 'kulturhaus-events' ),
			'search_items'       => __( 'Orte suchen', 'kulturhaus-events' ),
			'not_found'          => __( 'Keine Veranstaltungsorte gefunden.', 'kulturhaus-events' ),
			'not_found_in_trash' => __( 'Keine Veranstaltungsorte im Papierkorb.', 'kulturhaus-events' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=kh_event',
			'show_in_rest'        => true,
			'rest_base'           => 'venues',
			'query_var'           => true,
			'rewrite'             => array(
				'slug'       => 'veranstaltungsorte',
				'with_front' => false,
			),
			'capability_type'     => 'post',
			'has_archive'         => true,
			'hierarchical'        => false,
			'menu_icon'           => 'dashicons-location',
			'supports'            => array(
				'title',
				'editor',
				'thumbnail',
			),
		);

		register_post_type( self::POST_TYPE, $args );
	}
}
