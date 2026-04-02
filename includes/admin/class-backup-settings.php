<?php
/**
 * Backup Settings for Kulturhaus Events
 *
 * @package KulturhausEvents
 * @since   1.21.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Backup_Settings
 *
 * Verwaltet Backup-Einstellungen für das Plugin.
 */
class KH_Backup_Settings {

	/**
	 * Option key für Backup-Einstellungen.
	 */
	const OPTION_KEY = 'kh_events_backup_settings';

	/**
	 * Standard-Backup-Einstellungen.
	 */
	private static function get_default_settings(): array {
		return array(
			'enabled'           => false,
			'auto_backup'       => false,
			'backup_interval'   => 'daily', // daily, weekly, monthly
			'keep_backups'      => 7,        // Anzahl der zu behaltenden Backups
			'backup_location'   => 'wp_content', // wp_content, custom
			'custom_path'       => '',
			'include_media'     => false,
			'exclude_tables'    => array(),
			'notification_email' => get_option( 'admin_email' ),
			'last_backup'       => null,
		);
	}

	/**
	 * Backup-Einstellungen abrufen.
	 */
	public static function get_settings(): array {
		$saved = get_option( self::OPTION_KEY, array() );
		return wp_parse_args( $saved, self::get_default_settings() );
	}

	/**
	 * Backup-Einstellungen speichern.
	 */
	public static function save_settings( array $settings ): bool {
		$defaults = self::get_default_settings();
		$sanitized = array();

		foreach ( $defaults as $key => $default_value ) {
			if ( isset( $settings[ $key ] ) ) {
				switch ( $key ) {
					case 'enabled':
					case 'auto_backup':
					case 'include_media':
						$sanitized[ $key ] = (bool) $settings[ $key ];
						break;
					case 'backup_interval':
						$sanitized[ $key ] = in_array( $settings[ $key ], array( 'daily', 'weekly', 'monthly' ), true ) 
							? $settings[ $key ] 
							: $default_value;
						break;
					case 'keep_backups':
						$sanitized[ $key ] = absint( $settings[ $key ] ) ?: $default_value;
						break;
					case 'backup_location':
						$sanitized[ $key ] = in_array( $settings[ $key ], array( 'wp_content', 'custom' ), true ) 
							? $settings[ $key ] 
							: $default_value;
						break;
					case 'custom_path':
						$sanitized[ $key ] = sanitize_text_field( $settings[ $key ] );
						break;
					case 'exclude_tables':
						$sanitized[ $key ] = array_map( 'sanitize_text_field', (array) $settings[ $key ] );
						break;
					case 'notification_email':
						$sanitized[ $key ] = sanitize_email( $settings[ $key ] );
						break;
					default:
						$sanitized[ $key ] = $settings[ $key ];
				}
			} else {
				$sanitized[ $key ] = $default_value;
			}
		}

		return update_option( self::OPTION_KEY, $sanitized );
	}

	/**
	 * Backup-Einstellungen-Seite rendern.
	 */
	public static function render_settings_page(): void {
		$settings = self::get_settings();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Backup-Einstellungen', 'kulturhaus-events' ); ?></h1>
			
			<form method="post" action="options.php">
				<?php
				settings_fields( 'kh_events_backup_settings' );
				do_settings_sections( 'kh_events_backup_settings' );
				?>

				<div class="kh-backup-settings">
					<!-- General Settings -->
					<div class="kh-backup-section">
						<h2><?php esc_html_e( 'Allgemeine Einstellungen', 'kulturhaus-events' ); ?></h2>
						
						<table class="form-table">
							<tr>
								<th scope="row">
									<label for="kh_backup_enabled">
										<?php esc_html_e( 'Backup-Funktion aktivieren', 'kulturhaus-events' ); ?>
									</label>
								</th>
								<td>
									<input type="checkbox" id="kh_backup_enabled" name="kh_backup_settings[enabled]" 
										value="1" <?php checked( $settings['enabled'] ); ?>>
									<p class="description">
										<?php esc_html_e( 'Aktiviert die Backup-Funktion für Events und Einstellungen.', 'kulturhaus-events' ); ?>
									</p>
								</td>
							</tr>

							<tr>
								<th scope="row">
									<label for="kh_auto_backup">
										<?php esc_html_e( 'Automatische Backups', 'kulturhaus-events' ); ?>
									</label>
								</th>
								<td>
									<input type="checkbox" id="kh_auto_backup" name="kh_backup_settings[auto_backup]" 
										value="1" <?php checked( $settings['auto_backup'] ); ?>>
									<p class="description">
										<?php esc_html_e( 'Erstellt automatisch Backups im gewählten Intervall.', 'kulturhaus-events' ); ?>
									</p>
								</td>
							</tr>

							<tr>
								<th scope="row">
									<label for="kh_backup_interval">
										<?php esc_html_e( 'Backup-Intervall', 'kulturhaus-events' ); ?>
									</label>
								</th>
								<td>
									<select id="kh_backup_interval" name="kh_backup_settings[backup_interval]">
										<option value="daily" <?php selected( $settings['backup_interval'], 'daily' ); ?>>
											<?php esc_html_e( 'Täglich', 'kulturhaus-events' ); ?>
										</option>
										<option value="weekly" <?php selected( $settings['backup_interval'], 'weekly' ); ?>>
											<?php esc_html_e( 'Wöchentlich', 'kulturhaus-events' ); ?>
										</option>
										<option value="monthly" <?php selected( $settings['backup_interval'], 'monthly' ); ?>>
											<?php esc_html_e( 'Monatlich', 'kulturhaus-events' ); ?>
										</option>
									</select>
								</td>
							</tr>

							<tr>
								<th scope="row">
									<label for="kh_keep_backups">
										<?php esc_html_e( 'Anzahl der Backups behalten', 'kulturhaus-events' ); ?>
									</label>
								</th>
								<td>
									<input type="number" id="kh_keep_backups" name="kh_backup_settings[keep_backups]" 
										value="<?php echo esc_attr( (string) $settings['keep_backups'] ); ?>" min="1" max="30">
									<p class="description">
										<?php esc_html_e( 'Wie viele Backups aufbewahrt werden sollen (älteste werden gelöscht).', 'kulturhaus-events' ); ?>
									</p>
								</td>
							</tr>
						</table>
					</div>

					<!-- Storage Settings -->
					<div class="kh-backup-section">
						<h2><?php esc_html_e( 'Speicherort', 'kulturhaus-events' ); ?></h2>
						
						<table class="form-table">
							<tr>
								<th scope="row">
									<label for="kh_backup_location">
										<?php esc_html_e( 'Backup-Speicherort', 'kulturhaus-events' ); ?>
									</label>
								</th>
								<td>
									<select id="kh_backup_location" name="kh_backup_settings[backup_location]">
										<option value="wp_content" <?php selected( $settings['backup_location'], 'wp_content' ); ?>>
											<?php esc_html_e( 'wp-content/backups', 'kulturhaus-events' ); ?>
										</option>
										<option value="custom" <?php selected( $settings['backup_location'], 'custom' ); ?>>
											<?php esc_html_e( 'Benutzerdefinierter Pfad', 'kulturhaus-events' ); ?>
										</option>
									</select>
								</td>
							</tr>

							<tr id="kh_custom_path_row" style="<?php echo $settings['backup_location'] === 'custom' ? '' : 'display: none;'; ?>">
								<th scope="row">
									<label for="kh_custom_path">
										<?php esc_html_e( 'Benutzerdefinierter Pfad', 'kulturhaus-events' ); ?>
									</label>
								</th>
								<td>
									<input type="text" id="kh_custom_path" name="kh_backup_settings[custom_path]" 
										value="<?php echo esc_attr( $settings['custom_path'] ); ?>" 
										placeholder="<?php echo esc_attr( ABSPATH . 'backups' ); ?>">
									<p class="description">
										<?php esc_html_e( 'Absoluter Pfad zum Backup-Verzeichnis. Muss beschreibbar sein.', 'kulturhaus-events' ); ?>
									</p>
								</td>
							</tr>

							<tr>
								<th scope="row">
									<label for="kh_include_media">
										<?php esc_html_e( 'Medien-Dateien einbeziehen', 'kulturhaus-events' ); ?>
									</label>
								</th>
								<td>
									<input type="checkbox" id="kh_include_media" name="kh_backup_settings[include_media]" 
										value="1" <?php checked( $settings['include_media'] ); ?>>
									<p class="description">
										<?php esc_html_e( 'Fügt alle Event-bezogenen Medien-Dateien zum Backup hinzu.', 'kulturhaus-events' ); ?>
									</p>
								</td>
							</tr>
						</table>
					</div>

					<!-- Notification Settings -->
					<div class="kh-backup-section">
						<h2><?php esc_html_e( 'Benachrichtigungen', 'kulturhaus-events' ); ?></h2>
						
						<table class="form-table">
							<tr>
								<th scope="row">
									<label for="kh_notification_email">
										<?php esc_html_e( 'Benachrichtigungs-E-Mail', 'kulturhaus-events' ); ?>
									</label>
								</th>
								<td>
									<input type="email" id="kh_notification_email" name="kh_backup_settings[notification_email]" 
										value="<?php echo esc_attr( $settings['notification_email'] ); ?>">
									<p class="description">
										<?php esc_html_e( 'E-Mail-Adresse für Backup-Benachrichtigungen.', 'kulturhaus-events' ); ?>
									</p>
								</td>
							</tr>
						</table>
					</div>

					<!-- Manual Backup -->
					<div class="kh-backup-section">
						<h2><?php esc_html_e( 'Manuelles Backup', 'kulturhaus-events' ); ?></h2>
						
						<?php
						// Backup erstellen, wenn Formular gesendet
						if ( isset( $_POST['kh_create_backup'] ) && check_admin_referer( 'kh_backup_nonce' ) ) {
							$result = KH_Backup_Manager::create_backup( KH_Backup_Manager::BACKUP_FULL );
							$class = $result['success'] ? 'notice-success' : 'notice-error';
							echo '<div class="notice ' . $class . ' inline"><p>' . esc_html( $result['message'] ) . '</p></div>';
						}
						?>
						
						<form method="post" action="">
							<?php wp_nonce_field( 'kh_backup_nonce' ); ?>
							<input type="hidden" name="kh_create_backup" value="1">
							<button type="submit" class="button button-primary">
								<?php esc_html_e( 'Jetzt Backup erstellen', 'kulturhaus-events' ); ?>
							</button>
						</form>
					</div>
				</div>

				<?php submit_button(); ?>
			</form>
		</div>

		<style>
		.kh-backup-section {
			background: #fff;
			border: 1px solid #ccd0d4;
			border-radius: 8px;
			padding: 20px;
			margin-bottom: 20px;
		}
		
		.kh-backup-section h2 {
			margin-top: 0;
			margin-bottom: 15px;
			font-size: 1.3em;
			color: #1d2327;
		}
		</style>

		<script>
		jQuery(document).ready(function($) {
			$('#kh_backup_location').on('change', function() {
				var customRow = $('#kh_custom_path_row');
				if ($(this).val() === 'custom') {
					customRow.show();
				} else {
					customRow.hide();
				}
			});
		});
		</script>
		<?php
	}

	/**
	 * Settings registrieren.
	 */
	public static function register_settings(): void {
		register_setting(
			'kh_events_backup_settings',
			'kh_backup_settings',
			array(
				'sanitize_callback' => array( __CLASS__, 'save_settings' ),
			)
		);
	}
}
