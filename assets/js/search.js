/**
 * Event Search & Filter JavaScript
 *
 * @package KulturhausEvents
 * @since   1.20.0
 */

(function($) {
	'use strict';

	class EventSearch {
		constructor() {
			this.$form = $('.kh-search-form');
			this.$results = $('.kh-search-results');
			this.$loading = $('.kh-search-loading');
			this.$resetBtn = $('.kh-search-reset');
			
			this.init();
		}

		init() {
			// Form submit
			this.$form.on('submit', (e) => {
				e.preventDefault();
				this.performSearch();
			});

			// Live search on input (debounced)
			let searchTimeout;
			$('#kh-search-query').on('input', () => {
				clearTimeout(searchTimeout);
				searchTimeout = setTimeout(() => {
					this.performSearch();
				}, 500);
			});

			// Filter changes
			$('.kh-search-field select').on('change', () => {
				this.performSearch();
			});

			// Reset button
			this.$resetBtn.on('click', (e) => {
				e.preventDefault();
				this.resetFilters();
			});
		}

		performSearch() {
			const formData = {
				action: 'kh_search_events',
				nonce: khSearchData.nonce,
				query: $('#kh-search-query').val(),
				category: $('#kh-search-category').val(),
				month: $('#kh-search-month').val(),
				venue: $('#kh-search-venue').val()
			};

			// Show loading
			this.$loading.addClass('active');
			this.$results.hide();

			// AJAX request
			$.ajax({
				url: khSearchData.ajaxUrl,
				type: 'POST',
				data: formData,
				success: (response) => {
					this.$loading.removeClass('active');
					this.$results.html(response.data.html).fadeIn(300);
				},
				error: () => {
					this.$loading.removeClass('active');
					this.$results.html('<p>Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.</p>').fadeIn(300);
				}
			});
		}

		resetFilters() {
			$('#kh-search-query').val('');
			$('#kh-search-category').val('');
			$('#kh-search-month').val('');
			$('#kh-search-venue').val('');
			this.performSearch();
		}
	}

	// Initialize on document ready
	$(document).ready(function() {
		if ($('.kh-search-form').length) {
			new EventSearch();
		}
	});

})(jQuery);
