<?php
/**
 * Thankyou page - Norma Hana Luxury Editorial Template
 *
 * Top-tier Dribbble luxury e-commerce split 2-column layout.
 * Preserves all standard action hooks for PixelYourSite, Meta CAPI, and payment gateways.
 *
 * @package WooCommerce\Templates
 * @version 8.1.0
 * @var WC_Order|false $order
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="woocommerce-order nh-thankyou-container">

    <?php if ( $order ) : ?>

        <?php do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>

        <?php if ( $order->has_status( 'failed' ) ) : ?>

            <div class="nh-thankyou-hero nh-thankyou-hero--failed">
                <span class="nh-order-badge nh-order-badge--failed" id="nh-order-badge-state">
                    <svg class="nh-badge-icon" viewBox="0 0 20 20" fill="currentColor" width="14" height="14" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span><?php esc_html_e( 'Pago no completado', 'woocommerce' ); ?></span>
                </span>
                <h1><?php esc_html_e( 'No pudimos procesar tu pago', 'woocommerce' ); ?></h1>
                <p class="nh-hero-subtitle"><?php esc_html_e( 'La entidad bancaria o pasarela no autorizó la transacción. Por favor, intenta nuevamente con otro medio de pago para asegurar tus piezas.', 'woocommerce' ); ?></p>
                <div class="nh-failed-actions">
                    <a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="nh-btn nh-btn--primary">
                        <span><?php esc_html_e( 'Reintentar Pago', 'woocommerce' ); ?></span>
                        <svg class="nh-btn-arrow" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                    <?php if ( is_user_logged_in() ) : ?>
                        <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="nh-btn nh-btn--secondary">
                            <span><?php esc_html_e( 'Mi Cuenta', 'woocommerce' ); ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

        <?php else : ?>

            <?php
            $is_confirmed = $order->has_status( [ 'processing', 'completed' ] );
            $is_completed = $order->has_status( 'completed' );
            $badge_class  = $is_confirmed ? 'nh-order-badge--confirmed' : 'nh-order-badge--pending';
            $badge_text   = $is_confirmed ? 'Pedido Confirmado' : 'Validación Bancaria en Curso';
            ?>

            <!-- Hero & Storytelling Header -->
            <div class="nh-thankyou-hero">
                <span class="nh-order-badge <?php echo esc_attr( $badge_class ); ?>" id="nh-order-badge-state">
                    <?php if ( $is_confirmed ) : ?>
                        <svg class="nh-badge-icon" viewBox="0 0 20 20" fill="currentColor" width="14" height="14" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    <?php else : ?>
                        <svg class="nh-badge-icon" viewBox="0 0 20 20" fill="currentColor" width="14" height="14" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                    <?php endif; ?>
                    <span><?php echo esc_html( $badge_text ); ?></span>
                </span>

                <?php if ( $is_confirmed ) : ?>
                    <h1>Gracias por elegir el diseño consciente</h1>
                    <p class="nh-hero-subtitle">Tu pedido ha ingresado exitosamente a nuestro taller. Nuestras artesanas en Santa Marta están preparando tus piezas con el mayor cuidado, respeto por los tiempos de confección y atención a cada detalle.</p>
                <?php else : ?>
                    <h1>Hemos recibido la solicitud de tu pedido</h1>
                    <p class="nh-hero-subtitle">Tu entidad bancaria o pasarela de pago (PSE / Wompi) está procesando la transacción. No es necesario realizar un nuevo intento; una vez recibida la confirmación, iniciaremos la preparación en el taller.</p>
                <?php endif; ?>

                <!-- Interactive Meta Pill Bar -->
                <div class="nh-meta-pill-bar">
                    <div class="nh-meta-pill nh-meta-pill--order">
                        <span class="nh-meta-pill__label">Nº de Pedido:</span>
                        <strong class="nh-meta-pill__value">#<?php echo esc_html( $order->get_order_number() ); ?></strong>
                        <button type="button" class="nh-copy-btn" data-copy="<?php echo esc_attr( $order->get_order_number() ); ?>" aria-label="Copiar número de pedido" title="Copiar al portapapeles">
                            <svg class="nh-copy-icon nh-copy-icon--default" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                            </svg>
                            <svg class="nh-copy-icon nh-copy-icon--success" viewBox="0 0 20 20" fill="currentColor" width="14" height="14" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="nh-copy-tooltip">Copiar</span>
                        </button>
                    </div>

                    <div class="nh-meta-pill nh-meta-pill--date">
                        <svg class="nh-meta-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span class="nh-meta-pill__text"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></span>
                    </div>

                    <?php if ( $order->get_billing_email() ) : ?>
                        <div class="nh-meta-pill nh-meta-pill--email">
                            <svg class="nh-meta-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <span class="nh-meta-pill__text">Confirmación enviada a <strong><?php echo esc_html( $order->get_billing_email() ); ?></strong></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Split 2-Column Grid -->
            <div class="nh-thankyou-split-grid">

                <!-- Columna Izquierda (Workshop Stepper, WhatsApp Concierge, Customer & Delivery Info, Craft Stamp) -->
                <div class="nh-thankyou-col-main">

                    <!-- 1. Workshop Stepper Card -->
                    <div class="nh-stepper-card">
                        <h3 class="nh-card-eyebrow">ESTADO DE TU PEDIDO</h3>
                        <div class="nh-stepper">
                            <!-- Step 1 -->
                            <div class="nh-step is-completed">
                                <div class="nh-step__indicator">
                                    <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="nh-step__content">
                                    <h4 class="nh-step__title">Pedido Confirmado</h4>
                                    <p class="nh-step__desc">Orden registrada y verificada con éxito.</p>
                                </div>
                            </div>

                            <div class="nh-step-rail <?php echo $is_confirmed ? 'is-active' : ''; ?>"></div>

                            <!-- Step 2 -->
                            <div class="nh-step <?php echo $is_confirmed ? ( $is_completed ? 'is-completed' : 'is-active' ) : 'is-upcoming'; ?>">
                                <div class="nh-step__indicator">
                                    <?php if ( $is_completed ) : ?>
                                        <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    <?php elseif ( $is_confirmed ) : ?>
                                        <span class="nh-step__pulse-dot"></span>
                                    <?php else : ?>
                                        <span class="nh-step__number">2</span>
                                    <?php endif; ?>
                                </div>
                                <div class="nh-step__content">
                                    <h4 class="nh-step__title">
                                        En Confección Artesanal
                                        <span class="nh-step__tag">Taller Santa Marta</span>
                                    </h4>
                                    <p class="nh-step__desc">Corte manual y costuras con terminaciones de alta costura por nuestras artesanas.</p>
                                </div>
                            </div>

                            <div class="nh-step-rail <?php echo $is_completed ? 'is-active' : ''; ?>"></div>

                            <!-- Step 3 -->
                            <div class="nh-step <?php echo $is_completed ? 'is-active' : 'is-upcoming'; ?>">
                                <div class="nh-step__indicator">
                                    <?php if ( $is_completed ) : ?>
                                        <span class="nh-step__pulse-dot"></span>
                                    <?php else : ?>
                                        <span class="nh-step__number">3</span>
                                    <?php endif; ?>
                                </div>
                                <div class="nh-step__content">
                                    <h4 class="nh-step__title">Despacho y Entrega</h4>
                                    <p class="nh-step__desc">Empaque ecológico y coordinación logística nacional con guía de rastreo oficial.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Payment Gateway Instructions (if any) -->
                    <?php
                    ob_start();
                    do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() );
                    $gateway_instructions = trim( ob_get_clean() );
                    ?>
                    <?php if ( ! empty( $gateway_instructions ) && ! empty( wp_strip_all_tags( $gateway_instructions ) ) ) : ?>
                        <div class="nh-gateway-card">
                            <div class="nh-gateway-card__header">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                                    <line x1="2" y1="10" x2="22" y2="10"></line>
                                </svg>
                                <h4>Instrucciones para el Pago</h4>
                            </div>
                            <div class="nh-gateway-card__body">
                                <?php echo $gateway_instructions; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- 3. WhatsApp VIP Concierge Card -->
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
                            <div class="nh-whatsapp-card__badge">
                                <span class="nh-whatsapp-card__dot"></span>
                                <span>Línea VIP Directa</span>
                            </div>
                            <h4>¿Deseas atención personalizada sobre tu pedido?</h4>
                            <p>Escríbenos a nuestro canal oficial de WhatsApp para cualquier consulta o personalización de tus prendas.</p>
                        </div>
                        <a href="<?php echo esc_url( $wa_url ); ?>" target="_blank" rel="noopener noreferrer" class="nh-whatsapp-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="#ffffff" aria-hidden="true">
                                <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M8.53 7.33C8.37 7.33 8.1 7.39 7.87 7.64C7.65 7.89 7.02 8.48 7.02 9.68C7.02 10.88 7.89 12.04 8.01 12.2C8.13 12.37 9.71 14.81 12.14 15.86C12.72 16.11 13.17 16.26 13.52 16.37C14.1 16.56 14.63 16.53 15.05 16.47C15.52 16.4 16.49 15.88 16.69 15.31C16.89 14.74 16.89 14.25 16.83 14.15C16.77 14.05 16.61 13.99 16.37 13.87C16.13 13.75 14.95 13.17 14.73 13.09C14.51 13.01 14.35 12.97 14.19 13.21C14.03 13.45 13.57 13.99 13.43 14.15C13.29 14.31 13.15 14.33 12.91 14.21C12.67 14.09 11.9 13.84 10.99 13.03C10.28 12.4 9.8 11.62 9.66 11.38C9.52 11.14 9.65 11.01 9.77 10.89C9.88 10.78 10.02 10.6 10.14 10.46C10.26 10.32 10.3 10.22 10.38 10.06C10.46 9.9 10.42 9.76 10.36 9.64C10.3 9.52 9.84 8.38 9.65 7.92C9.46 7.47 9.27 7.53 9.13 7.52C8.99 7.51 8.83 7.51 8.67 7.51L8.53 7.33Z"/>
                            </svg>
                            <span>Contactar por WhatsApp</span>
                        </a>
                    </div>

                    <!-- 4. Customer Delivery & Contact Info Card -->
                    <?php
                    $recipient_name = trim( $order->get_formatted_shipping_full_name() );
                    if ( empty( $recipient_name ) ) {
                        $recipient_name = trim( $order->get_formatted_billing_full_name() );
                    }

                    $addr_1   = $order->get_shipping_address_1() ? $order->get_shipping_address_1() : $order->get_billing_address_1();
                    $addr_2   = $order->get_shipping_address_2() ? $order->get_shipping_address_2() : $order->get_billing_address_2();
                    $city     = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
                    $state    = $order->get_shipping_state() ? $order->get_shipping_state() : $order->get_billing_state();
                    $country  = $order->get_shipping_country() ? $order->get_shipping_country() : $order->get_billing_country();
                    $postcode = $order->get_shipping_postcode() ? $order->get_shipping_postcode() : $order->get_billing_postcode();

                    $states = WC()->countries ? WC()->countries->get_states( $country ) : [];
                    $state_name = isset( $states[ $state ] ) ? $states[ $state ] : $state;
                    $country_name = WC()->countries && isset( WC()->countries->countries[ $country ] ) ? WC()->countries->countries[ $country ] : $country;

                    $location_parts = array_filter( [ $city, $state_name, $postcode, $country_name ] );
                    $location_text  = implode( ', ', $location_parts );

                    $phone = $order->get_billing_phone();
                    $email = $order->get_billing_email();
                    $note  = $order->get_customer_note();
                    ?>
                    <div class="nh-customer-card">
                        <h3 class="nh-card-eyebrow">INFORMACIÓN DE ENTREGA Y CONTACTO</h3>
                        <div class="nh-customer-card__grid">
                            <?php if ( ! empty( $addr_1 ) || ! empty( $recipient_name ) ) : ?>
                                <div class="nh-customer-info-item">
                                    <div class="nh-customer-info-item__icon">
                                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                    </div>
                                    <div class="nh-customer-info-item__content">
                                        <span class="nh-customer-info-item__label">Dirección de Entrega</span>
                                        <?php if ( ! empty( $recipient_name ) ) : ?>
                                            <span class="nh-customer-info-item__name"><?php echo esc_html( $recipient_name ); ?></span>
                                        <?php endif; ?>
                                        <span class="nh-customer-info-item__value">
                                            <?php echo esc_html( $addr_1 ); ?><?php echo $addr_2 ? ' · ' . esc_html( $addr_2 ) : ''; ?>
                                        </span>
                                        <?php if ( ! empty( $location_text ) ) : ?>
                                            <span class="nh-customer-info-item__sub"><?php echo esc_html( $location_text ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ( ! empty( $phone ) ) : ?>
                                <div class="nh-customer-info-item">
                                    <div class="nh-customer-info-item__icon">
                                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                        </svg>
                                    </div>
                                    <div class="nh-customer-info-item__content">
                                        <span class="nh-customer-info-item__label">Teléfono de Contacto</span>
                                        <a href="tel:<?php echo esc_attr( $phone ); ?>" class="nh-customer-info-item__link"><?php echo esc_html( $phone ); ?></a>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ( ! empty( $email ) ) : ?>
                                <div class="nh-customer-info-item">
                                    <div class="nh-customer-info-item__icon">
                                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                            <polyline points="22,6 12,13 2,6"></polyline>
                                        </svg>
                                    </div>
                                    <div class="nh-customer-info-item__content">
                                        <span class="nh-customer-info-item__label">Correo de Confirmación</span>
                                        <a href="mailto:<?php echo esc_attr( $email ); ?>" class="nh-customer-info-item__link"><?php echo esc_html( $email ); ?></a>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ( ! empty( $note ) ) : ?>
                                <div class="nh-customer-info-item nh-customer-info-item--full">
                                    <div class="nh-customer-info-item__icon">
                                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                            <polyline points="10 9 9 9 8 9"></polyline>
                                        </svg>
                                    </div>
                                    <div class="nh-customer-info-item__content">
                                        <span class="nh-customer-info-item__label">Nota para el Taller</span>
                                        <span class="nh-customer-info-item__value">“<?php echo esc_html( $note ); ?>”</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 5. Craft Stamp -->
                    <div class="nh-craft-stamp">
                        <svg class="nh-craft-stamp__icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"></path>
                        </svg>
                        <span class="nh-craft-stamp__text">Hecho a mano en Santa Marta, Colombia • Moda consciente y atemporal</span>
                    </div>

                </div>

                <!-- Columna Derecha (Boutique Receipt Card) -->
                <div class="nh-thankyou-col-sidebar">
                    <div class="nh-receipt-card">
                        <div class="nh-receipt-header">
                            <h3 class="nh-card-eyebrow">RESUMEN DE TU COMPRA</h3>
                            <?php
                            $item_count = 0;
                            foreach ( $order->get_items() as $item ) {
                                $item_count += (int) $item->get_quantity();
                            }
                            ?>
                            <span class="nh-receipt-item-count"><?php echo (int) $item_count; ?> <?php echo $item_count === 1 ? 'pieza' : 'piezas'; ?></span>
                        </div>

                        <!-- Visual Items List -->
                        <div class="nh-receipt-items">
                            <?php foreach ( $order->get_items() as $item_id => $item ) : ?>
                                <?php
                                $product   = $item->get_product();
                                $thumbnail = $product ? $product->get_image( [ 80, 80 ], [ 'class' => 'nh-receipt-item-thumb-img' ] ) : wc_placeholder_img( [ 80, 80 ] );
                                $title     = ( $product && $product->is_type( 'variation' ) ) ? get_the_title( $product->get_parent_id() ) : $item->get_name();
                                $qty       = $item->get_quantity();

                                // Variation attribute pills
                                $pills = [];
                                if ( $product && $product->is_type( 'variation' ) ) {
                                    foreach ( $product->get_variation_attributes() as $attr_key => $attr_value ) {
                                        if ( '' === $attr_value ) continue;
                                        $taxonomy = str_replace( 'attribute_', '', $attr_key );
                                        $label = wc_attribute_label( $taxonomy, $product );
                                        $term = taxonomy_exists( $taxonomy ) ? get_term_by( 'slug', $attr_value, $taxonomy ) : false;
                                        $display_val = $term ? $term->name : ucfirst( $attr_value );
                                        $pills[] = [ 'label' => $label, 'value' => $display_val ];
                                    }
                                }
                                ?>
                                <div class="nh-receipt-item">
                                    <div class="nh-receipt-item__media">
                                        <div class="nh-receipt-item__thumb">
                                            <?php echo $thumbnail; ?>
                                        </div>
                                        <span class="nh-receipt-item__qty"><?php echo (int) $qty; ?></span>
                                    </div>
                                    <div class="nh-receipt-item__details">
                                        <h4 class="nh-receipt-item__title"><?php echo esc_html( $title ); ?></h4>
                                        <?php if ( ! empty( $pills ) ) : ?>
                                            <div class="nh-receipt-pills">
                                                <?php foreach ( $pills as $pill ) : ?>
                                                    <span class="nh-receipt-pill">
                                                        <span class="nh-receipt-pill__label"><?php echo esc_html( $pill['label'] ); ?>:</span>
                                                        <span class="nh-receipt-pill__val"><?php echo esc_html( $pill['value'] ); ?></span>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="nh-receipt-item__subtotal">
                                        <?php echo $order->get_formatted_line_subtotal( $item ); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="nh-receipt-divider"></div>

                        <!-- Financial Breakdown -->
                        <div class="nh-receipt-breakdown">
                            <div class="nh-receipt-row">
                                <span class="nh-receipt-label">Subtotal</span>
                                <span class="nh-receipt-value"><?php echo $order->get_subtotal_to_display(); ?></span>
                            </div>

                            <div class="nh-receipt-row">
                                <span class="nh-receipt-label">Envío</span>
                                <span class="nh-receipt-value"><?php echo $order->get_shipping_to_display(); ?></span>
                            </div>

                            <?php if ( $order->get_discount_total() > 0 ) : ?>
                                <div class="nh-receipt-row nh-receipt-discount-row">
                                    <span class="nh-receipt-label">
                                        Descuento
                                        <?php foreach ( $order->get_coupon_codes() as $code ) : ?>
                                            <span class="nh-receipt-coupon-pill"><?php echo esc_html( strtoupper( $code ) ); ?></span>
                                        <?php endforeach; ?>
                                    </span>
                                    <span class="nh-receipt-value nh-receipt-discount-value">-<?php echo wc_price( $order->get_discount_total(), [ 'currency' => $order->get_currency() ] ); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ( $order->get_payment_method_title() ) : ?>
                                <div class="nh-receipt-row">
                                    <span class="nh-receipt-label">Método de Pago</span>
                                    <span class="nh-receipt-value"><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="nh-receipt-total-row">
                                <span class="nh-receipt-total-label">Total</span>
                                <span class="nh-receipt-total-value"><?php echo $order->get_formatted_order_total(); ?></span>
                            </div>
                        </div>

                        <!-- Action CTA -->
                        <div class="nh-receipt-cta-wrap">
                            <a href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>" class="nh-shop-return-btn">
                                <span>Seguir explorando la tienda</span>
                                <svg class="nh-shop-return-btn__arrow" viewBox="0 0 20 20" fill="currentColor" width="16" height="16" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Copy-to-clipboard Micro-interaction Script -->
            <script>
            (function() {
                var copyBtns = document.querySelectorAll('.nh-copy-btn');
                copyBtns.forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        var text = this.getAttribute('data-copy');
                        if (!text) return;
                        var self = this;
                        var tooltip = self.querySelector('.nh-copy-tooltip');
                        var originalText = tooltip ? tooltip.textContent : 'Copiar';

                        var doSuccess = function() {
                            self.classList.add('is-copied');
                            if (tooltip) tooltip.textContent = '¡Copiado!';
                            setTimeout(function() {
                                self.classList.remove('is-copied');
                                if (tooltip) tooltip.textContent = originalText;
                            }, 2200);
                        };

                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(text).then(doSuccess).catch(function() {
                                fallbackCopy(text, doSuccess);
                            });
                        } else {
                            fallbackCopy(text, doSuccess);
                        }
                    });
                });

                function fallbackCopy(text, cb) {
                    var el = document.createElement('textarea');
                    el.value = text;
                    el.setAttribute('readonly', '');
                    el.style.position = 'absolute';
                    el.style.left = '-9999px';
                    document.body.appendChild(el);
                    el.select();
                    try {
                        document.execCommand('copy');
                        cb();
                    } catch (err) {}
                    document.body.removeChild(el);
                }
            })();
            </script>

        <?php endif; ?>

        <!-- Mandatory Tracking Hook for PixelYourSite, Meta CAPI, and Cart Cleanup -->
        <?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

    <?php else : ?>

        <div class="nh-thankyou-hero">
            <p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">
                <?php echo apply_filters( 'woocommerce_thankyou_order_received_text', esc_html__( 'Thank you. Your order has been received.', 'woocommerce' ), null ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </p>
        </div>

    <?php endif; ?>

</div>
