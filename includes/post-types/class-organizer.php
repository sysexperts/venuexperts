<?php
/**
 * Custom Post Type: Veranstalter (kh_organizer).
 *
 * @package KulturhausEvents
 * @since   1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Organizer
 *
 * Registriert den Custom Post Type für Veranstalter.
 */
class KH_Organizer {

	/**
	 * Post Type Slug.
	 */
	public const POST_TYPE = 'kh_organizer';

	/**
	 * Custom Post Type registrieren.
	 */
	public function register(): void {
		$labels = array(
			'name'               => __( 'Veranstalter', 'kulturhaus-events' ),
			'singular_name'      => __( 'Veranstalter', 'kulturhaus-events' ),
			'menu_name'          => __( 'Veranstalter', 'kulturhaus-events' ),
			'add_new'            => __( 'Neuer Veranstalter', 'kulturhaus-events' ),
			'add_new_item'       => __( 'Neuen Veranstalter erstellen', 'kulturhaus-events' ),
			'edit_item'          => __( 'Veranstalter bearbeiten', 'kulturhaus-events' ),
			'view_item'          => __( 'Veranstalter ansehen', 'kulturhaus-events' ),
			'all_items'          => __( 'Alle Veranstalter', 'kulturhaus-events' ),
			'search_items'       => __( 'Veranstalter suchen', 'kulturhaus-events' ),
			'not_found'          => __( 'Keine Veranstalter gefunden.', 'kulturhaus-events' ),
			'not_found_in_trash' => __( 'Keine Veranstalter im Papierkorb.', 'kulturhaus-events' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=kh_event',
			'show_in_rest'        => true,
			'rest_base'           => 'organizers',
			'query_var'           => true,
			'rewrite'             => array(
				'slug'       => 'veranstalter',
				'with_front' => false,
			),
			'capability_type'     => 'post',
			'has_archive'         => true,
			'hierarchical'        => false,
			'menu_icon'           => 'dashicons-groups',
			'supports'            => array(
				'title',
				'editor',
				'thumbnail',
			),
		);

		register_post_type( self::POST_TYPE, $args );
	}
}
