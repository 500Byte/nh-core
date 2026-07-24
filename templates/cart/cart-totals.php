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
		<?php nh_render_summary_row( __( 'Subtotal', 'woocommerce' ), nh_capture_wc_output( 'wc_cart_totals_subtotal_html' ), 'cart-subtotal' ); ?>

		<!-- Cupones Aplicados -->
		<?php foreach ( $cart->get_coupons() as $code => $coupon ) : ?>
			<?php 
			$coupon_label = wc_cart_totals_coupon_label( $coupon, false );
			$coupon_html  = nh_capture_wc_output( function() use ( $coupon ) {
				wc_cart_totals_coupon_html( $coupon );
			} );
			nh_render_summary_row( $coupon_label, $coupon_html, 'nh-cart-discount coupon-' . esc_attr( sanitize_title( $code ) ) ); 
			?>
		<?php endforeach; ?>

		<!-- Envío -->
		<?php if ( $cart->needs_shipping() && $cart->show_shipping() ) : ?>
			<?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>
			<div class="nh-cart-shipping-wrapper">
				<?php wc_cart_totals_shipping_html(); ?>
			</div>
			<?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>
		<?php elseif ( $cart->needs_shipping() && 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) : ?>
			<?php nh_render_summary_row( __( 'Envío', 'woocommerce' ), nh_capture_wc_output( 'woocommerce_shipping_calculator' ), 'shipping-calculator-row' ); ?>
		<?php endif; ?>

		<!-- Tarifas / Fees -->
		<?php foreach ( $cart->get_fees() as $fee ) : ?>
			<?php 
			$fee_html = nh_capture_wc_output( function() use ( $fee ) {
				wc_cart_totals_fee_html( $fee );
			} );
			nh_render_summary_row( esc_html( $fee->name ), $fee_html, 'fee' ); 
			?>
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
					nh_render_summary_row( esc_html( $tax->label ) . $estimated_text, wp_kses_post( $tax->formatted_amount ), 'tax-rate tax-rate-' . esc_attr( sanitize_title( $code ) ) );
				}
			} else {
				nh_render_summary_row( esc_html( WC()->countries->tax_or_vat() ) . $estimated_text, nh_capture_wc_output( 'wc_cart_totals_taxes_total_html' ), 'tax-total' );
			}
		}
		?>

		<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

		<!-- Total Final -->
		<?php nh_render_summary_row( __( 'Total', 'woocommerce' ), nh_capture_wc_output( 'wc_cart_totals_order_total_html' ), 'order-total' ); ?>

		<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>

	</div>

	<!-- Botón Finalizar Compra -->
	<div class="wc-proceed-to-checkout nh-cart-checkout-proceed">
		<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
	</div>

	<!-- Sellos de Garantía y Confianza -->
	<?php
	$trust_pills = apply_filters( 'nh_cart_payment_pills', [ 'Visa', 'Mastercard', 'PSE', 'Wompi', 'Addi', 'Efectivo' ] );
	nh_render_trust_box( __( 'Pago 100% Seguro con Cifrado SSL', 'nh-core' ), $trust_pills, 'nh-trust-box' );
	?>

	<?php do_action( 'woocommerce_after_cart_totals' ); ?>

</div>
