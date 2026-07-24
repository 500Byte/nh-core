<?php
/**
 * NH Core — Template Partes Compartidas (Cart + Checkout)
 *
 * Funciones de renderizado reutilizables para evitar duplicación
 * de markup entre las plantillas de carrito y checkout.
 *
 * @package NH_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renderiza las pills de variación de un producto.
 *
 * @param array  $cart_item      Elemento del carrito de WooCommerce.
 * @param object $_product       Objeto WooCommerce del producto.
 * @param string $wrapper_class  Clase CSS del wrapper (default: 'nh-pill-group').
 * @param string $pill_class     Clase CSS de cada pill (default: 'nh-pill').
 */
function nh_render_variation_pills( $cart_item, $_product, $wrapper_class = 'nh-pill-group', $pill_class = 'nh-pill' ) {
	if ( empty( $cart_item['variation'] ) ) {
		echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return;
	}
	?>
	<div class="<?php echo esc_attr( $wrapper_class ); ?>">
		<?php foreach ( $cart_item['variation'] as $attr_key => $attr_value ) :
			if ( '' === $attr_value ) continue;
			$taxonomy    = str_replace( 'attribute_', '', $attr_key );
			$label       = wc_attribute_label( $taxonomy, $_product );
			$term        = get_term_by( 'slug', $attr_value, $taxonomy );
			$display_val = $term ? $term->name : ucfirst( $attr_value );
		?>
			<span class="<?php echo esc_attr( $pill_class ); ?>">
				<span class="nh-pill-label"><?php echo esc_html( $label ); ?>:</span>
				<span class="nh-pill-value"><?php echo esc_html( $display_val ); ?></span>
			</span>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Renderiza la barra de progreso de envío gratis.
 *
 * @param float  $threshold Umbral para envío gratis.
 * @param float  $subtotal  Subtotal actual del carrito.
 * @param string $wrapper_class Clase CSS del wrapper (default: 'nh-shipping-bar').
 */
function nh_render_free_shipping_bar( $threshold, $subtotal, $wrapper_class = 'nh-shipping-bar' ) {
	if ( $threshold <= 0 || $subtotal <= 0 ) {
		return;
	}

	$percent   = min( 100, round( ( $subtotal / $threshold ) * 100 ) );
	$remaining = max( 0, $threshold - $subtotal );
	?>
	<div class="<?php echo esc_attr( $wrapper_class ); ?>">
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
	<?php
}

/**
 * Renderiza los sellos de confianza y badges de pasarela de pago.
 *
 * @param string $ssl_text  Texto de seguridad SSL.
 * @param array  $pills     Lista de nombres de pasarelas.
 * @param string $box_class Clase CSS del contenedor (default: 'nh-trust-box').
 */
function nh_render_trust_box( $ssl_text, $pills, $box_class = 'nh-trust-box' ) {
	if ( empty( $pills ) ) {
		return;
	}
	?>
	<div class="<?php echo esc_attr( $box_class ); ?>">
		<div class="nh-trust-box__badge">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
			<span><?php echo esc_html( $ssl_text ); ?></span>
		</div>
		<div class="nh-trust-box__pills">
			<?php foreach ( $pills as $pill_name ) : ?>
				<span class="nh-trust-box__pill"><?php echo esc_html( trim( $pill_name ) ); ?></span>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Renderiza una fila de resumen (label + value), usada en cart-totals y review-order.
 *
 * @param string $label     Texto de la etiqueta.
 * @param string $value     HTML del valor (ya escapado o con wc_price).
 * @param string $row_class Clases CSS adicionales para la fila.
 */
function nh_render_summary_row( $label, $value, $row_class = '' ) {
	?>
	<div class="nh-summary-row <?php echo esc_attr( $row_class ); ?>">
		<span class="nh-summary-row__label"><?php echo wp_kses_post( $label ?? '' ); ?></span>
		<span class="nh-summary-row__value"><?php echo wp_kses_post( $value ?? '' ); ?></span>
	</div>
	<?php
}

/**
 * Captura el output de una función WooCommerce que hace echo directo.
 *
 * WooCommerce como wc_cart_totals_subtotal_html() hacen echo y no retornan
 * string. Esta función captura ese output para pasarlo a nh_render_summary_row().
 *
 * @param callable $callback Función a ejecutar dentro del buffer.
 * @return string HTML capturado.
 */
function nh_capture_wc_output( $callback ) {
	ob_start();
	$callback();
	return ob_get_clean();
}

/**
 * Renderiza el input de cupón con botón.
 *
 * @param string $input_id   ID del input.
 * @param string $btn_id     ID del botón.
 * @param string $wrapper_class Clase CSS del wrapper (default: 'nh-coupon-box').
 */
function nh_render_coupon_box( $input_id = 'nh_coupon_code', $btn_id = 'nh_apply_coupon_btn', $wrapper_class = 'nh-coupon-box' ) {
	?>
	<div class="<?php echo esc_attr( $wrapper_class ); ?>">
		<input type="text" id="<?php echo esc_attr( $input_id ); ?>" class="input-text" placeholder="<?php esc_attr_e( 'Código de descuento', 'nh-core' ); ?>" />
		<button type="button" id="<?php echo esc_attr( $btn_id ); ?>" class="button"><?php esc_html_e( 'Aplicar', 'woocommerce' ); ?></button>
	</div>
	<?php
}
