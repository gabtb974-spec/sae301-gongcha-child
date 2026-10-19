<?php
/**
 * IT-6 · Instructions pour le retrait (US-6.1, US-6.2).
 * Texte réglable dans WooCommerce › Réglages › Expédition › Options d'expédition,
 * affiché dans le formulaire de validation, sur la page de confirmation et dans l'e-mail.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GONGCHA_INSTRUCTIONS_DEFAUT', 'Présentez votre numéro de commande au comptoir Click & Collect. Votre boisson est préparée dès réception de la commande et vous attend 30 minutes au comptoir.' );

function gongcha_instructions_retrait() {
	return (string) get_option( 'gongcha_instructions_retrait', GONGCHA_INSTRUCTIONS_DEFAUT );
}

/* US-6.1 : champs dans le back-office (onglet Expédition, section « Options d'expédition »). */
add_filter( 'woocommerce_get_settings_shipping', function ( $settings, $section ) {
	if ( 'options' !== $section ) {
		return $settings;
	}
	return array_merge( $settings, array(
		array(
			'title' => __( 'Retrait en boutique (Click & Collect)', 'gongcha-child' ),
			'type'  => 'title',
			'desc'  => __( 'Texte affiché au client pendant la validation de commande, sur la page de confirmation et dans l\'e-mail de commande, quand il choisit le retrait en boutique.', 'gongcha-child' ),
			'id'    => 'gongcha_retrait',
		),
		array(
			'title'   => __( 'Instructions pour le retrait', 'gongcha-child' ),
			'id'      => 'gongcha_instructions_retrait',
			'type'    => 'textarea',
			'default' => GONGCHA_INSTRUCTIONS_DEFAUT,
			'css'     => 'min-width: 420px; height: 110px;',
		),
		array(
			'title'   => __( 'Lieu de retrait', 'gongcha-child' ),
			'id'      => 'gongcha_lieu_retrait',
			'type'    => 'text',
			'default' => 'Gong cha, Paris 2e',
		),
		array(
			'type' => 'sectionend',
			'id'   => 'gongcha_retrait',
		),
	) );
}, 10, 2 );

/* Bloc affiché au client. */
function gongcha_bloc_retrait() {
	$texte = gongcha_instructions_retrait();
	if ( '' === trim( $texte ) ) {
		return;
	}
	$lieu = get_option( 'gongcha_lieu_retrait', 'Gong cha, Paris 2e' );
	?>
	<section class="gc-retrait" aria-labelledby="gc-retrait-titre">
		<div class="gc-retrait__inner">
			<h3 id="gc-retrait-titre" class="gc-retrait__titre"><?php esc_html_e( 'Instructions pour le retrait', 'gongcha-child' ); ?></h3>
			<?php echo wp_kses_post( wpautop( $texte ) ); ?>
			<?php if ( $lieu ) : ?>
				<p class="gc-retrait__lieu"><span><?php echo esc_html( $lieu ); ?></span></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/* US-6.2 : dans le formulaire de validation, avant « Informations complémentaires ». */
add_action( 'woocommerce_before_order_notes', 'gongcha_bloc_retrait' );

/* Le bloc ne s'affiche que si « Retrait en boutique » est choisi. */
add_action( 'wp_enqueue_scripts', function () {
	if ( is_checkout() ) {
		wp_enqueue_script(
			'gongcha-retrait',
			get_stylesheet_directory_uri() . '/assets/js/retrait.js',
			array( 'jquery', 'wc-checkout' ),
			GONGCHA_VERSION,
			true
		);
	}
}, 30 );

function gongcha_commande_en_retrait( $order ) {
	foreach ( $order->get_shipping_methods() as $methode ) {
		if ( 'local_pickup' === $methode->get_method_id() ) {
			return true;
		}
	}
	return false;
}

/* Rappel sur la page « Commande reçue ». */
add_action( 'woocommerce_thankyou', function ( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( $order && gongcha_commande_en_retrait( $order ) ) {
		gongcha_bloc_retrait();
	}
}, 5 );

/* Rappel dans l'e-mail envoyé au client. */
add_action( 'woocommerce_email_after_order_table', function ( $order, $sent_to_admin, $plain_text ) {
	if ( $sent_to_admin || ! gongcha_commande_en_retrait( $order ) ) {
		return;
	}
	$texte = gongcha_instructions_retrait();
	$lieu  = get_option( 'gongcha_lieu_retrait', '' );
	if ( $plain_text ) {
		echo "\n" . esc_html__( 'Instructions pour le retrait', 'gongcha-child' ) . "\n" . wp_strip_all_tags( $texte ) . ( $lieu ? "\n" . $lieu : '' ) . "\n\n";
		return;
	}
	echo '<h2>' . esc_html__( 'Instructions pour le retrait', 'gongcha-child' ) . '</h2>';
	echo wp_kses_post( wpautop( $texte ) );
	if ( $lieu ) {
		echo '<p><strong>' . esc_html( $lieu ) . '</strong></p>';
	}
}, 10, 3 );
