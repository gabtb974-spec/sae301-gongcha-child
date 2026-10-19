<?php
/**
 * IT-5 · Panier et expédition (US-5.3 : page Panier conforme à la maquette D06).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Frais de préparation de la maquette D06. Mettre 0 pour les supprimer. */
if ( ! defined( 'GONGCHA_FRAIS_PREPARATION' ) ) {
	define( 'GONGCHA_FRAIS_PREPARATION', 0.50 );
}

add_action( 'woocommerce_cart_calculate_fees', function ( $cart ) {
	if ( ( is_admin() && ! wp_doing_ajax() ) || GONGCHA_FRAIS_PREPARATION <= 0 || $cart->is_empty() ) {
		return;
	}
	$cart->add_fee( __( 'Frais de préparation', 'gongcha-child' ), GONGCHA_FRAIS_PREPARATION, false );
} );

/* « Vous pourriez être intéressé(e) par… » : 2 produits sur 2 colonnes. */
add_filter( 'woocommerce_cross_sells_total', function () {
	return 2;
} );
add_filter( 'woocommerce_cross_sells_columns', function () {
	return 2;
} );

/* Classe de catégorie sur chaque ligne du panier (dégradé derrière la vignette). */
add_filter( 'woocommerce_cart_item_class', function ( $class, $item ) {
	$terms = get_the_terms( $item['product_id'], 'product_cat' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		$class .= ' gc-cat-' . sanitize_html_class( $terms[0]->slug );
	}
	return $class;
}, 10, 2 );

/* Boutons − / + autour des champs quantité (panier et fiche produit). */
add_action( 'wp_enqueue_scripts', function () {
	if ( is_cart() || is_product() ) {
		wp_enqueue_script(
			'gongcha-quantite',
			get_stylesheet_directory_uri() . '/assets/js/quantite.js',
			array( 'jquery' ),
			GONGCHA_VERSION,
			true
		);
	}
}, 30 );
