<?php
/**
 * IT-7 · Espace client « Mon compte » (US-7.2, maquettes D10, D11, M08).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Menu du compte : pas de « Téléchargements » (aucun produit téléchargeable). */
add_filter( 'woocommerce_account_menu_items', function ( $items ) {
	unset( $items['downloads'] );
	return $items;
} );

/* État de la commande affiché en pastille de couleur (maquette D11). */
add_action( 'woocommerce_my_account_my_orders_column_order-status', function ( $order ) {
	$status = $order->get_status();
	$label  = wc_get_order_status_name( $status );
	if ( 'on-hold' === $status && 'bacs' === $order->get_payment_method() ) {
		$label .= ' · ' . __( 'virement', 'gongcha-child' );
	}
	printf(
		'<span class="gc-statut gc-statut--%s">%s</span>',
		esc_attr( $status ),
		esc_html( $label )
	);
} );

/* Titre des formulaires de connexion et d'inscription (maquette D10). */
add_filter( 'gettext', function ( $translation, $text, $domain ) {
	if ( 'woocommerce' === $domain && 'Register' === $text && is_account_page() && ! is_user_logged_in() ) {
		return __( "S'enregistrer", 'gongcha-child' );
	}
	return $translation;
}, 10, 3 );
