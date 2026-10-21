<?php
/**
 * IT-17 · Factures PDF (extension « PDF Invoices & Packing Slips for WooCommerce »).
 * Le modèle personnalisé est dans woocommerce/pdf/gongcha/ (copie du modèle Simple + charte).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Rappel du mode de retrait sous le détail de la facture. */
add_action( 'wpo_wcpdf_after_order_details', function ( $type, $order ) {
	if ( 'invoice' !== $type || ! function_exists( 'gongcha_commande_en_retrait' ) || ! gongcha_commande_en_retrait( $order ) ) {
		return;
	}
	$lieu = get_option( 'gongcha_lieu_retrait', '' );
	echo '<p style="margin-top:4mm;font-size:8.5pt;color:#6E625F;">' . esc_html( sprintf( __( 'Commande à retirer en boutique%s.', 'gongcha-child' ), $lieu ? ' : ' . $lieu : '' ) ) . '</p>';
}, 10, 2 );

/* Bouton « Facture » de Mon compte › Commandes aux couleurs du site. */
add_filter( 'woocommerce_my_account_my_orders_actions', function ( $actions ) {
	if ( isset( $actions['invoice'] ) ) {
		$actions['invoice']['name'] = __( 'Facture PDF', 'gongcha-child' );
	}
	return $actions;
}, 20 );
