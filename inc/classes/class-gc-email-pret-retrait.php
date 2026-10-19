<?php
/**
 * IT-9 · E-mail client envoyé quand la commande passe à « Prête à retirer ».
 * Réglable dans WooCommerce › Réglages › E-mails (« Commande prête à retirer »).
 */

if ( ! defined( 'ABSPATH' ) || class_exists( 'GC_Email_Pret_Retrait' ) ) {
	return;
}

class GC_Email_Pret_Retrait extends WC_Email {

	public function __construct() {
		$this->id             = 'customer_pret_retrait';
		$this->customer_email = true;
		$this->title          = __( 'Commande prête à retirer', 'gongcha-child' );
		$this->description    = __( 'Envoyé au client quand sa commande passe à l’état « Prête à retirer ».', 'gongcha-child' );
		$this->template_html  = 'emails/customer-pret-retrait.php';
		$this->template_plain = 'emails/plain/customer-pret-retrait.php';
		$this->template_base  = get_stylesheet_directory() . '/woocommerce/';
		$this->placeholders   = array(
			'{order_date}'   => '',
			'{order_number}' => '',
		);
		parent::__construct();
	}

	public function get_default_subject() {
		return __( 'Votre commande n° {order_number} est prête à retirer', 'gongcha-child' );
	}

	public function get_default_heading() {
		return __( 'Votre commande est prête', 'gongcha-child' );
	}

	public function get_default_additional_content() {
		return __( 'À tout de suite au comptoir !', 'gongcha-child' );
	}

	public function trigger( $order_id, $order = false ) {
		$this->setup_locale();
		if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
			$order = wc_get_order( $order_id );
		}
		if ( is_a( $order, 'WC_Order' ) ) {
			$this->object                         = $order;
			$this->recipient                      = $order->get_billing_email();
			$this->placeholders['{order_date}']   = wc_format_datetime( $order->get_date_created() );
			$this->placeholders['{order_number}'] = $order->get_order_number();
		}
		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}
		$this->restore_locale();
	}

	public function get_content_html() {
		return wc_get_template_html( $this->template_html, $this->args( false ), '', $this->template_base );
	}

	public function get_content_plain() {
		return wc_get_template_html( $this->template_plain, $this->args( true ), '', $this->template_base );
	}

	private function args( $plain ) {
		return array(
			'order'              => $this->object,
			'email_heading'      => $this->get_heading(),
			'additional_content' => $this->get_additional_content(),
			'sent_to_admin'      => false,
			'plain_text'         => $plain,
			'email'              => $this,
		);
	}
}
