<?php
/**
 * Itération Y · Audit et performances (US-Y.1 à US-Y.3).
 * Objectif : 80/100 minimum dans les 4 catégories Lighthouse sur une fiche produit.
 * Polices hébergées localement : voir functions.php et assets/css/polices.css.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Émojis WordPress : script et styles inutiles (les navigateurs affichent les émojis nativement). */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
add_filter( 'emoji_svg_url', '__return_false' );

/* Styles des blocs Gutenberg : inutiles avec l'éditeur classique, sauf si la page ou un widget affiché utilise des blocs. */
function gongcha_page_sans_blocs() {
	if ( is_singular() && has_blocks( get_queried_object() ) ) {
		return false;
	}
	$zones = array( 'header-1', 'footer-1', 'footer-2', 'footer-3', 'footer-4' );
	if ( function_exists( 'gongcha_page_boutique' ) && gongcha_page_boutique() ) {
		$zones[] = 'sidebar-1';
	}
	$widgets = wp_get_sidebars_widgets();
	foreach ( $zones as $zone ) {
		foreach ( (array) ( $widgets[ $zone ] ?? array() ) as $widget ) {
			if ( 0 === strpos( $widget, 'block-' ) ) {
				return false;
			}
		}
	}
	return true;
}
add_action( 'wp_enqueue_scripts', function () {
	if ( ! gongcha_page_sans_blocs() ) {
		return;
	}
	foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'wc-blocks-style', 'wc-blocks-vendors-style', 'storefront-gutenberg-blocks', 'global-styles', 'classic-theme-styles' ) as $handle ) {
		wp_dequeue_style( $handle );
	}
}, 100 );

/* Images : WordPress génère les tailles réduites en WebP (30 à 50 % plus légères que JPEG/PNG).
   Les originaux sont conservés. Pour les images déjà importées : wp media regenerate (voir le kit). */
add_filter( 'image_editor_output_format', function ( $formats ) {
	$formats['image/jpeg'] = 'image/webp';
	$formats['image/png']  = 'image/webp';
	return $formats;
} );

/* Fiche produit : l'image principale est l'élément le plus grand (LCP), on la charge en priorité. */
add_filter( 'woocommerce_gallery_image_html_attachment_image_params', function ( $params, $attachment_id, $taille, $principale ) {
	if ( $principale ) {
		$params['fetchpriority'] = 'high';
		$params['loading']       = 'eager';
	}
	return $params;
}, 10, 4 );

/* Loupe au survol : elle télécharge la photo originale (plusieurs centaines de Ko) dès l'ouverture de la fiche.
   La visionneuse plein écran reste disponible au clic. */
add_action( 'after_setup_theme', function () {
	remove_theme_support( 'wc-product-gallery-zoom' );
}, 20 );

/* Polices locales : préchargement des fichiers utilisés au-dessus de la ligne de flottaison. */
add_action( 'wp_head', function () {
	$polices = glob( get_stylesheet_directory() . '/assets/fonts/*.woff2' );
	foreach ( array_slice( (array) $polices, 0, 3 ) as $fichier ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( get_stylesheet_directory_uri() . '/assets/fonts/' . basename( $fichier ) )
		);
	}
}, 2 );

/* Mise en cache : le produit star (inc/produit-star.php) est calculé une fois puis gardé 12 h.
   Le cache est vidé dès qu'une commande change d'état ou qu'un produit est modifié. */
function gongcha_vider_cache_star() {
	delete_transient( 'gongcha_produit_star' );
}
add_action( 'woocommerce_order_status_changed', 'gongcha_vider_cache_star' );
add_action( 'save_post_product', 'gongcha_vider_cache_star' );
