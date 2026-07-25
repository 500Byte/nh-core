<?php
/**
 * Plantilla del Carrito de Compras sin Tablas (Table-less CRO Cart)
 *
 * @package NH_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_cart' ); ?>

<div class="nh-cart-layout">

	<!-- COLUMNA IZQUIERDA: PRODUCTOS Y CUPÓN -->
	<div class="nh-cart-products">
		<?php do_action( 'woocommerce_before_cart_table' ); ?>

		<div class="nh-cart-table-header">
			<span><?php esc_html_e( 'Producto', 'nh-core' ); ?></span>
			<span><?php esc_html_e( 'Precio', 'nh-core' ); ?></span>
			<span><?php esc_html_e( 'Cantidad', 'nh-core' ); ?></span>
			<span><?php esc_html_e( 'Subtotal', 'nh-core' ); ?></span>
			<span></span>
		</div>

		<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
			<?php do_action( 'woocommerce_before_cart_contents' ); ?>

			<div class="nh-cart-items-list">
				<?php
				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
					$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
					$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

					if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
						$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
						$quantity          = $cart_item['quantity'];
						$is_sold_individually = $_product->is_sold_individually();
						?>
						<div class="nh-cart-item woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>" data-key="<?php echo esc_attr( $cart_item_key ); ?>">
							
							<!-- Info del Producto e Imagen -->
							<div class="nh-cart-product-info">
								<?php
								$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'thumbnail', [ 'class' => 'nh-cart-product-img' ] ), $cart_item, $cart_item_key );
								if ( ! $product_permalink ) {
									echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								} else {
									printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
								?>
								<div class="nh-cart-product-details">
									<span class="nh-cart-product-name">
										<?php
										$product_title = $_product->get_title();
										if ( ! $product_permalink ) {
											echo esc_html( $product_title );
										} else {
											printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), esc_html( $product_title ) );
										}
										?>
									</span>

									<?php do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key ); ?>

								<!-- Atributos y Variaciones en Pills -->
								<?php nh_render_variation_pills( $cart_item, $_product, 'nh-pill-group', 'nh-pill' ); ?>

									<!-- Notificación Backorder -->
									<?php
									if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
										echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Disponible en reserva', 'woocommerce' ) . '</p>', $product_id ) );
									}
									?>
								</div>
							</div>

							<!-- Precio Unitario -->
							<div class="nh-cart-product-price" data-title="<?php esc_attr_e( 'Precio', 'woocommerce' ); ?>">
								<?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>

							<!-- Selector de Cantidad -->
							<div class="nh-qty" data-title="<?php esc_attr_e( 'Cantidad', 'woocommerce' ); ?>">
								<?php
								if ( $is_sold_individually ) {
									$min_quantity = 1;
									$max_quantity = 1;
								} else {
									$min_quantity = 0;
									$max_quantity = $_product->get_max_purchase_quantity();
								}

								$product_quantity = woocommerce_quantity_input(
									array(
										'input_name'   => "cart[{$cart_item_key}][qty]",
										'input_value'  => $cart_item['quantity'],
										'max_value'    => $max_quantity,
										'min_value'    => $min_quantity,
										'product_name' => $_product->get_title(),
									),
									$_product,
									false
								);

								echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								?>
							</div>

							<!-- Subtotal de Item -->
							<div class="nh-cart-product-subtotal" data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce' ); ?>">
								<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>

							<!-- Botón Eliminar -->
							<div class="nh-cart-product-remove">
								<?php
								echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									'woocommerce_cart_item_remove_link',
									sprintf(
										'<a href="%s" class="remove nh-cart-remove" aria-label="%s" data-product_id="%s" data-product_sku="%s" data-key="%s">&times;</a>',
										esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
										/* translators: %s is the product name */
										esc_attr( sprintf( __( 'Eliminar %s del carrito', 'woocommerce' ), $_product->get_name() ) ),
										esc_attr( $product_id ),
										esc_attr( $_product->get_sku() ),
										esc_attr( $cart_item_key )
									),
									$cart_item_key
								);
								?>
							</div>

						</div>
						<?php
					}
				}
				?>
			</div>

			<?php do_action( 'woocommerce_cart_contents' ); ?>

			<!-- Sección de Cupón de Descuento & Botón Actualizar -->
			<div class="nh-cart-actions-row">
				<?php if ( wc_coupons_enabled() ) { ?>
					<div class="nh-cart-coupon coupon">
						<input type="text" name="coupon_code" class="nh-cart-coupon-input input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Código de cupón', 'woocommerce' ); ?>" />
						<button type="submit" class="nh-btn nh-btn-secondary nh-cart-coupon-btn" name="apply_coupon" value="<?php esc_attr_e( 'Aplicar cupón', 'woocommerce' ); ?>"><?php esc_html_e( 'Aplicar', 'woocommerce' ); ?></button>
						<?php do_action( 'woocommerce_cart_coupon' ); ?>
					</div>
				<?php } ?>

				<button type="submit" class="nh-btn nh-btn-secondary nh-cart-update-btn" name="update_cart" value="<?php esc_attr_e( 'Actualizar carrito', 'woocommerce' ); ?>"><?php esc_html_e( 'Actualizar carrito', 'woocommerce' ); ?></button>

				<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
			</div>

			<?php do_action( 'woocommerce_after_cart_contents' ); ?>
		</form>

		<?php do_action( 'woocommerce_after_cart_table' ); ?>
	</div>

	<!-- COLUMNA DERECHA: TARJETA RESUMEN DE COMPRA -->
	<div class="nh-cart-summary">
		<?php
		if ( function_exists( 'woocommerce_cart_totals' ) ) {
			woocommerce_cart_totals();
		} else {
			do_action( 'woocommerce_cart_collateral' );
		}
		?>
	</div>

</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
