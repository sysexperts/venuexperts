/**
 * Event Countdown Timer
 * Live-Countdown für nächstes Event
 */

(function($) {
	'use strict';

	class EventCountdown {
		constructor(element) {
			this.$element = $(element);
			this.eventDate = new Date(this.$element.data('event-date')).getTime();
			this.$days = this.$element.find('[data-unit="days"]');
			this.$hours = this.$element.find('[data-unit="hours"]');
			this.$minutes = this.$element.find('[data-unit="minutes"]');
			this.$seconds = this.$element.find('[data-unit="seconds"]');

			if (isNaN(this.eventDate)) {
				console.error('Invalid event date');
				return;
			}

			this.start();
		}

		start() {
			// Initial update
			this.update();

			// Update every second
			this.interval = setInterval(() => {
				this.update();
			}, 1000);
		}

		update() {
			const now = new Date().getTime();
			const distance = this.eventDate - now;

			// Event bereits vorbei
			if (distance < 0) {
				clearInterval(this.interval);
				this.$days.text('0');
				this.$hours.text('0');
				this.$minutes.text('0');
				this.$seconds.text('0');
				return;
			}

			// Berechne Zeit-Einheiten
			const days = Math.floor(distance / (1000 * 60 * 60 * 24));
			const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
			const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
			const seconds = Math.floor((distance % (1000 * 60)) / 1000);

			// Update DOM mit Animation
			this.updateValue(this.$days, days);
			this.updateValue(this.$hours, hours);
			this.updateValue(this.$minutes, minutes);
			this.updateValue(this.$seconds, seconds);
		}

		updateValue($element, newValue) {
			const currentValue = parseInt($element.text());
			
			if (currentValue !== newValue) {
				$element.fadeOut(150, function() {
					$(this).text(newValue).fadeIn(150);
				});
			}
		}

		destroy() {
			if (this.interval) {
				clearInterval(this.interval);
			}
		}
	}

	// Initialize all countdown widgets
	$(document).ready(function() {
		$('.kh-event-countdown').each(function() {
			new EventCountdown(this);
		});
	});

})(jQuery);
