<?php
/**
 * Template: Einzelne Veranstaltung.
 *
 * Kann im Theme überschrieben werden unter:
 * kulturhaus-events/single-event.php
 *
 * @package KulturhausEvents
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="main" class="site-main kh-event-single" role="main">

	<?php while ( have_posts() ) : the_post(); ?>

		<article id="event-<?php the_ID(); ?>" <?php post_class( 'kh-event' ); ?> itemscope itemtype="https://schema.org/Event">

			<header class="kh-event__header">
				<h1 class="kh-event__title" itemprop="name"><?php the_title(); ?></h1>

				<?php
				$status = get_post_meta( get_the_ID(), '_kh_event_status', true );
				if ( $status && 'scheduled' !== $status ) :
					$status_labels = array(
						'cancelled' => __( 'Abgesagt', 'kulturhaus-events' ),
						'postponed' => __( 'Verschoben', 'kulturhaus-events' ),
						'soldout'   => __( 'Ausverkauft', 'kulturhaus-events' ),
					);
					?>
					<p class="kh-event__status kh-event__status--<?php echo esc_attr( $status ); ?>" role="alert">
						<strong><?php echo esc_html( $status_labels[ $status ] ?? $status ); ?></strong>
					</p>
				<?php endif; ?>
			</header>

			<div class="kh-event__content-wrap">

				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="kh-event__image">
						<?php the_post_thumbnail( 'large', array( 'itemprop' => 'image' ) ); ?>
					</figure>
				<?php endif; ?>

				<div class="kh-event__meta" aria-label="<?php esc_attr_e( 'Veranstaltungsdetails', 'kulturhaus-events' ); ?>">

					<?php
					$start_date = get_post_meta( get_the_ID(), '_kh_event_start_date', true );
					$end_date   = get_post_meta( get_the_ID(), '_kh_event_end_date', true );
					$all_day    = get_post_meta( get_the_ID(), '_kh_event_all_day', true );

					$date_format = get_option( 'kh_date_format', 'd.m.Y' );
					$time_format = get_option( 'kh_time_format', 'H:i' );

					if ( $start_date ) :
						?>
						<div class="kh-event__date">
							<strong><?php esc_html_e( 'Datum & Uhrzeit', 'kulturhaus-events' ); ?></strong>
							<time datetime="<?php echo esc_attr( gmdate( 'c', strtotime( $start_date ) ) ); ?>" itemprop="startDate" content="<?php echo esc_attr( gmdate( 'c', strtotime( $start_date ) ) ); ?>">
								<?php echo esc_html( wp_date( $date_format, strtotime( $start_date ) ) ); ?>
								<?php if ( ! $all_day ) : ?>
									<?php echo esc_html( wp_date( $time_format, strtotime( $start_date ) ) ); ?>
									<?php esc_html_e( 'Uhr', 'kulturhaus-events' ); ?>
								<?php else : ?>
									(<?php esc_html_e( 'ganztägig', 'kulturhaus-events' ); ?>)
								<?php endif; ?>
							</time>

							<?php if ( $end_date ) : ?>
								<br>
								<span><?php esc_html_e( 'bis', 'kulturhaus-events' ); ?></span>
								<time datetime="<?php echo esc_attr( gmdate( 'c', strtotime( $end_date ) ) ); ?>" itemprop="endDate" content="<?php echo esc_attr( gmdate( 'c', strtotime( $end_date ) ) ); ?>">
									<?php echo esc_html( wp_date( $date_format, strtotime( $end_date ) ) ); ?>
									<?php if ( ! $all_day ) : ?>
										<?php echo esc_html( wp_date( $time_format, strtotime( $end_date ) ) ); ?>
										<?php esc_html_e( 'Uhr', 'kulturhaus-events' ); ?>
									<?php endif; ?>
								</time>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php
					// Veranstaltungsort.
					$venue_id = get_post_meta( get_the_ID(), '_kh_event_venue_id', true );
					if ( $venue_id ) :
						$venue = get_post( (int) $venue_id );
						if ( $venue ) :
							$address = get_post_meta( $venue->ID, '_kh_venue_address', true );
							$zip     = get_post_meta( $venue->ID, '_kh_venue_zip', true );
							$city    = get_post_meta( $venue->ID, '_kh_venue_city', true );
							?>
							<div class="kh-event__venue" itemprop="location" itemscope itemtype="https://schema.org/Place">
								<strong><?php esc_html_e( 'Veranstaltungsort', 'kulturhaus-events' ); ?></strong>
								<span itemprop="name"><?php echo esc_html( $venue->post_title ); ?></span>
								<?php if ( $address || $city ) : ?>
									<address itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
										<?php if ( $address ) : ?>
											<span itemprop="streetAddress"><?php echo esc_html( $address ); ?></span><br>
										<?php endif; ?>
										<?php if ( $zip || $city ) : ?>
											<span itemprop="postalCode"><?php echo esc_html( $zip ); ?></span>
											<span itemprop="addressLocality"><?php echo esc_html( $city ); ?></span>
										<?php endif; ?>
									</address>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					<?php endif; ?>

					<?php
					// Veranstalter.
					$organizer_id = get_post_meta( get_the_ID(), '_kh_event_organizer_id', true );
					if ( $organizer_id ) :
						$organizer = get_post( (int) $organizer_id );
						if ( $organizer ) :
							?>
							<div class="kh-event__organizer" itemprop="organizer" itemscope itemtype="https://schema.org/Organization">
								<strong><?php esc_html_e( 'Veranstalter', 'kulturhaus-events' ); ?></strong>
								<span itemprop="name"><?php echo esc_html( $organizer->post_title ); ?></span>
							</div>
						<?php endif; ?>
					<?php endif; ?>

					<?php
					// Eintritt.
					$cost      = get_post_meta( get_the_ID(), '_kh_event_cost', true );
					$cost_free = get_post_meta( get_the_ID(), '_kh_event_cost_free', true );
					if ( $cost || $cost_free ) :
						?>
						<div class="kh-event__cost">
							<strong><?php esc_html_e( 'Eintritt', 'kulturhaus-events' ); ?></strong>
							<?php if ( $cost_free ) : ?>
								<span itemprop="isAccessibleForFree" content="true"><?php esc_html_e( 'Eintritt frei', 'kulturhaus-events' ); ?></span>
							<?php else : ?>
								<span itemprop="offers" itemscope itemtype="https://schema.org/Offer">
									<span itemprop="price"><?php echo esc_html( $cost ); ?></span>
								</span>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php
					// Barrierefreiheit.
					$accessibility = get_post_meta( get_the_ID(), '_kh_event_accessibility', true );
					if ( $accessibility ) :
						?>
						<div class="kh-event__accessibility">
							<strong><?php esc_html_e( 'Barrierefreiheit', 'kulturhaus-events' ); ?></strong>
							<?php echo esc_html( $accessibility ); ?>
						</div>
					<?php endif; ?>

					<?php
					// Kategorien.
					$categories = get_the_terms( get_the_ID(), 'kh_event_cat' );
					if ( $categories && ! is_wp_error( $categories ) ) :
						?>
						<div class="kh-event__categories">
							<strong><?php esc_html_e( 'Kategorie', 'kulturhaus-events' ); ?></strong>
							<?php
							$cat_links = array();
							foreach ( $categories as $cat ) {
								$cat_links[] = sprintf(
									'<a href="%s">%s</a>',
									esc_url( get_term_link( $cat ) ),
									esc_html( $cat->name )
								);
							}
							echo wp_kses_post( implode( ', ', $cat_links ) );
							?>
						</div>
					<?php endif; ?>

				</div>

				<div class="kh-event__description" itemprop="description">
					<?php the_content(); ?>
				</div>

			</div>

		</article>

	<?php endwhile; ?>

</main>

<?php
get_footer();
