<?php
/**
 * IT-3 · Sélecteur d'options en pastilles (US-3.2) et affichage du prix (US-3.3).
 * Les listes déroulantes de WooCommerce restent dans la page (accessibles, sans JavaScript)
 * et sont doublées par des pastilles qui les pilotent.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', function () {
	if ( is_product() ) {
		wp_enqueue_script(
			'gongcha-variations',
			get_stylesheet_directory_uri() . '/assets/js/variations.js',
			array( 'jquery', 'wc-add-to-cart-variation' ),
			GONGCHA_VERSION,
			true
		);
	}
}, 30 );

/* Titre de la zone d'options, comme sur la maquette D05. */
add_action( 'woocommerce_before_variations_form', function () {
	echo '<h2 class="gc-options-title">' . esc_html__( 'Faites-le vôtre.', 'gongcha-child' ) . '</h2>';
} );

/* Numérotation des étapes, dans l'ordre d'affichage. */
add_filter( 'woocommerce_attribute_label', function ( $label, $name ) {
	$steps = array(
		'pa_taille'      => '1. Taille',
		'pa_base-de-the' => '2. Base de thé',
		'pa_temperature' => '3. Température',
		'pa_glace'       => '4. Glace',
		'pa_douceur'     => '5. Douceur',
		'pa_supplement'  => '6. Garniture',
	);
	return ( ! is_admin() && isset( $steps[ $name ] ) ) ? $steps[ $name ] : $label;
}, 10, 2 );

/* Base de thé présélectionnée : la première proposée pour ce produit. */
add_filter( 'woocommerce_product_get_default_attributes', function ( $defaults, $product ) {
	if ( is_admin() || ! empty( $defaults['pa_base-de-the'] ) ) {
		return $defaults;
	}
	$bases = wc_get_product_terms( $product->get_id(), 'pa_base-de-the', array( 'fields' => 'slugs' ) );
	if ( $bases ) {
		$defaults['pa_base-de-the'] = $bases[0];
	}
	return $defaults;
}, 10, 2 );

/* Prix affiché sous le titre : « 5,20 € – 6,50 € » plutôt que « À partir de ». */
add_filter( 'woocommerce_variable_price_html', function ( $price, $product ) {
	$min = $product->get_variation_price( 'min', true );
	$max = $product->get_variation_price( 'max', true );
	return $min === $max ? wc_price( $min ) : wc_format_price_range( $min, $max );
}, 10, 2 );
