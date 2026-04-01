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
				'label'    => __( 'Eintritt (Legacy)', 'kulturhaus-events' ),
				'type'     => 'text',
				'sanitize' => 'sanitize_text_field',
			),
			'_kh_event_cost_free'      => array(
				'label'    => __( 'Eintritt frei (Legacy)', 'kulturhaus-events' ),
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
			'_kh_event_gallery'        => array(
				'label'    => __( 'Bildergalerie (für Slider)', 'kulturhaus-events' ),
				'type'     => 'gallery',
				'sanitize' => 'sanitize_text_field',
			),
			'_kh_event_pricing_model_id' => array(
				'label'    => __( 'Preismodell', 'kulturhaus-events' ),
				'type'     => 'pricing_model_select',
				'sanitize' => 'absint',
			),
			'_kh_event_prices' => array(
				'label'    => __( 'Preise für dieses Event', 'kulturhaus-events' ),
				'type'     => 'event_prices',
				'sanitize' => 'sanitize_text_field',
			),
			'_kh_event_featured' => array(
				'label'       => __( 'Als Highlight hervorheben', 'kulturhaus-events' ),
				'type'        => 'checkbox',
				'sanitize'    => 'sanitize_text_field',
				'description' => __( 'Dieses Event wird in Highlights-Shortcodes bevorzugt angezeigt', 'kulturhaus-events' ),
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
				<?php $this->render_field( $post, '_kh_event_url', $fields['_kh_event_url'] ); ?>
				<?php $this->render_field( $post, '_kh_event_status', $fields['_kh_event_status'] ); ?>
				<?php $this->render_field( $post, '_kh_event_featured', $fields['_kh_event_featured'] ); ?>
				<?php $this->render_field( $post, '_kh_event_accessibility', $fields['_kh_event_accessibility'] ); ?>
				<?php $this->render_field( $post, '_kh_event_gallery', $fields['_kh_event_gallery'] ); ?>
				<?php $this->render_field( $post, '_kh_event_pricing_model_id', $fields['_kh_event_pricing_model_id'] ); ?>
				<?php $this->render_field( $post, '_kh_event_prices', $fields['_kh_event_prices'] ); ?>
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

			case 'gallery':
				$gallery_ids = $value ? explode( ',', $value ) : array();
				?>
				<tr>
					<th><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label></th>
					<td>
						<div class="kh-gallery-container">
							<div class="kh-gallery-images" id="kh-gallery-images">
								<?php foreach ( $gallery_ids as $image_id ) : ?>
									<?php if ( $image_id ) : ?>
										<div class="kh-gallery-image" data-id="<?php echo esc_attr( $image_id ); ?>">
											<?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
											<button type="button" class="kh-remove-image">&times;</button>
										</div>
									<?php endif; ?>
								<?php endforeach; ?>
							</div>
							<input type="hidden" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $value ); ?>" />
							<button type="button" class="button kh-add-gallery-images"><?php esc_html_e( 'Bilder hinzufügen', 'kulturhaus-events' ); ?></button>
							<p class="description"><?php esc_html_e( 'Fügen Sie mehrere Bilder für den Slider hinzu.', 'kulturhaus-events' ); ?></p>
						</div>
						<style>
							.kh-gallery-images {
								display: flex;
								flex-wrap: wrap;
								gap: 10px;
								margin-bottom: 15px;
							}
							.kh-gallery-image {
								position: relative;
								width: 100px;
								height: 100px;
								border: 2px solid #ddd;
								border-radius: 4px;
								overflow: hidden;
							}
							.kh-gallery-image img {
								width: 100%;
								height: 100%;
								object-fit: cover;
							}
							.kh-remove-image {
								position: absolute;
								top: 0;
								right: 0;
								background: #dc3232;
								color: #fff;
								border: none;
								width: 24px;
								height: 24px;
								cursor: pointer;
								font-size: 18px;
								line-height: 1;
								border-radius: 0 0 0 4px;
							}
							.kh-remove-image:hover {
								background: #a00;
							}
						</style>
						<script>
						jQuery(document).ready(function($) {
							var frame;
							
							$('.kh-add-gallery-images').on('click', function(e) {
								e.preventDefault();
								
								if (frame) {
									frame.open();
									return;
								}
								
								frame = wp.media({
									title: '<?php esc_html_e( 'Bilder für Slider auswählen', 'kulturhaus-events' ); ?>',
									button: {
										text: '<?php esc_html_e( 'Bilder verwenden', 'kulturhaus-events' ); ?>'
									},
									multiple: true
								});
								
								frame.on('select', function() {
									var selection = frame.state().get('selection');
									var ids = $('#_kh_event_gallery').val().split(',').filter(Boolean);
									
									selection.each(function(attachment) {
										attachment = attachment.toJSON();
										if (ids.indexOf(attachment.id.toString()) === -1) {
											ids.push(attachment.id);
											$('#kh-gallery-images').append(
												'<div class="kh-gallery-image" data-id="' + attachment.id + '">' +
													'<img src="' + attachment.sizes.thumbnail.url + '" />' +
													'<button type="button" class="kh-remove-image">&times;</button>' +
												'</div>'
											);
										}
									});
									
									$('#_kh_event_gallery').val(ids.join(','));
								});
								
								frame.open();
							});
							
							$(document).on('click', '.kh-remove-image', function() {
								var $image = $(this).closest('.kh-gallery-image');
								var id = $image.data('id');
								var ids = $('#_kh_event_gallery').val().split(',').filter(Boolean);
								ids = ids.filter(function(i) { return i != id; });
								$('#_kh_event_gallery').val(ids.join(','));
								$image.remove();
							});
						});
						</script>
					</td>
				</tr>
				<?php
				break;

			case 'pricing_model_select':
				if ( ! class_exists( 'KH_Pricing_Model' ) ) {
					printf(
						'<tr><th><label>%s</label></th><td><p class="description">%s</p></td></tr>',
						esc_html( $label ),
						esc_html__( 'Preismodell-System wird geladen...', 'kulturhaus-events' )
					);
					break;
				}

				$models = get_posts(
					array(
						'post_type'      => KH_Pricing_Model::POST_TYPE,
						'posts_per_page' => -1,
						'orderby'        => 'title',
						'order'          => 'ASC',
						'post_status'    => 'publish',
					)
				);

				$options_html = '<option value="">' . esc_html__( '— Preismodell auswählen —', 'kulturhaus-events' ) . '</option>';
				foreach ( $models as $model ) {
					$model_type = get_post_meta( $model->ID, '_kh_pricing_model_type', true );
					$type_label = '';
					switch ( $model_type ) {
						case 'free':
							$type_label = ' [Kostenlos]';
							break;
						case 'solidarity':
							$type_label = ' [Solidarisch]';
							break;
						case 'fixed':
							$type_label = ' [Fester Preis]';
							break;
						case 'tiered':
							$type_label = ' [Gestaffelt]';
							break;
					}
					$options_html .= sprintf(
						'<option value="%d" %s>%s%s</option>',
						$model->ID,
						selected( $value, $model->ID, false ),
						esc_html( $model->post_title ),
						esc_html( $type_label )
					);
				}
				printf(
					'<tr><th><label for="%1$s">%2$s</label></th><td><select id="%1$s" name="%1$s" class="regular-text">%3$s</select><p class="description">%4$s</p></td></tr>',
					$id,
					$label,
					$options_html,
					esc_html__( 'Wählen Sie ein Preismodell oder erstellen Sie unter "Preismodelle" neue Vorlagen.', 'kulturhaus-events' )
				);
				break;

			case 'event_prices':
				$pricing_model_id = get_post_meta( $post->ID, '_kh_event_pricing_model_id', true );
				$event_prices     = get_post_meta( $post->ID, '_kh_event_prices', true );
				
				if ( ! is_array( $event_prices ) ) {
					$event_prices = array();
				}

				if ( ! $pricing_model_id || ! class_exists( 'KH_Pricing_Model' ) ) {
					printf(
						'<tr id="event_prices_row" style="display:none;"><th><label>%s</label></th><td><p class="description">%s</p></td></tr>',
						esc_html( $label ),
						esc_html__( 'Wählen Sie zuerst ein Preismodell aus.', 'kulturhaus-events' )
					);
					break;
				}

				$pricing_model = get_post( (int) $pricing_model_id );
				if ( ! $pricing_model || $pricing_model->post_status !== 'publish' ) {
					printf(
						'<tr id="event_prices_row" style="display:none;"><th><label>%s</label></th><td><p class="description">%s</p></td></tr>',
						esc_html( $label ),
						esc_html__( 'Das gewählte Preismodell ist nicht verfügbar.', 'kulturhaus-events' )
					);
					break;
				}

				$model_type  = get_post_meta( $pricing_model->ID, '_kh_pricing_model_type', true );
				$model_tiers = get_post_meta( $pricing_model->ID, '_kh_pricing_model_tiers', true );

				// Bei "free" keine Preiseingabe
				if ( $model_type === 'free' ) {
					echo '<tr id="event_prices_row" style="display:none;"></tr>';
					break;
				}

				if ( empty( $model_tiers ) || ! is_array( $model_tiers ) ) {
					printf(
						'<tr id="event_prices_row"><th><label>%s</label></th><td><p class="description">%s</p></td></tr>',
						esc_html( $label ),
						esc_html__( 'Das Preismodell hat keine Kategorien definiert.', 'kulturhaus-events' )
					);
					break;
				}

				?>
				<tr id="event_prices_row">
					<th><label><?php echo esc_html( $label ); ?></label></th>
					<td>
						<div class="event-prices-container">
							<?php foreach ( $model_tiers as $index => $tier ) : ?>
								<div class="event-price-field" style="margin-bottom: 15px;">
									<label style="display: inline-block; width: 150px; font-weight: 600;">
										<?php echo esc_html( $tier['label'] ); ?>
										<?php if ( ! empty( $tier['description'] ) ) : ?>
											<span style="font-weight: normal; color: #666; font-size: 12px;">
												(<?php echo esc_html( $tier['description'] ); ?>)
											</span>
										<?php endif; ?>
									</label>
									<input 
										type="text" 
										name="_kh_event_prices[<?php echo esc_attr( $index ); ?>]" 
										value="<?php echo esc_attr( $event_prices[ $index ] ?? '' ); ?>" 
										placeholder="z.B. 10€ oder 5-15€"
										class="regular-text"
									/>
								</div>
							<?php endforeach; ?>
						</div>
						<p class="description">
							<?php esc_html_e( 'Geben Sie die konkreten Preise für dieses Event ein.', 'kulturhaus-events' ); ?>
						</p>
					</td>
				</tr>
				
				<script>
				jQuery(document).ready(function($) {
					// Zeige/Verstecke Preisfelder wenn Preismodell geändert wird
					$('#_kh_event_pricing_model_id').on('change', function() {
						var modelId = $(this).val();
						if (modelId) {
							// Seite neu laden um Preisfelder anzuzeigen
							$('#event_prices_row').html('<td colspan="2"><em>Speichern Sie das Event um die Preisfelder anzuzeigen.</em></td>');
						} else {
							$('#event_prices_row').hide();
						}
					});
				});
				</script>
				<?php
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
	public function save_meta( int $post_id, \WP_Post $post ): void {
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

		// Event-Preise separat speichern (Array-Handling).
		if ( isset( $_POST['_kh_event_prices'] ) && is_array( $_POST['_kh_event_prices'] ) ) {
			$prices = array();
			foreach ( $_POST['_kh_event_prices'] as $index => $price ) {
				$prices[ $index ] = sanitize_text_field( wp_unslash( $price ) );
			}
			update_post_meta( $post_id, '_kh_event_prices', $prices );
		} else {
			delete_post_meta( $post_id, '_kh_event_prices' );
		}

		// Felder speichern.
		foreach ( self::get_fields() as $key => $field ) {
			// Event-Preise überspringen - bereits oben behandelt
			if ( $key === '_kh_event_prices' ) {
				continue;
			}

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
