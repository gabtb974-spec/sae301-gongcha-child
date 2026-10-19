/*
 * IT-6 · Affiche « Instructions pour le retrait » seulement quand le retrait en boutique est choisi.
 * La liste des modes d'expédition est rechargée par WooCommerce (événement updated_checkout).
 */
(function ($) {
	'use strict';

	function toggle() {
		var $choix = $('input.shipping_method:checked, input.shipping_method[type="hidden"]').first();
		var retrait = ($choix.val() || '').indexOf('local_pickup') === 0;
		$('.woocommerce-checkout .gc-retrait').toggle(retrait);
	}

	$(document.body).on('updated_checkout', toggle);
	$(document.body).on('change', 'input.shipping_method', toggle);
	$(toggle);
})(jQuery);
