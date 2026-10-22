<?php
/**
 * Itération X · Sécurité des formulaires (US-X.1 à US-X.3).
 * Protection invisible des formulaires d'inscription et d'avis, sans captcha :
 *  - champ piège : caché aux visiteurs, les robots le remplissent ;
 *  - jeton horodaté et signé : un formulaire envoyé moins de 2 secondes après son affichage
 *    (ou sans jeton valide) vient d'un script, pas d'une personne.
 * Les envois bloqués sont comptés et affichés sur le tableau de bord.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const GONGCHA_ANTISPAM_DELAI_MIN = 2;           // secondes
const GONGCHA_ANTISPAM_DUREE_MAX = DAY_IN_SECONDS;

/* Jeton « horodatage.signature » (signature = clé secrète du site, impossible à fabriquer de l'extérieur). */
function gongcha_antispam_jeton( $horodatage = null ) {
	$horodatage = null === $horodatage ? time() : (int) $horodatage;
	return $horodatage . '.' . substr( wp_hash( 'gongcha-antispam|' . $horodatage ), 0, 16 );
}

/* Champs ajoutés aux formulaires (US-X.2 : rien de visible ni à faire pour le visiteur). */
function gongcha_antispam_champs() {
	return '<div class="gc-piege" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;">'
		. '<label for="gc_champ_piege">' . esc_html__( 'Ne remplissez pas ce champ', 'gongcha-child' ) . '</label>'
		. '<input type="text" id="gc_champ_piege" name="gc_champ_piege" value="" tabindex="-1" autocomplete="off">'
		. '</div>'
		. '<input type="hidden" name="gc_jeton" value="' . esc_attr( gongcha_antispam_jeton() ) . '">';
}

/* Contrôle d'un envoi. Retourne un message d'erreur, ou une chaîne vide si l'envoi est valide. */
function gongcha_antispam_verifier() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- contrôle anti-robot, pas une action privilégiée.
	$piege = isset( $_POST['gc_champ_piege'] ) ? trim( wp_unslash( $_POST['gc_champ_piege'] ) ) : '';
	$jeton = isset( $_POST['gc_jeton'] ) ? sanitize_text_field( wp_unslash( $_POST['gc_jeton'] ) ) : '';
	// phpcs:enable

	$raison = '';
	if ( '' !== $piege ) {
		$raison = 'piege';
	} else {
		$parties = explode( '.', $jeton );
		$t       = (int) $parties[0];
		if ( 2 !== count( $parties ) || ! hash_equals( gongcha_antispam_jeton( $t ), $jeton ) ) {
			$raison = 'jeton';
		} elseif ( time() - $t < GONGCHA_ANTISPAM_DELAI_MIN ) {
			$raison = 'rapide';
		} elseif ( time() - $t > GONGCHA_ANTISPAM_DUREE_MAX ) {
			$raison = 'expire';
		}
	}
	if ( '' === $raison ) {
		return '';
	}

	$stats = get_option( 'gongcha_antispam', array( 'total' => 0 ) );
	$stats['total']  = (int) $stats['total'] + 1;
	$stats[ $raison ] = (int) ( $stats[ $raison ] ?? 0 ) + 1;
	$stats['dernier'] = current_time( 'mysql' );
	update_option( 'gongcha_antispam', $stats, false );

	/* US-X.3 : message clair, une personne n'a qu'à renvoyer le formulaire. */
	return in_array( $raison, array( 'rapide', 'expire' ), true )
		? __( 'Le formulaire a été envoyé trop vite ou a expiré : vérifiez vos informations puis renvoyez-le.', 'gongcha-child' )
		: __( 'Votre envoi n’a pas pu être vérifié. Rechargez la page puis réessayez.', 'gongcha-child' );
}

/* Inscription « Mon compte » (WooCommerce). */
add_action( 'woocommerce_register_form', function () {
	echo gongcha_antispam_champs(); // phpcs:ignore WordPress.Security.EscapeOutput -- échappé dans la fonction.
} );
add_filter( 'woocommerce_process_registration_errors', function ( $erreurs ) {
	$message = gongcha_antispam_verifier();
	if ( $message ) {
		$erreurs->add( 'gongcha_antispam', $message );
	}
	return $erreurs;
} );

/* Inscription WordPress (wp-login.php?action=register), si elle est ouverte. */
add_action( 'register_form', function () {
	echo gongcha_antispam_champs(); // phpcs:ignore WordPress.Security.EscapeOutput
} );
add_filter( 'registration_errors', function ( $erreurs ) {
	$message = gongcha_antispam_verifier();
	if ( $message ) {
		$erreurs->add( 'gongcha_antispam', $message );
	}
	return $erreurs;
} );

/* Avis produits (et commentaires) : champs juste avant le bouton « Soumettre ». */
add_filter( 'comment_form_submit_field', function ( $champ ) {
	return gongcha_antispam_champs() . $champ;
} );
add_filter( 'preprocess_comment', function ( $commentaire ) {
	/* Réponses depuis l'administration, rétroliens : non concernés. */
	if ( is_admin() || wp_doing_ajax() || current_user_can( 'moderate_comments' )
		|| in_array( $commentaire['comment_type'] ?? '', array( 'pingback', 'trackback' ), true ) ) {
		return $commentaire;
	}
	$message = gongcha_antispam_verifier();
	if ( $message ) {
		wp_die( esc_html( $message ), esc_html__( 'Avis non envoyé', 'gongcha-child' ), array( 'response' => 403, 'back_link' => true ) );
	}
	return $commentaire;
}, 1 );

/* US-X.1 : suivi pour l'administrateur. */
add_action( 'wp_dashboard_setup', function () {
	wp_add_dashboard_widget( 'gongcha_antispam', __( 'Anti-spam des formulaires', 'gongcha-child' ), function () {
		$s = get_option( 'gongcha_antispam', array( 'total' => 0 ) );
		printf(
			'<p><strong>%d</strong> %s</p><p>%s : %d · %s : %d · %s : %d</p><p>%s</p>',
			(int) $s['total'],
			esc_html__( 'envois automatisés bloqués (inscription, avis).', 'gongcha-child' ),
			esc_html__( 'Champ piège rempli', 'gongcha-child' ), (int) ( $s['piege'] ?? 0 ),
			esc_html__( 'Jeton absent ou faux', 'gongcha-child' ), (int) ( $s['jeton'] ?? 0 ),
			esc_html__( 'Envoi trop rapide', 'gongcha-child' ), (int) ( $s['rapide'] ?? 0 ),
			esc_html( empty( $s['dernier'] ) ? __( 'Aucun blocage pour l’instant.', 'gongcha-child' ) : sprintf( __( 'Dernier blocage : %s', 'gongcha-child' ), $s['dernier'] ) )
		);
	} );
} );
