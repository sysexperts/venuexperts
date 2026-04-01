<?php
/**
 * Meta-Felder für Veranstalter.
 *
 * @package KulturhausEvents
 * @since   1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Organizer_Meta
 */
class KH_Organizer_Meta {

	/**
	 * Nonce-Action.
	 */
	private const NONCE_ACTION = 'kh_organizer_meta_nonce_action';

	/**
	 * Nonce-Feldname.
	 */
	private const NONCE_NAME = 'kh_organizer_meta_nonce';

	/**
	 * Meta-Felder Definition.
	 *
	 * @return array<string, array{label: string, type: string, sanitize: callable}>
	 */
	public static function get_fields(): array {
		return array(
			'_kh_org_email'   => array(
				'label'    => __( 'E-Mail-Adresse', 'kulturhaus-events' ),
				'type'     => 'email',
				'sanitize' => 'sanitize_email',
			),
			'_kh_org_phone'   => array(
				'label'    => __( 'Telefon', 'kulturhaus-events' ),
				'type'     => 'tel',
				'sanitize' => 'sanitize_text_field',
			),
			'_kh_org_website' => array(
				'label'    => __( 'Website', 'kulturhaus-events' ),
				'type'     => 'url',
				'sanitize' => 'esc_url_raw',
			),
			'_kh_org_role'    => array(
				'label'    => __( 'Funktion / Rolle', 'kulturhaus-events' ),
				'type'     => 'text',
				'sanitize' => 'sanitize_text_field',
			),
		);
	}

	/**
	 * Meta-Boxen registrieren.
	 */
	public function add_meta_boxes(): void {
		add_meta_box(
			'kh_organizer_details',
			__( 'Kontaktdaten', 'kulturhaus-events' ),
			array( $this, 'render' ),
			KH_Organizer::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Meta-Box rendern.
	 *
	 * @param \WP_Post $post Das aktuelle Post-Objekt.
	 */
	public function render( \WP_Post $post ): void {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		echo '<table class="form-table kh-meta-table" role="presentation"><tbody>';

		foreach ( self::get_fields() as $key => $field ) {
			$value = get_post_meta( $post->ID, $key, true );
			$id    = esc_attr( $key );
			$label = esc_html( $field['label'] );

			printf(
				'<tr><th><label for="%1$s">%2$s</label></th><td><input type="%3$s" id="%1$s" name="%1$s" value="%4$s" class="regular-text" /></td></tr>',
				$id,
				$label,
				esc_attr( $field['type'] ),
				'url' === $field['type'] ? esc_url( (string) $value ) : esc_attr( (string) $value )
			);
		}

		echo '</tbody></table>';
	}

	/**
	 * Meta-Daten speichern.
	 *
	 * @param int      $post_id Die Post-ID.
	 * @param \WP_Post $post    Das Post-Objekt.
	 */
	public function save( int $post_id, \WP_Post $post ): void {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		foreach ( self::get_fields() as $key => $field ) {
			if ( isset( $_POST[ $key ] ) ) {
				$sanitize = $field['sanitize'];
				$value    = call_user_func( $sanitize, wp_unslash( $_POST[ $key ] ) );
				update_post_meta( $post_id, $key, $value );
			}
		}
	}
}
