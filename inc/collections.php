<?php
/**
 * IT-12 · Taxonomie « Collection » (US-12.1, US-12.3, maquette D03).
 * Critère de classement indépendant des catégories : Produits › Collections dans le back-office,
 * une page par collection (/collection/fruite-et-frais/) avec la grille WooCommerce.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	register_taxonomy( 'collection', array( 'product' ), array(
		'labels'            => array(
			'name'          => __( 'Collections', 'gongcha-child' ),
			'singular_name' => __( 'Collection', 'gongcha-child' ),
			'menu_name'     => __( 'Collections', 'gongcha-child' ),
			'all_items'     => __( 'Toutes les collections', 'gongcha-child' ),
			'edit_item'     => __( 'Modifier la collection', 'gongcha-child' ),
			'view_item'     => __( 'Voir la collection', 'gongcha-child' ),
			'add_new_item'  => __( 'Ajouter une collection', 'gongcha-child' ),
			'new_item_name' => __( 'Nom de la collection', 'gongcha-child' ),
			'search_items'  => __( 'Rechercher une collection', 'gongcha-child' ),
			'not_found'     => __( 'Aucune collection', 'gongcha-child' ),
			'back_to_items' => __( '← Retour aux collections', 'gongcha-child' ),
		),
		'hierarchical'      => true,   // cases à cocher dans la fiche produit, comme les catégories
		'public'            => true,
		'show_admin_column' => true,   // colonne « Collections » dans la liste des produits
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'collection', 'with_front' => false ),
	) );
}, 5 );

function gongcha_est_collection() {
	return is_tax( 'collection' );
}

/* Bandeau de la collection (maquette D03) à la place du titre et de la description standards. */
add_action( 'template_redirect', function () {
	if ( ! gongcha_est_collection() ) {
		return;
	}
	add_filter( 'woocommerce_show_page_title', '__return_false' );
	remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
	add_action( 'woocommerce_archive_description', function () {
		$terme = get_queried_object();
		?>
		<div class="gc-collection-bandeau">
			<span class="gc-collection-bandeau__surtitre"><?php esc_html_e( 'Collection', 'gongcha-child' ); ?></span>
			<h1 class="gc-collection-bandeau__titre"><?php echo esc_html( $terme->name ); ?></h1>
			<?php if ( $terme->description ) : ?>
				<div class="gc-collection-bandeau__texte"><?php echo wp_kses_post( wpautop( $terme->description ) ); ?></div>
			<?php endif; ?>
		</div>
		<?php
	}, 5 );
} );

/* Barre latérale des pages boutique : cartes « Collections » et « Catégories » (maquette D03). */
add_filter( 'is_active_sidebar', function ( $actif, $index ) {
	return ( 'sidebar-1' === $index && function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) ) ? true : $actif;
}, 10, 2 );

function gongcha_carte_termes( $taxonomie, $titre ) {
	$termes = get_terms( array( 'taxonomy' => $taxonomie, 'hide_empty' => true, 'parent' => 0 ) );
	if ( is_wp_error( $termes ) || ! $termes ) {
		return;
	}
	$courant = is_tax( $taxonomie ) ? get_queried_object_id() : 0;
	echo '<nav class="widget gc-carte-termes" aria-label="' . esc_attr( $titre ) . '">';
	echo '<span class="gc-carte-termes__titre">' . esc_html( $titre ) . '</span><ul>';
	foreach ( $termes as $t ) {
		if ( 'uncategorized' === $t->slug || 'non-classe' === $t->slug ) {
			continue;
		}
		printf(
			'<li><a href="%s"%s>%s <span>(%d)</span></a></li>',
			esc_url( get_term_link( $t ) ),
			$t->term_id === $courant ? ' aria-current="page" class="is-courant"' : '',
			esc_html( $t->name ),
			(int) $t->count
		);
	}
	echo '</ul></nav>';
}

add_action( 'dynamic_sidebar_before', function ( $index ) {
	if ( 'sidebar-1' !== $index || ! function_exists( 'is_shop' ) || ! ( is_shop() || is_product_taxonomy() ) ) {
		return;
	}
	gongcha_carte_termes( 'collection', __( 'Collections', 'gongcha-child' ) );
	gongcha_carte_termes( 'product_cat', __( 'Catégories', 'gongcha-child' ) );
} );

/* Fiche produit : lien vers la ou les collections, sous la catégorie. */
add_action( 'woocommerce_product_meta_end', function () {
	$liste = get_the_term_list( get_the_ID(), 'collection', '<span class="posted_in">' . esc_html__( 'Collection :', 'gongcha-child' ) . ' ', ', ', '</span>' );
	if ( $liste && ! is_wp_error( $liste ) ) {
		echo wp_kses_post( $liste );
	}
} );
