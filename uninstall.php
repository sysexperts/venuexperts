<?php
/**
 * Saubere Deinstallation des Plugins.
 *
 * Entfernt alle Plugin-Daten aus der Datenbank:
 * - Custom Post Types und deren Posts
 * - Taxonomien und Terme
 * - Plugin-Optionen
 * - Post-Meta-Daten
 *
 * @package KulturhausEvents
 * @since   1.0.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Alle Events, Venues und Organizer löschen.
$post_types = array( 'kh_event', 'kh_venue', 'kh_organizer' );

foreach ( $post_types as $post_type ) {
	$posts = get_posts(
		array(
			'post_type'      => $post_type,
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		)
	);

	foreach ( $posts as $post_id ) {
		wp_delete_post( $post_id, true );
	}
}

// Taxonomie-Terme löschen.
$taxonomies = array( 'kh_event_cat', 'kh_event_tag' );

foreach ( $taxonomies as $taxonomy ) {
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'fields'     => 'ids',
		)
	);

	if ( is_array( $terms ) ) {
		foreach ( $terms as $term_id ) {
			wp_delete_term( $term_id, $taxonomy );
		}
	}
}

// Plugin-Optionen entfernen.
$options = array(
	'kh_events_version',
	'kh_events_per_page',
	'kh_default_view',
	'kh_date_format',
	'kh_time_format',
	'kh_map_provider',
	'kh_enable_ical_export',
);

foreach ( $options as $option ) {
	delete_option( $option );
}

// Verwaiste Post-Meta-Daten aufräumen.
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_kh_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
