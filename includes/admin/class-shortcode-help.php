<?php
/**
 * Shortcode-Hilfe Admin-Seite
 *
 * @package KulturhausEvents
 * @since   1.10.1
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KH_Shortcode_Help
 *
 * Zeigt eine Übersicht aller verfügbaren Shortcodes im Admin.
 */
class KH_Shortcode_Help {

	/**
	 * Hooks registrieren.
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_help_page' ) );
	}

	/**
	 * Hilfe-Seite zum Admin-Menü hinzufügen.
	 */
	public function add_help_page(): void {
		add_submenu_page(
			'edit.php?post_type=kh_event',
			__( 'Shortcodes', 'kulturhaus-events' ),
			__( 'Shortcodes', 'kulturhaus-events' ),
			'edit_posts',
			'kh-shortcodes',
			array( $this, 'render_help_page' )
		);
	}

	/**
	 * Hilfe-Seite rendern.
	 */
	public function render_help_page(): void {
		?>
		<div class="wrap kh-shortcode-help">
			<h1><?php esc_html_e( 'Verfügbare Shortcodes', 'kulturhaus-events' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Kopiere die Shortcodes und füge sie in deine Seiten oder Beiträge ein.', 'kulturhaus-events' ); ?>
			</p>

			<div class="kh-shortcode-cards">
				
				<!-- Event Highlights -->
				<div class="kh-shortcode-card">
					<div class="kh-shortcode-card__header">
						<h2>🎨 <?php esc_html_e( 'Event Highlights', 'kulturhaus-events' ); ?></h2>
						<span class="kh-badge kh-badge--new"><?php esc_html_e( 'Neu', 'kulturhaus-events' ); ?></span>
					</div>
					<p><?php esc_html_e( 'Modernes Grid-Layout für Featured Events auf der Startseite', 'kulturhaus-events' ); ?></p>
					
					<div class="kh-shortcode-code">
						<code>[kh_event_highlights limit="3" title="HIGHLIGHTS" subtitle="Dein Kulturhaus" button_text="UNSER AKTUELLES PROGRAMM" button_link="/veranstaltungen/"]</code>
						<button class="button button-small kh-copy-btn" data-clipboard="[kh_event_highlights limit=&quot;3&quot; title=&quot;HIGHLIGHTS&quot; subtitle=&quot;Dein Kulturhaus&quot; button_text=&quot;UNSER AKTUELLES PROGRAMM&quot; button_link=&quot;/veranstaltungen/&quot;]">
							<?php esc_html_e( 'Kopieren', 'kulturhaus-events' ); ?>
						</button>
					</div>

					<details class="kh-shortcode-details">
						<summary><?php esc_html_e( 'Alle Attribute', 'kulturhaus-events' ); ?></summary>
						<table class="kh-attributes-table">
							<tr>
								<td><code>limit</code></td>
								<td><?php esc_html_e( 'Anzahl der Events (Standard: 3)', 'kulturhaus-events' ); ?></td>
							</tr>
							<tr>
								<td><code>category</code></td>
								<td><?php esc_html_e( 'Kategorie-Slug (optional)', 'kulturhaus-events' ); ?></td>
							</tr>
							<tr>
								<td><code>title</code></td>
								<td><?php esc_html_e( 'Überschrift (Standard: "HIGHLIGHTS")', 'kulturhaus-events' ); ?></td>
							</tr>
							<tr>
								<td><code>subtitle</code></td>
								<td><?php esc_html_e( 'Untertitel (optional)', 'kulturhaus-events' ); ?></td>
							</tr>
							<tr>
								<td><code>button_text</code></td>
								<td><?php esc_html_e( 'Button-Text', 'kulturhaus-events' ); ?></td>
							</tr>
							<tr>
								<td><code>button_link</code></td>
								<td><?php esc_html_e( 'Button-Link (optional)', 'kulturhaus-events' ); ?></td>
							</tr>
						</table>
					</details>
				</div>

				<!-- Event Liste -->
				<div class="kh-shortcode-card">
					<div class="kh-shortcode-card__header">
						<h2>📋 <?php esc_html_e( 'Event-Liste', 'kulturhaus-events' ); ?></h2>
					</div>
					<p><?php esc_html_e( 'Zeigt eine Liste von Veranstaltungen mit Filtern', 'kulturhaus-events' ); ?></p>
					
					<div class="kh-shortcode-code">
						<code>[kh_events limit="10" category="konzerte" order="ASC"]</code>
						<button class="button button-small kh-copy-btn" data-clipboard="[kh_events limit=&quot;10&quot; category=&quot;konzerte&quot; order=&quot;ASC&quot;]">
							<?php esc_html_e( 'Kopieren', 'kulturhaus-events' ); ?>
						</button>
					</div>

					<details class="kh-shortcode-details">
						<summary><?php esc_html_e( 'Alle Attribute', 'kulturhaus-events' ); ?></summary>
						<table class="kh-attributes-table">
							<tr>
								<td><code>limit</code></td>
								<td><?php esc_html_e( 'Anzahl der Events (Standard: 10)', 'kulturhaus-events' ); ?></td>
							</tr>
							<tr>
								<td><code>category</code></td>
								<td><?php esc_html_e( 'Kategorie-Slug', 'kulturhaus-events' ); ?></td>
							</tr>
							<tr>
								<td><code>tag</code></td>
								<td><?php esc_html_e( 'Tag-Slug', 'kulturhaus-events' ); ?></td>
							</tr>
							<tr>
								<td><code>venue</code></td>
								<td><?php esc_html_e( 'Veranstaltungsort-ID', 'kulturhaus-events' ); ?></td>
							</tr>
							<tr>
								<td><code>organizer</code></td>
								<td><?php esc_html_e( 'Veranstalter-ID', 'kulturhaus-events' ); ?></td>
							</tr>
							<tr>
								<td><code>order</code></td>
								<td><?php esc_html_e( 'ASC oder DESC (Standard: ASC)', 'kulturhaus-events' ); ?></td>
							</tr>
							<tr>
								<td><code>show_past</code></td>
								<td><?php esc_html_e( 'true/false - Vergangene Events anzeigen', 'kulturhaus-events' ); ?></td>
							</tr>
						</table>
					</details>
				</div>

				<!-- Kommende Events -->
				<div class="kh-shortcode-card">
					<div class="kh-shortcode-card__header">
						<h2>📅 <?php esc_html_e( 'Kommende Events (Kompakt)', 'kulturhaus-events' ); ?></h2>
					</div>
					<p><?php esc_html_e( 'Kompakte Liste kommender Veranstaltungen', 'kulturhaus-events' ); ?></p>
					
					<div class="kh-shortcode-code">
						<code>[kh_upcoming_events limit="5" category="konzerte"]</code>
						<button class="button button-small kh-copy-btn" data-clipboard="[kh_upcoming_events limit=&quot;5&quot; category=&quot;konzerte&quot;]">
							<?php esc_html_e( 'Kopieren', 'kulturhaus-events' ); ?>
						</button>
					</div>

					<details class="kh-shortcode-details">
						<summary><?php esc_html_e( 'Alle Attribute', 'kulturhaus-events' ); ?></summary>
						<table class="kh-attributes-table">
							<tr>
								<td><code>limit</code></td>
								<td><?php esc_html_e( 'Anzahl der Events (Standard: 5)', 'kulturhaus-events' ); ?></td>
							</tr>
							<tr>
								<td><code>category</code></td>
								<td><?php esc_html_e( 'Kategorie-Slug', 'kulturhaus-events' ); ?></td>
							</tr>
						</table>
					</details>
				</div>

			</div>

			<div class="kh-help-footer">
				<h3><?php esc_html_e( 'Verwendung', 'kulturhaus-events' ); ?></h3>
				<ul>
					<li><strong>Gutenberg:</strong> <?php esc_html_e( 'Shortcode-Block verwenden', 'kulturhaus-events' ); ?></li>
					<li><strong>Classic Editor:</strong> <?php esc_html_e( 'Direkt in den Editor einfügen', 'kulturhaus-events' ); ?></li>
					<li><strong>Widgets:</strong> <?php esc_html_e( 'Text-Widget verwenden', 'kulturhaus-events' ); ?></li>
					<li><strong>PHP:</strong> <code>&lt;?php echo do_shortcode('[kh_events]'); ?&gt;</code></li>
				</ul>
			</div>
		</div>

		<style>
			.kh-shortcode-help {
				max-width: 1200px;
			}

			.kh-shortcode-cards {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
				gap: 20px;
				margin: 30px 0;
			}

			.kh-shortcode-card {
				background: #fff;
				border: 1px solid #ddd;
				border-radius: 8px;
				padding: 24px;
				box-shadow: 0 2px 8px rgba(0,0,0,0.05);
			}

			.kh-shortcode-card__header {
				display: flex;
				align-items: center;
				gap: 12px;
				margin-bottom: 12px;
			}

			.kh-shortcode-card h2 {
				margin: 0;
				font-size: 18px;
			}

			.kh-badge {
				display: inline-block;
				padding: 4px 10px;
				font-size: 11px;
				font-weight: 600;
				border-radius: 12px;
				text-transform: uppercase;
			}

			.kh-badge--new {
				background: #ffc107;
				color: #000;
			}

			.kh-shortcode-code {
				position: relative;
				background: #f5f5f5;
				border: 1px solid #ddd;
				border-radius: 4px;
				padding: 12px;
				margin: 16px 0;
			}

			.kh-shortcode-code code {
				display: block;
				word-wrap: break-word;
				font-size: 13px;
				line-height: 1.6;
				color: #d63638;
			}

			.kh-copy-btn {
				position: absolute;
				top: 8px;
				right: 8px;
			}

			.kh-shortcode-details {
				margin-top: 16px;
			}

			.kh-shortcode-details summary {
				cursor: pointer;
				font-weight: 600;
				padding: 8px 0;
				color: #2271b1;
			}

			.kh-shortcode-details summary:hover {
				color: #135e96;
			}

			.kh-attributes-table {
				width: 100%;
				margin-top: 12px;
				border-collapse: collapse;
			}

			.kh-attributes-table td {
				padding: 8px 12px;
				border-bottom: 1px solid #f0f0f0;
			}

			.kh-attributes-table td:first-child {
				font-weight: 600;
				color: #d63638;
				white-space: nowrap;
			}

			.kh-help-footer {
				background: #f9f9f9;
				border: 1px solid #ddd;
				border-radius: 8px;
				padding: 24px;
				margin-top: 30px;
			}

			.kh-help-footer h3 {
				margin-top: 0;
			}

			.kh-help-footer ul {
				list-style: none;
				padding: 0;
			}

			.kh-help-footer li {
				padding: 8px 0;
			}
		</style>

		<script>
		jQuery(document).ready(function($) {
			$('.kh-copy-btn').on('click', function() {
				var text = $(this).data('clipboard');
				navigator.clipboard.writeText(text).then(function() {
					var btn = $(this);
					var originalText = btn.text();
					btn.text('✓ Kopiert!');
					setTimeout(function() {
						btn.text(originalText);
					}, 2000);
				}.bind(this));
			});
		});
		</script>
		<?php
	}
}
