<?php
/**
 * Options de personnalisation des produits simples.
 * Le sujet demande des options pour CHAQUE produit : les boissons simples (prix unique) reçoivent
 * des options gratuites (température, glace, douceur), enregistrées dans le panier et la commande.
 * Les produits variables gardent leurs variations (IT-3).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Options proposées (la première valeur est sélectionnée par défaut). */
function gongcha_options_simples() {
	return array(
		'temperature' => array( 'label' => __( 'Température', 'gongcha-child' ), 'choix' => array( 'Froid', 'Chaud' ) ),
		'glace'       => array( 'label' => __( 'Glace', 'gongcha-child' ), 'choix' => array( 'Niveau 2', 'Sans glace', 'Niveau 1', 'Niveau 3' ) ),
		'douceur'     => array( 'label' => __( 'Douceur', 'gongcha-child' ), 'choix' => array( 'Standard', 'Moins', 'Peu', 'Zéro' ) ),
	);
}

/* Formulaire sur la fiche produit, avant la quantité (même présentation que le sélecteur de l'IT-3). */
add_action( 'woocommerce_before_add_to_cart_button', function () {
	global $product;
	if ( ! $product || ! $product->is_type( 'simple' ) ) {
		return;
	}
	echo '<div class="gc-options-simples">';
	echo '<h2 class="gc-options-title">' . esc_html__( 'Faites-le vôtre.', 'gongcha-child' ) . '</h2>';
	echo '<table class="variations" role="presentation"><tbody>';
	$n = 1;
	foreach ( gongcha_options_simples() as $cle => $option ) {
		$id = 'gc_opt_' . $cle;
		printf( '<tr><th class="label"><label for="%1$s">%2$s</label></th><td class="value"><select id="%1$s" name="gc_options[%3$s]">', esc_attr( $id ), esc_html( $n++ . '. ' . $option['label'] ), esc_attr( $cle ) );
		foreach ( $option['choix'] as $choix ) {
			printf( '<option value="%1$s">%1$s</option>', esc_attr( $choix ) );
		}
		echo '</select></td></tr>';
	}
	echo '</tbody></table></div>';
} );

/* Panier : options validées (liste blanche) et enregistrées avec l'article. */
add_filter( 'woocommerce_add_cart_item_data', function ( $donnees, $product_id, $variation_id ) {
	$produit = wc_get_product( $product_id );
	if ( $variation_id || ! $produit || ! $produit->is_type( 'simple' ) ) {
		return $donnees;
	}
	$recu  = isset( $_POST['gc_options'] ) ? (array) wp_unslash( $_POST['gc_options'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
	$choix = array();
	foreach ( gongcha_options_simples() as $cle => $option ) {
		$valeur        = isset( $recu[ $cle ] ) ? sanitize_text_field( $recu[ $cle ] ) : '';
		$choix[ $cle ] = in_array( $valeur, $option['choix'], true ) ? $valeur : $option['choix'][0];
	}
	$donnees['gc_options'] = $choix;
	return $donnees;
}, 10, 3 );

/* Affichage dans le panier, la validation et les e-mails. */
add_filter( 'woocommerce_get_item_data', function ( $lignes, $article ) {
	if ( empty( $article['gc_options'] ) ) {
		return $lignes;
	}
	foreach ( gongcha_options_simples() as $cle => $option ) {
		if ( isset( $article['gc_options'][ $cle ] ) ) {
			$lignes[] = array( 'key' => $option['label'], 'value' => $article['gc_options'][ $cle ] );
		}
	}
	return $lignes;
}, 10, 2 );

/* Commande : options enregistrées sur la ligne (visibles en back-office, sur la facture et dans Mon compte). */
add_action( 'woocommerce_checkout_create_order_line_item', function ( $ligne, $cle_panier, $article ) {
	if ( empty( $article['gc_options'] ) ) {
		return;
	}
	foreach ( gongcha_options_simples() as $cle => $option ) {
		if ( isset( $article['gc_options'][ $cle ] ) ) {
			$ligne->add_meta_data( $option['label'], $article['gc_options'][ $cle ], true );
		}
	}
}, 10, 3 );
