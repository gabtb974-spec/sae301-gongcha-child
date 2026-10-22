<?php
/**
 * IT-22 · Mentions légales et CGV (US-22.1 à US-22.3).
 * Liens légaux dans le pied de page de toutes les pages, case CGV obligatoire à la validation de commande.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* US-22.1 / 22.2 : liens dans le pied de page (maquettes : « Mentions légales · CGV · Confidentialité »). */
add_action( 'storefront_footer', function () {
	$liens = array(
		get_page_by_path( 'mentions-legales' ),
		get_post( (int) get_option( 'woocommerce_terms_page_id' ) ),
		get_post( (int) get_option( 'wp_page_for_privacy_policy' ) ),
	);
	echo '<nav class="gc-liens-legaux" aria-label="' . esc_attr__( 'Informations légales', 'gongcha-child' ) . '"><ul>';
	foreach ( $liens as $page ) {
		if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
			printf( '<li><a href="%s">%s</a></li>', esc_url( get_permalink( $page ) ), esc_html( get_the_title( $page ) ) );
		}
	}
	echo '</ul></nav>';
}, 15 );

/* US-22.3 : libellé de la case à cocher (WooCommerce la rend obligatoire quand une page CGV est définie). */
add_filter( 'woocommerce_get_terms_and_conditions_checkbox_text', function () {
	/* [terms] est remplacé par le lien vers la page CGV. */
	return __( 'J’ai lu et j’accepte les [terms] de Gong cha Click & Collect', 'gongcha-child' );
} );

/* Message d'erreur explicite si la case n'est pas cochée. */
add_action( 'woocommerce_after_checkout_validation', function ( $data, $errors ) {
	if ( $errors->get_error_message( 'terms' ) ) {
		$errors->remove( 'terms' );
		$errors->add( 'terms', __( 'Veuillez lire et accepter les conditions générales de vente pour valider votre commande.', 'gongcha-child' ) );
	}
}, 20, 2 );
