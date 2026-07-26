<?php
/**
 * NH Add to Cart — Variable Product Template
 * Compatible con woo-variation-swatches-pro 2.3.0
 *
 * CONTRATOS DE COMPATIBILIDAD (no modificar sin revisar informe WVS):
 *  [C1] form.variations_form            — anchor de todo el JS del plugin
 *  [C2] form[data-product_id]           — requerido por VariationForm()
 *  [C3] form[data-product_variations]   — JSON de variaciones o "false" (AJAX mode)
 *  [C4] >div (primer hijo directo)      — JS lee data-threshold_max y data-total con $form.find('>div')
 *  [C5] .variations select              — fuente de verdad; swatch hace select.val().trigger('change')
 *  [C6] select y ul.variable-items-wrapper deben compartir el mismo padre directo
 *  [C7] .single_variation_wrap          — requerido por WC para mostrar precio/imagen de variación
 *  [C8] .reset_variations               — link de reset esperado por el JS
 */

defined( 'ABSPATH' ) || exit;

$get_variations       = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );
$available_variations = $get_variations ? $product->get_available_variations() : false;
$threshold_max        = absint( apply_filters( 'woo_variation_swatches_global_ajax_variation_threshold_max', 100, $product ) );

$blocks = apply_filters( 'nh_atc_blocks_order', [
	'variations',
	'price',
	'quantity',
	'add_to_cart',
	'buy_now',
	'promo',
	'stock',
], $product, $settings );
?>

<form class="variations_form cart"                                            <?php /* [C1] */ ?>
      action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>"
      method="post"
      enctype="multipart/form-data"
      data-product_id="<?php echo absint( $product->get_id() ); ?>"           <?php /* [C2] */ ?>
      data-product_variations="<?php echo $available_variations ? wc_esc_json( wp_json_encode( $available_variations ) ) : 'false'; ?>"> <?php /* [C3] */ ?>

	<?php do_action( 'woocommerce_before_variations_form' ); ?>

	<div data-product_id="<?php echo absint( $product->get_id() ); ?>"        <?php /* [C4] primer hijo directo */ ?>
	     data-threshold_min="<?php echo absint( apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product ) ); ?>"
	     data-threshold_max="<?php echo $threshold_max; ?>"
	     data-total="<?php echo count( $product->get_children() ); ?>">

		<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
			<p class="stock out-of-stock"><?php esc_html_e( 'Este producto está agotado y no disponible.', 'woocommerce' ); ?></p>
		<?php else : ?>

			<div class="nh-add-to-cart__layout">
				<?php foreach ( $blocks as $block_name ) : ?>
					<?php nh_render_atc_block( $block_name, $product, $settings ); ?>
				<?php endforeach; ?>
			</div>

		<?php endif; ?>

	</div>

	<?php do_action( 'woocommerce_after_variations_form' ); ?>

	<a href="#" class="reset_variations" style="display:none">  <?php /* [C8] */ ?>
		<?php esc_html_e( 'Limpiar selección', 'woocommerce' ); ?>
	</a>
</form>
