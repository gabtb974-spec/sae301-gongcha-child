<?php
/**
 * IT-2 · Réglages Storefront et WooCommerce pour coller aux maquettes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Pied de page : remplacer « Construit avec Storefront et WooCommerce ». */
add_filter( 'storefront_credit_link', '__return_false' );
add_filter( 'storefront_copyright_text', function () {
	return '© ' . gmdate( 'Y' ) . ' Gong cha · projet étudiant SAé 3.01, site non officiel';
} );

/* Boutique : 3 colonnes (US-2.3), 9 produits par page. */
add_filter( 'loop_shop_columns', function () {
	return 3;
} );
add_filter( 'loop_shop_per_page', function () {
	return 9;
} );

/* Fiche produit : 3 produits similaires sur 3 colonnes (US-2.4). */
add_filter( 'woocommerce_output_related_products_args', function ( $args ) {
	$args['posts_per_page'] = 3;
	$args['columns']        = 3;
	return $args;
} );

/* Badge promo comme sur la maquette. */
add_filter( 'woocommerce_sale_flash', function () {
	return '<span class="onsale">' . esc_html__( 'Promo !', 'gongcha-child' ) . '</span>';
} );

/* Accueil (modèle « Page d'accueil » de Storefront) : titres en français et 3 produits par section. */
$gongcha_home_sections = array(
	'storefront_product_categories_args' => 'Nos catégories',
	'storefront_recent_products_args'    => 'Nouveautés',
	'storefront_featured_products_args'  => 'Produit star',
	'storefront_popular_products_args'   => 'Les mieux notés',
	'storefront_on_sale_products_args'   => 'En promotion',
	'storefront_best_selling_products_args' => 'Les plus commandés',
);
foreach ( $gongcha_home_sections as $gongcha_filter => $gongcha_title ) {
	add_filter( $gongcha_filter, function ( $args ) use ( $gongcha_title ) {
		$args['title']   = $gongcha_title;
		$args['limit']   = 3;
		$args['columns'] = 3;
		return $args;
	} );
}

/* Classe de catégorie sur <body> pour les fiches produit (dégradés de la charte). */
add_filter( 'body_class', function ( $classes ) {
	if ( is_product() ) {
		$terms = get_the_terms( get_the_ID(), 'product_cat' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$classes[] = 'gc-cat-' . sanitize_html_class( $terms[0]->slug );
		}
	}
	return $classes;
} );

/* Cartes catégories : ajoute le slug en classe pour les dégradés de la charte. */
add_filter( 'product_cat_class', function ( $classes, $class, $category ) {
	$classes[] = 'gc-cat-' . sanitize_html_class( $category->slug );
	return $classes;
}, 10, 3 );
