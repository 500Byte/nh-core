<?php
/**
 * Plantilla de Sección de Pago (CRO Payment Section)
 *
 * @package NH_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_ajax() ) {
	do_action( 'woocommerce_review_order_before_payment' );
}
?>
<div id="payment" class="woocommerce-checkout-payment nh-checkout-payment-card">
	<?php if ( WC()->cart->needs_payment() ) : ?>
		<ul class="wc_payment_methods payment_methods methods">
			<?php
			if ( ! empty( $available_gateways ) ) {
				foreach ( $available_gateways as $gateway ) {
					wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
				}
			} else {
				echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . esc_html( apply_filters( 'woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? __( 'Lo sentimos, no parece haber ningún método de pago disponible para tu estado. Ponte en contacto con nosotros si necesitas ayuda o deseas realizar otro arreglo.', 'woocommerce' ) : __( 'Por favor, rellena los detalles de facturación arriba para ver los métodos de pago disponibles.', 'woocommerce' ) ) ) . '</li>';
			}
			?>
		</ul>
	<?php endif; ?>

	<div class="form-row place-order">
		<noscript>
			<?php
			/* translators: $1. OPEN LINK TAG, $2. CLOSE LINK TAG */
			printf( esc_html__( 'Dado que tu navegador no soporta JavaScript, o lo tiene desactivado, por favor asegúrate de hacer clic en el botón %1$sActualizar totales%2$s antes de realizar tu pedido.', 'woocommerce' ), '<em>', '</em>' );
			?>
			<br/><button type="submit" class="button alt" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'Actualizar totales', 'woocommerce' ); ?>"><?php esc_html_e( 'Actualizar totales', 'woocommerce' ); ?></button>
		</noscript>

		<?php wc_get_template( 'checkout/terms.php' ); ?>

		<?php do_action( 'woocommerce_review_order_before_submit' ); ?>

		<!-- Trust Badges de Seguridad SSL y Métodos de Pago -->
		<div class="nh-checkout-trust-badges">
			<div class="nh-trust-badge-item">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
				<span>Pago 100% Seguro con Cifrado SSL</span>
			</div>
			<div class="nh-trust-payment-icons">
				<span class="nh-trust-pill">Visa</span>
				<span class="nh-trust-pill">Mastercard</span>
				<span class="nh-trust-pill">PSE</span>
				<span class="nh-trust-pill">Wompi</span>
				<span class="nh-trust-pill">Addi</span>
				<span class="nh-trust-pill">Efectivo</span>
			</div>
		</div>

		<?php
		$order_button_text = apply_filters( 'woocommerce_order_button_text', __( 'Realizar el pedido', 'woocommerce' ) );
		echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>' );
		?>

		<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

		<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
	</div>
</div>
<?php
if ( ! is_ajax() ) {
	do_action( 'woocommerce_review_order_after_payment' );
}
