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

/**
 * Feuilles de style : parent, polices Google, variables, styles du thème.
 */
add_action( 'wp_enqueue_scripts', function () {
	$parent = wp_get_theme( 'storefront' );

	wp_enqueue_style( 'storefront-style', get_template_directory_uri() . '/style.css', array(), $parent->get( 'Version' ) );

	wp_enqueue_style(
		'gongcha-fonts',
		'https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;600;700&family=Vidaloka&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'gongcha-tokens', get_stylesheet_directory_uri() . '/assets/css/tokens.css', array( 'storefront-style' ), GONGCHA_VERSION );
	wp_enqueue_style( 'gongcha-theme', get_stylesheet_directory_uri() . '/assets/css/theme.css', array_filter( array( 'gongcha-tokens', 'gongcha-fonts', wp_style_is( 'storefront-woocommerce-style', 'registered' ) ? 'storefront-woocommerce-style' : '' ) ), GONGCHA_VERSION );
}, 30 );

/**
 * Préconnexion aux serveurs de polices.
 */
add_filter( 'wp_resource_hints', function ( $urls, $relation ) {
	if ( 'preconnect' === $relation ) {
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}, 10, 2 );

/**
 * Storefront n'a plus besoin de ses propres polices (Source Sans Pro) : on les retire.
 */
add_action( 'wp_enqueue_scripts', function () {
	wp_dequeue_style( 'storefront-fonts' );
}, 30 );

/**
 * Fichiers des itérations suivantes (créés au fur et à mesure).
 */
foreach ( glob( get_stylesheet_directory() . '/inc/*.php' ) as $gongcha_file ) {
	require_once $gongcha_file;
}
