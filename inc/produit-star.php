<?php
/**
 * Itération W · Produit star (US-W.1 à US-W.3).
 * Le produit le plus vendu (champ total_sales tenu à jour par WooCommerce) est identifié automatiquement.
 * Tant qu'aucune vente n'existe, on reprend le produit « mis en avant » (étoile dans Produits).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* US-W.1 : identifiant du produit star (calculé une fois par page). */
function gongcha_produit_star() {
	static $id = null;
	if ( null !== $id ) {
		return $id;
	}
	$plus_vendu = get_posts( array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_key'       => 'total_sales', // phpcs:ignore WordPress.DB.SlowDBQuery
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
		'meta_query'     => array( array( 'key' => 'total_sales', 'value' => 0, 'compare' => '>', 'type' => 'NUMERIC' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
	) );
	if ( $plus_vendu ) {
		$id = (int) $plus_vendu[0];
	} else {
		$mis_en_avant = wc_get_featured_product_ids();
		$id           = $mis_en_avant ? (int) reset( $mis_en_avant ) : 0;
	}
	return $id;
}

/* Vrai si le produit star vient vraiment des ventes (sinon : choix manuel). */
function gongcha_star_par_ventes() {
	$id = gongcha_produit_star();
	return $id && (int) get_post_meta( $id, 'total_sales', true ) > 0;
}

/* US-W.3 : badge « Produit star » dans les grilles et sur la fiche. */
function gongcha_badge_star() {
	global $product;
	if ( $product && $product->get_id() === gongcha_produit_star() ) {
		echo '<span class="gc-badge-star">' . esc_html__( '★ Produit star', 'gongcha-child' ) . '</span>';
	}
}
add_action( 'woocommerce_before_shop_loop_item_title', 'gongcha_badge_star', 9 );
add_action( 'woocommerce_single_product_summary', 'gongcha_badge_star', 4 );
