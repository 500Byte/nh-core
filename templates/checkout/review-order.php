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

	<!-- Lista de Productos en Carrito (Divs Flex) -->
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

	<!-- Cupón de Descuento (CRO Summary Embedded Form) -->
	<?php if ( wc_coupons_enabled() ) : ?>
		<div class="nh-checkout-coupon-card">
			<div class="nh-coupon-input-group">
				<input type="text" id="nh_summary_coupon_code" class="input-text" placeholder="Código de descuento" />
				<button type="button" id="nh_summary_apply_coupon_btn" class="button"><?php esc_html_e( 'Aplicar', 'woocommerce' ); ?></button>
			</div>
		</div>
	<?php endif; ?>

	<!-- Totales y Opciones de Envío (Divs Flex) -->
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
				<div class="nh-shipping-section-title"><?php esc_html_e( 'Envío', 'woocommerce' ); ?></div>
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

	<!-- Sección de Pago y Términos -->
	<?php do_action( 'woocommerce_review_order_before_payment' ); ?>
	<div id="payment" class="woocommerce-checkout-payment">
		<?php if ( WC()->cart->needs_payment() ) : ?>
			<ul class="wc_payment_methods payment_methods methods">
				<?php
				$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
				if ( ! empty( $available_gateways ) ) {
					foreach ( $available_gateways as $gateway ) {
						wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
					}
				} else {
					echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . esc_html__( 'No hay métodos de pago disponibles.', 'woocommerce' ) . '</li>';
				}
				?>
			</ul>
		<?php endif; ?>
		<div class="form-row place-order">
			<noscript>
				<?php esc_html_e( 'Tu navegador no soporta JavaScript.', 'woocommerce' ); ?>
				<br/><button type="submit" class="button alt" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'Actualizar totales', 'woocommerce' ); ?>"><?php esc_html_e( 'Actualizar totales', 'woocommerce' ); ?></button>
			</noscript>

			<?php wc_get_template( 'checkout/terms.php' ); ?>

			<?php do_action( 'woocommerce_review_order_before_submit' ); ?>

			<?php
			$order_button_text = apply_filters( 'woocommerce_order_button_text', __( 'Realizar el pedido', 'woocommerce' ) );
			echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>' );
			?>

			<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

			<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
		</div>
	</div>
	<?php do_action( 'woocommerce_review_order_after_payment' ); ?>

</div>
