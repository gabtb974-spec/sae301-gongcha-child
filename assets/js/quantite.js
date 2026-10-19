/*
 * IT-5 · Boutons − / + autour des champs quantité (maquettes D04, D05, D06).
 * Le champ reste un vrai <input type="number"> : WooCommerce continue de le lire normalement.
 */
(function ($) {
	'use strict';

	function addButtons() {
		$('.quantity').each(function () {
			var $q = $(this);
			var $input = $q.find('input.qty[type="number"]');
			if (!$input.length || $q.find('.gc-qty-btn').length) {
				return; // pas de champ (article unique) ou boutons déjà présents
			}
			$input.before('<button type="button" class="gc-qty-btn" data-dir="-1" aria-label="Retirer un">−</button>');
			$input.after('<button type="button" class="gc-qty-btn" data-dir="1" aria-label="Ajouter un">+</button>');
		});
	}

	$(document).on('click', '.gc-qty-btn', function () {
		var $input = $(this).siblings('input.qty');
		var step = parseFloat($input.attr('step')) || 1;
		var min = $input.attr('min') !== '' && $input.attr('min') !== undefined ? parseFloat($input.attr('min')) : 0;
		var max = parseFloat($input.attr('max')) || Infinity;
		var value = (parseFloat($input.val()) || 0) + $(this).data('dir') * step;
		$input.val(Math.max(min, Math.min(max, value))).trigger('change');
	});

	$(addButtons);
	// Le panier est rechargé en AJAX après « Mettre à jour le panier ».
	$(document.body).on('updated_wc_div updated_cart_totals', addButtons);
})(jQuery);
