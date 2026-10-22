<?php
/**
 * IT-20 · Créneaux de retrait et de livraison (US-20.1 à US-20.3, maquettes D06 et D07).
 * À la validation de commande, le client choisit un jour puis un créneau :
 *  - retrait en boutique : créneaux de 15 min, au plus tôt 20 min après la commande ;
 *  - livraison : plages d'une heure, au plus tôt 1 h après la commande.
 * Réglages : WooCommerce › Réglages › Expédition › Options d'expédition › « Créneaux ».
 * Le créneau est enregistré dans la commande et repris partout : confirmation, Mon compte, e-mails, back-office, facture.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gongcha_creneaux_reglages() {
	return array(
		'ouverture'       => (string) get_option( 'gongcha_creneau_ouverture', '11:00' ),
		'fermeture'       => (string) get_option( 'gongcha_creneau_fermeture', '21:00' ),
		'pas'             => max( 5, (int) get_option( 'gongcha_creneau_pas', 15 ) ),
		'delai_retrait'   => max( 0, (int) get_option( 'gongcha_creneau_delai_retrait', 20 ) ),
		'delai_livraison' => max( 0, (int) get_option( 'gongcha_creneau_delai_livraison', 60 ) ),
		'jours'           => max( 1, (int) get_option( 'gongcha_creneau_jours', 6 ) ),
	);
}

/* Mode choisi dans le panier : retrait (local_pickup) ou livraison. */
function gongcha_mode_retrait() {
	$methodes = WC()->session ? (array) WC()->session->get( 'chosen_shipping_methods' ) : array();
	return isset( $methodes[0] ) && 0 === strpos( (string) $methodes[0], 'local_pickup' );
}

/* Créneaux d'un jour (Y-m-d) : clé « HH:MM », libellé « 12:30 – 12:45 ». */
function gongcha_creneaux_du_jour( $ymd, $retrait ) {
	$r     = gongcha_creneaux_reglages();
	$tz    = wp_timezone();
	$debut = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $ymd . ' ' . $r['ouverture'], $tz );
	$fin   = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $ymd . ' ' . $r['fermeture'], $tz );
	if ( ! $debut || ! $fin ) {
		return array();
	}
	$pas    = $retrait ? $r['pas'] : 60;
	$plutot = ( new DateTimeImmutable( 'now', $tz ) )->modify( '+' . ( $retrait ? $r['delai_retrait'] : $r['delai_livraison'] ) . ' minutes' );
	$liste  = array();
	for ( $t = $debut; $t < $fin; $t = $t->modify( "+{$pas} minutes" ) ) {
		$f = $t->modify( "+{$pas} minutes" );
		if ( $f > $fin ) {
			break;
		}
		if ( $t >= $plutot ) {
			$liste[ $t->format( 'H:i' ) ] = $t->format( 'H:i' ) . ' – ' . $f->format( 'H:i' );
		}
	}
	return $liste;
}

function gongcha_libelle_jour( $ymd ) {
	$tz   = wp_timezone();
	$jour = DateTimeImmutable::createFromFormat( '!Y-m-d', $ymd, $tz );
	$auj  = new DateTimeImmutable( 'today', $tz );
	if ( ! $jour ) {
		return $ymd;
	}
	$prefixe = '';
	if ( $jour == $auj ) { // phpcs:ignore Universal.Operators.StrictComparisons
		$prefixe = __( 'Aujourd’hui, ', 'gongcha-child' );
	} elseif ( $jour == $auj->modify( '+1 day' ) ) { // phpcs:ignore Universal.Operators.StrictComparisons
		$prefixe = __( 'Demain, ', 'gongcha-child' );
	}
	return $prefixe . ucfirst( wp_date( 'l j F', $jour->getTimestamp(), $tz ) );
}

/* US-20.1 : jours proposés (seulement ceux qui ont encore au moins un créneau). */
function gongcha_jours_disponibles( $retrait ) {
	$r     = gongcha_creneaux_reglages();
	$auj   = new DateTimeImmutable( 'today', wp_timezone() );
	$jours = array();
	for ( $i = 0; $i < $r['jours'] + 1 && count( $jours ) < $r['jours']; $i++ ) {
		$ymd = $auj->modify( "+{$i} day" )->format( 'Y-m-d' );
		if ( gongcha_creneaux_du_jour( $ymd, $retrait ) ) {
			$jours[ $ymd ] = gongcha_libelle_jour( $ymd );
		}
	}
	return $jours;
}

/* Réglages dans le back-office. */
add_filter( 'woocommerce_get_settings_shipping', function ( $settings, $section ) {
	if ( 'options' !== $section ) {
		return $settings;
	}
	$champ = function ( $id, $titre, $defaut, $type = 'number', $desc = '' ) {
		return array( 'title' => $titre, 'id' => $id, 'type' => $type, 'default' => $defaut, 'desc' => $desc );
	};
	return array_merge( $settings, array(
		array( 'title' => __( 'Créneaux de retrait et de livraison', 'gongcha-child' ), 'type' => 'title', 'id' => 'gongcha_creneaux',
			'desc' => __( 'Jours et créneaux proposés au client pendant la validation de commande.', 'gongcha-child' ) ),
		$champ( 'gongcha_creneau_ouverture', __( 'Ouverture', 'gongcha-child' ), '11:00', 'text', 'HH:MM' ),
		$champ( 'gongcha_creneau_fermeture', __( 'Fermeture', 'gongcha-child' ), '21:00', 'text', 'HH:MM' ),
		$champ( 'gongcha_creneau_pas', __( 'Durée d’un créneau de retrait (min)', 'gongcha-child' ), '15' ),
		$champ( 'gongcha_creneau_delai_retrait', __( 'Délai minimum avant un retrait (min)', 'gongcha-child' ), '20', 'number', __( 'Temps de préparation.', 'gongcha-child' ) ),
		$champ( 'gongcha_creneau_delai_livraison', __( 'Délai minimum avant une livraison (min)', 'gongcha-child' ), '60' ),
		$champ( 'gongcha_creneau_jours', __( 'Nombre de jours proposés', 'gongcha-child' ), '6' ),
		array( 'type' => 'sectionend', 'id' => 'gongcha_creneaux' ),
	) );
}, 40, 2 );

/* US-20.1 à 20.3 : sélecteurs dans le récapitulatif de commande, sous les modes d'expédition. */
add_action( 'woocommerce_review_order_after_shipping', function () {
	if ( ! WC()->cart || ! WC()->cart->needs_shipping() ) {
		return;
	}
	$retrait = gongcha_mode_retrait();
	$jours   = gongcha_jours_disponibles( $retrait );
	$titre   = $retrait ? __( 'Jour et créneau de retrait', 'gongcha-child' ) : __( 'Jour et créneau de livraison', 'gongcha-child' );
	echo '<tr class="gc-creneau"><th>' . esc_html( $titre ) . '</th><td data-title="' . esc_attr( $titre ) . '">';
	if ( ! $jours ) {
		echo '<p class="gc-creneau__vide">' . esc_html__( 'Aucun créneau disponible pour le moment.', 'gongcha-child' ) . '</p></td></tr>';
		return;
	}
	$jour = (string) WC()->session->get( 'gc_creneau_jour' );
	$jour = isset( $jours[ $jour ] ) ? $jour : (string) key( $jours );
	$liste = gongcha_creneaux_du_jour( $jour, $retrait );
	$heure = (string) WC()->session->get( 'gc_creneau_heure' );
	$heure = isset( $liste[ $heure ] ) ? $heure : (string) key( $liste );

	echo '<span class="gc-creneau__champs">';
	echo '<label class="screen-reader-text" for="gc_creneau_jour">' . esc_html__( 'Jour', 'gongcha-child' ) . '</label><select id="gc_creneau_jour" name="gc_creneau_jour">';
	foreach ( $jours as $cle => $libelle ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $cle ), selected( $cle, $jour, false ), esc_html( $libelle ) );
	}
	echo '</select><label class="screen-reader-text" for="gc_creneau_heure">' . esc_html__( 'Créneau', 'gongcha-child' ) . '</label><select id="gc_creneau_heure" name="gc_creneau_heure">';
	foreach ( $liste as $cle => $libelle ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $cle ), selected( $cle, $heure, false ), esc_html( $libelle ) );
	}
	echo '</select></span></td></tr>';
} );

/* Le choix est gardé quand WooCommerce recharge le récapitulatif (changement de mode ou de jour). */
add_action( 'woocommerce_checkout_update_order_review', function ( $post_data ) {
	parse_str( (string) $post_data, $d );
	foreach ( array( 'gc_creneau_jour', 'gc_creneau_heure' ) as $cle ) {
		if ( isset( $d[ $cle ] ) ) {
			WC()->session->set( $cle, sanitize_text_field( $d[ $cle ] ) );
		}
	}
} );
add_action( 'wp_enqueue_scripts', function () {
	if ( is_checkout() ) {
		wp_add_inline_script( 'wc-checkout', "jQuery(document.body).on('change','#gc_creneau_jour',function(){jQuery(document.body).trigger('update_checkout');});" );
	}
}, 40 );

/* Validation : le créneau doit exister et être encore disponible. */
function gongcha_creneau_poste() {
	return array(
		sanitize_text_field( wp_unslash( $_POST['gc_creneau_jour'] ?? '' ) ),  // phpcs:ignore WordPress.Security.NonceVerification
		sanitize_text_field( wp_unslash( $_POST['gc_creneau_heure'] ?? '' ) ), // phpcs:ignore WordPress.Security.NonceVerification
	);
}
add_action( 'woocommerce_after_checkout_validation', function ( $data, $errors ) {
	if ( ! WC()->cart->needs_shipping() ) {
		return;
	}
	$retrait = gongcha_mode_retrait();
	list( $jour, $heure ) = gongcha_creneau_poste();
	$liste = $jour ? gongcha_creneaux_du_jour( $jour, $retrait ) : array();
	if ( ! isset( $liste[ $heure ] ) || ! isset( gongcha_jours_disponibles( $retrait )[ $jour ] ) ) {
		$errors->add( 'gc_creneau', $retrait ? __( 'Choisissez un jour et un créneau de retrait disponibles.', 'gongcha-child' ) : __( 'Choisissez un jour et un créneau de livraison disponibles.', 'gongcha-child' ) );
	}
}, 10, 2 );

/* Enregistrement dans la commande. */
add_action( 'woocommerce_checkout_create_order', function ( $order ) {
	if ( ! WC()->cart->needs_shipping() ) {
		return;
	}
	$retrait = gongcha_mode_retrait();
	list( $jour, $heure ) = gongcha_creneau_poste();
	$liste = gongcha_creneaux_du_jour( $jour, $retrait );
	if ( isset( $liste[ $heure ] ) ) {
		$order->update_meta_data( '_gc_creneau', array(
			'mode'    => $retrait ? 'retrait' : 'livraison',
			'jour'    => $jour,
			'heure'   => $heure,
			'libelle' => gongcha_libelle_jour( $jour ) . ' · ' . $liste[ $heure ],
		) );
	}
	WC()->session->__unset( 'gc_creneau_jour' );
	WC()->session->__unset( 'gc_creneau_heure' );
} );

/* Créneau d'une commande : array( titre, libellé ) ou null. */
function gongcha_creneau_commande( $order ) {
	$c = $order ? $order->get_meta( '_gc_creneau' ) : null;
	if ( empty( $c['libelle'] ) ) {
		return null;
	}
	$titre = 'retrait' === $c['mode'] ? __( 'Créneau de retrait', 'gongcha-child' ) : __( 'Créneau de livraison', 'gongcha-child' );
	return array( $titre, $c['libelle'] );
}

function gongcha_afficher_creneau( $order ) {
	$c = gongcha_creneau_commande( is_numeric( $order ) ? wc_get_order( $order ) : $order );
	if ( $c ) {
		printf( '<p class="gc-creneau-choisi"><strong>%s :</strong> <span>%s</span></p>', esc_html( $c[0] ), esc_html( $c[1] ) );
	}
}
add_action( 'woocommerce_thankyou', 'gongcha_afficher_creneau', 4 );                          // page « Commande reçue »
add_action( 'woocommerce_view_order', 'gongcha_afficher_creneau', 5 );                        // Mon compte › commande
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'gongcha_afficher_creneau' ); // back-office

/* E-mails (sous le récapitulatif). */
add_filter( 'woocommerce_email_order_meta_fields', function ( $champs, $sent_to_admin, $order ) {
	$c = gongcha_creneau_commande( $order );
	if ( $c ) {
		$champs['gc_creneau'] = array( 'label' => $c[0], 'value' => $c[1] );
	}
	return $champs;
}, 10, 3 );

/* Facture PDF (IT-17). */
add_action( 'wpo_wcpdf_after_order_data', function ( $type, $order ) {
	$c = gongcha_creneau_commande( $order );
	if ( $c ) {
		printf( '<tr class="gc-creneau"><th>%s :</th><td>%s</td></tr>', esc_html( $c[0] ), esc_html( $c[1] ) );
	}
}, 10, 2 );
