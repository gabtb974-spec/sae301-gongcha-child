<?php
/**
 * IT-12 · Page « Collections » (US-12.3) : toutes les collections sur une seule page.
 * Gabarit appliqué automatiquement à la page dont l'adresse est /collections/ (hiérarchie WordPress page-{slug}.php).
 * Pour chaque terme de la taxonomie Collection : bandeau (nom, description), grille de ses produits, lien vers sa page.
 *
 * @package gongcha-child
 */

get_header();

$gc_collections = get_terms( array( 'taxonomy' => 'collection', 'hide_empty' => true, 'parent' => 0 ) );
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main gc-collections" role="main">

		<header class="gc-collections__entete">
			<span class="gc-collections__surtitre"><?php esc_html_e( 'Nos collections', 'gongcha-child' ); ?></span>
			<h1 class="gc-collections__titre"><?php the_title(); ?></h1>
			<?php
			while ( have_posts() ) :
				the_post();
				if ( get_the_content() ) {
					echo '<div class="gc-collections__intro">' . wp_kses_post( wpautop( get_the_content() ) ) . '</div>';
				}
			endwhile;
			?>
			<?php if ( $gc_collections && ! is_wp_error( $gc_collections ) ) : ?>
				<nav class="gc-collections__sommaire" aria-label="<?php esc_attr_e( 'Aller à une collection', 'gongcha-child' ); ?>">
					<?php foreach ( $gc_collections as $gc_terme ) : ?>
						<a class="term-<?php echo esc_attr( $gc_terme->slug ); ?>" href="#collection-<?php echo esc_attr( $gc_terme->slug ); ?>"><span class="gc-collections__pastille" aria-hidden="true"></span><?php echo esc_html( $gc_terme->name ); ?> <span class="gc-collections__nb">(<?php echo (int) $gc_terme->count; ?>)</span></a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
		</header>

		<?php
		if ( ! $gc_collections || is_wp_error( $gc_collections ) ) :
			echo '<p>' . esc_html__( 'Aucune collection pour le moment.', 'gongcha-child' ) . '</p>';
		else :
			foreach ( $gc_collections as $gc_terme ) :
				$gc_produits = new WP_Query( array(
					'post_type'      => 'product',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery
						array( 'taxonomy' => 'collection', 'terms' => $gc_terme->term_id ),
						array( 'taxonomy' => 'product_visibility', 'field' => 'name', 'terms' => array( 'exclude-from-catalog' ), 'operator' => 'NOT IN' ),
					),
				) );
				?>
				<section id="collection-<?php echo esc_attr( $gc_terme->slug ); ?>" class="gc-collections__bloc term-<?php echo esc_attr( $gc_terme->slug ); ?>" aria-labelledby="titre-<?php echo esc_attr( $gc_terme->slug ); ?>">
					<div class="gc-collection-bandeau gc-collections__bandeau">
						<div class="gc-collections__bandeau-texte">
							<span class="gc-collection-bandeau__surtitre"><?php echo esc_html( sprintf( _n( 'Collection · %d thé', 'Collection · %d thés', $gc_terme->count, 'gongcha-child' ), $gc_terme->count ) ); ?></span>
							<h2 id="titre-<?php echo esc_attr( $gc_terme->slug ); ?>" class="gc-collection-bandeau__titre"><?php echo esc_html( $gc_terme->name ); ?></h2>
							<?php if ( $gc_terme->description ) : ?>
								<div class="gc-collection-bandeau__texte"><?php echo wp_kses_post( wpautop( $gc_terme->description ) ); ?></div>
							<?php endif; ?>
						</div>
						<a class="gc-collections__voir" href="<?php echo esc_url( get_term_link( $gc_terme ) ); ?>"><?php esc_html_e( 'Voir la collection', 'gongcha-child' ); ?> <span aria-hidden="true">→</span><span class="screen-reader-text"> <?php echo esc_html( $gc_terme->name ); ?></span></a>
					</div>
					<?php if ( $gc_produits->have_posts() ) : ?>
						<div class="woocommerce columns-3">
							<?php
							wc_set_loop_prop( 'columns', 3 );
							woocommerce_product_loop_start();
							while ( $gc_produits->have_posts() ) :
								$gc_produits->the_post();
								wc_get_template_part( 'content', 'product' );
							endwhile;
							woocommerce_product_loop_end();
							wp_reset_postdata();
							?>
						</div>
					<?php endif; ?>
				</section>
				<?php
			endforeach;
		endif;
		?>

	</main>
</div>
<?php
get_footer();
