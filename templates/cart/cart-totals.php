<?php
/**
 * Plantilla de Totales del Carrito sin Tablas
 *
 * @package NH_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cart = WC()->cart;
?>
<div class="nh-cart-summary-card cart_totals <?php echo ( $cart->needs_shipping() && $cart->show_shipping() ) ? 'calculated_shipping' : ''; ?>">

	<?php do_action( 'woocommerce_before_cart_totals' ); ?>

	<h3 class="nh-cart-summary-title"><?php esc_html_e( 'Resumen de Compra', 'woocommerce' ); ?></h3>

	<div class="nh-cart-summary-rows">

		<!-- Subtotal -->
		<div class="nh-cart-summary-row cart-subtotal">
			<span class="nh-row-label"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></span>
			<span class="nh-row-value"><?php wc_cart_totals_subtotal_html(); ?></span>
		</div>

		<!-- Cupones Aplicados -->
		<?php foreach ( $cart->get_coupons() as $code => $coupon ) : ?>
			<div class="nh-cart-summary-row nh-cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<span class="nh-row-label"><?php wc_cart_totals_coupon_label( $coupon ); ?></span>
				<span class="nh-row-value"><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
			</div>
		<?php endforeach; ?>

		<!-- Envío -->
		<?php if ( $cart->needs_shipping() && $cart->show_shipping() ) : ?>
			<?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>
			<div class="nh-cart-shipping-wrapper">
				<?php wc_cart_totals_shipping_html(); ?>
			</div>
			<?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>
		<?php elseif ( $cart->needs_shipping() && 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) : ?>
			<div class="nh-cart-summary-row shipping-calculator-row">
				<span class="nh-row-label"><?php esc_html_e( 'Envío', 'woocommerce' ); ?></span>
				<span class="nh-row-value"><?php woocommerce_shipping_calculator(); ?></span>
			</div>
		<?php endif; ?>

		<!-- Tarifas / Fees -->
		<?php foreach ( $cart->get_fees() as $fee ) : ?>
			<div class="nh-cart-summary-row fee">
				<span class="nh-row-label"><?php echo esc_html( $fee->name ); ?></span>
				<span class="nh-row-value"><?php wc_cart_totals_fee_html( $fee ); ?></span>
			</div>
		<?php endforeach; ?>

		<!-- Impuestos -->
		<?php
		if ( wc_tax_enabled() && ! $cart->display_prices_including_tax() ) {
			$taxable_address = $cart->get_taxable_address();
			$estimated_text  = '';

			if ( $cart->is_customer_outside_base() && ! $cart->has_calculated_shipping() ) {
				/* translators: %s location. */
				$estimated_text = sprintf( ' <small>' . esc_html__( '(estimado para %s)', 'woocommerce' ) . '</small>', WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] );
			}

			if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
				foreach ( $cart->get_tax_totals() as $code => $tax ) {
					?>
					<div class="nh-cart-summary-row tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<span class="nh-row-label"><?php echo esc_html( $tax->label ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="nh-row-value"><?php echo wp_kses_post( $tax->formatted_amount ); ?></span>
					</div>
					<?php
				}
			} else {
				?>
				<div class="nh-cart-summary-row tax-total">
					<span class="nh-row-label"><?php echo esc_html( WC()->countries->tax_or_vat() ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="nh-row-value"><?php wc_cart_totals_taxes_total_html(); ?></span>
				</div>
				<?php
			}
		}
		?>

		<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

		<!-- Total Final -->
		<div class="nh-cart-summary-row order-total">
			<span class="nh-row-label"><?php esc_html_e( 'Total', 'woocommerce' ); ?></span>
			<span class="nh-row-value"><?php wc_cart_totals_order_total_html(); ?></span>
		</div>

		<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>

	</div>

	<!-- Botón Finalizar Compra -->
	<div class="wc-proceed-to-checkout nh-cart-checkout-proceed">
		<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
	</div>

	<!-- Sellos de Garantía y Confianza -->
	<?php
	$trust_pills = apply_filters( 'nh_cart_payment_pills', [ 'Visa', 'Mastercard', 'PSE', 'Wompi', 'Addi', 'Efectivo' ] );
	if ( ! empty( $trust_pills ) ) :
	?>
		<div class="nh-cart-ssl-box">
			<div class="nh-trust-badge-item">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
				<span><?php esc_html_e( 'Pago 100% Seguro con Cifrado SSL', 'nh-core' ); ?></span>
			</div>
			<div class="nh-trust-payment-icons">
				<?php foreach ( $trust_pills as $pill_name ) : ?>
					<span class="nh-trust-pill"><?php echo esc_html( trim( $pill_name ) ); ?></span>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_after_cart_totals' ); ?>

</div>
