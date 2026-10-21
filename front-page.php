<?php
/**
 * Page d'accueil (landing page), IT-16 · maquette D01.
 * Gabarit statique dont trois blocs sont dynamiques :
 *  - « Nos catégories » : catégories WooCommerce ;
 *  - « Nouveautés » : les 3 derniers produits ajoutés au catalogue (US-16.1) ;
 *  - « Produit star » : le produit mis en avant (étoile dans Produits).
 * Le texte d'accroche et l'image du bandeau se modifient dans Pages › Accueil.
 *
 * @package gongcha-child
 */

get_header();

$gc_carte = wc_get_page_permalink( 'shop' );
?>

<section class="gc-hero">
	<div class="gc-hero__texte">
		<span class="gc-hero__badge"><?php esc_html_e( 'Nouveau · Click & Collect', 'gongcha-child' ); ?></span>
		<h1 class="gc-hero__titre"><?php esc_html_e( 'L’art du Bubble tea, prêt à votre heure.', 'gongcha-child' ); ?></h1>
		<?php
		while ( have_posts() ) :
			the_post();
			echo '<div class="gc-hero__accroche">' . wp_kses_post( wpautop( get_the_content() ) ) . '</div>';
			$gc_hero_image = get_post_thumbnail_id();
		endwhile;
		?>
		<div class="gc-hero__actions">
			<a class="gc-btn gc-btn--creme" href="<?php echo esc_url( $gc_carte ); ?>"><?php esc_html_e( 'Commander en ligne', 'gongcha-child' ); ?></a>
			<a class="gc-btn gc-btn--contour" href="#nos-categories"><?php esc_html_e( 'Voir la carte', 'gongcha-child' ); ?></a>
		</div>
	</div>
	<?php if ( ! empty( $gc_hero_image ) ) : ?>
		<div class="gc-hero__visuel"><?php echo wp_get_attachment_image( $gc_hero_image, 'large', false, array( 'alt' => __( 'Gobelets Gong cha garnis de perles et de mousse de lait', 'gongcha-child' ) ) ); ?></div>
	<?php endif; ?>
</section>

<div class="gc-accueil">

	<?php /* Nos catégories */ ?>
	<section class="gc-section" id="nos-categories" aria-labelledby="gc-titre-categories">
		<div class="gc-section__entete">
			<h2 id="gc-titre-categories"><?php esc_html_e( 'Nos catégories', 'gongcha-child' ); ?></h2>
			<a href="<?php echo esc_url( $gc_carte ); ?>"><?php esc_html_e( 'Toute la carte', 'gongcha-child' ); ?></a>
		</div>
		<div class="gc-categories">
			<?php
			$gc_cats = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'exclude' => array( (int) get_option( 'default_product_cat' ) ), 'number' => 3 ) );
			foreach ( (array) $gc_cats as $gc_cat ) :
				if ( ! $gc_cat instanceof WP_Term ) {
					continue;
				}
				$gc_thumb = (int) get_term_meta( $gc_cat->term_id, 'thumbnail_id', true );
				?>
				<a class="gc-categorie gc-cat-<?php echo esc_attr( $gc_cat->slug ); ?>" href="<?php echo esc_url( get_term_link( $gc_cat ) ); ?>">
					<span class="gc-categorie__texte">
						<span class="gc-categorie__nom"><?php echo esc_html( $gc_cat->name ); ?></span>
						<span class="gc-categorie__nb"><?php echo esc_html( sprintf( _n( '%d thé', '%d thés', $gc_cat->count, 'gongcha-child' ), $gc_cat->count ) ); ?></span>
					</span>
					<?php echo $gc_thumb ? wp_get_attachment_image( $gc_thumb, 'woocommerce_thumbnail', false, array( 'alt' => '' ) ) : ''; ?>
				</a>
			<?php endforeach; ?>
		</div>
	</section>

	<?php /* US-16.1 / US-16.2 : les 3 derniers produits ajoutés */ ?>
	<section class="gc-section gc-nouveautes" aria-labelledby="gc-titre-nouveautes">
		<div class="gc-section__entete">
			<h2 id="gc-titre-nouveautes"><?php esc_html_e( 'Nouveautés', 'gongcha-child' ); ?></h2>
			<span><?php esc_html_e( 'Les 3 derniers thés ajoutés à la carte', 'gongcha-child' ); ?></span>
		</div>
		<?php echo do_shortcode( '[products limit="3" columns="3" orderby="id" order="DESC" visibility="visible"]' ); ?>
	</section>

	<?php
	/* Produit star : le premier produit « mis en avant » */
	$gc_star = wc_get_products( array( 'featured' => true, 'limit' => 1, 'status' => 'publish' ) );
	if ( $gc_star ) :
		$gc_star = $gc_star[0];
		$gc_cats_star = wc_get_product_term_ids( $gc_star->get_id(), 'product_cat' );
		$gc_slug_star = $gc_cats_star ? get_term( $gc_cats_star[0] )->slug : '';
		?>
		<section class="gc-star" aria-labelledby="gc-titre-star">
			<a class="gc-star__visuel gc-cat-<?php echo esc_attr( $gc_slug_star ); ?>" href="<?php echo esc_url( $gc_star->get_permalink() ); ?>">
				<span class="gc-star__badge"><?php esc_html_e( 'Le plus commandé', 'gongcha-child' ); ?></span>
				<?php echo $gc_star->get_image( 'woocommerce_single', array( 'alt' => '' ) ); ?>
			</a>
			<div class="gc-star__texte">
				<span class="gc-star__surtitre"><?php esc_html_e( 'Produit star', 'gongcha-child' ); ?></span>
				<h2 id="gc-titre-star"><?php echo esc_html( $gc_star->get_name() ); ?></h2>
				<div class="gc-star__description"><?php echo wp_kses_post( wpautop( $gc_star->get_short_description() ) ); ?></div>
				<?php $gc_compo = function_exists( 'gongcha_champ' ) ? gongcha_champ( 'gc_composition', $gc_star->get_id() ) : ''; ?>
				<?php if ( $gc_compo ) : ?>
					<p class="gc-star__composition"><?php echo esc_html( ucfirst( $gc_compo ) ); ?></p>
				<?php endif; ?>
				<p class="gc-star__prix price"><?php echo wp_kses_post( $gc_star->get_price_html() ); ?></p>
				<a class="gc-btn" href="<?php echo esc_url( $gc_star->get_permalink() ); ?>"><?php esc_html_e( 'Faites-le vôtre', 'gongcha-child' ); ?></a>
			</div>
		</section>
	<?php endif; ?>

	<?php /* Comment ça marche */ ?>
	<section class="gc-etapes" aria-label="<?php esc_attr_e( 'Comment ça marche', 'gongcha-child' ); ?>">
		<?php
		$gc_etapes = array(
			array( __( 'Composez', 'gongcha-child' ), __( 'Base de thé, glace, douceur et garnitures.', 'gongcha-child' ) ),
			array( __( 'Choisissez le retrait', 'gongcha-child' ), __( 'Retrait gratuit en boutique, ou livraison.', 'gongcha-child' ) ),
			array( __( 'Retirez en boutique', 'gongcha-child' ), __( 'Paiement au retrait ou par virement.', 'gongcha-child' ) ),
		);
		foreach ( $gc_etapes as $gc_i => $gc_etape ) :
			?>
			<div class="gc-etape">
				<span class="gc-etape__num"><?php echo (int) $gc_i + 1; ?></span>
				<span class="gc-etape__texte"><b><?php echo esc_html( $gc_etape[0] ); ?></b><span><?php echo esc_html( $gc_etape[1] ); ?></span></span>
			</div>
		<?php endforeach; ?>
	</section>

</div>

<?php
get_footer();
