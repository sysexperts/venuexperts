<?php
/**
 * Custom Post Type: Preismodell.
 *
 * @package KulturhausEvents
 * @since   1.2.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Pricing_Model
 *
 * Registriert den Custom Post Type für Preismodelle.
 */
class KH_Pricing_Model {

	/**
	 * Post Type Slug.
	 */
	public const POST_TYPE = 'kh_pricing_model';

	/**
	 * Registriert den Custom Post Type.
	 */
	public function register(): void {
		$labels = array(
			'name'                  => _x( 'Preismodelle', 'Post Type General Name', 'kulturhaus-events' ),
			'singular_name'         => _x( 'Preismodell', 'Post Type Singular Name', 'kulturhaus-events' ),
			'menu_name'             => __( 'Preismodelle', 'kulturhaus-events' ),
			'name_admin_bar'        => __( 'Preismodell', 'kulturhaus-events' ),
			'archives'              => __( 'Preismodell-Archive', 'kulturhaus-events' ),
			'attributes'            => __( 'Preismodell-Attribute', 'kulturhaus-events' ),
			'parent_item_colon'     => __( 'Übergeordnetes Preismodell:', 'kulturhaus-events' ),
			'all_items'             => __( 'Alle Preismodelle', 'kulturhaus-events' ),
			'add_new_item'          => __( 'Neues Preismodell hinzufügen', 'kulturhaus-events' ),
			'add_new'               => __( 'Neu hinzufügen', 'kulturhaus-events' ),
			'new_item'              => __( 'Neues Preismodell', 'kulturhaus-events' ),
			'edit_item'             => __( 'Preismodell bearbeiten', 'kulturhaus-events' ),
			'update_item'           => __( 'Preismodell aktualisieren', 'kulturhaus-events' ),
			'view_item'             => __( 'Preismodell ansehen', 'kulturhaus-events' ),
			'view_items'            => __( 'Preismodelle ansehen', 'kulturhaus-events' ),
			'search_items'          => __( 'Preismodell suchen', 'kulturhaus-events' ),
			'not_found'             => __( 'Nicht gefunden', 'kulturhaus-events' ),
			'not_found_in_trash'    => __( 'Nicht im Papierkorb gefunden', 'kulturhaus-events' ),
			'featured_image'        => __( 'Beitragsbild', 'kulturhaus-events' ),
			'set_featured_image'    => __( 'Beitragsbild festlegen', 'kulturhaus-events' ),
			'remove_featured_image' => __( 'Beitragsbild entfernen', 'kulturhaus-events' ),
			'use_featured_image'    => __( 'Als Beitragsbild verwenden', 'kulturhaus-events' ),
			'insert_into_item'      => __( 'In Preismodell einfügen', 'kulturhaus-events' ),
			'uploaded_to_this_item' => __( 'Zu diesem Preismodell hochgeladen', 'kulturhaus-events' ),
			'items_list'            => __( 'Preismodell-Liste', 'kulturhaus-events' ),
			'items_list_navigation' => __( 'Preismodell-Listennavigation', 'kulturhaus-events' ),
			'filter_items_list'     => __( 'Preismodell-Liste filtern', 'kulturhaus-events' ),
		);

		$args = array(
			'label'               => __( 'Preismodell', 'kulturhaus-events' ),
			'description'         => __( 'Preismodelle für Veranstaltungen', 'kulturhaus-events' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor' ),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=' . KH_Event::POST_TYPE,
			'menu_position'       => 6,
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'capability_type'     => 'post',
			'show_in_rest'        => false,
		);

		register_post_type( self::POST_TYPE, $args );
	}
}
