<?php
/**
 * Taxonomie: Veranstaltungs-Schlagwort (kh_event_tag).
 *
 * @package KulturhausEvents
 * @since   1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Event_Tag
 */
class KH_Event_Tag {

	/**
	 * Taxonomie Slug.
	 */
	public const TAXONOMY = 'kh_event_tag';

	/**
	 * Taxonomie registrieren.
	 */
	public function register(): void {
		$labels = array(
			'name'                       => __( 'Schlagwörter', 'kulturhaus-events' ),
			'singular_name'              => __( 'Schlagwort', 'kulturhaus-events' ),
			'search_items'               => __( 'Schlagwörter suchen', 'kulturhaus-events' ),
			'popular_items'              => __( 'Beliebte Schlagwörter', 'kulturhaus-events' ),
			'all_items'                  => __( 'Alle Schlagwörter', 'kulturhaus-events' ),
			'edit_item'                  => __( 'Schlagwort bearbeiten', 'kulturhaus-events' ),
			'update_item'                => __( 'Schlagwort aktualisieren', 'kulturhaus-events' ),
			'add_new_item'               => __( 'Neues Schlagwort', 'kulturhaus-events' ),
			'new_item_name'              => __( 'Neuer Schlagwortname', 'kulturhaus-events' ),
			'separate_items_with_commas' => __( 'Schlagwörter mit Kommas trennen', 'kulturhaus-events' ),
			'add_or_remove_items'        => __( 'Schlagwörter hinzufügen oder entfernen', 'kulturhaus-events' ),
			'choose_from_most_used'      => __( 'Aus häufig genutzten wählen', 'kulturhaus-events' ),
			'not_found'                  => __( 'Keine Schlagwörter gefunden.', 'kulturhaus-events' ),
			'menu_name'                  => __( 'Schlagwörter', 'kulturhaus-events' ),
		);

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => false,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_in_rest'      => true,
			'rest_base'         => 'event-tags',
			'rewrite'           => array(
				'slug'       => 'veranstaltungen/schlagwort',
				'with_front' => false,
			),
		);

		register_taxonomy( self::TAXONOMY, KH_Event::POST_TYPE, $args );
	}
}
