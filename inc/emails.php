<?php
/**
 * IT-8 · E-mails transactionnels aux couleurs de Gong cha (US-8.2, US-8.3, maquette D09).
 * Les gabarits surchargés sont dans woocommerce/emails/ (en-tête et pied de page).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* US-8.2 : styles ajoutés à ceux de WooCommerce (ils sont ensuite mis en ligne dans le HTML). */
add_filter( 'woocommerce_email_styles', function ( $css ) {
	$css .= '
	body, #outer_wrapper { background-color: #FCF6ED; }
	#wrapper { padding: 40px 0; }
	#template_container { background-color: #FFFFFF; border: 0; border-radius: 24px; overflow: hidden; box-shadow: none; }
	#template_header { background-color: #C10230; border-radius: 24px 24px 0 0; }
	#header_wrapper { padding: 30px 36px; text-align: left; }
	#header_wrapper img.gc-logo { display: block; width: 150px; height: auto; margin: 0 0 18px; border: 0; }
	#header_wrapper h1 { font-family: Vidaloka, Georgia, "Times New Roman", serif; font-weight: 400; font-size: 32px; line-height: 1.15; color: #F6E4CB; margin: 0; text-shadow: none; text-align: left; }
	#body_content { background-color: #FFFFFF; }
	#body_content table td { padding: 30px 36px; }
	#body_content table td td, #body_content table td th { padding: 10px 14px; }
	#body_content_inner { font-family: "Public Sans", Helvetica, Arial, sans-serif; font-size: 15px; line-height: 1.55; color: #382F2D; }
	h2 { font-family: Vidaloka, Georgia, "Times New Roman", serif; font-weight: 400; font-size: 22px; color: #B10023; }
	h3 { font-family: "Public Sans", Helvetica, Arial, sans-serif; color: #382F2D; }
	a, .link { color: #B10023; }
	.td { border-color: #EDEDED; color: #382F2D; }
	table.td thead th, .td th { background-color: #FCF6ED; }
	.address { border: 1px solid #EDEDED; border-radius: 12px; color: #6E625F; }
	.gc-retrait-mail { background-color: #FCF6ED; border-radius: 24px; padding: 18px 22px; margin: 0 0 24px; }
	.gc-retrait-mail h2 { color: #382F2D; margin: 0 0 6px; }
	.gc-retrait-mail p { margin: 0 0 6px; }
	.gc-lieu { display: inline-block; padding: 7px 14px; background-color: #E0C7E6; border-radius: 9999px; font-weight: 600; font-size: 14px; }
	#template_footer td { padding: 18px 36px 0; }
	#template_footer #credit { color: #6E625F; font-size: 13px; text-align: center; }
	#template_footer #credit p { margin: 0 0 6px; }
	';
	return $css;
} );

/* US-8.3 : instructions de retrait dans les e-mails clients concernés, avant le détail de la commande. */
add_action( 'woocommerce_email_before_order_table', function ( $order, $sent_to_admin, $plain_text, $email = null ) {
	$concernes = array( 'customer_on_hold_order', 'customer_processing_order', 'customer_completed_order', 'customer_invoice' );
	if ( $sent_to_admin || ! $email || ! in_array( $email->id, $concernes, true ) || ! function_exists( 'gongcha_commande_en_retrait' ) || ! gongcha_commande_en_retrait( $order ) ) {
		return;
	}
	$texte = gongcha_instructions_retrait();
	$lieu  = get_option( 'gongcha_lieu_retrait', '' );
	if ( '' === trim( $texte ) ) {
		return;
	}
	if ( $plain_text ) {
		echo esc_html( mb_strtoupper( __( 'Instructions pour le retrait', 'gongcha-child' ) ) ) . "\n" . wp_strip_all_tags( $texte ) . ( $lieu ? "\n" . $lieu : '' ) . "\n\n";
		return;
	}
	echo '<div class="gc-retrait-mail">';
	echo '<h2>' . esc_html__( 'Instructions pour le retrait', 'gongcha-child' ) . '</h2>';
	echo wp_kses_post( wpautop( $texte ) );
	if ( $lieu ) {
		echo '<span class="gc-lieu">' . esc_html( $lieu ) . '</span>';
	}
	echo '</div>';
}, 5, 4 );
