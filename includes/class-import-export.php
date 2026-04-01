<?php
/**
 * Event Import & Export
 *
 * @package KulturhausEvents
 * @since   1.14.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Import_Export
 *
 * Ermöglicht Import und Export von Events via CSV.
 */
class KH_Import_Export {

	/**
	 * Hooks registrieren.
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_admin_page' ) );
		add_action( 'admin_post_kh_export_events', array( $this, 'handle_export' ) );
		add_action( 'admin_post_kh_import_events', array( $this, 'handle_import' ) );
	}

	/**
	 * Admin-Seite hinzufügen.
	 */
	public function add_admin_page(): void {
		add_submenu_page(
			'edit.php?post_type=kh_event',
			__( 'Import/Export', 'kulturhaus-events' ),
			__( 'Import/Export', 'kulturhaus-events' ),
			'manage_options',
			'kh-import-export',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Admin-Seite rendern.
	 */
	public function render_admin_page(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Events Import/Export', 'kulturhaus-events' ); ?></h1>

			<?php if ( isset( $_GET['imported'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php printf( esc_html__( '%d Events erfolgreich importiert!', 'kulturhaus-events' ), absint( $_GET['imported'] ) ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( isset( $_GET['error'] ) ) : ?>
				<div class="notice notice-error is-dismissible">
					<p><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['error'] ) ) ); ?></p>
				</div>
			<?php endif; ?>

			<div class="kh-import-export-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 30px;">
				
				<!-- Export -->
				<div class="kh-export-section" style="background: #fff; padding: 30px; border: 1px solid #ddd; border-radius: 8px;">
					<h2><?php esc_html_e( 'Events exportieren', 'kulturhaus-events' ); ?></h2>
					<p><?php esc_html_e( 'Exportiere alle Events als CSV-Datei.', 'kulturhaus-events' ); ?></p>
					
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<?php wp_nonce_field( 'kh_export_events', 'kh_export_nonce' ); ?>
						<input type="hidden" name="action" value="kh_export_events">
						
						<p>
							<label>
								<input type="checkbox" name="include_past" value="1">
								<?php esc_html_e( 'Vergangene Events einschließen', 'kulturhaus-events' ); ?>
							</label>
						</p>

						<p>
							<button type="submit" class="button button-primary button-large">
								<span class="dashicons dashicons-download" style="margin-top: 3px;"></span>
								<?php esc_html_e( 'CSV exportieren', 'kulturhaus-events' ); ?>
							</button>
						</p>
					</form>

					<hr style="margin: 30px 0;">
					
					<h3><?php esc_html_e( 'CSV-Format', 'kulturhaus-events' ); ?></h3>
					<p style="font-size: 12px; color: #666;">
						<strong>Spalten:</strong><br>
						title, content, start_date, end_date, location, organizer, price_regular, price_reduced, 
						status, category, tags, featured_image_url
					</p>
				</div>

				<!-- Import -->
				<div class="kh-import-section" style="background: #fff; padding: 30px; border: 1px solid #ddd; border-radius: 8px;">
					<h2><?php esc_html_e( 'Events importieren', 'kulturhaus-events' ); ?></h2>
					<p><?php esc_html_e( 'Importiere Events aus einer CSV-Datei.', 'kulturhaus-events' ); ?></p>
					
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
						<?php wp_nonce_field( 'kh_import_events', 'kh_import_nonce' ); ?>
						<input type="hidden" name="action" value="kh_import_events">
						
						<p>
							<label for="csv_file"><?php esc_html_e( 'CSV-Datei:', 'kulturhaus-events' ); ?></label><br>
							<input type="file" name="csv_file" id="csv_file" accept=".csv" required>
						</p>

						<p>
							<label>
								<input type="checkbox" name="update_existing" value="1">
								<?php esc_html_e( 'Existierende Events aktualisieren (nach Titel)', 'kulturhaus-events' ); ?>
							</label>
						</p>

						<p>
							<button type="submit" class="button button-primary button-large">
								<span class="dashicons dashicons-upload" style="margin-top: 3px;"></span>
								<?php esc_html_e( 'CSV importieren', 'kulturhaus-events' ); ?>
							</button>
						</p>
					</form>

					<hr style="margin: 30px 0;">

					<h3><?php esc_html_e( 'Hinweise', 'kulturhaus-events' ); ?></h3>
					<ul style="font-size: 12px; color: #666;">
						<li>Datumsformat: YYYY-MM-DD HH:MM:SS</li>
						<li>Kategorien/Tags: Komma-getrennt</li>
						<li>Status: upcoming, cancelled, sold_out, postponed</li>
						<li>Featured Image: Vollständige URL</li>
					</ul>
				</div>

			</div>

			<!-- Beispiel-CSV Download -->
			<div style="margin-top: 30px; padding: 20px; background: #f0f0f0; border-radius: 8px;">
				<h3><?php esc_html_e( 'Beispiel-CSV herunterladen', 'kulturhaus-events' ); ?></h3>
				<p><?php esc_html_e( 'Lade eine Beispiel-CSV mit Test-Events herunter, um das Format zu sehen.', 'kulturhaus-events' ); ?></p>
				<a href="<?php echo esc_url( KH_EVENTS_PLUGIN_URL . 'assets/sample-events.csv' ); ?>" class="button" download>
					<span class="dashicons dashicons-media-spreadsheet" style="margin-top: 3px;"></span>
					<?php esc_html_e( 'Beispiel-CSV herunterladen', 'kulturhaus-events' ); ?>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Export-Handler.
	 */
	public function handle_export(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Keine Berechtigung.', 'kulturhaus-events' ) );
		}

		check_admin_referer( 'kh_export_events', 'kh_export_nonce' );

		$include_past = isset( $_POST['include_past'] ) && $_POST['include_past'] === '1';

		$args = array(
			'post_type'      => KH_Event::POST_TYPE,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_key'       => '_kh_event_start_date',
		);

		if ( ! $include_past ) {
			$args['meta_query'] = array(
				array(
					'key'     => '_kh_event_start_date',
					'value'   => current_time( 'mysql' ),
					'compare' => '>=',
					'type'    => 'DATETIME',
				),
			);
		}

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			wp_safe_redirect( add_query_arg( 'error', urlencode( __( 'Keine Events zum Exportieren gefunden.', 'kulturhaus-events' ) ), wp_get_referer() ) );
			exit;
		}

		// CSV generieren
		$filename = 'events-export-' . gmdate( 'Y-m-d-His' ) . '.csv';
		
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );

		// BOM für UTF-8
		fprintf( $output, chr(0xEF).chr(0xBB).chr(0xBF) );

		// Header
		fputcsv( $output, array(
			'title',
			'content',
			'start_date',
			'end_date',
			'location',
			'organizer',
			'price_regular',
			'price_reduced',
			'status',
			'category',
			'tags',
			'featured_image_url',
		), ';' );

		// Events
		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id = get_the_ID();

			$categories = wp_get_post_terms( $post_id, KH_Event_Category::TAXONOMY, array( 'fields' => 'names' ) );
			$tags = wp_get_post_terms( $post_id, KH_Event_Tag::TAXONOMY, array( 'fields' => 'names' ) );
			
			$featured_image = '';
			if ( has_post_thumbnail( $post_id ) ) {
				$featured_image = get_the_post_thumbnail_url( $post_id, 'full' );
			}

			fputcsv( $output, array(
				get_the_title(),
				get_the_content(),
				get_post_meta( $post_id, '_kh_event_start_date', true ),
				get_post_meta( $post_id, '_kh_event_end_date', true ),
				get_post_meta( $post_id, '_kh_event_location', true ),
				get_post_meta( $post_id, '_kh_event_organizer', true ),
				get_post_meta( $post_id, '_kh_event_price_regular', true ),
				get_post_meta( $post_id, '_kh_event_price_reduced', true ),
				get_post_meta( $post_id, '_kh_event_status', true ),
				! empty( $categories ) ? implode( ',', $categories ) : '',
				! empty( $tags ) ? implode( ',', $tags ) : '',
				$featured_image,
			), ';' );
		}

		fclose( $output );
		wp_reset_postdata();
		exit;
	}

	/**
	 * Import-Handler.
	 */
	public function handle_import(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Keine Berechtigung.', 'kulturhaus-events' ) );
		}

		check_admin_referer( 'kh_import_events', 'kh_import_nonce' );

		if ( empty( $_FILES['csv_file']['tmp_name'] ) ) {
			wp_safe_redirect( add_query_arg( 'error', urlencode( __( 'Keine Datei hochgeladen.', 'kulturhaus-events' ) ), wp_get_referer() ) );
			exit;
		}

		$update_existing = isset( $_POST['update_existing'] ) && $_POST['update_existing'] === '1';

		$file = fopen( $_FILES['csv_file']['tmp_name'], 'r' );
		
		// BOM überspringen
		$bom = fread( $file, 3 );
		if ( $bom !== chr(0xEF).chr(0xBB).chr(0xBF) ) {
			rewind( $file );
		}

		$header = fgetcsv( $file, 0, ';', '"', '' );
		$imported = 0;

		while ( ( $row = fgetcsv( $file, 0, ';', '"', '' ) ) !== false ) {
			if ( count( $row ) < count( $header ) ) {
				continue;
			}

			$data = array_combine( $header, $row );

			// Event erstellen oder aktualisieren
			$post_id = 0;
			
			if ( $update_existing ) {
				$existing = get_page_by_title( $data['title'], OBJECT, KH_Event::POST_TYPE );
				if ( $existing ) {
					$post_id = $existing->ID;
				}
			}

			$post_data = array(
				'post_title'   => sanitize_text_field( $data['title'] ),
				'post_content' => wp_kses_post( $data['content'] ),
				'post_status'  => 'publish',
				'post_type'    => KH_Event::POST_TYPE,
			);

			if ( $post_id ) {
				$post_data['ID'] = $post_id;
				wp_update_post( $post_data );
			} else {
				$post_id = wp_insert_post( $post_data );
			}

			if ( ! $post_id || is_wp_error( $post_id ) ) {
				continue;
			}

			// Meta-Daten
			update_post_meta( $post_id, '_kh_event_start_date', sanitize_text_field( $data['start_date'] ) );
			update_post_meta( $post_id, '_kh_event_end_date', sanitize_text_field( $data['end_date'] ) );
			update_post_meta( $post_id, '_kh_event_location', sanitize_text_field( $data['location'] ) );
			update_post_meta( $post_id, '_kh_event_organizer', sanitize_text_field( $data['organizer'] ) );
			update_post_meta( $post_id, '_kh_event_price_regular', sanitize_text_field( $data['price_regular'] ) );
			update_post_meta( $post_id, '_kh_event_price_reduced', sanitize_text_field( $data['price_reduced'] ) );
			update_post_meta( $post_id, '_kh_event_status', sanitize_text_field( $data['status'] ) );

			// Kategorien
			if ( ! empty( $data['category'] ) ) {
				$categories = array_map( 'trim', explode( ',', $data['category'] ) );
				wp_set_object_terms( $post_id, $categories, KH_Event_Category::TAXONOMY );
			}

			// Tags
			if ( ! empty( $data['tags'] ) ) {
				$tags = array_map( 'trim', explode( ',', $data['tags'] ) );
				wp_set_object_terms( $post_id, $tags, KH_Event_Tag::TAXONOMY );
			}

			// Featured Image
			if ( ! empty( $data['featured_image_url'] ) ) {
				$this->set_featured_image_from_url( $post_id, $data['featured_image_url'] );
			}

			$imported++;
		}

		fclose( $file );

		wp_safe_redirect( add_query_arg( 'imported', $imported, wp_get_referer() ) );
		exit;
	}

	/**
	 * Featured Image von URL setzen.
	 *
	 * @param int    $post_id Post-ID.
	 * @param string $image_url Bild-URL.
	 */
	private function set_featured_image_from_url( int $post_id, string $image_url ): void {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$attachment_id = media_sideload_image( $image_url, $post_id, null, 'id' );
		
		if ( ! is_wp_error( $attachment_id ) ) {
			set_post_thumbnail( $post_id, $attachment_id );
		}
	}
}
