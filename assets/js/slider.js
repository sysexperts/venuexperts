/**
 * Event Slider Funktionalität
 * 
 * @package KulturhausEvents
 * @since   1.3.2
 */

(function() {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {
		const sliderContainer = document.querySelector('.kh-slider-container');
		if (!sliderContainer) return;

		const track = sliderContainer.querySelector('.kh-slider-track');
		const slides = sliderContainer.querySelectorAll('.kh-slider-image');
		const prevBtn = sliderContainer.querySelector('.kh-slider-prev');
		const nextBtn = sliderContainer.querySelector('.kh-slider-next');
		const dots = sliderContainer.querySelectorAll('.kh-slider-dot');

		if (!track || slides.length <= 1) return;

		let currentSlide = 0;
		const totalSlides = slides.length;

		function goToSlide(index) {
			if (index < 0) {
				currentSlide = totalSlides - 1;
			} else if (index >= totalSlides) {
				currentSlide = 0;
			} else {
				currentSlide = index;
			}

			track.style.transform = `translateX(-${currentSlide * 100}%)`;

			// Update dots
			dots.forEach((dot, i) => {
				dot.classList.toggle('active', i === currentSlide);
			});
		}

		// Previous button
		if (prevBtn) {
			prevBtn.addEventListener('click', function() {
				goToSlide(currentSlide - 1);
			});
		}

		// Next button
		if (nextBtn) {
			nextBtn.addEventListener('click', function() {
				goToSlide(currentSlide + 1);
			});
		}

		// Dots
		dots.forEach((dot, index) => {
			dot.addEventListener('click', function() {
				goToSlide(index);
			});
		});

		// Keyboard navigation
		document.addEventListener('keydown', function(e) {
			if (e.key === 'ArrowLeft') {
				goToSlide(currentSlide - 1);
			} else if (e.key === 'ArrowRight') {
				goToSlide(currentSlide + 1);
			}
		});

		// Touch/Swipe support
		let touchStartX = 0;
		let touchEndX = 0;

		track.addEventListener('touchstart', function(e) {
			touchStartX = e.changedTouches[0].screenX;
		}, { passive: true });

		track.addEventListener('touchend', function(e) {
			touchEndX = e.changedTouches[0].screenX;
			handleSwipe();
		}, { passive: true });

		function handleSwipe() {
			const swipeThreshold = 50;
			const diff = touchStartX - touchEndX;

			if (Math.abs(diff) > swipeThreshold) {
				if (diff > 0) {
					// Swipe left - next slide
					goToSlide(currentSlide + 1);
				} else {
					// Swipe right - previous slide
					goToSlide(currentSlide - 1);
				}
			}
		}

		// Auto-play (optional, 5 seconds)
		// Uncomment if you want auto-play
		/*
		setInterval(function() {
			goToSlide(currentSlide + 1);
		}, 5000);
		*/
	});
})();
