/*
 * IT-3 · Pastilles d'options (US-3.2)
 * Chaque liste déroulante de variation est doublée par des boutons « pastilles ».
 * Un clic choisit la valeur dans la liste et déclenche l'événement que WooCommerce écoute :
 * le prix et le stock de la variation s'affichent alors (US-3.3).
 */
(function ($) {
	'use strict';

	function buildPills($select) {
		var $cell = $select.closest('td.value');
		var $pills = $cell.find('.gc-pills');

		if (!$pills.length) {
			$pills = $('<div class="gc-pills" role="radiogroup"></div>');
			$pills.attr('aria-label', $select.closest('tr').find('th label').text());
			$select.after($pills);
			$select.addClass('gc-select-hidden');
		}

		$pills.empty();
		$select.find('option').each(function () {
			var value = this.value;
			if (!value) {
				return; // option « Choisir une option »
			}
			var $btn = $('<button type="button" class="gc-pill" role="radio"></button>')
				.text($(this).text())
				.attr('data-value', value)
				.attr('aria-checked', $select.val() === value ? 'true' : 'false');

			if ($select.val() === value) {
				$btn.addClass('is-selected');
			}
			if (this.disabled || !$(this).hasClass('enabled') && $select.find('option.enabled').length) {
				$btn.prop('disabled', true).addClass('is-disabled');
			}
			$pills.append($btn);
		});
	}

	function refreshAll($form) {
		$form.find('table.variations select').each(function () {
			buildPills($(this));
		});
	}

	$(document).on('click', '.gc-pill', function () {
		var $btn = $(this);
		var $select = $btn.closest('td.value').find('select');
		var value = $btn.data('value');
		$select.val($select.val() === value ? '' : value).trigger('change');
	});

	$(function () {
		$('form.variations_form').each(function () {
			var $form = $(this);
			refreshAll($form);
			// WooCommerce met à jour les options disponibles après chaque choix.
			$form.on('woocommerce_update_variation_values reset_data', function () {
				refreshAll($form);
			});
			$form.on('change', 'table.variations select', function () {
				buildPills($(this));
			});
		});
	});
})(jQuery);
