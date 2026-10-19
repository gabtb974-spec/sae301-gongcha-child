<?php
/**
 * Gabarit du profil d'un membre de l'équipe (/equipe/prenom/), US-11.3 · maquette D13.
 *
 * @package gongcha-child
 */

get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main">
		<?php
		while ( have_posts() ) :
			the_post();
			$poste   = gongcha_champ( 'gc_poste', get_the_ID() );
			$boisson = gongcha_champ( 'gc_boisson', get_the_ID() );
			?>
			<article class="gc-profil">
				<?php gongcha_visuel_membre( get_the_ID(), 'gc-profil__visuel' ); ?>

				<div class="gc-profil__texte">
					<?php if ( $poste ) : ?>
						<p class="gc-profil__poste"><?php echo esc_html( $poste ); ?></p>
					<?php endif; ?>
					<h1 class="gc-profil__nom"><?php the_title(); ?></h1>
					<div class="gc-profil__bio"><?php the_content(); ?></div>

					<?php if ( $boisson && get_post_status( $boisson ) === 'publish' ) : ?>
						<div class="gc-profil__boisson">
							<span>
								<span class="gc-profil__label"><?php esc_html_e( 'Sa boisson préférée', 'gongcha-child' ); ?></span>
								<span class="gc-profil__produit"><?php echo esc_html( get_the_title( $boisson ) ); ?></span>
								<a href="<?php echo esc_url( get_permalink( $boisson ) ); ?>"><?php esc_html_e( 'Commander la même', 'gongcha-child' ); ?> →</a>
							</span>
							<?php echo get_the_post_thumbnail( $boisson, 'woocommerce_thumbnail', array( 'alt' => '' ) ); ?>
						</div>
					<?php endif; ?>

					<a class="gc-profil__retour" href="<?php echo esc_url( get_post_type_archive_link( 'membre' ) ); ?>">← <?php esc_html_e( 'Retour à l’équipe', 'gongcha-child' ); ?></a>
				</div>
			</article>
		<?php endwhile; ?>
	</main>
</div>

<?php
get_footer();
