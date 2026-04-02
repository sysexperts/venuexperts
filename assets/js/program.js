/**
 * Event Programm Navigation & Filter
 *
 * @package KulturhausEvents
 * @since   1.15.0
 */

function khNavigateMonth(direction) {
	const currentUrl = new URL(window.location.href);
	const params = currentUrl.searchParams;
	
	let currentMonth = parseInt(params.get('month')) || new Date().getMonth() + 1;
	let currentYear = parseInt(params.get('year')) || new Date().getFullYear();
	
	currentMonth += direction;
	
	if (currentMonth > 12) {
		currentMonth = 1;
		currentYear++;
	} else if (currentMonth < 1) {
		currentMonth = 12;
		currentYear--;
	}
	
	params.set('month', currentMonth);
	params.set('year', currentYear);
	
	window.location.href = currentUrl.toString();
}

(function($) {
	'use strict';

	class ProgramFilter {
		constructor() {
			this.$container = $('.kh-event-program');
			if (this.$container.length === 0) return;

			this.$searchInput = $('#kh-program-search');
			this.$categorySelect = $('#kh-program-category');
			this.$timeframeSelect = $('#kh-program-timeframe');
			this.$resetBtn = $('#kh-program-filter-reset');
			this.$eventsList = $('#kh-program-events-list');
			this.$resultsCount = $('#kh-program-results-count');

			this.limit = this.$container.data('limit') || 5;
			this.showAllLink = this.$container.data('show-all-link') === 1;
			this.allLinkUrl = this.$container.data('all-link-url') || '/veranstaltungen/';
			
			this.searchTimeout = null;
			
			this.init();
		}

		init() {
			// Search with debounce
			this.$searchInput.on('input', () => {
				clearTimeout(this.searchTimeout);
				this.searchTimeout = setTimeout(() => this.applyFilters(), 500);
			});

			// Filter changes
			this.$categorySelect.on('change', () => this.applyFilters());
			this.$timeframeSelect.on('change', () => this.applyFilters());

			// Reset
			this.$resetBtn.on('click', () => this.resetFilters());
		}

		applyFilters() {
			const search = this.$searchInput.val();
			const category = this.$categorySelect.val();
			const timeframe = this.$timeframeSelect.val();

			// Loading state
			this.$eventsList.addClass('kh-program-loading');

			$.ajax({
				url: khProgramData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'kh_filter_program',
					nonce: khProgramData.nonce,
					search: search,
					category: category,
					timeframe: timeframe,
					limit: this.limit,
					show_all_link: this.showAllLink,
					all_link_url: this.allLinkUrl
				},
				success: (response) => {
					if (response.success) {
						this.$eventsList.html(response.data.html);
						this.$resultsCount.text(response.data.count);
					}
				},
				error: () => {
					alert('Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.');
				},
				complete: () => {
					this.$eventsList.removeClass('kh-program-loading');
				}
			});
		}

		resetFilters() {
			this.$searchInput.val('');
			this.$categorySelect.val('');
			this.$timeframeSelect.val('upcoming');
			this.applyFilters();
		}
	}

	// Initialize
	$(document).ready(function() {
		new ProgramFilter();
	});

})(jQuery);
