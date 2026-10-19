<?php
/**
 * E-mail client « Votre commande est prête à retirer », version texte (IT-9).
 *
 * @package gongcha-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo '= ' . esc_html( wp_strip_all_tags( $email_heading ) ) . " =\n\n";
printf( esc_html__( 'Bonjour %s,', 'gongcha-child' ), esc_html( $order->get_billing_first_name() ) );
echo "\n\n";
printf( esc_html__( 'Bonne nouvelle : votre commande n° %s est prête. Elle vous attend au comptoir Click & Collect.', 'gongcha-child' ), esc_html( $order->get_order_number() ) );
echo "\n\n";
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );
if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) ) . "\n\n";
}
echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
