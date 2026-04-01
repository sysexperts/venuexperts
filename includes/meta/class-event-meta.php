<?php
/**
 * Meta-Felder für Veranstaltungen.
 *
 * @package KulturhausEvents
 * @since   1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Event_Meta
 *
 * Verwaltet die Meta-Boxen und Meta-Felder für den Event Post Type.
 */
class KH_Event_Meta {

	/**
	 * Nonce-Action für Formularsicherheit.
	 */
	private const NONCE_ACTION = 'kh_event_meta_nonce_action';

	/**
	 * Nonce-Feldname.
	 */
	private const NONCE_NAME = 'kh_event_meta_nonce';

	/**
	 * Alle Meta-Felder mit Sanitization-Callbacks.
	 *
	 * @return array<string, array{label: string, type: string, sanitize: callable}>
	 */
	public static function get_fields(): array {
		return array(
			'_kh_event_start_date'     => array(
				'label'    => __( 'Startdatum', 'kulturhaus-events' ),
				'type'     => 'datetime-local',
				'sanitize' => 'sanitize_text_field',
			),
			'_kh_event_end_date'       => array(
				'label'    => __( 'Enddatum', 'kulturhaus-events' ),
				'type'     => 'datetime-local',
				'sanitize' => 'sanitize_text_field',
			),
			'_kh_event_all_day'        => array(
				'label'    => __( 'Ganztägig', 'kulturhaus-events' ),
				'type'     => 'checkbox',
				'sanitize' => 'absint',
			),
			'_kh_event_venue_id'       => array(
				'label'    => __( 'Veranstaltungsort', 'kulturhaus-events' ),
				'type'     => 'venue_select',
				'sanitize' => 'absint',
			),
			'_kh_event_organizer_id'   => array(
				'label'    => __( 'Veranstalter', 'kulturhaus-events' ),
				'type'     => 'organizer_select',
				'sanitize' => 'absint',
			),
			'_kh_event_cost'           => array(
				'label'    => __( 'Eintritt', 'kulturhaus-events' ),
				'type'     => 'text',
				'sanitize' => 'sanitize_text_field',
			),
			'_kh_event_cost_free'      => array(
				'label'    => __( 'Eintritt frei', 'kulturhaus-events' ),
				'type'     => 'checkbox',
				'sanitize' => 'absint',
			),
			'_kh_event_url'            => array(
				'label'    => __( 'Website / Ticketlink', 'kulturhaus-events' ),
				'type'     => 'url',
				'sanitize' => 'esc_url_raw',
			),
			'_kh_event_status'         => array(
				'label'    => __( 'Status', 'kulturhaus-events' ),
				'type'     => 'select',
				'sanitize' => 'sanitize_text_field',
				'options'  => array(
					'scheduled' => 'Geplant',
					'cancelled' => 'Abgesagt',
					'postponed' => 'Verschoben',
					'soldout'   => 'Ausverkauft',
				),
			),
			'_kh_event_featured'       => array(
				'label'    => __( 'Hervorgehoben', 'kulturhaus-events' ),
				'type'     => 'checkbox',
				'sanitize' => 'absint',
			),
			'_kh_event_accessibility'  => array(
				'label'    => __( 'Barrierefreiheits-Hinweise', 'kulturhaus-events' ),
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
			'kh_event_details',
			__( 'Veranstaltungsdetails', 'kulturhaus-events' ),
			array( $this, 'render_details_meta_box' ),
			KH_Event::POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'kh_event_location',
			__( 'Ort & Veranstalter', 'kulturhaus-events' ),
			array( $this, 'render_location_meta_box' ),
			KH_Event::POST_TYPE,
			'side',
			'default'
		);
	}

	/**
	 * Meta-Box: Veranstaltungsdetails rendern.
	 *
	 * @param \WP_Post $post Das aktuelle Post-Objekt.
	 */
	public function render_details_meta_box( \WP_Post $post ): void {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		$fields = self::get_fields();
		?>
		<table class="form-table kh-meta-table" role="presentation">
			<tbody>
				<?php $this->render_field( $post, '_kh_event_start_date', $fields['_kh_event_start_date'] ); ?>
				<?php $this->render_field( $post, '_kh_event_end_date', $fields['_kh_event_end_date'] ); ?>
				<?php $this->render_field( $post, '_kh_event_all_day', $fields['_kh_event_all_day'] ); ?>
				<?php $this->render_field( $post, '_kh_event_cost', $fields['_kh_event_cost'] ); ?>
				<?php $this->render_field( $post, '_kh_event_cost_free', $fields['_kh_event_cost_free'] ); ?>
				<?php $this->render_field( $post, '_kh_event_url', $fields['_kh_event_url'] ); ?>
				<?php $this->render_field( $post, '_kh_event_status', $fields['_kh_event_status'] ); ?>
				<?php $this->render_field( $post, '_kh_event_featured', $fields['_kh_event_featured'] ); ?>
				<?php $this->render_field( $post, '_kh_event_accessibility', $fields['_kh_event_accessibility'] ); ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Meta-Box: Ort & Veranstalter rendern.
	 *
	 * @param \WP_Post $post Das aktuelle Post-Objekt.
	 */
	public function render_location_meta_box( \WP_Post $post ): void {
		$fields = self::get_fields();

		$this->render_field( $post, '_kh_event_venue_id', $fields['_kh_event_venue_id'] );
		$this->render_field( $post, '_kh_event_organizer_id', $fields['_kh_event_organizer_id'] );
	}

	/**
	 * Einzelnes Feld rendern.
	 *
	 * @param \WP_Post             $post  Das aktuelle Post-Objekt.
	 * @param string               $key   Der Meta-Key.
	 * @param array<string, mixed> $field Die Feld-Konfiguration.
	 */
	private function render_field( \WP_Post $post, string $key, array $field ): void {
		$value = get_post_meta( $post->ID, $key, true );
		$id    = esc_attr( $key );
		$label = esc_html( $field['label'] );

		switch ( $field['type'] ) {
			case 'datetime-local':
				$formatted = '';
				if ( $value ) {
					$formatted = gmdate( 'Y-m-d\TH:i', strtotime( $value ) );
				}
				printf(
					'<tr><th><label for="%1$s">%2$s</label></th><td><input type="datetime-local" id="%1$s" name="%1$s" value="%3$s" class="regular-text" /></td></tr>',
					$id,
					$label,
					esc_attr( $formatted )
				);
				break;

			case 'text':
				printf(
					'<tr><th><label for="%1$s">%2$s</label></th><td><input type="text" id="%1$s" name="%1$s" value="%3$s" class="regular-text" /></td></tr>',
					$id,
					$label,
					esc_attr( (string) $value )
				);
				break;

			case 'url':
				printf(
					'<tr><th><label for="%1$s">%2$s</label></th><td><input type="url" id="%1$s" name="%1$s" value="%3$s" class="regular-text" placeholder="https://" /></td></tr>',
					$id,
					$label,
					esc_url( (string) $value )
				);
				break;

			case 'textarea':
				printf(
					'<tr><th><label for="%1$s">%2$s</label></th><td><textarea id="%1$s" name="%1$s" rows="3" class="large-text">%3$s</textarea></td></tr>',
					$id,
					$label,
					esc_textarea( (string) $value )
				);
				break;

			case 'checkbox':
				printf(
					'<tr><th>%2$s</th><td><label><input type="checkbox" id="%1$s" name="%1$s" value="1" %3$s /> %2$s</label></td></tr>',
					$id,
					$label,
					checked( $value, '1', false )
				);
				break;

			case 'select':
				$options_html = '<option value="">' . esc_html__( '— Auswählen —', 'kulturhaus-events' ) . '</option>';
				if ( isset( $field['options'] ) ) {
					foreach ( $field['options'] as $opt_value => $opt_label ) {
						$options_html .= sprintf(
							'<option value="%s" %s>%s</option>',
							esc_attr( $opt_value ),
							selected( $value, $opt_value, false ),
							esc_html( $opt_label )
						);
					}
				}
				printf(
					'<tr><th><label for="%1$s">%2$s</label></th><td><select id="%1$s" name="%1$s">%3$s</select></td></tr>',
					$id,
					$label,
					$options_html
				);
				break;

			case 'venue_select':
				$venues = get_posts(
					array(
						'post_type'      => KH_Venue::POST_TYPE,
						'posts_per_page' => -1,
						'orderby'        => 'title',
						'order'          => 'ASC',
						'post_status'    => 'publish',
					)
				);

				$options_html = '<option value="">' . esc_html__( '— Ort auswählen —', 'kulturhaus-events' ) . '</option>';
				foreach ( $venues as $venue ) {
					$options_html .= sprintf(
						'<option value="%d" %s>%s</option>',
						$venue->ID,
						selected( $value, $venue->ID, false ),
						esc_html( $venue->post_title )
					);
				}
				printf(
					'<p><label for="%1$s"><strong>%2$s</strong></label></p><select id="%1$s" name="%1$s" class="widefat">%3$s</select>',
					$id,
					$label,
					$options_html
				);
				break;

			case 'organizer_select':
				$organizers = get_posts(
					array(
						'post_type'      => KH_Organizer::POST_TYPE,
						'posts_per_page' => -1,
						'orderby'        => 'title',
						'order'          => 'ASC',
						'post_status'    => 'publish',
					)
				);

				$options_html = '<option value="">' . esc_html__( '— Veranstalter auswählen —', 'kulturhaus-events' ) . '</option>';
				foreach ( $organizers as $organizer ) {
					$options_html .= sprintf(
						'<option value="%d" %s>%s</option>',
						$organizer->ID,
						selected( $value, $organizer->ID, false ),
						esc_html( $organizer->post_title )
					);
				}
				printf(
					'<p><label for="%1$s"><strong>%2$s</strong></label></p><select id="%1$s" name="%1$s" class="widefat">%3$s</select>',
					$id,
					$label,
					$options_html
				);
				break;
		}
	}

	/**
	 * Meta-Daten speichern.
	 *
	 * @param int      $post_id Die Post-ID.
	 * @param \WP_Post $post    Das Post-Objekt.
	 */
	public function save( int $post_id, \WP_Post $post ): void {
		// Nonce prüfen.
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}

		// Autosave ignorieren.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Berechtigungen prüfen.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Felder speichern.
		foreach ( self::get_fields() as $key => $field ) {
			if ( 'checkbox' === $field['type'] ) {
				$value = isset( $_POST[ $key ] ) ? 1 : 0;
				update_post_meta( $post_id, $key, $value );
				continue;
			}

			if ( isset( $_POST[ $key ] ) ) {
				$sanitize = $field['sanitize'];
				$value    = call_user_func( $sanitize, wp_unslash( $_POST[ $key ] ) );

				// Datetime-Felder in einheitliches Format konvertieren.
				if ( 'datetime-local' === $field['type'] && $value ) {
					$value = gmdate( 'Y-m-d H:i:s', strtotime( $value ) );
				}

				update_post_meta( $post_id, $key, $value );
			}
		}
	}
}
