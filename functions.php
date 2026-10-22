<?php
/**
 * Gong cha Click & Collect · thème enfant de Storefront.
 *
 * Chargement du thème parent, des polices et des styles, puis des fonctionnalités de inc/ (une par fichier).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Version des fichiers CSS/JS = version du thème + date de la dernière modification d'un fichier de assets/.
   Le navigateur recharge donc les styles dès qu'ils changent (plus besoin de Cmd + Maj + R). */
$gongcha_assets = glob( get_stylesheet_directory() . '/assets/*/*.*' );
define( 'GONGCHA_VERSION', wp_get_theme()->get( 'Version' ) . '.' . ( $gongcha_assets ? max( array_map( 'filemtime', $gongcha_assets ) ) : 0 ) );

/* Itération Z : traductions du thème (languages/gongcha-child.pot, modifiables avec Loco Translate). */
add_action( 'after_setup_theme', function () {
	load_child_theme_textdomain( 'gongcha-child', get_stylesheet_directory() . '/languages' );
} );

/**
 * Feuilles de style : parent, polices, variables, styles du thème.
 * Itération Y : polices hébergées sur le site (assets/css/polices.css) plutôt que chez Google :
 * pas de requête vers un serveur tiers qui bloque l'affichage, ni de transfert de l'adresse IP du visiteur.
 */
add_action( 'wp_enqueue_scripts', function () {
	$parent = wp_get_theme( 'storefront' );

	wp_enqueue_style( 'storefront-style', get_template_directory_uri() . '/style.css', array(), $parent->get( 'Version' ) );

	$polices_locales = file_exists( get_stylesheet_directory() . '/assets/css/polices.css' );
	wp_enqueue_style(
		'gongcha-fonts',
		$polices_locales
			? get_stylesheet_directory_uri() . '/assets/css/polices.css'
			: 'https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;600;700&family=Vidaloka&display=swap',
		array(),
		$polices_locales ? GONGCHA_VERSION : null
	);

	wp_enqueue_style( 'gongcha-tokens', get_stylesheet_directory_uri() . '/assets/css/tokens.css', array( 'storefront-style' ), GONGCHA_VERSION );
	wp_enqueue_style( 'gongcha-theme', get_stylesheet_directory_uri() . '/assets/css/theme.css', array_filter( array( 'gongcha-tokens', 'gongcha-fonts', wp_style_is( 'storefront-woocommerce-style', 'registered' ) ? 'storefront-woocommerce-style' : '' ) ), GONGCHA_VERSION );
}, 30 );

/**
 * Storefront n'a plus besoin de ses propres polices (Source Sans Pro) : on les retire.
 */
add_action( 'wp_enqueue_scripts', function () {
	wp_dequeue_style( 'storefront-fonts' );
}, 30 );

/**
 * Fichiers des itérations (un par fonctionnalité).
 */
foreach ( glob( get_stylesheet_directory() . '/inc/*.php' ) as $gongcha_file ) {
	require_once $gongcha_file;
}
