/**
 * Kulturhaus Events - Admin JavaScript
 *
 * @package KulturhausEvents
 * @since   1.0.0
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		// Eintritt-frei Checkbox: Kostenfeld deaktivieren wenn "Eintritt frei" aktiv.
		var costFreeCheckbox = document.getElementById('_kh_event_cost_free');
		var costField = document.getElementById('_kh_event_cost');

		if (costFreeCheckbox && costField) {
			function toggleCostField() {
				costField.disabled = costFreeCheckbox.checked;
				if (costFreeCheckbox.checked) {
					costField.value = '';
				}
			}

			costFreeCheckbox.addEventListener('change', toggleCostField);
			toggleCostField();
		}

		// Enddatum-Validierung: Enddatum muss nach Startdatum liegen.
		var startDate = document.getElementById('_kh_event_start_date');
		var endDate = document.getElementById('_kh_event_end_date');

		if (startDate && endDate) {
			startDate.addEventListener('change', function () {
				endDate.min = startDate.value;

				if (endDate.value && endDate.value < startDate.value) {
					endDate.value = startDate.value;
				}
			});
		}
	});
})();
