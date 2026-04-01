<?php
/**
 * Event Duplicate Functionality
 *
 * @package KulturhausEvents
 * @since   1.10.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Duplicate_Event
 *
 * Ermöglicht das Duplizieren von Veranstaltungen.
 */
class KH_Duplicate_Event {

	/**
	 * Hooks registrieren.
	 */
	public function register(): void {
		add_filter( 'post_row_actions', array( $this, 'add_duplicate_link' ), 10, 2 );
		add_action( 'admin_action_kh_duplicate_event', array( $this, 'duplicate_event' ) );
	}

	/**
	 * "Duplizieren"-Link zur Event-Liste hinzufügen.
	 *
	 * @param array<string, string> $actions Vorhandene Actions.
	 * @param WP_Post               $post    Post-Objekt.
	 * @return array<string, string> Modifizierte Actions.
	 */
	public function add_duplicate_link( array $actions, WP_Post $post ): array {
		if ( KH_Event::POST_TYPE !== $post->post_type ) {
			return $actions;
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return $actions;
		}

		$url = wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'kh_duplicate_event',
					'post'   => $post->ID,
				),
				admin_url( 'admin.php' )
			),
			'kh_duplicate_event_' . $post->ID
		);

		$actions['kh_duplicate'] = sprintf(
			'<a href="%s" title="%s">%s</a>',
			esc_url( $url ),
			esc_attr__( 'Veranstaltung duplizieren', 'kulturhaus-events' ),
			esc_html__( 'Duplizieren', 'kulturhaus-events' )
		);

		return $actions;
	}

	/**
	 * Event duplizieren.
	 */
	public function duplicate_event(): void {
		// Sicherheitsprüfungen.
		if ( ! isset( $_GET['post'] ) ) {
			wp_die( esc_html__( 'Keine Veranstaltung zum Duplizieren angegeben.', 'kulturhaus-events' ) );
		}

		$post_id = absint( $_GET['post'] );

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'kh_duplicate_event_' . $post_id ) ) {
			wp_die( esc_html__( 'Sicherheitsprüfung fehlgeschlagen.', 'kulturhaus-events' ) );
		}

		$post = get_post( $post_id );

		if ( ! $post || KH_Event::POST_TYPE !== $post->post_type ) {
			wp_die( esc_html__( 'Veranstaltung nicht gefunden.', 'kulturhaus-events' ) );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'Sie haben keine Berechtigung, diese Veranstaltung zu duplizieren.', 'kulturhaus-events' ) );
		}

		// Event duplizieren.
		$new_post_id = $this->create_duplicate( $post );

		if ( is_wp_error( $new_post_id ) ) {
			wp_die( esc_html( $new_post_id->get_error_message() ) );
		}

		// Zur Bearbeitungsseite des neuen Events weiterleiten.
		wp_safe_redirect(
			add_query_arg(
				array(
					'post'    => $new_post_id,
					'action'  => 'edit',
					'message' => 99, // Custom message.
				),
				admin_url( 'post.php' )
			)
		);
		exit;
	}

	/**
	 * Duplikat erstellen.
	 *
	 * @param WP_Post $post Original-Post.
	 * @return int|WP_Error Post-ID des Duplikats oder WP_Error.
	 */
	private function create_duplicate( WP_Post $post ) {
		$current_user = wp_get_current_user();

		// Neuen Post erstellen.
		$new_post_args = array(
			'post_title'     => $post->post_title . ' (' . __( 'Kopie', 'kulturhaus-events' ) . ')',
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_status'    => 'draft', // Als Entwurf speichern.
			'post_type'      => $post->post_type,
			'post_author'    => $current_user->ID,
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_parent'    => $post->post_parent,
			'menu_order'     => $post->menu_order,
		);

		$new_post_id = wp_insert_post( $new_post_args );

		if ( is_wp_error( $new_post_id ) ) {
			return $new_post_id;
		}

		// Taxonomien kopieren.
		$this->copy_taxonomies( $post->ID, $new_post_id );

		// Meta-Daten kopieren.
		$this->copy_meta_data( $post->ID, $new_post_id );

		// Featured Image kopieren.
		$this->copy_featured_image( $post->ID, $new_post_id );

		return $new_post_id;
	}

	/**
	 * Taxonomien kopieren.
	 *
	 * @param int $source_id Quell-Post-ID.
	 * @param int $target_id Ziel-Post-ID.
	 */
	private function copy_taxonomies( int $source_id, int $target_id ): void {
		$taxonomies = get_object_taxonomies( get_post_type( $source_id ) );

		foreach ( $taxonomies as $taxonomy ) {
			$terms = wp_get_object_terms( $source_id, $taxonomy, array( 'fields' => 'ids' ) );

			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				wp_set_object_terms( $target_id, $terms, $taxonomy );
			}
		}
	}

	/**
	 * Meta-Daten kopieren.
	 *
	 * @param int $source_id Quell-Post-ID.
	 * @param int $target_id Ziel-Post-ID.
	 */
	private function copy_meta_data( int $source_id, int $target_id ): void {
		$meta_data = get_post_meta( $source_id );

		foreach ( $meta_data as $key => $values ) {
			// Interne WordPress-Meta überspringen.
			if ( '_edit_lock' === $key || '_edit_last' === $key ) {
				continue;
			}

			foreach ( $values as $value ) {
				add_post_meta( $target_id, $key, maybe_unserialize( $value ) );
			}
		}
	}

	/**
	 * Featured Image kopieren.
	 *
	 * @param int $source_id Quell-Post-ID.
	 * @param int $target_id Ziel-Post-ID.
	 */
	private function copy_featured_image( int $source_id, int $target_id ): void {
		$thumbnail_id = get_post_thumbnail_id( $source_id );

		if ( $thumbnail_id ) {
			set_post_thumbnail( $target_id, $thumbnail_id );
		}
	}
}
