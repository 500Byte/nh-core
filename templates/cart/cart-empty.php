<?php
/**
 * Plantilla de Carrito Vacío
 *
 * @package NH_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * @hooked wc_empty_cart_message - 10
 */
do_action( 'woocommerce_cart_is_empty' );

if ( wc_get_page_id( 'shop' ) > 0 ) : ?>
	<div class="nh-cart-empty-container">
		<div class="nh-cart-empty-icon">
			<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
		</div>
		<h2 class="nh-cart-empty-title"><?php esc_html_e( 'Tu carrito está vacío', 'nh-core' ); ?></h2>
		<p class="nh-cart-empty-text"><?php esc_html_e( 'Explora nuestra colección y descubre piezas exclusivas creadas para ti.', 'nh-core' ); ?></p>
		<p class="return-to-shop">
			<a class="button wc-backward nh-cart-empty-btn" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
				<?php esc_html_e( 'Explorar la Tienda', 'nh-core' ); ?>
			</a>
		</p>
	</div>
<?php endif; ?>
