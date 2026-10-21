<?php
/**
 * IT-13 · Ventes croisées (US-13.2, US-13.3, maquettes D04, D05, D06).
 * Fiche produit : « Vous aimerez peut-être aussi… » (produits suggérés de la fiche, onglet Produits liés).
 * Panier : « Vous pourriez être intéressé(e) par… » (ventes croisées, réglées à l'IT-5).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* 3 suggestions sur 3 colonnes. */
add_filter( 'woocommerce_upsell_display_args', function ( $args ) {
	$args['posts_per_page'] = 3;
	$args['columns']        = 3;
	$args['orderby']        = 'menu_order';
	return $args;
} );
add_filter( 'woocommerce_upsells_columns', function () {
	return 3;
} );

/* Si la fiche a des suggestions choisies, on n'ajoute pas en plus les « Produits similaires » automatiques. */
add_action( 'woocommerce_after_single_product_summary', function () {
	global $product;
	if ( $product && $product->get_upsell_ids() ) {
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
	}
}, 1 );

/* Dans les grilles (boutique, suggestions, panier) : « à partir de 5,20 € » pour les boissons à options. */
add_filter( 'woocommerce_variable_price_html', function ( $price, $product ) {
	$fiche_courante = is_product() && get_queried_object_id() === $product->get_id();
	if ( $fiche_courante ) {
		return $price; // fourchette « 5,20 € – 6,50 € » sous le titre de la fiche (IT-3)
	}
	$min = $product->get_variation_price( 'min', true );
	return sprintf( '<span class="gc-a-partir">%s</span> %s', esc_html__( 'à partir de', 'gongcha-child' ), wc_price( $min ) );
}, 20, 2 );

/* Pas de suggestion d'un produit épuisé (fiche et panier). */
$gongcha_en_stock = function ( $ids ) {
	if ( is_admin() ) {
		return $ids;
	}
	return array_values( array_filter( (array) $ids, function ( $id ) {
		$p = wc_get_product( $id );
		return $p && $p->is_in_stock();
	} ) );
};
add_filter( 'woocommerce_product_get_upsell_ids', $gongcha_en_stock );
add_filter( 'woocommerce_product_get_cross_sell_ids', $gongcha_en_stock );
