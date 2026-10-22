<?php
/**
 * IT-18 · Recherche et filtrage (US-18.1 à US-18.3, maquettes D02 et M03).
 * Widgets WooCommerce dans la barre latérale de la boutique : filtres actifs, prix, taille.
 * Les cartes « Collections » et « Catégories » (IT-12) passent sous les filtres.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gongcha_page_boutique() {
	return function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() );
}

/* La barre latérale n'existe que sur les pages boutique : les autres pages restent en pleine largeur. */
add_filter( 'is_active_sidebar', function ( $actif, $index ) {
	if ( 'sidebar-1' !== $index || is_admin() ) {
		return $actif;
	}
	return gongcha_page_boutique();
}, 20, 2 );

/* Ordre de la maquette : filtres d'abord, puis Collections et Catégories
   (dans inc/collections.php, les cartes passent de dynamic_sidebar_before à dynamic_sidebar_after). */

/* Mobile (maquette M03) : bouton « Filtrer » au-dessus de la grille, volet avec bouton de fermeture. */
add_action( 'woocommerce_before_shop_loop', function () {
	echo '<button type="button" class="gc-filtres-ouvrir" aria-controls="secondary" aria-expanded="false">' . esc_html__( 'Filtrer', 'gongcha-child' ) . '</button>';
}, 25 );
add_action( 'dynamic_sidebar_before', function ( $index ) {
	if ( 'sidebar-1' === $index && gongcha_page_boutique() ) {
		echo '<button type="button" class="gc-filtres-fermer">' . esc_html__( 'Voir les résultats', 'gongcha-child' ) . '</button>';
	}
}, 5 );
add_action( 'wp_enqueue_scripts', function () {
	if ( gongcha_page_boutique() ) {
		wp_enqueue_script( 'gongcha-filtres', get_stylesheet_directory_uri() . '/assets/js/filtres.js', array(), GONGCHA_VERSION, true );
	}
}, 30 );

/* Aucun résultat : lien pour tout réinitialiser (maquette D02). */
add_action( 'woocommerce_no_products_found', function () {
	echo '<p class="gc-filtres-reset"><a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'Réinitialiser les filtres', 'gongcha-child' ) . '</a></p>';
}, 20 );

/* Curseur de prix au pas de 0,50 € (le pas par défaut de 10 € écrase les prix de 4 à 7 € sur « 10 € — 10 € »). */
add_filter( 'woocommerce_price_filter_widget_step', function () {
	return 0.5;
} );
