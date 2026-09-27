<?php
/**
 * Thankyou page - Norma Hana Luxury Editorial Template
 *
 * Inherits 1:1 from canonical WooCommerce templates/checkout/thankyou.php v8.1.0
 * Preserves all standard action hooks for PixelYourSite and payment gateways.
 *
 * @package WooCommerce\Templates
 * @version 8.1.0
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="woocommerce-order nh-thankyou-container">

    <?php if ( $order ) : ?>

        <?php do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>

        <?php if ( $order->has_status( 'failed' ) ) : ?>

            <div class="nh-thankyou-hero">
                <span class="nh-order-badge nh-order-badge--failed"><?php esc_html_e( 'Pago no completado', 'woocommerce' ); ?></span>
                <h1><?php esc_html_e( 'No pudimos procesar tu pago', 'woocommerce' ); ?></h1>
                <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed">
                    <?php esc_html_e( 'La entidad bancaria o pasarela no autorizó la transacción. Por favor, intenta nuevamente con otro medio de pago.', 'woocommerce' ); ?>
                </p>
                <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
                    <a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="nh-whatsapp-btn pay"><?php esc_html_e( 'Reintentar Pago', 'woocommerce' ); ?></a>
                    <?php if ( is_user_logged_in() ) : ?>
                        <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php esc_html_e( 'Mi Cuenta', 'woocommerce' ); ?></a>
                    <?php endif; ?>
                </p>
            </div>

        <?php else : ?>

            <?php
            $is_confirmed = $order->has_status( [ 'processing', 'completed' ] );
            $badge_class  = $is_confirmed ? 'nh-order-badge--confirmed' : 'nh-order-badge--pending';
            $badge_text   = $is_confirmed ? 'Pedido Confirmado' : 'Validación Bancaria en Curso';
            ?>

            <!-- Hero & Workshop Storytelling -->
            <div class="nh-thankyou-hero">
                <span class="nh-order-badge <?php echo esc_attr( $badge_class ); ?>" id="nh-order-badge-state">
                    <?php echo esc_html( $badge_text ); ?>
                </span>
                
                <?php if ( $is_confirmed ) : ?>
                    <h1>Gracias por elegir el diseño consciente</h1>
                    <p>Tu pedido ha ingresado exitosamente a nuestro taller. Nuestras artesanas en Santa Marta están preparando tus piezas con el mayor cuidado y dedicación.</p>
                <?php else : ?>
                    <h1>Hemos recibido la solicitud de tu pedido</h1>
                    <p>Tu entidad bancaria o pasarela (PSE / Wompi) está procesando la transacción. No es necesario realizar un nuevo intento; una vez recibida la confirmación, iniciaremos la preparación en el taller.</p>
                <?php endif; ?>
            </div>

            <!-- Overview Receipt Grid -->
            <ul class="nh-order-overview-grid">
                <li class="nh-overview-card">
                    <span class="nh-overview-card__label"><?php esc_html_e( 'Nº de Pedido', 'woocommerce' ); ?></span>
                    <span class="nh-overview-card__value">#<?php echo $order->get_order_number(); ?></span>
                </li>

                <li class="nh-overview-card">
                    <span class="nh-overview-card__label"><?php esc_html_e( 'Fecha', 'woocommerce' ); ?></span>
                    <span class="nh-overview-card__value"><?php echo wc_format_datetime( $order->get_date_created() ); ?></span>
                </li>

                <li class="nh-overview-card">
                    <span class="nh-overview-card__label"><?php esc_html_e( 'Total', 'woocommerce' ); ?></span>
                    <span class="nh-overview-card__value"><?php echo $order->get_formatted_order_total(); ?></span>
                </li>

                <?php if ( $order->get_payment_method_title() ) : ?>
                    <li class="nh-overview-card">
                        <span class="nh-overview-card__label"><?php esc_html_e( 'Método de Pago', 'woocommerce' ); ?></span>
                        <span class="nh-overview-card__value"><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></span>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- WhatsApp Concierge Card -->
            <?php
            $first_name = $order->get_billing_first_name() ? $order->get_billing_first_name() : 'Cliente';
            $wa_msg     = sprintf(
                'Hola Norma Hana, acabo de realizar el pedido #%s a nombre de %s y me gustaría recibir actualizaciones sobre la confección y despacho.',
                $order->get_order_number(),
                $first_name
            );
            $wa_url     = 'https://wa.me/573043510019?text=' . rawurlencode( $wa_msg );
            ?>
            <div class="nh-whatsapp-card">
                <div class="nh-whatsapp-card__info">
                    <h4>¿Deseas atención personalizada sobre tu pedido?</h4>
                    <p>Escríbenos a nuestro canal oficial de WhatsApp para cualquier consulta o personalización adicional.</p>
                </div>
                <a href="<?php echo esc_url( $wa_url ); ?>" target="_blank" rel="noopener noreferrer" class="nh-whatsapp-btn">
                    <span>Contactar por WhatsApp</span>
                </a>
            </div>

        <?php endif; ?>

        <?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
        <?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

    <?php else : ?>

        <div class="nh-thankyou-hero">
            <p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">
                <?php echo apply_filters( 'woocommerce_thankyou_order_received_text', esc_html__( 'Thank you. Your order has been received.', 'woocommerce' ), null ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </p>
        </div>

    <?php endif; ?>

</div>
