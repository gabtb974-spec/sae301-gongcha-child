<?php
/**
 * IT-21 · Référencement (US-21.1 à US-21.3).
 * - Champs SCF « Balise Title » et « Meta description » sur les produits, catégories et collections (acf-json/group_gc_seo.json).
 * - Dans <head> : <title>, meta description, balises Open Graph (Facebook, LinkedIn, WhatsApp…) et Twitter.
 * Si une extension SEO (Yoast, Rank Math) est activée, ce fichier se désactive pour éviter les doublons.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) ) {
	return;
}

function gongcha_seo_champ( $nom, $objet ) {
	return function_exists( 'get_field' ) ? trim( (string) get_field( $nom, $objet ) ) : '';
}

function gongcha_seo_couper( $texte, $max = 155 ) {
	$texte = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( strip_shortcodes( (string) $texte ) ) ) );
	return mb_strlen( $texte ) > $max ? rtrim( mb_substr( $texte, 0, $max - 1 ) ) . '…' : $texte;
}

/* Données SEO de la page courante, ou null (WordPress garde alors son comportement normal). */
function gongcha_seo_donnees() {
	static $d = false;
	if ( false !== $d ) {
		return $d;
	}
	$site = get_bloginfo( 'name' );
	$d    = null;

	if ( is_singular( 'product' ) && ( $p = wc_get_product( get_queried_object_id() ) ) ) {
		$titre = gongcha_seo_champ( 'gc_seo_titre', $p->get_id() );
		$desc  = gongcha_seo_champ( 'gc_seo_description', $p->get_id() );
		$d     = array(
			'titre'       => $titre ? $titre : $p->get_name() . ' | ' . $site,
			'description' => gongcha_seo_couper( $desc ? $desc : ( $p->get_short_description() ? $p->get_short_description() : $p->get_description() ) ),
			'image'       => $p->get_image_id() ? wp_get_attachment_image_url( $p->get_image_id(), 'large' ) : '',
			'url'         => get_permalink( $p->get_id() ),
			'type'        => 'product',
			'prix'        => wc_format_decimal( $p->get_price(), 2 ),
			'stock'       => $p->is_in_stock() ? 'instock' : 'oos',
		);
	} elseif ( is_product_category() || is_tax( 'collection' ) ) {
		$t     = get_queried_object();
		$cle   = $t->taxonomy . '_' . $t->term_id;
		$titre = gongcha_seo_champ( 'gc_seo_titre', $cle );
		$desc  = gongcha_seo_champ( 'gc_seo_description', $cle );
		$img   = (int) get_term_meta( $t->term_id, 'thumbnail_id', true );
		$d     = array(
			'titre'       => $titre ? $titre : $t->name . ' | ' . $site,
			'description' => gongcha_seo_couper( $desc ? $desc : ( $t->description ? $t->description : sprintf( __( '%1$s : bubble tea à commander en ligne et à retirer en boutique.', 'gongcha-child' ), $t->name ) ) ),
			'image'       => $img ? wp_get_attachment_image_url( $img, 'large' ) : '',
			'url'         => get_term_link( $t ),
			'type'        => 'website',
		);
	} elseif ( is_front_page() ) {
		$accueil = get_post( (int) get_option( 'page_on_front' ) );
		$d       = array(
			'titre'       => $site . ' · ' . __( 'Bubble tea à commander en ligne et à retirer en boutique', 'gongcha-child' ),
			'description' => gongcha_seo_couper( $accueil ? $accueil->post_content : get_bloginfo( 'description' ) ),
			'image'       => $accueil && has_post_thumbnail( $accueil ) ? get_the_post_thumbnail_url( $accueil, 'large' ) : '',
			'url'         => home_url( '/' ),
			'type'        => 'website',
		);
	} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
		$d = array(
			'titre'       => __( 'La carte', 'gongcha-child' ) . ' | ' . $site,
			'description' => __( 'Thés au lait, thés aux fruits et thés à la mousse de lait Gong cha, personnalisables et à retirer en boutique.', 'gongcha-child' ),
			'image'       => '',
			'url'         => wc_get_page_permalink( 'shop' ),
			'type'        => 'website',
		);
	}
	if ( $d && ! $d['image'] && ( $logo = (int) get_theme_mod( 'custom_logo' ) ) ) {
		$d['image'] = wp_get_attachment_image_url( $logo, 'full' );
	}
	return $d;
}

/* US-21.1 : balise <title>. */
add_filter( 'pre_get_document_title', function ( $titre ) {
	$d = gongcha_seo_donnees();
	return $d ? $d['titre'] : $titre;
}, 20 );

/* US-21.2 / 21.3 : meta description, Open Graph, Twitter. */
add_action( 'wp_head', function () {
	$d = gongcha_seo_donnees();
	if ( ! $d ) {
		return;
	}
	$meta = function ( $attr, $nom, $valeur ) {
		if ( '' !== (string) $valeur ) {
			printf( '<meta %s="%s" content="%s" />' . "\n", esc_attr( $attr ), esc_attr( $nom ), esc_attr( $valeur ) );
		}
	};
	echo "\n<!-- SEO : thème gongcha-child (IT-21) -->\n";
	$meta( 'name', 'description', $d['description'] );
	$meta( 'property', 'og:locale', 'fr_FR' );
	$meta( 'property', 'og:site_name', get_bloginfo( 'name' ) );
	$meta( 'property', 'og:type', 'product' === $d['type'] ? 'product' : 'website' );
	$meta( 'property', 'og:title', $d['titre'] );
	$meta( 'property', 'og:description', $d['description'] );
	$meta( 'property', 'og:url', $d['url'] );
	$meta( 'property', 'og:image', $d['image'] );
	if ( 'product' === $d['type'] ) {
		$meta( 'property', 'product:price:amount', $d['prix'] );
		$meta( 'property', 'product:price:currency', get_woocommerce_currency() );
		$meta( 'property', 'product:availability', $d['stock'] );
	}
	$meta( 'name', 'twitter:card', $d['image'] ? 'summary_large_image' : 'summary' );
	$meta( 'name', 'twitter:title', $d['titre'] );
	$meta( 'name', 'twitter:description', $d['description'] );
	$meta( 'name', 'twitter:image', $d['image'] );
}, 1 );
