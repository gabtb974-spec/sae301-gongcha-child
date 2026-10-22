/*
 * IT-18 · Volet des filtres en mobile (maquette M03).
 * Le bouton « Filtrer » ouvre la barre latérale par-dessus la page ; « Voir les résultats » ou Échap la referme.
 */
(function () {
	'use strict';
	var ouvrir = document.querySelector('.gc-filtres-ouvrir');
	var volet = document.getElementById('secondary');
	if (!ouvrir || !volet) {
		return;
	}
	function basculer(ouvert) {
		document.body.classList.toggle('gc-filtres-ouverts', ouvert);
		ouvrir.setAttribute('aria-expanded', ouvert ? 'true' : 'false');
		if (ouvert) {
			volet.setAttribute('tabindex', '-1');
			volet.focus();
		} else {
			ouvrir.focus();
		}
	}
	ouvrir.addEventListener('click', function () { basculer(true); });
	document.addEventListener('click', function (e) {
		if (e.target.closest('.gc-filtres-fermer')) {
			basculer(false);
		}
	});
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && document.body.classList.contains('gc-filtres-ouverts')) {
			basculer(false);
		}
	});
})();
