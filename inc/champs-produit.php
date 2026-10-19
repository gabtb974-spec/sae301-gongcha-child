<?php
/**
 * IT-4 · Champs personnalisés des fiches produit (Secure Custom Fields).
 * Le groupe de champs est versionné dans acf-json/ : SCF le charge automatiquement
 * depuis le thème enfant, et y réenregistre toute modification faite dans le back-office.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Lecture d'un champ, même si SCF est désactivé. */
function gongcha_champ( $name, $post_id ) {
	return function_exists( 'get_field' ) ? get_field( $name, $post_id ) : get_post_meta( $post_id, $name, true );
}

/* US-4.3 / US-4.4 : bloc d'informations entre le titre (priorité 5) et le prix (priorité 10). */
add_action( 'woocommerce_single_product_summary', function () {
	$id          = get_the_ID();
	$composition = gongcha_champ( 'gc_composition', $id );
	$allergenes  = (array) gongcha_champ( 'gc_allergenes', $id );
	$kcal        = gongcha_champ( 'gc_kcal', $id );
	$sucres      = gongcha_champ( 'gc_sucres', $id );
	$contenance  = gongcha_champ( 'gc_contenance', $id );
	$remarque    = gongcha_champ( 'gc_remarque', $id );

	if ( ! $composition && '' === (string) $kcal ) {
		return; // produit non renseigné : on n'affiche rien
	}

	$allergenes = array_filter( $allergenes );
	$nutrition  = array();
	if ( '' !== (string) $kcal ) {
		$nutrition[] = sprintf( '%s kcal', number_format_i18n( (float) $kcal ) );
	}
	if ( '' !== (string) $sucres ) {
		$nutrition[] = sprintf( '%s g de sucres', number_format_i18n( (float) $sucres ) );
	}
	?>
	<dl class="gc-infos">
		<?php if ( $composition ) : ?>
			<dt><?php esc_html_e( 'Composition', 'gongcha-child' ); ?></dt>
			<dd><?php echo esc_html( $composition ); ?></dd>
		<?php endif; ?>

		<dt><?php esc_html_e( 'Allergènes', 'gongcha-child' ); ?></dt>
		<dd><?php echo $allergenes ? esc_html( mb_strtolower( implode( ', ', $allergenes ) ) ) : esc_html__( 'aucun allergène majeur', 'gongcha-child' ); ?></dd>

		<?php if ( $nutrition ) : ?>
			<dt><?php esc_html_e( 'Valeurs nutritives', 'gongcha-child' ); ?></dt>
			<dd>
				<?php
				echo esc_html( implode( ' · ', $nutrition ) );
				if ( $contenance ) {
					/* translators: %s: contenance en ml */
					echo ' ' . esc_html( sprintf( __( 'pour %s ml', 'gongcha-child' ), number_format_i18n( (float) $contenance ) ) );
				}
				?>
			</dd>
		<?php endif; ?>
	</dl>
	<?php if ( $remarque || $nutrition ) : ?>
		<p class="gc-infos-note">
			<?php
			echo esc_html( trim( $remarque . ' ' . ( $nutrition ? __( 'Valeurs indicatives, taille Regular, douceur standard.', 'gongcha-child' ) : '' ) ) );
			?>
		</p>
	<?php endif; ?>
	<?php
}, 7 );
