<?php
/**
 * Template: Veranstaltungsarchiv (Grid-Ansicht im Highlights-Design).
 *
 * Kann im Theme überschrieben werden unter:
 * kulturhaus-events/archive-event.php
 *
 * @package KulturhausEvents
 * @since   1.18.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="main" class="site-main kh-events-archive" role="main">

	<header class="kh-events-archive__header">
		<h1 class="kh-events-archive__title"><?php esc_html_e( 'Veranstaltungen', 'kulturhaus-events' ); ?></h1>
	</header>

	<?php if ( have_posts() ) : ?>

		<div class="kh-events-grid">

			<?php
			while ( have_posts() ) :
				the_post();

				$start_date = get_post_meta( get_the_ID(), '_kh_event_start_date', true );
				$date_format = 'D, d. M';
				?>

				<article class="kh-highlight-card">
					<a href="<?php the_permalink(); ?>" class="kh-highlight-card__link">
						
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="kh-highlight-card__image">
								<?php the_post_thumbnail( 'large' ); ?>
							</div>
						<?php endif; ?>

						<div class="kh-highlight-card__content">
							<?php if ( $start_date ) : ?>
								<time class="kh-highlight-card__date">
									<?php echo esc_html( strtoupper( wp_date( $date_format, strtotime( $start_date ) ) ) ); ?>
								</time>
							<?php endif; ?>
							
							<h3 class="kh-highlight-card__title"><?php echo esc_html( get_the_title() ); ?></h3>
						</div>
					</a>
				</article>

			<?php endwhile; ?>

		</div>

		<?php
		the_posts_pagination(
			array(
				'prev_text' => __( '&laquo; Zurück', 'kulturhaus-events' ),
				'next_text' => __( 'Weiter &raquo;', 'kulturhaus-events' ),
			)
		);
		?>

	<?php else : ?>

		<p class="kh-events-archive__empty">
			<?php esc_html_e( 'Derzeit sind keine Veranstaltungen geplant.', 'kulturhaus-events' ); ?>
		</p>

	<?php endif; ?>

</main>

<?php
get_footer();
