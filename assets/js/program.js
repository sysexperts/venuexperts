/**
 * Event Programm Navigation
 *
 * @package KulturhausEvents
 * @since   1.15.0
 */

(function() {
	'use strict';

	// Aktuelle Monat/Jahr aus dem DOM lesen
	let currentMonth = new Date().getMonth() + 1;
	let currentYear = new Date().getFullYear();

	/**
	 * Monat navigieren
	 */
	window.khNavigateMonth = function(direction) {
		currentMonth += direction;

		if (currentMonth > 12) {
			currentMonth = 1;
			currentYear++;
		} else if (currentMonth < 1) {
			currentMonth = 12;
			currentYear--;
		}

		// Seite neu laden mit neuen Parametern
		const url = new URL(window.location.href);
		url.searchParams.set('month', currentMonth);
		url.searchParams.set('year', currentYear);
		window.location.href = url.toString();
	};

})();
