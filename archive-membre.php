<?php
/**
 * Gabarit de la page « L'équipe » (/equipe/), US-11.3 · maquette D12.
 *
 * @package gongcha-child
 */

get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main gc-equipe-liste">

		<header class="gc-equipe-entete">
			<h1><?php esc_html_e( 'L’équipe', 'gongcha-child' ); ?></h1>
			<div class="gc-equipe-intro"><?php echo wp_kses_post( get_the_post_type_description() ); ?></div>
		</header>

		<?php if ( have_posts() ) : ?>
			<ul class="gc-membres">
				<?php
				while ( have_posts() ) :
					the_post();
					$poste   = gongcha_champ( 'gc_poste', get_the_ID() );
					$boisson = gongcha_champ( 'gc_boisson', get_the_ID() );
					?>
					<li>
						<a class="gc-membre" href="<?php the_permalink(); ?>">
							<?php gongcha_visuel_membre( get_the_ID(), 'gc-membre__visuel' ); ?>
							<span class="gc-membre__nom"><?php the_title(); ?></span>
							<?php if ( $poste ) : ?>
								<span class="gc-membre__poste"><?php echo esc_html( $poste ); ?></span>
							<?php endif; ?>
							<span class="gc-membre__pied">
								<?php if ( $boisson ) : ?>
									<span class="gc-membre__prefere"><?php echo esc_html( sprintf( __( 'Préféré : %s', 'gongcha-child' ), get_the_title( $boisson ) ) ); ?></span>
								<?php endif; ?>
								<span class="gc-membre__lien"><?php esc_html_e( 'Voir le profil', 'gongcha-child' ); ?></span>
							</span>
						</a>
					</li>
				<?php endwhile; ?>
			</ul>
		<?php else : ?>
			<p><?php esc_html_e( 'L’équipe se présente bientôt.', 'gongcha-child' ); ?></p>
		<?php endif; ?>

	</main>
</div>

<?php
get_footer();
