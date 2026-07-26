<?php
/**
 * NH Add to Cart — Helper Functions
 *
 * Funciones de renderizado para los bloques del widget NH Add to Cart.
 * Cada bloque es autocontenido y falla silenciosamente si no aplica
 * al tipo de producto actual.
 *
 * @package NH_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renderiza un bloque del widget NH Add to Cart.
 *
 * Sistema de hooks por bloque:
 *  - apply_filters( "nh_atc_block_{$block_name}", null, $product, $settings )
 *    → Sobreescribe el bloque completo. Retorna HTML para reemplazar el bloque.
 *  - do_action( "nh_atc_before_block_{$block_name}", $product, $settings )
 *    → Inyecta contenido antes del bloque.
 *  - do_action( "nh_atc_after_block_{$block_name}", $product, $settings )
 *    → Inyecta contenido después del bloque.
 *
 * @param string     $block_name  Identificador del bloque.
 * @param WC_Product $product     Producto actual.
 * @param array      $settings    Settings de Elementor.
 */
function nh_render_atc_block( string $block_name, WC_Product $product, array $settings ): void {

	// Permite que terceros registren bloques propios o sobreescriban los nativos
	$override = apply_filters( "nh_atc_block_{$block_name}", null, $product, $settings );
	if ( null !== $override ) {
		echo $override; // phpcs:ignore
		return;
	}

	do_action( "nh_atc_before_block_{$block_name}", $product, $settings );

	switch ( $block_name ) {

		case 'variations':
			// Solo aplica a productos variables [C5][C6]
			if ( ! $product->is_type( 'variable' ) ) break;
			echo '<div class="nh-add-to-cart__variations">';
			echo '<table class="variations" cellspacing="0" role="presentation"><tbody>';
			foreach ( $product->get_variation_attributes() as $attribute_name => $options ) :
				?>
				<tr>
					<th class="label">
						<label for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>">
							<?php echo wp_kses_post( wc_attribute_label( $attribute_name ) ); ?>
						</label>
						<span class="woo-selected-variation-item-name" data-default=""></span>
					</th>
					<td class="value woo-variation-items-wrapper"> <?php /* [C6] contenedor compartido select+ul */ ?>
						<?php
						wc_dropdown_variation_attribute_options( [
							'options'   => $options,
							'attribute' => $attribute_name,
							'product'   => $product,
						] ); // El filtro del plugin convierte esto en swatches automáticamente
						?>
					</td>
				</tr>
				<?php
			endforeach;
			echo '</tbody></table></div>';
			echo '
			<div class="single_variation_wrap">
				<div class="woocommerce-variation single_variation"></div>
			</div>';
			break;

		case 'price':
			echo '<div class="nh-add-to-cart__price">';
			woocommerce_template_single_price();
			echo '</div>';
			break;

		case 'quantity':
			if ( $product->is_sold_individually() ) break;
			echo '<div class="nh-add-to-cart__quantity">';
			woocommerce_quantity_input( [], $product );
			echo '</div>';
			break;

		case 'add_to_cart':
			if ( $product->is_type( 'external' ) ) {
				printf(
					'<div class="nh-add-to-cart__button"><a href="%s" target="_blank" rel="nofollow" class="single_add_to_cart_button button alt nh-btn nh-btn-primary">%s</a></div>',
					esc_url( $product->get_product_url() ),
					esc_html( $product->single_add_to_cart_text() )
				);
			} else {
				$label = apply_filters( 'woocommerce_product_single_add_to_cart_text', $product->single_add_to_cart_text(), $product );
				printf(
					'<div class="nh-add-to-cart__button"><button type="submit" name="add-to-cart" value="%d" class="single_add_to_cart_button button alt nh-btn nh-btn-primary">%s</button>%s</div>',
					absint( $product->get_id() ),
					esc_html( $label ),
					$product->is_type( 'variable' ) ? '<input type="hidden" name="variation_id" class="variation_id" value="0" />' : ''
				);
			}
			break;

		case 'buy_now':
			if ( 'yes' !== ( $settings['show_buy_now'] ?? '' ) ) break;
			printf(
				'<div class="nh-add-to-cart__buy-now-wrapper"><button type="button" class="nh-btn nh-btn-secondary nh-add-to-cart__buy-now" data-nh-product-id="%d" data-nh-product-name="%s" data-nh-product-price="%s" data-is-variable="%s">%s</button></div>',
				absint( $product->get_id() ),
				esc_attr( $product->get_name() ),
				esc_attr( (string) $product->get_price() ),
				$product->is_type( 'variable' ) ? 'true' : 'false',
				esc_html( $settings['buy_now_text'] ?? __( 'Comprar Ahora', 'nh-core' ) )
			);
			break;

		case 'promo':
			if ( empty( $settings['promo_text'] ) ) break;
			$icon_html = '';
			$promo_icon = $settings['promo_icon'] ?? [];
			if ( ! empty( $promo_icon['value'] ) && class_exists( '\Elementor\Icons_Manager' ) ) {
				ob_start();
				\Elementor\Icons_Manager::render_icon( $promo_icon, [ 'aria-hidden' => 'true' ] );
				$icon_rendered = ob_get_clean();
				$icon_html = '<span class="nh-add-to-cart__promo-icon" aria-hidden="true">' . $icon_rendered . '</span>';
			}
			printf(
				'<div class="nh-add-to-cart__promo">%s<span>%s</span></div>',
				$icon_html,
				esc_html( $settings['promo_text'] )
			);
			break;

		case 'backorder':
			if ( $product->is_type( 'variable' ) || ! $product->is_on_backorder() ) break;
			echo '<div class="nh-add-to-cart__backorder">';
			echo wp_kses_post( $settings['backorder_text'] ?? '' );
			echo '</div>';
			break;

		case 'stock':
			if ( $product->is_type( 'variable' ) ) break;
			$stock_html = wc_get_stock_html( $product );
			if ( ! empty( $stock_html ) ) {
				echo '<div class="nh-add-to-cart__stock">' . $stock_html . '</div>';
			}
			break;
	}

	do_action( "nh_atc_after_block_{$block_name}", $product, $settings );
}
