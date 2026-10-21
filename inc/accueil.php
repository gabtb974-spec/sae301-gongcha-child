<?php
/**
 * IT-16 · Page d'accueil (front-page.php) : pleine largeur et badge « Nouveau ».
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'body_class', function ( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'storefront-full-width-content';
		$classes[] = 'gc-landing';
	}
	return $classes;
} );
