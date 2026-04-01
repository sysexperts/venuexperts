<?php
/**
 * Meta-Felder für Veranstaltungsorte.
 *
 * @package KulturhausEvents
 * @since   1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Venue_Meta
 */
class KH_Venue_Meta {

	/**
	 * Nonce-Action.
	 */
	private const NONCE_ACTION = 'kh_venue_meta_nonce_action';

	/**
	 * Nonce-Feldname.
	 */
	private const NONCE_NAME = 'kh_venue_meta_nonce';

	/**
	 * Meta-Felder Definition.
	 *
	 * @return array<string, array{label: string, type: string, sanitize: callable}>
	 */
	public static function get_fields(): array {
		return array(
			'_kh_venue_address'       => array(
				'label'    => __( 'Straße & Hausnummer', 'kulturhaus-events' ),
				'type'     => 'text',
				'sanitize' => 'sanitize_text_field',
			),
			'_kh_venue_zip'           => array(
				'label'    => __( 'Postleitzahl', 'kulturhaus-events' ),
				'type'     => 'text',
				'sanitize' => 'sanitize_text_field',
			),
			'_kh_venue_city'          => array(
				'label'    => __( 'Stadt', 'kulturhaus-events' ),
				'type'     => 'text',
				'sanitize' => 'sanitize_text_field',
			),
			'_kh_venue_country'       => array(
				'label'    => __( 'Land', 'kulturhaus-events' ),
				'type'     => 'text',
				'sanitize' => 'sanitize_text_field',
			),
			'_kh_venue_lat'           => array(
				'label'    => __( 'Breitengrad', 'kulturhaus-events' ),
				'type'     => 'text',
				'sanitize' => 'sanitize_text_field',
			),
			'_kh_venue_lng'           => array(
				'label'    => __( 'Längengrad', 'kulturhaus-events' ),
				'type'     => 'text',
				'sanitize' => 'sanitize_text_field',
			),
			'_kh_venue_phone'         => array(
				'label'    => __( 'Telefon', 'kulturhaus-events' ),
				'type'     => 'tel',
				'sanitize' => 'sanitize_text_field',
			),
			'_kh_venue_website'       => array(
				'label'    => __( 'Website', 'kulturhaus-events' ),
				'type'     => 'url',
				'sanitize' => 'esc_url_raw',
			),
			'_kh_venue_accessibility' => array(
				'label'    => __( 'Barrierefreiheit', 'kulturhaus-events' ),
				'type'     => 'textarea',
				'sanitize' => 'sanitize_textarea_field',
			),
		);
	}

	/**
	 * Meta-Boxen registrieren.
	 */
	public function add_meta_boxes(): void {
		add_meta_box(
			'kh_venue_details',
			__( 'Adresse & Kontakt', 'kulturhaus-events' ),
			array( $this, 'render' ),
			KH_Venue::POST_TYPE,
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

			echo '<tr>';
			printf( '<th><label for="%s">%s</label></th>', $id, $label );
			echo '<td>';

			switch ( $field['type'] ) {
				case 'textarea':
					printf(
						'<textarea id="%s" name="%s" rows="3" class="large-text">%s</textarea>',
						$id,
						$id,
						esc_textarea( (string) $value )
					);
					break;

				case 'url':
					printf(
						'<input type="url" id="%s" name="%s" value="%s" class="regular-text" placeholder="https://" />',
						$id,
						$id,
						esc_url( (string) $value )
					);
					break;

				default:
					printf(
						'<input type="%s" id="%s" name="%s" value="%s" class="regular-text" />',
						esc_attr( $field['type'] ),
						$id,
						$id,
						esc_attr( (string) $value )
					);
					break;
			}

			echo '</td></tr>';
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

	/**
	 * Meta-Daten speichern (Alias für save).
	 *
	 * @param int      $post_id Die Post-ID.
	 * @param \WP_Post $post    Das Post-Objekt.
	 */
	public function save_meta( int $post_id, \WP_Post $post ): void {
		$this->save( $post_id, $post );
	}
}
