<?php
/**
 * IT-15 · Frais de préparation sous un seuil de panier (US-15.1 à US-15.3).
 * Réglages : WooCommerce › Réglages › Expédition › Options d'expédition › « Frais de préparation ».
 * Règle : si le sous-total du panier (après codes promo) est inférieur au seuil, on ajoute les frais.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gongcha_frais_reglages() {
	return array(
		'actif'   => 'yes' === get_option( 'gongcha_frais_actif', 'yes' ),
		'libelle' => (string) get_option( 'gongcha_frais_libelle', 'Frais de préparation' ),
		'montant' => (float) wc_format_decimal( get_option( 'gongcha_frais_montant', '0.50' ) ),
		'seuil'   => (float) wc_format_decimal( get_option( 'gongcha_frais_seuil', '10.00' ) ),
	);
}

/* Montant comparé au seuil : articles du panier, codes promo déduits, hors frais et livraison. */
function gongcha_frais_base( $cart ) {
	return max( 0, (float) $cart->get_subtotal() + (float) $cart->get_subtotal_tax() - (float) $cart->get_discount_total() - (float) $cart->get_discount_tax() );
}

/* US-15.1 : réglages dans le back-office. */
add_filter( 'woocommerce_get_settings_shipping', function ( $settings, $section ) {
	if ( 'options' !== $section ) {
		return $settings;
	}
	return array_merge( $settings, array(
		array(
			'title' => __( 'Frais de préparation', 'gongcha-child' ),
			'type'  => 'title',
			'desc'  => __( 'Frais fixes ajoutés automatiquement quand le panier est inférieur au seuil (codes promo déduits).', 'gongcha-child' ),
			'id'    => 'gongcha_frais',
		),
		array(
			'title'   => __( 'Activer', 'gongcha-child' ),
			'desc'    => __( 'Appliquer des frais de préparation aux petits paniers', 'gongcha-child' ),
			'id'      => 'gongcha_frais_actif',
			'type'    => 'checkbox',
			'default' => 'yes',
		),
		array(
			'title'   => __( 'Libellé', 'gongcha-child' ),
			'id'      => 'gongcha_frais_libelle',
			'type'    => 'text',
			'default' => 'Frais de préparation',
		),
		array(
			'title'             => __( 'Montant des frais (€)', 'gongcha-child' ),
			'id'                => 'gongcha_frais_montant',
			'type'              => 'number',
			'default'           => '0.50',
			'custom_attributes' => array( 'min' => '0', 'step' => '0.01' ),
		),
		array(
			'title'             => __( 'Seuil (€)', 'gongcha-child' ),
			'desc'              => __( 'Pas de frais à partir de ce montant.', 'gongcha-child' ),
			'id'                => 'gongcha_frais_seuil',
			'type'              => 'number',
			'default'           => '10.00',
			'custom_attributes' => array( 'min' => '0', 'step' => '0.01' ),
		),
		array(
			'type' => 'sectionend',
			'id'   => 'gongcha_frais',
		),
	) );
}, 20, 2 );

/* US-15.2 : règle appliquée à chaque calcul du panier (ajout, quantité, code promo…). */
add_action( 'woocommerce_cart_calculate_fees', function ( $cart ) {
	if ( ( is_admin() && ! wp_doing_ajax() ) || $cart->is_empty() ) {
		return;
	}
	$r = gongcha_frais_reglages();
	if ( $r['actif'] && $r['montant'] > 0 && gongcha_frais_base( $cart ) < $r['seuil'] ) {
		$cart->add_fee( $r['libelle'], $r['montant'], false );
	}
} );

/* US-15.3 : le client voit combien il lui manque pour ne plus payer les frais. */
add_action( 'woocommerce_before_cart_totals', function () {
	$cart = WC()->cart;
	$r    = gongcha_frais_reglages();
	if ( ! $cart || $cart->is_empty() || ! $r['actif'] || $r['montant'] <= 0 ) {
		return;
	}
	$manque = $r['seuil'] - gongcha_frais_base( $cart );
	if ( $manque > 0 ) {
		/* translators: 1: montant manquant, 2: libellé des frais */
		$texte = sprintf( __( 'Plus que %1$s pour ne plus payer les %2$s.', 'gongcha-child' ), wc_price( $manque ), mb_strtolower( $r['libelle'] ) );
		echo '<p class="gc-seuil">' . wp_kses_post( $texte ) . '</p>';
	} else {
		echo '<p class="gc-seuil gc-seuil--ok">' . esc_html( sprintf( __( 'Pas de %s sur ce panier.', 'gongcha-child' ), mb_strtolower( $r['libelle'] ) ) ) . '</p>';
	}
} );
