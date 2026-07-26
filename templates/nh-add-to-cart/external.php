<?php
/**
 * NH Add to Cart — External/Affiliate Product Template
 */
defined( 'ABSPATH' ) || exit;

// Productos externos no tienen variaciones ni quantity input.
// Los bloques que no aplican son ignorados silenciosamente por el helper,
// pero los excluimos del array por defecto para mayor claridad.
$blocks = apply_filters( 'nh_atc_blocks_order', [
	'price',
	'add_to_cart',
	'promo',
	'stock',
], $product, $settings );
?>
<div class="nh-add-to-cart__layout">
	<?php foreach ( $blocks as $block_name ) : ?>
		<?php nh_render_atc_block( $block_name, $product, $settings ); ?>
	<?php endforeach; ?>
</div>
