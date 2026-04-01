<?php
/**
 * Meta-Boxen für Preismodelle.
 *
 * @package KulturhausEvents
 * @since   1.4.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Pricing_Model_Meta
 */
class KH_Pricing_Model_Meta {

	/**
	 * Nonce-Name für Sicherheit.
	 */
	private const NONCE_NAME = 'kh_pricing_model_meta_nonce';

	/**
	 * Nonce-Action für Sicherheit.
	 */
	private const NONCE_ACTION = 'kh_save_pricing_model_meta';

	/**
	 * Meta-Boxen und Hooks registrieren.
	 */
	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_' . KH_Pricing_Model::POST_TYPE, array( $this, 'save_meta' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Meta-Boxen hinzufügen.
	 */
	public function add_meta_boxes(): void {
		add_meta_box(
			'kh_pricing_model_details',
			__( 'Preismodell-Details', 'kulturhaus-events' ),
			array( $this, 'render_meta_box' ),
			KH_Pricing_Model::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Meta-Box rendern.
	 *
	 * @param \WP_Post $post Das aktuelle Post-Objekt.
	 */
	public function render_meta_box( \WP_Post $post ): void {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		$model_type   = get_post_meta( $post->ID, '_kh_pricing_model_type', true );
		$model_notice = get_post_meta( $post->ID, '_kh_pricing_model_notice', true );
		$model_tiers  = get_post_meta( $post->ID, '_kh_pricing_model_tiers', true );

		if ( ! is_array( $model_tiers ) ) {
			$model_tiers = array();
		}
		?>
		<table class="form-table">
			<tr>
				<th><label for="kh_pricing_model_type"><?php esc_html_e( 'Preismodell-Typ', 'kulturhaus-events' ); ?></label></th>
				<td>
					<select id="kh_pricing_model_type" name="kh_pricing_model_type" class="regular-text">
						<option value="free" <?php selected( $model_type, 'free' ); ?>><?php esc_html_e( 'Kostenlose Veranstaltung', 'kulturhaus-events' ); ?></option>
						<option value="solidarity" <?php selected( $model_type, 'solidarity' ); ?>><?php esc_html_e( 'Solidarisches Preissystem', 'kulturhaus-events' ); ?></option>
						<option value="fixed" <?php selected( $model_type, 'fixed' ); ?>><?php esc_html_e( 'Fester Preis', 'kulturhaus-events' ); ?></option>
						<option value="tiered" <?php selected( $model_type, 'tiered' ); ?>><?php esc_html_e( 'Gestaffelte Preise', 'kulturhaus-events' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Wählen Sie den Typ des Preismodells.', 'kulturhaus-events' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="kh_pricing_model_notice"><?php esc_html_e( 'Hinweistext', 'kulturhaus-events' ); ?></label></th>
				<td>
					<textarea id="kh_pricing_model_notice" name="kh_pricing_model_notice" rows="4" class="large-text"><?php echo esc_textarea( $model_notice ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Optionaler Hinweistext (z.B. Erklärung zum solidarischen Preissystem).', 'kulturhaus-events' ); ?></p>
				</td>
			</tr>
			<tr id="pricing_tiers_row">
				<th><label><?php esc_html_e( 'Preiskategorien (Vorlage)', 'kulturhaus-events' ); ?></label></th>
				<td>
					<div id="pricing_tiers_container">
						<?php
						if ( empty( $model_tiers ) ) {
							$model_tiers = array(
								array( 'label' => '', 'description' => '' ),
							);
						}
						foreach ( $model_tiers as $index => $tier ) :
							?>
							<div class="pricing-tier" data-index="<?php echo esc_attr( $index ); ?>">
								<div class="tier-fields">
									<input type="text" name="kh_pricing_tiers[<?php echo esc_attr( $index ); ?>][label]" placeholder="<?php esc_attr_e( 'Bezeichnung (z.B. Normal, Ermäßigt, Soli)', 'kulturhaus-events' ); ?>" value="<?php echo esc_attr( $tier['label'] ?? '' ); ?>" class="regular-text" style="flex: 2;" />
									<input type="text" name="kh_pricing_tiers[<?php echo esc_attr( $index ); ?>][description]" placeholder="<?php esc_attr_e( 'Beschreibung (optional)', 'kulturhaus-events' ); ?>" value="<?php echo esc_attr( $tier['description'] ?? '' ); ?>" class="large-text" style="flex: 3;" />
									<button type="button" class="button remove-tier"><?php esc_html_e( 'Entfernen', 'kulturhaus-events' ); ?></button>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" id="add_tier" class="button"><?php esc_html_e( 'Kategorie hinzufügen', 'kulturhaus-events' ); ?></button>
					<p class="description"><?php esc_html_e( 'Definieren Sie die Preiskategorien (z.B. Normal, Ermäßigt, Soli). Die konkreten Preise werden bei jedem Event individuell eingegeben.', 'kulturhaus-events' ); ?></p>
				</td>
			</tr>
		</table>

		<style>
			.pricing-tier {
				margin-bottom: 15px;
				padding: 15px;
				background: #f9f9f9;
				border: 1px solid #ddd;
				border-radius: 4px;
			}
			.tier-fields {
				display: flex;
				gap: 10px;
				align-items: center;
			}
			.tier-fields input {
				flex: 1;
			}
			.remove-tier {
				flex-shrink: 0;
			}
		</style>
		<?php
	}

	/**
	 * Admin-Scripts einbinden.
	 *
	 * @param string $hook_suffix Die aktuelle Admin-Seite.
	 */
	public function enqueue_admin_scripts( string $hook_suffix ): void {
		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== KH_Pricing_Model::POST_TYPE ) {
			return;
		}

		wp_add_inline_script( 'jquery', "
			jQuery(document).ready(function($) {
				var tierIndex = " . count( get_post_meta( get_the_ID(), '_kh_pricing_model_tiers', true ) ?: array() ) . ";
				
				$('#add_tier').on('click', function() {
					var html = '<div class=\"pricing-tier\" data-index=\"' + tierIndex + '\">' +
						'<div class=\"tier-fields\">' +
							'<input type=\"text\" name=\"kh_pricing_tiers[' + tierIndex + '][label]\" placeholder=\"Bezeichnung (z.B. Normal, Ermäßigt)\" class=\"regular-text\" style=\"flex: 2;\" />' +
							'<input type=\"text\" name=\"kh_pricing_tiers[' + tierIndex + '][description]\" placeholder=\"Beschreibung (optional)\" class=\"large-text\" style=\"flex: 3;\" />' +
							'<button type=\"button\" class=\"button remove-tier\">Entfernen</button>' +
						'</div>' +
					'</div>';
					$('#pricing_tiers_container').append(html);
					tierIndex++;
				});
				
				$(document).on('click', '.remove-tier', function() {
					$(this).closest('.pricing-tier').remove();
				});
				
				// Zeige/Verstecke Preisstufen basierend auf Typ
				function toggleTiers() {
					var type = $('#kh_pricing_model_type').val();
					if (type === 'free') {
						$('#pricing_tiers_row').hide();
					} else {
						$('#pricing_tiers_row').show();
					}
				}
				
				toggleTiers();
				$('#kh_pricing_model_type').on('change', toggleTiers);
			});
		" );
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

		// Typ speichern.
		if ( isset( $_POST['kh_pricing_model_type'] ) ) {
			update_post_meta( $post_id, '_kh_pricing_model_type', sanitize_text_field( wp_unslash( $_POST['kh_pricing_model_type'] ) ) );
		}

		// Hinweistext speichern.
		if ( isset( $_POST['kh_pricing_model_notice'] ) ) {
			update_post_meta( $post_id, '_kh_pricing_model_notice', sanitize_textarea_field( wp_unslash( $_POST['kh_pricing_model_notice'] ) ) );
		}

		// Preiskategorien speichern (nur Labels, keine Preise).
		if ( isset( $_POST['kh_pricing_tiers'] ) && is_array( $_POST['kh_pricing_tiers'] ) ) {
			$tiers = array();
			foreach ( $_POST['kh_pricing_tiers'] as $tier ) {
				if ( ! empty( $tier['label'] ) ) {
					$tiers[] = array(
						'label'       => sanitize_text_field( $tier['label'] ?? '' ),
						'description' => sanitize_text_field( $tier['description'] ?? '' ),
					);
				}
			}
			update_post_meta( $post_id, '_kh_pricing_model_tiers', $tiers );
		} else {
			delete_post_meta( $post_id, '_kh_pricing_model_tiers' );
		}
	}
}
