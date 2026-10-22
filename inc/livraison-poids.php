<?php
/**
 * IT-19 · Tarifs de livraison selon le poids (US-19.1 à US-19.3).
 * Le tarif d'une livraison = tarif de la zone (IT-5 : vélo Paris 3,90 €, express 9,90 €)
 *                          + supplément de la tranche de poids du panier.
 * Le retrait en boutique reste gratuit quel que soit le poids.
 * Réglages : WooCommerce › Réglages › Expédition › Options d'expédition › « Tarifs selon le poids ».
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gongcha_tranches_poids() {
	$g = function ( $cle, $defaut ) {
		return (float) wc_format_decimal( get_option( $cle, $defaut ) );
	};
	return array(
		array( 'max' => $g( 'gongcha_poids_seuil_1', '1' ), 'supplement' => $g( 'gongcha_poids_supp_1', '0' ) ),
		array( 'max' => $g( 'gongcha_poids_seuil_2', '3' ), 'supplement' => $g( 'gongcha_poids_supp_2', '2' ) ),
		array( 'max' => INF, 'supplement' => $g( 'gongcha_poids_supp_3', '4.5' ) ),
	);
}

/* Poids total d'un colis (articles × quantité), en kg. */
function gongcha_poids_colis( $contenu ) {
	$poids = 0.0;
	foreach ( (array) $contenu as $article ) {
		if ( ! empty( $article['data'] ) && $article['data']->needs_shipping() ) {
			$poids += (float) wc_get_weight( (float) $article['data']->get_weight(), 'kg' ) * (int) $article['quantity'];
		}
	}
	return $poids;
}

function gongcha_tranche_pour( $poids ) {
	foreach ( gongcha_tranches_poids() as $i => $tranche ) {
		if ( $poids <= $tranche['max'] ) {
			return array( $i + 1, $tranche['supplement'] );
		}
	}
	return array( 3, 0 );
}

function gongcha_format_kg( $poids ) {
	return number_format_i18n( $poids, 2 ) . ' kg';
}

/* US-19.2 : les 3 tranches dans le back-office. */
add_filter( 'woocommerce_get_settings_shipping', function ( $settings, $section ) {
	if ( 'options' !== $section ) {
		return $settings;
	}
	$num = function ( $id, $titre, $defaut, $desc = '' ) {
		return array( 'title' => $titre, 'id' => $id, 'type' => 'number', 'default' => $defaut, 'desc' => $desc, 'custom_attributes' => array( 'min' => '0', 'step' => '0.01' ) );
	};
	return array_merge( $settings, array(
		array( 'title' => __( 'Tarifs selon le poids', 'gongcha-child' ), 'type' => 'title', 'id' => 'gongcha_poids',
			'desc' => __( 'Supplément ajouté au tarif de chaque livraison (pas au retrait en boutique) selon le poids total du panier.', 'gongcha-child' ) ),
		$num( 'gongcha_poids_seuil_1', __( 'Tranche 1 : jusqu’à (kg)', 'gongcha-child' ), '1' ),
		$num( 'gongcha_poids_supp_1', __( 'Tranche 1 : supplément (€)', 'gongcha-child' ), '0' ),
		$num( 'gongcha_poids_seuil_2', __( 'Tranche 2 : jusqu’à (kg)', 'gongcha-child' ), '3' ),
		$num( 'gongcha_poids_supp_2', __( 'Tranche 2 : supplément (€)', 'gongcha-child' ), '2' ),
		$num( 'gongcha_poids_supp_3', __( 'Tranche 3 : au-delà, supplément (€)', 'gongcha-child' ), '4.5' ),
		array( 'type' => 'sectionend', 'id' => 'gongcha_poids' ),
	) );
}, 30, 2 );

/* US-19.1 : supplément appliqué aux tarifs de livraison calculés par WooCommerce. */
add_filter( 'woocommerce_package_rates', function ( $tarifs, $colis ) {
	$poids = gongcha_poids_colis( $colis['contents'] ?? array() );
	list( $tranche, $supplement ) = gongcha_tranche_pour( $poids );
	foreach ( $tarifs as $tarif ) {
		if ( 'flat_rate' !== $tarif->get_method_id() ) {
			continue; // retrait en boutique : gratuit
		}
		$tarif->set_cost( (float) $tarif->get_cost() + $supplement );
		$tarif->add_meta_data( __( 'Poids', 'gongcha-child' ), gongcha_format_kg( $poids ) . ' (tranche ' . $tranche . ')' );
	}
	return $tarifs;
}, 20, 2 );

/* Les tarifs mémorisés par WooCommerce sont recalculés si les tranches changent. */
add_filter( 'woocommerce_cart_shipping_packages', function ( $colis ) {
	$version = md5( wp_json_encode( gongcha_tranches_poids() ) );
	foreach ( $colis as $i => $c ) {
		$colis[ $i ]['gc_tranches'] = $version;
	}
	return $colis;
} );

/* US-19.3 : poids et tranche affichés dans le panier et à la validation, au-dessus des tarifs. */
function gongcha_ligne_poids() {
	if ( ! WC()->cart || ! WC()->cart->needs_shipping() ) {
		return;
	}
	$poids = gongcha_poids_colis( WC()->cart->get_cart() );
	list( $tranche, $supplement ) = gongcha_tranche_pour( $poids );
	printf(
		'<tr class="gc-poids"><th>%1$s</th><td data-title="%1$s">%2$s <small>%3$s</small></td></tr>',
		esc_html__( 'Poids de la commande', 'gongcha-child' ),
		esc_html( gongcha_format_kg( $poids ) ),
		esc_html( sprintf( __( 'tranche %1$d · livraison %2$s', 'gongcha-child' ), $tranche, $supplement > 0 ? '+' . wp_strip_all_tags( wc_price( $supplement ) ) : __( 'sans supplément', 'gongcha-child' ) ) )
	);
}
add_action( 'woocommerce_cart_totals_before_shipping', 'gongcha_ligne_poids' );
add_action( 'woocommerce_review_order_before_shipping', 'gongcha_ligne_poids' );
