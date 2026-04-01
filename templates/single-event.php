<?php
/**
 * Template: Einzelne Veranstaltung
 * Design nach Screenshot-Vorlage
 *
 * @package KulturhausEvents
 * @since   1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$event_id    = get_the_ID();
$start_date  = get_post_meta( $event_id, '_kh_event_start_date', true );
$end_date    = get_post_meta( $event_id, '_kh_event_end_date', true );
$all_day     = get_post_meta( $event_id, '_kh_event_all_day', true );
$venue_id    = get_post_meta( $event_id, '_kh_event_venue_id', true );
$organizer_id = get_post_meta( $event_id, '_kh_event_organizer_id', true );
$ticket_url  = get_post_meta( $event_id, '_kh_event_url', true );
$cost        = get_post_meta( $event_id, '_kh_event_cost', true );
$cost_free   = get_post_meta( $event_id, '_kh_event_cost_free', true );
$accessibility = get_post_meta( $event_id, '_kh_event_accessibility', true );

$date_format = get_option( 'kh_date_format', 'd.m.Y' );
$time_format = get_option( 'kh_time_format', 'H:i' );

// Venue-Daten
$venue = null;
$venue_name = '';
$venue_city = '';
$venue_address = '';
if ( $venue_id ) {
	$venue = get_post( (int) $venue_id );
	if ( $venue ) {
		$venue_name = $venue->post_title;
		$venue_city = get_post_meta( $venue->ID, '_kh_venue_city', true );
		$venue_address = get_post_meta( $venue->ID, '_kh_venue_address', true );
	}
}

// Organizer-Daten
$organizer = null;
$organizer_name = '';
if ( $organizer_id ) {
	$organizer = get_post( (int) $organizer_id );
	if ( $organizer ) {
		$organizer_name = $organizer->post_title;
	}
}
?>

<div class="kh-single-event">
	
	<?php while ( have_posts() ) : the_post(); ?>

		<!-- Bild-Slider -->
		<?php
		$gallery_ids = get_post_meta( $event_id, '_kh_event_gallery', true );
		$images = array();
		
		if ( $gallery_ids ) {
			$images = explode( ',', $gallery_ids );
		} elseif ( has_post_thumbnail() ) {
			$images = array( get_post_thumbnail_id() );
		}
		
		if ( ! empty( $images ) ) :
		?>
			<div class="kh-event-slider">
				<div class="kh-slider-container">
					<?php if ( count( $images ) > 1 ) : ?>
						<button class="kh-slider-arrow kh-slider-prev" aria-label="Vorheriges Bild">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
								<path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
							</svg>
						</button>
					<?php endif; ?>
					
					<div class="kh-slider-track">
						<?php foreach ( $images as $image_id ) : ?>
							<?php if ( $image_id ) : ?>
								<div class="kh-slider-image">
									<?php echo wp_get_attachment_image( $image_id, 'full' ); ?>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
					
					<?php if ( count( $images ) > 1 ) : ?>
						<button class="kh-slider-arrow kh-slider-next" aria-label="Nächstes Bild">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
								<path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
							</svg>
						</button>
						
						<div class="kh-slider-dots">
							<?php foreach ( $images as $index => $image_id ) : ?>
								<button class="kh-slider-dot <?php echo $index === 0 ? 'active' : ''; ?>" data-slide="<?php echo esc_attr( $index ); ?>"></button>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Content Wrapper -->
		<div class="kh-event-wrapper">
			
			<!-- Main Content -->
			<div class="kh-event-main">
				
				<h1 class="kh-event-title"><?php the_title(); ?></h1>
				
				<div class="kh-event-content">
					<?php the_content(); ?>
				</div>

				<?php if ( $accessibility ) : ?>
					<div class="kh-event-accessibility">
						<h3>Barrierefreiheit</h3>
						<p><?php echo esc_html( $accessibility ); ?></p>
					</div>
				<?php endif; ?>

			</div>

			<!-- Sidebar -->
			<aside class="kh-event-sidebar">
				
				<div class="kh-sidebar-box">
					
					<!-- Datum -->
					<?php if ( $start_date ) : ?>
						<div class="kh-info-item">
							<div class="kh-info-icon">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
									<line x1="16" y1="2" x2="16" y2="6"></line>
									<line x1="8" y1="2" x2="8" y2="6"></line>
									<line x1="3" y1="10" x2="21" y2="10"></line>
								</svg>
							</div>
							<div class="kh-info-text">
								<?php 
								$weekday = wp_date( 'l', strtotime( $start_date ) );
								$date = wp_date( $date_format, strtotime( $start_date ) );
								$time = wp_date( $time_format, strtotime( $start_date ) );
								echo esc_html( $weekday . ', ' . $date . ', ' . $time . ' Uhr' );
								?>
							</div>
						</div>
					<?php endif; ?>

					<!-- Ort -->
					<?php if ( $venue_name ) : ?>
						<div class="kh-info-item">
							<div class="kh-info-icon">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
									<circle cx="12" cy="10" r="3"></circle>
								</svg>
							</div>
							<div class="kh-info-text">
								<?php 
								echo esc_html( $venue_name );
								if ( $venue_city ) {
									echo ', ' . esc_html( $venue_city );
								}
								?>
							</div>
						</div>
					<?php endif; ?>

					<!-- Adresse -->
					<?php if ( $venue && $venue_address ) : ?>
						<div class="kh-info-item">
							<div class="kh-info-icon">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
									<polyline points="9 22 9 12 15 12 15 22"></polyline>
								</svg>
							</div>
							<div class="kh-info-text">
								<?php echo esc_html( $venue_address ); ?>
							</div>
						</div>
					<?php endif; ?>

					<!-- Preis -->
					<?php if ( $cost || $cost_free ) : ?>
						<div class="kh-info-item">
							<div class="kh-info-icon">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<line x1="12" y1="1" x2="12" y2="23"></line>
									<path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
								</svg>
							</div>
							<div class="kh-info-text">
								<?php 
								if ( $cost_free ) {
									echo esc_html__( 'Eintritt frei', 'kulturhaus-events' );
								} else {
									echo esc_html( $cost );
								}
								?>
							</div>
						</div>
					<?php endif; ?>

					<!-- Veranstalter -->
					<?php if ( $organizer_name ) : ?>
						<div class="kh-info-item">
							<div class="kh-info-icon">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
									<circle cx="9" cy="7" r="4"></circle>
									<path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
									<path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
								</svg>
							</div>
							<div class="kh-info-text">
								<?php echo esc_html( $organizer_name ); ?>
							</div>
						</div>
					<?php endif; ?>

				</div>

				<!-- Ticket Button -->
				<?php if ( $ticket_url ) : ?>
					<a href="<?php echo esc_url( $ticket_url ); ?>" class="kh-btn kh-btn-tickets" target="_blank" rel="noopener">
						Tickets kaufen
					</a>
				<?php endif; ?>

				<?php
				// Preismodell-Darstellung in Sidebar
				$pricing_model_id = get_post_meta( $event_id, '_kh_event_pricing_model_id', true );
				$event_prices     = get_post_meta( $event_id, '_kh_event_prices', true );
				
				if ( $pricing_model_id && class_exists( 'KH_Pricing_Model' ) ) :
					$pricing_model = get_post( (int) $pricing_model_id );
					if ( $pricing_model && $pricing_model->post_status === 'publish' ) :
						$model_type   = get_post_meta( $pricing_model->ID, '_kh_pricing_model_type', true );
						$model_notice = get_post_meta( $pricing_model->ID, '_kh_pricing_model_notice', true );
						$model_tiers  = get_post_meta( $pricing_model->ID, '_kh_pricing_model_tiers', true );
						
						if ( ! is_array( $event_prices ) ) {
							$event_prices = array();
						}
						?>
						
						<?php if ( $model_type === 'free' ) : ?>
							<div class="kh-sidebar-pricing kh-sidebar-pricing--free">
								<div class="kh-sidebar-pricing__badge">Eintritt frei</div>
								<?php if ( $model_notice ) : ?>
									<p class="kh-sidebar-pricing__notice"><?php echo esc_html( $model_notice ); ?></p>
								<?php endif; ?>
							</div>
						<?php else : ?>
							<?php if ( ! empty( $model_tiers ) && is_array( $model_tiers ) ) : ?>
								<div class="kh-sidebar-pricing">
									<h3 class="kh-sidebar-pricing__title">Eintritt</h3>
									<div class="kh-sidebar-pricing__list">
										<?php foreach ( $model_tiers as $index => $tier ) : ?>
											<?php $price = $event_prices[ $index ] ?? ''; ?>
											<?php if ( $price ) : ?>
												<div class="kh-sidebar-pricing__item">
													<span class="kh-sidebar-pricing__label"><?php echo esc_html( $tier['label'] ); ?></span>
													<span class="kh-sidebar-pricing__price"><?php echo esc_html( $price ); ?></span>
												</div>
											<?php endif; ?>
										<?php endforeach; ?>
									</div>
									<?php if ( $model_notice ) : ?>
										<div class="kh-sidebar-pricing__notice"><?php echo esc_html( $model_notice ); ?></div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						<?php endif; ?>
					<?php endif; ?>
				<?php endif; ?>

				<!-- Kalender Button -->
				<a href="<?php echo esc_url( KH_ICal_Export::get_ical_url( $event_id ) ); ?>" class="kh-btn kh-btn-calendar" download>
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
						<line x1="16" y1="2" x2="16" y2="6"></line>
						<line x1="8" y1="2" x2="8" y2="6"></line>
						<line x1="3" y1="10" x2="21" y2="10"></line>
					</svg>
					Zum Kalender hinzufügen
				</a>

			</aside>

		</div>

	<?php endwhile; ?>

</div>

<?php get_footer(); ?>
