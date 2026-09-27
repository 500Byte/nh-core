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
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="#ffffff" aria-hidden="true">
                        <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M8.53 7.33C8.37 7.33 8.1 7.39 7.87 7.64C7.65 7.89 7.02 8.48 7.02 9.68C7.02 10.88 7.89 12.04 8.01 12.2C8.13 12.37 9.71 14.81 12.14 15.86C12.72 16.11 13.17 16.26 13.52 16.37C14.1 16.56 14.63 16.53 15.05 16.47C15.52 16.4 16.49 15.88 16.69 15.31C16.89 14.74 16.89 14.25 16.83 14.15C16.77 14.05 16.61 13.99 16.37 13.87C16.13 13.75 14.95 13.17 14.73 13.09C14.51 13.01 14.35 12.97 14.19 13.21C14.03 13.45 13.57 13.99 13.43 14.15C13.29 14.31 13.15 14.33 12.91 14.21C12.67 14.09 11.9 13.84 10.99 13.03C10.28 12.4 9.8 11.62 9.66 11.38C9.52 11.14 9.65 11.01 9.77 10.89C9.88 10.78 10.02 10.6 10.14 10.46C10.26 10.32 10.3 10.22 10.38 10.06C10.46 9.9 10.42 9.76 10.36 9.64C10.3 9.52 9.84 8.38 9.65 7.92C9.46 7.47 9.27 7.53 9.13 7.52C8.99 7.51 8.83 7.51 8.67 7.51L8.53 7.33Z"/>
                    </svg>
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
