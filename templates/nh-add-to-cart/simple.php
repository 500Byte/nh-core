<?php
/**
 * NH Add to Cart — Simple Product Template
 * Compatible con WooCommerce 8.0+
 */
defined( 'ABSPATH' ) || exit;

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
<form class="cart" method="post" enctype="multipart/form-data">
	<div class="nh-add-to-cart__layout">
		<?php foreach ( $blocks as $block_name ) : ?>
			<?php nh_render_atc_block( $block_name, $product, $settings ); ?>
		<?php endforeach; ?>
	</div>
</form>
