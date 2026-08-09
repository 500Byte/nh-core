<?php
/**
 * Plantilla de Resumen de Pedido sin Tablas (Table-less CRO Review Order)
 *
 * @package NH_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="nh-checkout-review-order-wrapper woocommerce-checkout-review-order-table">

	<!-- TARJETA 1: RESUMEN DEL PEDIDO -->
	<div class="nh-checkout-summary-card">
		<!-- Header Toggle para Celulares -->
		<div class="nh-checkout-mobile-summary-toggle">
			<div class="nh-mobile-toggle-left">
				<i class="ph-light ph-shopping-bag nh-cart-icon" style="font-size: 16px;" aria-hidden="true"></i>
				<span class="nh-toggle-label">Mostrar resumen</span>
				<span class="nh-toggle-arrow">▾</span>
			</div>
			<div class="nh-mobile-toggle-right">
				<span class="nh-mobile-summary-total"><?php wc_cart_totals_order_total_html(); ?></span>
			</div>
		</div>

		<h3 class="nh-checkout-card-title nh-desktop-only-title"><?php esc_html_e( 'Resumen del pedido', 'woocommerce' ); ?></h3>

		<div class="nh-checkout-collapsible-content">
			<!-- Lista de Productos en Carrito -->
			<div class="nh-checkout-order-items">
				<?php
				do_action( 'woocommerce_review_order_before_cart_contents' );

				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
					$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

					if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
						$base_title = $_product->get_title();
						$thumbnail  = $_product->get_image( [ 56, 56 ], [ 'class' => 'nh-checkout-item-thumb-img' ] );
						$quantity   = isset( $cart_item['quantity'] ) ? $cart_item['quantity'] : 1;
						?>
						<div class="nh-checkout-order-item">
							<div class="nh-checkout-item-flex">
								<div class="nh-checkout-thumb-wrap">
									<?php echo wp_kses_post( $thumbnail ); ?>
									<span class="nh-checkout-qty-badge"><?php echo (int) $quantity; ?></span>
								</div>
								<div class="nh-checkout-item-info">
									<span class="nh-checkout-item-title"><?php echo esc_html( $base_title ); ?></span>
								<?php nh_render_variation_pills( $cart_item, $_product ); ?>
								</div>
							</div>
							<div class="nh-checkout-item-subtotal">
								<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
							</div>
						</div>
						<?php
					}
				}

				do_action( 'woocommerce_review_order_after_cart_contents' );
				?>
			</div>
		</div>

		<!-- Cupón de Descuento (Fuera del Acordeón) -->
		<?php if ( wc_coupons_enabled() ) : ?>
			<div class="nh-checkout-coupon-card">
				<?php nh_render_coupon_box( 'nh_summary_coupon_code', 'nh_summary_apply_coupon_btn', 'nh-coupon-box' ); ?>
			</div>
		<?php endif; ?>

		<!-- Barra de Progreso Envío Gratis -->
		<?php
		$free_shipping_threshold = (float) apply_filters( "nh_checkout_free_shipping_threshold", nh_get_free_shipping_threshold() );
		$subtotal = (float) WC()->cart->get_subtotal();
		if ( $free_shipping_threshold > 0 && $subtotal > 0 ) :
			$percent = min( 100, round( ( $subtotal / $free_shipping_threshold ) * 100 ) );
			$remaining = max( 0, $free_shipping_threshold - $subtotal );
		?>
			<div class="nh-shipping-bar">
				<div class="nh-shipping-bar__text">
					<?php if ( $remaining > 0 ) : ?>
						<?php printf( esc_html__( '¡Añade %s más para obtener Envío Gratis!', 'nh-core' ), wc_price( $remaining ) ); ?>
					<?php else : ?>
						<strong><?php esc_html_e( '¡Felicidades! Tienes Envío Gratis en este pedido 🎉', 'nh-core' ); ?></strong>
					<?php endif; ?>
				</div>
				<div class="nh-shipping-bar__track">
					<div class="nh-shipping-bar__fill" style="width: <?php echo (int) $percent; ?>%;"></div>
				</div>
			</div>
		<?php endif; ?>

		<!-- Desglose de Totales y Envío -->
		<div class="nh-checkout-order-summary-rows">
			<!-- Subtotal -->
			<?php nh_render_summary_row( __( 'Subtotal', 'woocommerce' ), nh_capture_wc_output( 'wc_cart_totals_subtotal_html' ), 'cart-subtotal' ); ?>

			<!-- Cupones aplicados -->
			<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
				<?php 
				$coupon_label = wc_cart_totals_coupon_label( $coupon, false );
				$coupon_html  = nh_capture_wc_output( function() use ( $coupon ) {
					wc_cart_totals_coupon_html( $coupon );
				} );
				nh_render_summary_row( $coupon_label, $coupon_html, 'coupon-' . esc_attr( sanitize_title( $code ) ) ); 
				?>
			<?php endforeach; ?>

			<!-- Opciones de Envío -->
			<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
				<div class="nh-checkout-shipping-block">
					<div class="nh-shipping-methods-container">
						<?php wc_cart_totals_shipping_html(); ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Cargos adicionales -->
			<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
				<?php 
				$fee_html = nh_capture_wc_output( function() use ( $fee ) {
					wc_cart_totals_fee_html( $fee );
				} );
				nh_render_summary_row( esc_html( $fee->name ), $fee_html, 'fee' ); 
				?>
			<?php endforeach; ?>

			<!-- Total final -->
			<?php nh_render_summary_row( __( 'Total', 'woocommerce' ), nh_capture_wc_output( 'wc_cart_totals_order_total_html' ), 'order-total' ); ?>
		</div>
	</div>

	<!-- TARJETA 2: MÉTODOS DE PAGO Y CONFIRMACIÓN -->
	<div class="nh-checkout-payment-wrapper">
		<h3 class="nh-checkout-card-title nh-payment-title"><?php esc_html_e( 'Método de pago', 'woocommerce' ); ?></h3>
		<?php woocommerce_checkout_payment(); ?>
	</div>

</div>

