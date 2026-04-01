<?php
/**
 * Template: Veranstaltungsarchiv (Listenansicht).
 *
 * Kann im Theme überschrieben werden unter:
 * kulturhaus-events/archive-event.php
 *
 * @package KulturhausEvents
 * @since   1.0.0
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

		<div class="kh-events-list" role="list" aria-label="<?php esc_attr_e( 'Liste der Veranstaltungen', 'kulturhaus-events' ); ?>">

			<?php
			while ( have_posts() ) :
				the_post();

				$start_date    = get_post_meta( get_the_ID(), '_kh_event_start_date', true );
				$end_date      = get_post_meta( get_the_ID(), '_kh_event_end_date', true );
				$venue_id      = get_post_meta( get_the_ID(), '_kh_event_venue_id', true );
				$status        = get_post_meta( get_the_ID(), '_kh_event_status', true );
				$cost          = get_post_meta( get_the_ID(), '_kh_event_cost', true );
				$cost_free     = get_post_meta( get_the_ID(), '_kh_event_cost_free', true );

				$date_format   = get_option( 'kh_date_format', 'd.m.Y' );
				$time_format   = get_option( 'kh_time_format', 'H:i' );

				$status_labels = array(
					'cancelled' => __( 'Abgesagt', 'kulturhaus-events' ),
					'postponed' => __( 'Verschoben', 'kulturhaus-events' ),
					'soldout'   => __( 'Ausverkauft', 'kulturhaus-events' ),
				);
				?>

				<article <?php post_class( 'kh-event-card' ); ?> role="listitem" itemscope itemtype="https://schema.org/Event">

					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="kh-event-card__image">
							<a href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail( 'medium', array( 'itemprop' => 'image' ) ); ?>
							</a>
						</figure>
					<?php endif; ?>

					<div class="kh-event-card__content">

						<?php if ( $start_date ) : ?>
							<time class="kh-event-card__date" datetime="<?php echo esc_attr( gmdate( 'c', strtotime( $start_date ) ) ); ?>" itemprop="startDate">
								<span class="kh-event-card__date-day"><?php echo esc_html( wp_date( 'd', strtotime( $start_date ) ) ); ?></span>
								<span class="kh-event-card__date-month"><?php echo esc_html( wp_date( 'M', strtotime( $start_date ) ) ); ?></span>
							</time>
						<?php endif; ?>

						<div class="kh-event-card__details">
							<h2 class="kh-event-card__title" itemprop="name">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h2>

							<?php if ( $status && 'scheduled' !== $status && isset( $status_labels[ $status ] ) ) : ?>
								<span class="kh-event-card__status kh-event-card__status--<?php echo esc_attr( $status ); ?>">
									<?php echo esc_html( $status_labels[ $status ] ); ?>
								</span>
							<?php endif; ?>

							<div class="kh-event-card__meta">
								<?php if ( $start_date ) : ?>
									<span class="kh-event-card__time">
										<?php echo esc_html( wp_date( $date_format, strtotime( $start_date ) ) ); ?>,
										<?php echo esc_html( wp_date( $time_format, strtotime( $start_date ) ) ); ?>
										<?php esc_html_e( 'Uhr', 'kulturhaus-events' ); ?>
									</span>
								<?php endif; ?>

								<?php
								if ( $venue_id ) :
									$venue = get_post( (int) $venue_id );
									if ( $venue ) :
										?>
										<span class="kh-event-card__venue" itemprop="location" itemscope itemtype="https://schema.org/Place">
											<span itemprop="name"><?php echo esc_html( $venue->post_title ); ?></span>
										</span>
									<?php endif; ?>
								<?php endif; ?>

								<?php if ( $cost_free ) : ?>
									<span class="kh-event-card__cost kh-event-card__cost--free">
										<?php esc_html_e( 'Eintritt frei', 'kulturhaus-events' ); ?>
									</span>
								<?php elseif ( $cost ) : ?>
									<span class="kh-event-card__cost"><?php echo esc_html( $cost ); ?></span>
								<?php endif; ?>
							</div>

							<?php if ( has_excerpt() ) : ?>
								<p class="kh-event-card__excerpt" itemprop="description"><?php echo esc_html( get_the_excerpt() ); ?></p>
							<?php endif; ?>
						</div>

					</div>

				</article>

			<?php endwhile; ?>

		</div>

		<?php the_posts_pagination( array(
			'prev_text' => __( '&laquo; Zurück', 'kulturhaus-events' ),
			'next_text' => __( 'Weiter &raquo;', 'kulturhaus-events' ),
		) ); ?>

	<?php else : ?>

		<p class="kh-events-archive__empty">
			<?php esc_html_e( 'Derzeit sind keine Veranstaltungen geplant.', 'kulturhaus-events' ); ?>
		</p>

	<?php endif; ?>

</main>

<?php
get_footer();
