/**
 * Event Calendar JavaScript
 *
 * @package KulturhausEvents
 * @since   1.12.0
 */

(function($) {
	'use strict';

	/**
	 * Kalender Navigation
	 */
	function initCalendarNavigation() {
		$('.kh-calendar-nav').on('click', function(e) {
			e.preventDefault();
			
			const $button = $(this);
			const month = $button.data('month');
			const year = $button.data('year');
			const category = $button.data('category') || '';
			
			// Loading State
			const $calendar = $button.closest('.kh-calendar');
			$calendar.addClass('kh-calendar--loading');
			
			// AJAX Request
			$.ajax({
				url: khCalendar.ajaxUrl,
				type: 'POST',
				data: {
					action: 'kh_load_calendar',
					nonce: khCalendar.nonce,
					month: month,
					year: year,
					category: category
				},
				success: function(response) {
					if (response.success && response.data.html) {
						$calendar.replaceWith(response.data.html);
						// Re-init nach dem Ersetzen
						initCalendarNavigation();
					}
				},
				error: function() {
					alert('Fehler beim Laden des Kalenders.');
				},
				complete: function() {
					$calendar.removeClass('kh-calendar--loading');
				}
			});
		});
	}

	/**
	 * Event Tooltips
	 */
	function initEventTooltips() {
		$('.kh-calendar-event').on('mouseenter', function() {
			const $event = $(this);
			const title = $event.attr('title');
			
			if (title && window.innerWidth > 768) {
				// Tooltip anzeigen (optional - kann später erweitert werden)
			}
		});
	}

	/**
	 * Init
	 */
	$(document).ready(function() {
		if ($('.kh-calendar').length) {
			initCalendarNavigation();
			initEventTooltips();
		}
	});

})(jQuery);
