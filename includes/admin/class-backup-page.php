<?php
/**
 * Admin-Seite für Backup & Restore.
 *
 * Bietet UI für ZIP-basiertes Backup und Restore neben dem CSV-System.
 *
 * @package KulturhausEvents
 * @since   1.21.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Backup_Page
 *
 * Verwaltet die Backup & Restore Admin-Seite.
 */
class KH_Backup_Page {

	/**
	 * Seite registrieren.
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_admin_page' ) );
		add_action( 'admin_post_kh_backup_create', array( $this, 'handle_backup_create' ) );
		add_action( 'admin_post_kh_restore_upload', array( $this, 'handle_restore_upload' ) );
	}

	/**
	 * Admin-Seite hinzufügen.
	 */
	public function add_admin_page(): void {
		add_submenu_page(
			'edit.php?post_type=' . KH_Event::POST_TYPE,
			__( 'Backup & Restore', 'kulturhaus-events' ),
			__( 'Backup & Restore', 'kulturhaus-events' ),
			'manage_options',
			'kh-backup-restore',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Seite rendern.
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Status-Nachrichten
		$this->render_status_messages();

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<div class="kh-backup-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 20px;">

				<!-- BACKUP (ZIP) SECTION -->
				<div class="kh-backup-box" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
					<h2 style="margin-top: 0; border-bottom: 2px solid var(--kh-accent-color, #0073aa); padding-bottom: 10px;">
						📦 <?php esc_html_e( 'ZIP-Backup erstellen', 'kulturhaus-events' ); ?>
					</h2>

					<p class="description">
						<?php esc_html_e( 'Erstellt ein vollständiges Backup aller Events, Venues, Organizer und Bilder in einem ZIP-Archiv.', 'kulturhaus-events' ); ?>
					</p>

					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="kh_backup_create">
						<?php wp_nonce_field( 'kh_backup_create_nonce', 'kh_backup_nonce' ); ?>

						<table class="form-table" style="margin: 20px 0;">
							<tr>
								<th scope="row">
									<label for="kh_backup_include_images">
										<input type="checkbox" id="kh_backup_include_images" name="kh_backup_include_images" value="1" checked>
										<?php esc_html_e( 'Bilder einbeziehen', 'kulturhaus-events' ); ?>
									</label>
								</th>
								<td class="description">
									<?php esc_html_e( 'Wenn aktiviert, werden alle Bilder in das Backup einbezogen (größere Dateigröße).', 'kulturhaus-events' ); ?>
								</td>
							</tr>
						</table>

						<button type="submit" class="button button-primary button-large">
							🔽 <?php esc_html_e( 'Backup jetzt erstellen & herunterladen', 'kulturhaus-events' ); ?>
						</button>
					</form>

					<p style="margin-top: 20px; padding: 10px; background: #f0f6fc; border-left: 4px solid #0073aa; font-size: 12px;">
						<strong><?php esc_html_e( 'Hinweis:', 'kulturhaus-events' ); ?></strong><br>
						<?php esc_html_e( 'Das Backup wird automatisch heruntergeladen. Speichern Sie die ZIP-Datei an einem sicheren Ort.', 'kulturhaus-events' ); ?>
					</p>
				</div>

				<!-- RESTORE (ZIP) SECTION -->
				<div class="kh-restore-box" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
					<h2 style="margin-top: 0; border-bottom: 2px solid var(--kh-accent-color, #0073aa); padding-bottom: 10px;">
						📥 <?php esc_html_e( 'ZIP-Backup wiederherstellen', 'kulturhaus-events' ); ?>
					</h2>

					<p class="description">
						<?php esc_html_e( 'Lädt ein Backup hoch und stellt alle Daten wieder her. Duplikate werden ignoriert (Merge-Strategie).', 'kulturhaus-events' ); ?>
					</p>

					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" id="kh-restore-form">
						<input type="hidden" name="action" value="kh_restore_upload">
						<?php wp_nonce_field( 'kh_restore_upload_nonce', 'kh_restore_nonce' ); ?>

						<div style="margin: 20px 0; padding: 20px; border: 2px dashed #ddd; border-radius: 5px; background: #fafafa; text-align: center; cursor: pointer;" id="kh-file-drop">
							<input type="file" name="kh_restore_file" id="kh_restore_file" accept=".zip" style="display: none;">
							<p style="margin: 0; color: #666;">
								<?php esc_html_e( 'ZIP-Datei hier ablegen oder klicken zum Auswählen', 'kulturhaus-events' ); ?>
							</p>
							<p class="description" style="margin-top: 10px;">
								<?php esc_html_e( 'Nur .zip Dateien werden akzeptiert', 'kulturhaus-events' ); ?>
							</p>
						</div>

						<div id="kh-file-name" style="margin: 10px 0; display: none; padding: 10px; background: #ecf0f1; border-radius: 3px;">
							<strong><?php esc_html_e( 'Ausgewählte Datei:', 'kulturhaus-events' ); ?></strong>
							<span id="kh-file-name-text"></span>
						</div>

						<button type="submit" class="button button-primary button-large" id="kh-restore-button" style="display: none;">
							🚀 <?php esc_html_e( 'Restore starten', 'kulturhaus-events' ); ?>
						</button>
					</form>

					<p style="margin-top: 20px; padding: 10px; background: #fff3cd; border-left: 4px solid #ffc107; font-size: 12px;">
						<strong><?php esc_html_e( 'Warnung:', 'kulturhaus-events' ); ?></strong><br>
						<?php esc_html_e( 'Gleichnamige Events werden nicht überschrieben, sondern übersprungen. Überprüfen Sie die Log-Meldungen.', 'kulturhaus-events' ); ?>
					</p>
				</div>

			</div>

			<!-- CSV SYSTEM (ALT) - BLEIBT BESTEHEN -->
			<div style="margin-top: 40px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;">
				<h3><?php esc_html_e( 'CSV-Import/Export (veraltet)', 'kulturhaus-events' ); ?></h3>
				<p class="description">
					<?php esc_html_e( 'Das alte CSV-System steht weiterhin zur Verfügung. Für umfassende Backups wird das ZIP-System empfohlen.', 'kulturhaus-events' ); ?>
				</p>
				<!-- CSV-Inhalt wird durch bestehende Import/Export-Klasse eingefügt -->
			</div>

		</div>

		<script>
			// Drag & Drop für Restore-Upload
			const dropZone = document.getElementById('kh-file-drop');
			const fileInput = document.getElementById('kh_restore_file');
			const fileName = document.getElementById('kh-file-name');
			const fileNameText = document.getElementById('kh-file-name-text');
			const restoreButton = document.getElementById('kh-restore-button');

			dropZone.addEventListener('click', () => fileInput.click());

			dropZone.addEventListener('dragover', (e) => {
				e.preventDefault();
				dropZone.style.background = '#e8f4f8';
				dropZone.style.borderColor = '#0073aa';
			});

			dropZone.addEventListener('dragleave', () => {
				dropZone.style.background = '#fafafa';
				dropZone.style.borderColor = '#ddd';
			});

			dropZone.addEventListener('drop', (e) => {
				e.preventDefault();
				dropZone.style.background = '#fafafa';
				dropZone.style.borderColor = '#ddd';

				const files = e.dataTransfer.files;
				if (files.length > 0) {
					fileInput.files = files;
					updateFileName();
				}
			});

			fileInput.addEventListener('change', updateFileName);

			function updateFileName() {
				if (fileInput.files.length > 0) {
					fileNameText.textContent = fileInput.files[0].name;
					fileName.style.display = 'block';
					restoreButton.style.display = 'inline-block';
				} else {
					fileName.style.display = 'none';
					restoreButton.style.display = 'none';
				}
			}
		</script>

		<?php
	}

	/**
	 * Status-Meldungen rendern.
	 */
	private function render_status_messages(): void {
		// Erfolgreiche Wiederherstellung
		if ( isset( $_GET['kh_restore_success'] ) ) {
			$stats = isset( $_GET['kh_restore_stats'] ) ? json_decode( wp_unslash( $_GET['kh_restore_stats'] ), true ) : array();
			?>
			<div class="notice notice-success is-dismissible">
				<p>
					<strong><?php esc_html_e( '✅ Backup erfolgreich wiederhergestellt!', 'kulturhaus-events' ); ?></strong><br>
					<?php
					if ( is_array( $stats ) ) {
						foreach ( $stats as $key => $value ) {
							echo esc_html( ucfirst( str_replace( '_', ' ', $key ) ) ) . ': ' . esc_html( $value ) . '<br>';
						}
					}
					?>
				</p>
			</div>
			<?php
		}

		// Fehler
		if ( isset( $_GET['kh_backup_error'] ) ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p>
					<strong><?php esc_html_e( '❌ Fehler:', 'kulturhaus-events' ); ?></strong>
					<?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['kh_backup_error'] ) ) ); ?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Backup-Erstellung verarbeiten.
	 */
	public function handle_backup_create(): void {
		// Sicherheitschecks
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unberechtigt', 'kulturhaus-events' ) );
		}

		// Nonce verifizieren
		check_admin_referer( 'kh_backup_create_nonce', 'kh_backup_nonce' );

		// Backup erstellen
		$backup_manager = new KH_Backup_Manager();
		$backup_manager->create_backup();
	}

	/**
	 * Restore-Upload verarbeiten.
	 */
	public function handle_restore_upload(): void {
		// Sicherheitschecks
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unberechtigt', 'kulturhaus-events' ) );
		}

		// Nonce verifizieren
		check_admin_referer( 'kh_restore_upload_nonce', 'kh_restore_nonce' );

		// Datei validieren
		if ( empty( $_FILES['kh_restore_file'] ) ) {
			wp_safe_redirect(
				add_query_arg(
					'kh_backup_error',
					__( 'Keine Datei ausgewählt', 'kulturhaus-events' ),
					admin_url( 'admin.php?page=kh-backup-restore' )
				)
			);
			exit;
		}

		$file      = $_FILES['kh_restore_file'];
		$file_name = isset( $file['name'] ) ? sanitize_file_name( $file['name'] ) : '';
		$file_tmp  = isset( $file['tmp_name'] ) ? $file['tmp_name'] : '';
		$file_type = isset( $file['type'] ) ? $file['type'] : '';

		// ZIP-Datei validieren
		if ( 'application/zip' !== $file_type ) {
			wp_safe_redirect(
				add_query_arg(
					'kh_backup_error',
					__( 'Nur ZIP-Dateien werden akzeptiert', 'kulturhaus-events' ),
					admin_url( 'admin.php?page=kh-backup-restore' )
				)
			);
			exit;
		}

		if ( ! file_exists( $file_tmp ) ) {
			wp_safe_redirect(
				add_query_arg(
					'kh_backup_error',
					__( 'Datei-Upload fehlgeschlagen', 'kulturhaus-events' ),
					admin_url( 'admin.php?page=kh-backup-restore' )
				)
			);
			exit;
		}

		// Restore durchführen
		$restore_manager = new KH_Restore_Manager();
		$result          = $restore_manager->restore_backup( $file_tmp );

		if ( $result['success'] ) {
			wp_safe_redirect(
				add_query_arg(
					array(
						'kh_restore_success' => '1',
						'kh_restore_stats'   => wp_json_encode( $result['stats'] ),
					),
					admin_url( 'admin.php?page=kh-backup-restore' )
				)
			);
		} else {
			wp_safe_redirect(
				add_query_arg(
					'kh_backup_error',
					$result['message'],
					admin_url( 'admin.php?page=kh-backup-restore' )
				)
			);
		}

		exit;
	}
}
