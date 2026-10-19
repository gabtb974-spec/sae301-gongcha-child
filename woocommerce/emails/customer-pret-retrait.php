<?php
/**
 * E-mail client « Votre commande est prête à retirer » (IT-9, gabarit propre au thème Gong cha).
 *
 * @package gongcha-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php printf( esc_html__( 'Bonjour %s,', 'gongcha-child' ), esc_html( $order->get_billing_first_name() ) ); ?></p>
<p><?php printf( esc_html__( 'Bonne nouvelle : votre commande n° %s est prête. Elle vous attend au comptoir Click & Collect.', 'gongcha-child' ), esc_html( $order->get_order_number() ) ); ?></p>
<?php if ( 'cod' === $order->get_payment_method() ) : ?>
	<p><?php printf( esc_html__( 'Montant à régler sur place : %s.', 'gongcha-child' ), wp_kses_post( $order->get_formatted_order_total() ) ); ?></p>
<?php endif; ?>

<?php
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

do_action( 'woocommerce_email_footer', $email );
