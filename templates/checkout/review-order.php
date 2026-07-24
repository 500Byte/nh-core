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
				<svg class="nh-cart-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
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
									<?php if ( ! empty( $cart_item['variation'] ) ) : ?>
										<div class="nh-cart-product-variation-pills">
											<?php foreach ( $cart_item['variation'] as $attr_key => $attr_value ) :
												if ( '' === $attr_value ) continue;
												$taxonomy = str_replace( 'attribute_', '', $attr_key );
												$label = wc_attribute_label( $taxonomy, $_product );
												$term = get_term_by( 'slug', $attr_value, $taxonomy );
												$display_val = $term ? $term->name : ucfirst( $attr_value );
											?>
												<span class="nh-cart-variation-pill">
													<span class="nh-variation-label"><?php echo esc_html( $label ); ?>:</span>
													<span class="nh-variation-val"><?php echo esc_html( $display_val ); ?></span>
												</span>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
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
				<div class="nh-coupon-input-group">
					<input type="text" id="nh_summary_coupon_code" class="input-text" placeholder="Código de descuento" />
					<button type="button" id="nh_summary_apply_coupon_btn" class="button"><?php esc_html_e( 'Aplicar', 'woocommerce' ); ?></button>
				</div>
			</div>
		<?php endif; ?>

		<!-- Desglose de Totales y Envío -->
		<div class="nh-checkout-order-summary-rows">
			<!-- Subtotal -->
			<div class="nh-checkout-summary-row cart-subtotal">
				<span class="nh-summary-label"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></span>
				<span class="nh-summary-value"><?php wc_cart_totals_subtotal_html(); ?></span>
			</div>

			<!-- Cupones aplicados -->
			<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
				<div class="nh-checkout-summary-row coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
					<span class="nh-summary-label"><?php wc_cart_totals_coupon_label( $coupon ); ?></span>
					<span class="nh-summary-value"><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
				</div>
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
				<div class="nh-checkout-summary-row fee">
					<span class="nh-summary-label"><?php echo esc_html( $fee->name ); ?></span>
					<span class="nh-summary-value"><?php wc_cart_totals_fee_html( $fee ); ?></span>
				</div>
			<?php endforeach; ?>

			<!-- Total final -->
			<div class="nh-checkout-summary-row order-total">
				<span class="nh-summary-label"><?php esc_html_e( 'Total', 'woocommerce' ); ?></span>
				<span class="nh-summary-value"><?php wc_cart_totals_order_total_html(); ?></span>
			</div>
		</div>
	</div>

	<!-- TARJETA 2: MÉTODOS DE PAGO Y CONFIRMACIÓN -->
	<div class="nh-checkout-payment-wrapper">
		<h3 class="nh-checkout-card-title nh-payment-title"><?php esc_html_e( 'Método de pago', 'woocommerce' ); ?></h3>
		<?php woocommerce_checkout_payment(); ?>
	</div>

</div>

