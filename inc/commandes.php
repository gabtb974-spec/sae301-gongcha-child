<?php
/**
 * IT-9 · Suivi des commandes Click & Collect (US-9.1, US-9.2, US-9.3).
 * Ajoute l'état « Prête à retirer » (avec son e-mail) et permet au client d'annuler
 * une commande tant qu'elle n'est pas prête.
 *
 * Parcours retrait : En attente de paiement / En cours (préparation) → Prête à retirer → Terminée (retirée et payée).
 * Parcours livraison par virement : En attente (virement) → En cours (paiement reçu) → Terminée (expédiée).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* État « Prête à retirer » */
add_action( 'init', function () {
	register_post_status( 'wc-pret-retrait', array(
		'label'                     => _x( 'Prête à retirer', 'Order status', 'gongcha-child' ),
		'public'                    => false,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		/* translators: %s: nombre de commandes */
		'label_count'               => _n_noop( 'Prête à retirer <span class="count">(%s)</span>', 'Prêtes à retirer <span class="count">(%s)</span>', 'gongcha-child' ),
	) );
} );

add_filter( 'wc_order_statuses', function ( $statuses ) {
	$nouveaux = array();
	foreach ( $statuses as $cle => $libelle ) {
		$nouveaux[ $cle ] = $libelle;
		if ( 'wc-processing' === $cle ) {
			$nouveaux['wc-pret-retrait'] = _x( 'Prête à retirer', 'Order status', 'gongcha-child' );
		}
	}
	return $nouveaux;
} );

/* Action groupée dans la liste des commandes (ancien et nouveau stockage des commandes). */
foreach ( array( 'bulk_actions-edit-shop_order', 'bulk_actions-woocommerce_page_wc-orders' ) as $gongcha_ecran ) {
	add_filter( $gongcha_ecran, function ( $actions ) {
		$actions['mark_pret-retrait'] = __( 'Changer l’état en Prête à retirer', 'gongcha-child' );
		return $actions;
	}, 20 );
}

/* Une commande prête reste « à traiter » dans les rapports et le tableau de bord. */
add_filter( 'woocommerce_reports_order_statuses', function ( $statuses ) {
	if ( is_array( $statuses ) && in_array( 'processing', $statuses, true ) ) {
		$statuses[] = 'pret-retrait';
	}
	return $statuses;
} );

/* E-mail « Votre commande est prête à retirer » */
add_filter( 'woocommerce_email_classes', function ( $emails ) {
	require_once __DIR__ . '/classes/class-gc-email-pret-retrait.php';
	$emails['GC_Email_Pret_Retrait'] = new GC_Email_Pret_Retrait();
	return $emails;
} );

add_action( 'woocommerce_order_status_pret-retrait', function ( $order_id, $order = null ) {
	$emails = WC()->mailer()->get_emails();
	if ( isset( $emails['GC_Email_Pret_Retrait'] ) ) {
		$emails['GC_Email_Pret_Retrait']->trigger( $order_id, $order );
	}
}, 10, 2 );

/* US-9.3 : le client peut annuler depuis « Mon compte » tant que la commande n'est pas prête. */
add_filter( 'woocommerce_valid_order_statuses_for_cancel', function ( $statuses ) {
	return array_unique( array_merge( (array) $statuses, array( 'pending', 'on-hold', 'processing' ) ) );
} );
