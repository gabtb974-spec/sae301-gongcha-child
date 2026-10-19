<?php
/**
 * IT-14 · Avis clients (US-14.1, US-14.2, maquette D05).
 * Seuls les clients ayant acheté le produit peuvent le noter (réglage WooCommerce, voir it14-commandes.sh).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Pastille avec l'initiale du client à la place de l'avatar Gravatar. */
add_action( 'init', function () {
	remove_action( 'woocommerce_review_before', 'woocommerce_review_display_gravatar', 10 );
} );
add_action( 'woocommerce_review_before', function ( $comment ) {
	$nom = trim( get_comment_author( $comment ) );
	echo '<span class="gc-avis-initiale" aria-hidden="true">' . esc_html( mb_strtoupper( mb_substr( $nom ? $nom : '?', 0, 1 ) ) ) . '</span>';
}, 10 );

/* Prénom seulement (pas de nom de famille ni d'identifiant affiché publiquement). */
add_filter( 'get_comment_author', function ( $auteur, $comment_id, $comment ) {
	if ( is_admin() || ! $comment || 'review' !== $comment->comment_type || ! $comment->user_id ) {
		return $auteur;
	}
	$prenom = get_user_meta( $comment->user_id, 'first_name', true );
	return $prenom ? $prenom : $auteur;
}, 10, 3 );

/* Formulaire : rappel de la règle, comme sur la maquette. */
add_filter( 'woocommerce_product_review_comment_form_args', function ( $args ) {
	$args['comment_field'] = '<p class="gc-avis-regle">' . esc_html__( 'Seuls les clients ayant acheté ce produit peuvent laisser un avis.', 'gongcha-child' ) . '</p>' . $args['comment_field'];
	$args['label_submit']  = __( 'Soumettre', 'gongcha-child' );
	return $args;
} );
