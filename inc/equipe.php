<?php
/**
 * IT-11 · Équipe (US-11.1, US-11.3, US-11.5).
 * Type de contenu « Membre » : une fiche par salarié dans le back-office (menu « Équipe »),
 * une page par membre (/equipe/prenom/) et une page qui les liste tous (/equipe/).
 * Champs SCF : acf-json/group_gc_membre.json. Gabarits : archive-membre.php et single-membre.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	register_post_type( 'membre', array(
		'labels'        => array(
			'name'               => __( 'Équipe', 'gongcha-child' ),
			'singular_name'      => __( 'Membre', 'gongcha-child' ),
			'menu_name'          => __( 'Équipe', 'gongcha-child' ),
			'all_items'          => __( 'Tous les membres', 'gongcha-child' ),
			'add_new'            => __( 'Ajouter un membre', 'gongcha-child' ),
			'add_new_item'       => __( 'Ajouter un membre de l’équipe', 'gongcha-child' ),
			'edit_item'          => __( 'Modifier le membre', 'gongcha-child' ),
			'view_item'          => __( 'Voir le profil', 'gongcha-child' ),
			'search_items'       => __( 'Rechercher un membre', 'gongcha-child' ),
			'not_found'          => __( 'Aucun membre', 'gongcha-child' ),
			'featured_image'     => __( 'Photo', 'gongcha-child' ),
			'set_featured_image' => __( 'Choisir la photo', 'gongcha-child' ),
		),
		'description'   => __( 'Ceux qui infusent, secouent et montent la mousse de votre thé, chaque jour en boutique.', 'gongcha-child' ),
		'public'        => true,
		'has_archive'   => 'equipe',
		'rewrite'       => array( 'slug' => 'equipe', 'with_front' => false ),
		'menu_icon'     => 'dashicons-groups',
		'menu_position' => 25,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		'show_in_rest'  => true,
	) );
} );

/* « Prénom » à la place de « Ajouter un titre » dans l'éditeur. */
add_filter( 'enter_title_here', function ( $text, $post ) {
	return 'membre' === $post->post_type ? __( 'Prénom', 'gongcha-child' ) : $text;
}, 10, 2 );

/* Liste du back-office : photo et poste visibles d'un coup d'œil. */
add_filter( 'manage_membre_posts_columns', function ( $cols ) {
	return array(
		'cb'        => $cols['cb'],
		'gc_photo'  => __( 'Photo', 'gongcha-child' ),
		'title'     => __( 'Prénom', 'gongcha-child' ),
		'gc_poste'  => __( 'Poste', 'gongcha-child' ),
		'menu_order'=> __( 'Ordre', 'gongcha-child' ),
		'date'      => $cols['date'],
	);
} );
add_action( 'manage_membre_posts_custom_column', function ( $col, $id ) {
	if ( 'gc_photo' === $col ) {
		echo has_post_thumbnail( $id ) ? get_the_post_thumbnail( $id, array( 48, 48 ) ) : '—';
	} elseif ( 'gc_poste' === $col ) {
		echo esc_html( (string) gongcha_champ( 'gc_poste', $id ) );
	} elseif ( 'menu_order' === $col ) {
		echo (int) get_post_field( 'menu_order', $id );
	}
}, 10, 2 );

/* Page « L'équipe » : ordre choisi dans le back-office, tous les membres sur une page. */
add_action( 'pre_get_posts', function ( $q ) {
	if ( ! is_admin() && $q->is_main_query() && $q->is_post_type_archive( 'membre' ) ) {
		$q->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
		$q->set( 'posts_per_page', -1 );
	}
} );

/* Pleine largeur (pas de barre latérale Storefront) sur les pages équipe. */
add_filter( 'body_class', function ( $classes ) {
	if ( is_post_type_archive( 'membre' ) || is_singular( 'membre' ) ) {
		$classes[] = 'storefront-full-width-content';
		$classes[] = 'gc-equipe';
	}
	return $classes;
} );

/* Visuel d'un membre : photo, ou à défaut son initiale sur le dégradé choisi. */
function gongcha_visuel_membre( $id, $classe ) {
	$degrade = gongcha_champ( 'gc_degrade', $id );
	$degrade = $degrade ? $degrade : 'rouge-or';
	echo '<span class="' . esc_attr( $classe . ' gc-degrade--' . $degrade ) . '">';
	if ( has_post_thumbnail( $id ) ) {
		echo get_the_post_thumbnail( $id, 'large', array( 'alt' => '' ) );
	} else {
		echo '<span class="gc-initiale" aria-hidden="true">' . esc_html( mb_substr( get_the_title( $id ), 0, 1 ) ) . '</span>';
	}
	echo '</span>';
}
