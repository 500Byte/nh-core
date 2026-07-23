<?php
/**
 * NH Menu Cart Widget — Clon del widget Elementor Pro Menu Cart.
 *
 * Copia fiel del original con adaptaciones para uso standalone en child theme.
 * Original: pro-elements/modules/woocommerce/widgets/menu-cart.php
 *
 * @package Normahana
 */

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Icons_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NH_Menu_Cart_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'nh-menu-cart';
	}

	public function get_title() {
		return esc_html__( 'NH - Menu Cart', 'normahana' );
	}

	public function get_icon() {
		return 'eicon-cart';
	}

	public function get_categories() {
		return [ 'nh-widgets' ];
	}

	public function get_keywords() {
		return [ 'cart', 'carrito', 'menu', 'woocommerce', 'norma hana', 'mini cart', 'side cart' ];
	}

	public function get_style_depends(): array {
		return [ 'nh-menu-cart' ];
	}

	public function get_script_depends(): array {
		return [ 'nh-menu-cart', 'wc-cart-fragments' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_menu_icon_content',
			[
				'label' => esc_html__( 'Menu Icon', 'normahana' ),
			]
		);

		$this->add_control(
			'icon',
			[
				'label' => esc_html__( 'Icon', 'normahana' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'cart-light' => esc_html__( 'Cart', 'normahana' ) . ' ' . esc_html__( 'Light', 'normahana' ),
					'cart-medium' => esc_html__( 'Cart', 'normahana' ) . ' ' . esc_html__( 'Medium', 'normahana' ),
					'cart-solid' => esc_html__( 'Cart', 'normahana' ) . ' ' . esc_html__( 'Solid', 'normahana' ),
					'basket-light' => esc_html__( 'Basket', 'normahana' ) . ' ' . esc_html__( 'Light', 'normahana' ),
					'basket-medium' => esc_html__( 'Basket', 'normahana' ) . ' ' . esc_html__( 'Medium', 'normahana' ),
					'basket-solid' => esc_html__( 'Basket', 'normahana' ) . ' ' . esc_html__( 'Solid', 'normahana' ),
					'bag-light' => esc_html__( 'Bag', 'normahana' ) . ' ' . esc_html__( 'Light', 'normahana' ),
					'bag-medium' => esc_html__( 'Bag', 'normahana' ) . ' ' . esc_html__( 'Medium', 'normahana' ),
					'bag-solid' => esc_html__( 'Bag', 'normahana' ) . ' ' . esc_html__( 'Solid', 'normahana' ),
					'custom' => esc_html__( 'Custom', 'normahana' ),
				],
				'default' => 'cart-medium',
				'prefix_class' => 'toggle-icon--', // Prefix class not used anymore, but kept for BC reasons.
				'render_type' => 'template',
			]
		);

		$this->add_control(
			'menu_icon_svg',
			[
				'label' => esc_html__( 'Custom Icon', 'normahana' ),
				'type' => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon_active',
				'default' => [
					'value' => 'fas fa-shopping-cart',
					'library' => 'fa-solid',
				],
				'skin_settings' => [
					'inline' => [
						'none' => [
							'label' => 'None',
						],
					],
				],
				'recommended' => [
					'fa-solid' => [
						'shopping-bag',
						'shopping-basket',
						'shopping-cart',
						'cart-arrow-down',
						'cart-plus',
					],
				],
				'skin' => 'inline',
				'label_block' => false,
				'condition' => [
					'icon' => 'custom',
				],
			]
		);

		$this->add_control(
			'items_indicator',
			[
				'label' => esc_html__( 'Items Indicator', 'normahana' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'none' => esc_html__( 'None', 'normahana' ),
					'bubble' => esc_html__( 'Bubble', 'normahana' ),
					'plain' => esc_html__( 'Plain', 'normahana' ),
				],
				'prefix_class' => 'elementor-menu-cart--items-indicator-',
				'default' => 'bubble',
			]
		);

		$this->add_control(
			'hide_empty_indicator',
			[
				'label' => esc_html__( 'Hide Empty', 'normahana' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'normahana' ),
				'label_off' => esc_html__( 'No', 'normahana' ),
				'return_value' => 'hide',
				'prefix_class' => 'elementor-menu-cart--empty-indicator-',
				'condition' => [
					'items_indicator!' => 'none',
				],
			]
		);

		$this->add_control(
			'show_subtotal',
			[
				'label' => esc_html__( 'Subtotal', 'normahana' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'normahana' ),
				'label_off' => esc_html__( 'Hide', 'normahana' ),
				'return_value' => 'yes',
				'default' => 'yes',
				'prefix_class' => 'elementor-menu-cart--show-subtotal-',
			]
		);

		$this->add_responsive_control(
			'alignment',
			[
				'label' => esc_html__( 'Alignment', 'normahana' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'normahana' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'normahana' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'normahana' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--main-alignment: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_cart',
			[
				'label' => esc_html__( 'Cart', 'normahana' ),
			]
		);

		$this->add_control(
			'cart_type',
			[
				'label' => esc_html__( 'Cart Type', 'normahana' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'side-cart' => esc_html__( 'Side Cart', 'normahana' ),
					'mini-cart' => esc_html__( 'Mini Cart', 'normahana' ),
				],
				'default' => 'side-cart',
				'prefix_class' => 'elementor-menu-cart--cart-type-',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'open_cart',
			[
				'label' => esc_html__( 'Open Cart', 'normahana' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'click' => esc_html__( 'On Click', 'normahana' ),
					'mouseover' => esc_html__( 'On Hover', 'normahana' ),
				],
				'default' => 'click',
				'frontend_available' => true,
				'render_type' => 'template',
			]
		);

		$this->add_responsive_control(
			'side_cart_alignment',
			[
				'label' => esc_html__( 'Cart Position', 'normahana' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'start' => [
						'title' => esc_html__( 'Left', 'normahana' ),
						'icon' => 'eicon-h-align-left',
					],
					'end' => [
						'title' => esc_html__( 'Right', 'normahana' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'condition' => [
					'cart_type' => 'side-cart',
				],
				'selectors' => [
					'{{WRAPPER}}' => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'start' => '--side-cart-alignment-transform: translateX(-100%); --side-cart-alignment-right: auto; --side-cart-alignment-left: 0;',
					'end' => '--side-cart-alignment-transform: translateX(100%); --side-cart-alignment-left: auto; --side-cart-alignment-right: 0;',
				],
			]
		);

		$this->add_responsive_control(
			'mini_cart_alignment',
			[
				'label' => esc_html__( 'Cart Position', 'normahana' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'start' => [
						'title' => esc_html__( 'Left', 'normahana' ),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'normahana' ),
						'icon' => 'eicon-h-align-center',
					],
					'end' => [
						'title' => esc_html__( 'Right', 'normahana' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'condition' => [
					'cart_type' => 'mini-cart',
				],
				'selectors' => [
					'{{WRAPPER}}.elementor-menu-cart--cart-type-mini-cart .elementor-menu-cart__container' => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'start' => 'left: 0; right: auto; transform: none;',
					'center' => 'left: 50%; right: auto; transform: translateX(-50%);',
					'end' => 'right: 0; left: auto; transform: none;',
				],
			]
		);

		$this->add_responsive_control(
			'mini_cart_spacing',
			[
				'label' => esc_html__( 'Distance', 'normahana' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'range' => [
					'px' => [
						'min' => -300,
						'max' => 300,
					],
					'em' => [
						'min' => -30,
						'max' => 30,
					],
					'rem' => [
						'min' => -30,
						'max' => 30,
					],
					'%' => [
						'min' => -100,
						'max' => 100,
					],
				],
				'condition' => [
					'cart_type' => 'mini-cart',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--mini-cart-spacing: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'heading_close_cart_button',
			[
				'label' => esc_html__( 'Close Cart', 'normahana' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'close_cart_button_show',
			[
				'label' => esc_html__( 'Close Icon', 'normahana' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'normahana' ),
				'label_off' => esc_html__( 'Hide', 'normahana' ),
				'return_value' => 'yes',
				'default' => 'yes',
				'selectors' => [
					'{{WRAPPER}} .elementor-menu-cart__close-button, {{WRAPPER}} .elementor-menu-cart__close-button-custom' => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'' => 'display: none;',
				],
				'control_type' => 'content',
			]
		);

		$this->add_control(
			'close_cart_icon_svg',
			[
				'label' => esc_html__( 'Custom Icon', 'normahana' ),
				'type' => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon_active',
				'skin_settings' => [
					'inline' => [
						'none' => [
							'label' => 'Default',
							'icon' => 'fas fa-times',
						],
						'icon' => [
							'icon' => 'eicon-star',
						],
					],
				],
				'recommended' => [
					'fa-regular' => [
						'times-circle',
					],
					'fa-solid' => [
						'times',
						'times-circle',
					],
				],
				'skin' => 'inline',
				'label_block' => false,
				'condition' => [
					'close_cart_button_show!' => '',
				],
			]
		);

		$this->add_control(
			'close_cart_button_alignment',
			[
				'label' => esc_html__( 'Icon Position', 'normahana' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'start' => [
						'title' => esc_html__( 'Left', 'normahana' ),
						'icon' => 'eicon-h-align-left',
					],
					'end' => [
						'title' => esc_html__( 'Right', 'normahana' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'condition' => [
					'close_cart_button_show!' => '',
				],
				'selectors_dictionary' => [
					'start' => 'margin-right: auto',
					'end' => 'margin-left: auto',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-menu-cart__close-button, {{WRAPPER}} .elementor-menu-cart__close-button-custom' => '{{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_remove_item_button',
			[
				'label' => esc_html__( 'Remove Item', 'normahana' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'show_remove_icon',
			[
				'label' => esc_html__( 'Remove Item Icon', 'normahana' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'normahana' ),
				'label_off' => esc_html__( 'Hide', 'normahana' ),
				'return_value' => 'yes',
				'default' => 'yes',
				'prefix_class' => 'elementor-menu-cart--show-remove-button-',
			]
		);

		$this->add_control(
			'remove_item_button_position',
			[
				'label' => esc_html__( 'Icon Position', 'normahana' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'top' => [
						'title' => esc_html__( 'Top', 'normahana' ),
						'icon' => 'eicon-v-align-top',
					],
					'middle' => [
						'title' => esc_html__( 'Middle', 'normahana' ),
						'icon' => 'eicon-v-align-middle',
					],
					'bottom' => [
						'title' => esc_html__( 'Bottom', 'normahana' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'default' => '',
				'prefix_class' => 'remove-item-position--',
				'condition' => [
					'show_remove_icon!' => '',
				],
			]
		);

		$this->add_control(
			'heading_price_quantity',
			[
				'label' => esc_html__( 'Price and Quantity', 'normahana' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'price_quantity_position',
			[
				'label' => esc_html__( 'Position', 'normahana' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'top' => [
						'title' => esc_html__( 'Top', 'normahana' ),
						'icon' => 'eicon-v-align-top',
					],
					'bottom' => [
						'title' => esc_html__( 'Bottom', 'normahana' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'top' => '--price-quantity-position--grid-template-rows: auto 75%; --price-quantity-position--align-self: start;',
					'bottom' => '',
				],
			]
		);

		$this->add_control(
			'show_divider',
			[
				'label' => esc_html__( 'Cart Dividers', 'normahana' ),
				'type' => Controls_Manager::SWITCHER,
				'separator' => 'before',
				'label_on' => esc_html__( 'Show', 'normahana' ),
				'label_off' => esc_html__( 'Hide', 'normahana' ),
				'return_value' => 'yes',
				'default' => 'yes',
				'selectors' => [
					'{{WRAPPER}}' => '--divider-style: {{VALUE}}; --subtotal-divider-style: {{VALUE}};',
				],
				'selectors_dictionary' => [
					'' => 'none',
					'yes' => 'solid',
				],
			]
		);

		$this->add_control(
			'heading_buttons',
			[
				'label' => esc_html__( 'Buttons', 'normahana' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'view_cart_button_show',
			[
				'label' => esc_html__( 'View Cart', 'normahana' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'normahana' ),
				'label_off' => esc_html__( 'Hide', 'normahana' ),
				'return_value' => 'yes',
				'default' => 'yes',
				'selectors' => [
					'{{WRAPPER}}' => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'' => '--view-cart-button-display: none; --cart-footer-layout: 1fr;',
				],
			]
		);

		$this->add_control(
			'view_cart_button_alignment',
			[
				'label' => esc_html__( 'Alignment', 'normahana' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'start' => [
						'title' => esc_html__( 'Left', 'normahana' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'normahana' ),
						'icon' => 'eicon-text-align-center',
					],
					'end' => [
						'title' => esc_html__( 'Right', 'normahana' ),
						'icon' => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justify', 'normahana' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'condition' => [
					'view_cart_button_show!' => '',
					'checkout_button_show' => '',
				],
				'selectors' => [
					'{{WRAPPER}}' => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'start' => '--cart-footer-buttons-alignment-display: block; --cart-footer-buttons-alignment-text-align: left; --cart-footer-buttons-alignment-button-width: auto;',
					'center' => '--cart-footer-buttons-alignment-display: block; --cart-footer-buttons-alignment-text-align: center; --cart-footer-buttons-alignment-button-width: auto;',
					'end' => '--cart-footer-buttons-alignment-display: block; --cart-footer-buttons-alignment-text-align: right; --cart-footer-buttons-alignment-button-width: auto;',
					'justify' => '--cart-footer-layout: 1fr;',
				],
			]
		);

		$this->add_control(
			'checkout_button_show',
			[
				'label' => esc_html__( 'Checkout', 'normahana' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'normahana' ),
				'label_off' => esc_html__( 'Hide', 'normahana' ),
				'return_value' => 'yes',
				'default' => 'yes',
				'selectors' => [
					'{{WRAPPER}}' => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'' => '--checkout-button-display: none; --cart-footer-layout: 1fr;',
				],
			]
		);

		$this->add_control(
			'checkout_button_alignment',
			[
				'label' => esc_html__( 'Alignment', 'normahana' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'start' => [
						'title' => esc_html__( 'Left', 'normahana' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'normahana' ),
						'icon' => 'eicon-text-align-center',
					],
					'end' => [
						'title' => esc_html__( 'Right', 'normahana' ),
						'icon' => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justify', 'normahana' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'condition' => [
					'checkout_button_show!' => '',
					'view_cart_button_show' => '',
				],
				'selectors' => [
					'{{WRAPPER}}' => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'start' => '--cart-footer-buttons-alignment-display: block; --cart-footer-buttons-alignment-text-align: left; --cart-footer-buttons-alignment-button-width: auto;',
					'center' => '--cart-footer-buttons-alignment-display: block; --cart-footer-buttons-alignment-text-align: center; --cart-footer-buttons-alignment-button-width: auto;',
					'end' => '--cart-footer-buttons-alignment-display: block; --cart-footer-buttons-alignment-text-align: right; --cart-footer-buttons-alignment-button-width: auto;',
					'justify' => '--cart-footer-layout: 1fr;',
				],
			]
		);

		$this->add_control(
			'checkout_button_display',
			[
				'label' => esc_html__( 'Alignment', 'normahana' ),
				'type' => Controls_Manager::HIDDEN,
				'condition' => [
					'checkout_button_show' => '',
					'view_cart_button_show' => '',
				],
				'default' => '--cart-footer-buttons-alignment-display: none;',
				'selectors' => [
					'{{WRAPPER}}' => '{{VALUE}}',
				],
			]
		);

		$this->add_control(
			'buttons_position',
			[
				'label' => esc_html__( 'Vertical Position', 'normahana' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'top' => [
						'title' => esc_html__( 'Top', 'normahana' ),
						'icon' => 'eicon-v-align-top',
					],
					'bottom' => [
						'title' => esc_html__( 'Bottom', 'normahana' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'default' => '',
				'condition' => [
					'cart_type' => 'side-cart',
				],
				'conditions' => [
					'relation' => 'or',
					'terms' => [
						[
							'name' => 'view_cart_button_show',
							'operator' => '!=',
							'value' => '',
						],
						[
							'name' => 'checkout_button_show',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'bottom' => '--cart-buttons-position-margin: auto;',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_additional_options',
			[
				'label' => esc_html__( 'Additional Options', 'normahana' ),
			]
		);

		$this->add_control(
			'heading_additional_options',
			[
				'label' => esc_html__( 'Cart', 'normahana' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'automatically_open_cart',
			[
				'label' => esc_html__( 'Automatically Open Cart', 'normahana' ),
				'type' => Controls_Manager::SWITCHER,
				'description' => esc_html__( 'Open the cart every time an item is added.', 'normahana' ),
				'label_on' => esc_html__( 'Yes', 'normahana' ),
				'label_off' => esc_html__( 'No', 'normahana' ),
				'return_value' => 'yes',
				'default' => 'no',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'automatically_update_cart',
			[
				'label' => esc_html__( 'Automatically Update Cart', 'normahana' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'normahana' ),
				'label_off' => esc_html__( 'No', 'normahana' ),
				'return_value' => 'yes',
				'default' => 'yes',
				'description' => esc_html__( 'Updates to the cart (e.g., a removed item) via Ajax. The cart will update without refreshing the whole page.', 'normahana' ),
				'selectors' => [
					'{{WRAPPER}}' => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'yes' => '--elementor-remove-from-cart-button: none; --remove-from-cart-button: block;',
					''    => '--elementor-remove-from-cart-button: block; --remove-from-cart-button: none;',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_toggle_style',
			[
				'label' => esc_html__( 'Menu Icon', 'normahana' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'toggle_button_colors' );

		$this->start_controls_tab( 'toggle_button_normal_colors', [ 'label' => esc_html__( 'Normal', 'normahana' ) ] );

		$this->add_control(
			'toggle_button_text_color',
			[
				'label' => esc_html__( 'Text Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--toggle-button-text-color: {{VALUE}};',
				],
				'condition' => [
					'show_subtotal!' => '',
				],
			]
		);

		$this->add_control(
			'toggle_button_icon_color',
			[
				'label' => esc_html__( 'Icon Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--toggle-button-icon-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'toggle_button_background_color',
			[
				'label' => esc_html__( 'Background Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--toggle-button-background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'toggle_button_border_color',
			[
				'label' => esc_html__( 'Border Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--toggle-button-border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'toggle_button_normal_box_shadow',
				'selector' => '{{WRAPPER}} .elementor-menu-cart__toggle .elementor-button',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'toggle_button_hover_colors', [ 'label' => esc_html__( 'Hover', 'normahana' ) ] );

		$this->add_control(
			'toggle_button_hover_text_color',
			[
				'label' => esc_html__( 'Text Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--toggle-button-hover-text-color: {{VALUE}};',
				],
				'condition' => [
					'show_subtotal!' => '',
				],
			]
		);

		$this->add_control(
			'toggle_button_hover_icon_color',
			[
				'label' => esc_html__( 'Icon Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--toggle-button-icon-hover-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'toggle_button_hover_background_color',
			[
				'label' => esc_html__( 'Background Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--toggle-button-hover-background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'toggle_button_hover_border_color',
			[
				'label' => esc_html__( 'Border Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--toggle-button-hover-border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'toggle_button_hover_box_shadow',
				'selector' => '{{WRAPPER}} .elementor-menu-cart__toggle .elementor-button:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'toggle_button_border_width',
			[
				'label' => esc_html__( 'Border Width', 'normahana' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
				'range' => [
					'px' => [
						'max' => 20,
					],
					'em' => [
						'max' => 2,
					],
					'rem' => [
						'max' => 2,
					],
				],
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}}' => '--toggle-button-border-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'toggle_button_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'normahana' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'range' => [
					'px' => [
						'max' => 100,
					],
					'em' => [
						'max' => 10,
					],
					'rem' => [
						'max' => 10,
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--toggle-button-border-radius: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'toggle_button_typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .elementor-menu-cart__toggle .elementor-button',
				'separator' => 'before',
				'condition' => [
					'show_subtotal!' => '',
				],
			]
		);

		$this->add_control(
			'heading_icon_style',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Icon', 'normahana' ),
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'toggle_icon_size',
			[
				'label' => esc_html__( 'Size', 'normahana' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'range' => [
					'px' => [
						'max' => 100,
					],
					'em' => [
						'max' => 10,
					],
					'rem' => [
						'max' => 10,
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--toggle-icon-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'toggle_icon_spacing',
			[
				'label' => esc_html__( 'Spacing', 'normahana' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
				'range' => [
					'px' => [
						'max' => 100,
					],
					'em' => [
						'max' => 10,
					],
					'rem' => [
						'max' => 10,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-menu-cart__toggle .elementor-button' => 'gap: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'show_subtotal!' => '',
				],
			]
		);

		$start = is_rtl() ? 'right' : 'left';
		$end = is_rtl() ? 'left' : 'right';

		$this->add_control(
			'toggle_icon_position',
			[
				'label' => esc_html__( 'Position', 'normahana' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'row-reverse' => [
						'title' => esc_html__( 'Start', 'normahana' ),
						'icon' => "eicon-h-align-{$start}",
					],
					'row' => [
						'title' => esc_html__( 'End', 'normahana' ),
						'icon' => "eicon-h-align-{$end}",
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-menu-cart__toggle .elementor-button' => 'flex-direction: {{VALUE}};',
				],
				'condition' => [
					'show_subtotal!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'toggle_button_padding',
			[
				'label' => esc_html__( 'Padding', 'normahana' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
				'selectors' => [
					'{{WRAPPER}}' => '--toggle-icon-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'items_indicator_style',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Items Indicator', 'normahana' ),
				'separator' => 'before',
				'condition' => [
					'items_indicator!' => 'none',
				],
			]
		);
		$this->add_control(
			'items_indicator_text_color',
			[
				'label' => esc_html__( 'Text Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--items-indicator-text-color: {{VALUE}};',
				],
				'condition' => [
					'items_indicator!' => 'none',
				],
			]
		);

		$this->add_control(
			'items_indicator_background_color',
			[
				'label' => esc_html__( 'Background Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--items-indicator-background-color: {{VALUE}};',
				],
				'condition' => [
					'items_indicator' => 'bubble',
				],
			]
		);

		$this->add_responsive_control(
			'items_indicator_distance',
			[
				'label' => esc_html__( 'Distance', 'normahana' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'range' => [
					'em' => [
						'max' => 50,
					],
					'em' => [
						'max' => 5,
					],
					'rem' => [
						'max' => 5,
					],
				],
				'selectors' => [
					'body:not(.rtl) {{WRAPPER}} .elementor-menu-cart__toggle .elementor-button-icon .elementor-button-icon-qty[data-counter]' => 'right: -{{SIZE}}{{UNIT}}; top: -{{SIZE}}{{UNIT}};',
					'body.rtl {{WRAPPER}} .elementor-menu-cart__toggle .elementor-button-icon .elementor-button-icon-qty[data-counter]' => 'right: {{SIZE}}{{UNIT}}; top: -{{SIZE}}{{UNIT}}; left: auto;',
				],
				'condition' => [
					'items_indicator' => 'bubble',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_cart_style',
			[
				'label' => esc_html__( 'Cart', 'normahana' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'background_color',
			[
				'label' => esc_html__( 'Background Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--cart-background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'border_type',
			[
				'label' => esc_html__( 'Border Type', 'normahana' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'none' => esc_html__( 'None', 'normahana' ),
					'solid' => esc_html__( 'Solid', 'normahana' ),
					'double' => esc_html__( 'Double', 'normahana' ),
					'dotted' => esc_html__( 'Dotted', 'normahana' ),
					'dashed' => esc_html__( 'Dashed', 'normahana' ),
					'groove' => esc_html__( 'Groove', 'normahana' ),
				],
				'selectors' => [
					'{{WRAPPER}}' => '--cart-border-style: {{VALUE}};',
				],
				'default' => 'none',
			]
		);

		$this->add_responsive_control(
			'border_width',
			[
				'label' => esc_html__( 'Width', 'normahana' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-menu-cart__main' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'border_type!' => 'none',
				],
			]
		);

		$this->add_control(
			'border_color',
			[
				'label' => esc_html__( 'Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--cart-border-color: {{VALUE}};',
				],
				'condition' => [
					'border_type!' => 'none',
				],
			]
		);

		$this->add_responsive_control(
			'border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'normahana' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors' => [
					'{{WRAPPER}}' => '--cart-border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'cart_box_shadow',
				'selector' => '{{WRAPPER}} .elementor-menu-cart__main',
			]
		);

		$this->add_responsive_control(
			'cart_padding',
			[
				'label' => esc_html__( 'Padding', 'normahana' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
				'selectors' => [
					'{{WRAPPER}}' => '--cart-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'heading_close',
			[
				'label' => esc_html__( 'Close Cart', 'normahana' ),
				'type' => Controls_Manager::HEADING,
				'condition' => [
					'close_cart_button_show!' => '',
				],
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'close_cart_icon_size',
			[
				'label' => esc_html__( 'Icon Size', 'normahana' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors' => [
					'{{WRAPPER}}' => '--cart-close-icon-size: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'close_cart_button_show!' => '',
				],
			]
		);

		$this->start_controls_tabs( 'cart_icon_style' );

		$this->start_controls_tab(
			'icon_normal',
			[
				'label' => esc_html__( 'Normal', 'normahana' ),
				'condition' => [
					'close_cart_button_show!' => '',
				],
			]
		);

		$this->add_control(
			'close_cart_icon_color',
			[
				'label' => esc_html__( 'Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--cart-close-button-color: {{VALUE}};',
				],
				'condition' => [
					'close_cart_button_show!' => '',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'icon_hover',
			[
				'label' => esc_html__( 'Hover', 'normahana' ),
				'condition' => [
					'close_cart_button_show!' => '',
				],
			]
		);

		$this->add_control(
			'close_cart_icon_hover_color',
			[
				'label' => esc_html__( 'Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--cart-close-button-hover-color: {{VALUE}};',
				],
				'condition' => [
					'close_cart_button_show!' => '',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'heading_remove_item_button_style',
			[
				'label' => esc_html__( 'Remove Item', 'normahana' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'show_remove_icon!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'remove_item_button_size',
			[
				'label' => esc_html__( 'Icon Size', 'normahana' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', 'custom' ],
				'selectors' => [
					'{{WRAPPER}}' => '--remove-item-button-size: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'show_remove_icon!' => '',
				],
			]
		);

		$this->start_controls_tabs(
			'cart_remove_item_button_style',
			[
				'condition' => [
					'show_remove_icon!' => '',
				],
			]
		);

		$this->start_controls_tab(
			'remove_item_button_normal',
			[
				'label' => esc_html__( 'Normal', 'normahana' ),
				'condition' => [
					'show_remove_icon!' => '',
				],
			]
		);

		$this->add_control(
			'remove_item_button_color',
			[
				'label' => esc_html__( 'Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--remove-item-button-color: {{VALUE}}',
				],
				'condition' => [
					'show_remove_icon!' => '',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'remove_item_button_hover',
			[
				'label' => esc_html__( 'Hover', 'normahana' ),
				'condition' => [
					'show_remove_icon!' => '',
				],
			]
		);

		$this->add_control(
			'remove_item_button_hover_color',
			[
				'label' => esc_html__( 'Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--remove-item-button-hover-color: {{VALUE}};',
				],
				'condition' => [
					'show_remove_icon!' => '',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'heading_subtotal_style',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Subtotal', 'normahana' ),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'subtotal_color',
			[
				'label' => esc_html__( 'Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--menu-cart-subtotal-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'subtotal_typography',
				'selector' => '{{WRAPPER}} .elementor-menu-cart__subtotal',
			]
		);

		$this->add_responsive_control(
			'subtotal_alignment',
			[
				'label' => esc_html__( 'Alignment', 'normahana' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'normahana' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'normahana' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'normahana' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--menu-cart-subtotal-text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'subtotal_divider_style',
			[
				'label' => esc_html__( 'Divider Style', 'normahana' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'' => esc_html__( 'None', 'normahana' ),
					'solid' => esc_html__( 'Solid', 'normahana' ),
					'double' => esc_html__( 'Double', 'normahana' ),
					'dotted' => esc_html__( 'Dotted', 'normahana' ),
					'dashed' => esc_html__( 'Dashed', 'normahana' ),
					'groove' => esc_html__( 'Groove', 'normahana' ),
				],
				'selectors' => [
					'{{WRAPPER}} .widget_shopping_cart_content' => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'' => '--subtotal-divider-left-width: 0; --subtotal-divider-right-width: 0;',
					'solid' => '--subtotal-divider-style: solid;',
					'double' => '--subtotal-divider-style: double;',
					'dotted' => '--subtotal-divider-style: dotted;',
					'dashed' => '--subtotal-divider-style: dashed;',
					'groove' => '--subtotal-divider-style: groove;',
				],
			]
		);

		$this->add_responsive_control(
			'subtotal_divider_width',
			[
				'label' => esc_html__( 'Width', 'normahana' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
				'selectors' => [
					'{{WRAPPER}} .widget_shopping_cart_content' => '--subtotal-divider-top-width: {{TOP}}{{UNIT}}; --subtotal-divider-right-width: {{RIGHT}}{{UNIT}}; --subtotal-divider-bottom-width: {{BOTTOM}}{{UNIT}}; --subtotal-divider-left-width: {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'subtotal_divider_color',
			[
				'label' => esc_html__( 'Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .widget_shopping_cart_content' => '--subtotal-divider-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_product_tabs_style',
			[
				'label' => esc_html__( 'Products', 'normahana' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'heading_product_title_style',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Product Title', 'normahana' ),
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'product_title_typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .elementor-menu-cart__product-name a',
			]
		);

		$this->start_controls_tabs( 'product_title_colors' );

		$this->start_controls_tab( 'product_title_normal_colors', [ 'label' => esc_html__( 'Normal', 'normahana' ) ] );

		$this->add_control(
			'product_title_color',
			[
				'label' => esc_html__( 'Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-menu-cart__product-name a' => 'color: {{VALUE}};',

				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'product_title_hover_colors', [ 'label' => esc_html__( 'Hover', 'normahana' ) ] );

		$this->add_control(
			'product_title_hover_color',
			[
				'label' => esc_html__( 'Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-menu-cart__product-name a:hover' => 'color: {{VALUE}};',

				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'heading_product_variations_style',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Variations', 'normahana' ),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'product_variations_color',
			[
				'label' => esc_html__( 'Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--product-variations-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'product_variations_typography',
				'selector' => '{{WRAPPER}} .elementor-menu-cart__product .variation',
			]
		);

		$this->add_control(
			'heading_product_price_style',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Product Price', 'normahana' ),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'product_price_color',
			[
				'label' => esc_html__( 'Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--product-price-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'product_price_typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .elementor-menu-cart__product-price',
			]
		);

		$this->add_control(
			'heading_quantity_title_style',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Quantity', 'normahana' ),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'product_quantity_color',
			[
				'label' => esc_html__( 'Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-menu-cart__product-price .product-quantity' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'product_quantity_typography',
				'selector' => '{{WRAPPER}} .elementor-menu-cart__product-price .product-quantity',
			]
		);

		$this->add_control(
			'heading_product_divider_style',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Divider', 'normahana' ),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'divider_style',
			[
				'label' => esc_html__( 'Style', 'normahana' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'' => esc_html__( 'None', 'normahana' ),
					'solid' => esc_html__( 'Solid', 'normahana' ),
					'double' => esc_html__( 'Double', 'normahana' ),
					'dotted' => esc_html__( 'Dotted', 'normahana' ),
					'dashed' => esc_html__( 'Dashed', 'normahana' ),
					'groove' => esc_html__( 'Groove', 'normahana' ),
				],
				'selectors' => [
					'{{WRAPPER}}' => '--divider-style: {{VALUE}}; --subtotal-divider-style: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'divider_color',
			[
				'label' => esc_html__( 'Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--divider-color: {{VALUE}}; --subtotal-divider-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'divider_width',
			[
				'label' => esc_html__( 'Weight', 'normahana' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', 'custom' ],
				'range' => [
					'px' => [
						'max' => 10,
					],
					'em' => [
						'max' => 1,
					],
					'rem' => [
						'max' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--divider-width: {{SIZE}}{{UNIT}}; --subtotal-divider-top-width: {{SIZE}}{{UNIT}}; --subtotal-divider-right-width: {{SIZE}}{{UNIT}}; --subtotal-divider-bottom-width: {{SIZE}}{{UNIT}}; --subtotal-divider-left-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'divider_gap',
			[
				'label' => esc_html__( 'Spacing', 'normahana' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', 'custom' ],
				'range' => [
					'px' => [
						'max' => 50,
					],
					'em' => [
						'max' => 5,
					],
					'rem' => [
						'max' => 5,
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--product-divider-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_buttons',
			[
				'label' => esc_html__( 'Buttons', 'normahana' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'conditions' => [
					'relation' => 'or',
					'terms' => [
						[
							'name' => 'view_cart_button_show',
							'operator' => '!=',
							'value' => '',
						],
						[
							'name' => 'checkout_button_show',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$this->add_responsive_control(
			'buttons_layout',
			[
				'label' => esc_html__( 'Layout', 'normahana' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'inline' => esc_html__( 'Inline', 'normahana' ),
					'stacked' => esc_html__( 'Stacked', 'normahana' ),
				],
				'default' => 'inline',
				'devices' => [ 'desktop', 'tablet', 'mobile' ],
				'condition' => [
					'view_cart_button_show!' => '',
					'checkout_button_show!' => '',
				],
				'selectors' => [
					'{{WRAPPER}}' => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'inline' => '--cart-footer-layout: 1fr 1fr; --products-max-height-sidecart: calc(100vh - 240px); --products-max-height-minicart: calc(100vh - 385px)',
					'stacked' => '--cart-footer-layout: 1fr; --products-max-height-sidecart: calc(100vh - 300px); --products-max-height-minicart: calc(100vh - 450px)',
				],
			]
		);

		$this->add_responsive_control(
			'space_between_buttons',
			[
				'label' => esc_html__( 'Space Between', 'normahana' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', 'custom' ],
				'range' => [
					'px' => [
						'max' => 50,
					],
					'em' => [
						'max' => 5,
					],
					'rem' => [
						'max' => 5,
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--space-between-buttons: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'view_cart_button_show!' => '',
					'checkout_button_show!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'product_buttons_typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .elementor-menu-cart__footer-buttons .elementor-button',
			]
		);

		$this->add_control(
			'button_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'normahana' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'range' => [
					'px' => [
						'max' => 100,
					],
					'em' => [
						'max' => 10,
					],
					'rem' => [
						'max' => 10,
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--cart-footer-buttons-border-radius: {{SIZE}}{{UNIT}};',
				],
				'separator' => 'after',
			]
		);

		$this->add_control(
			'heading_view_cart_button_style',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'View Cart', 'normahana' ),
				'condition' => [
					'view_cart_button_show!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'view_cart_buttons_typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .elementor-menu-cart__footer-buttons a.elementor-button--view-cart',
				'separator' => 'before',
				'condition' => [
					'view_cart_button_show!' => '',
				],
			]
		);

		$this->start_controls_tabs(
			'view_cart_button_text_colors',
			[
				'condition' => [
					'view_cart_button_show!' => '',
				],
			]
		);

		$this->start_controls_tab(
			'heading_view_cart_button_normal_style',
			[
				'label' => esc_html__( 'Normal', 'normahana' ),
				'condition' => [
					'view_cart_button_show!' => '',
				],
			]
		);

		$this->add_control(
			'view_cart_button_text_color',
			[
				'label' => esc_html__( 'Text Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--view-cart-button-text-color: {{VALUE}};',
				],
				'condition' => [
					'view_cart_button_show!' => '',
				],
			]
		);

		$this->add_control(
			'view_cart_button_background_color',
			[
				'label' => esc_html__( 'Background Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--view-cart-button-background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'heading_view_cart_button_hover_style',
			[
				'label' => esc_html__( 'Hover', 'normahana' ),
				'condition' => [
					'view_cart_button_show!' => '',
				],
			]
		);

		$this->add_control(
			'view_cart_button_hover_text_color',
			[
				'label' => esc_html__( 'Text Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--view-cart-button-hover-text-color: {{VALUE}};',
				],
				'condition' => [
					'view_cart_button_show!' => '',
				],
			]
		);

		$this->add_control(
			'view_cart_button_hover_background',
			[
				'label' => esc_html__( 'Background Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--view-cart-button-hover-background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'view_cart_button_border_hover_color',
			[
				'label' => esc_html__( 'Border Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-menu-cart__footer-buttons .elementor-button--view-cart:hover' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'view_cart_border_border!' => '',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'view_cart_border',
				'selector' => '{{WRAPPER}} .elementor-button--view-cart',
				'separator' => 'before',
				'condition' => [
					'view_cart_button_show!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'view_cart_button_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'normahana' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-menu-cart__footer-buttons a.elementor-button--view-cart' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'view_cart_button_show!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'view_cart_button_box_shadow',
				'selector' => '{{WRAPPER}} .elementor-button--view-cart',
				'condition' => [
					'view_cart_button_show!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'view_cart_button_padding',
			[
				'label' => esc_html__( 'Padding', 'normahana' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
				'selectors' => [
					'{{WRAPPER}}' => '--view-cart-button-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'view_cart_button_show!' => '',
				],
				'separator' => 'after',
			]
		);

		$this->add_control(
			'heading_checkout_button_style',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Checkout', 'normahana' ),
				'condition' => [
					'checkout_button_show!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'cart_checkout_button_typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .elementor-menu-cart__footer-buttons a.elementor-button--checkout',
				'separator' => 'before',
				'condition' => [
					'checkout_button_show!' => '',
				],
			]
		);

		$this->start_controls_tabs(
			'cart_checkout_button_text_colors',
			[
				'condition' => [
					'checkout_button_show!' => '',
				],
			]
		);

		$this->start_controls_tab(
			'heading_cart_checkout_button_normal_style',
			[
				'label' => esc_html__( 'Normal', 'normahana' ),
				'condition' => [
					'checkout_button_show!' => '',
				],
			]
		);

		$this->add_control(
			'checkout_button_text_color',
			[
				'label' => esc_html__( 'Text Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--checkout-button-text-color: {{VALUE}};',
				],
				'condition' => [
					'checkout_button_show!' => '',
				],
			]
		);

		$this->add_control(
			'checkout_button_background_color',
			[
				'label' => esc_html__( 'Background Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--checkout-button-background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'heading_cart_checkout_button_hover_style',
			[
				'label' => esc_html__( 'Hover', 'normahana' ),
				'condition' => [
					'checkout_button_show!' => '',
				],
			]
		);

		$this->add_control(
			'checkout_button_hover_text_color',
			[
				'label' => esc_html__( 'Text Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--checkout-button-hover-text-color: {{VALUE}};',
				],
				'condition' => [
					'checkout_button_show!' => '',
				],
			]
		);

		$this->add_control(
			'checkout_button_hover_background',
			[
				'label' => esc_html__( 'Background Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--checkout-button-hover-background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'checkout_button_border_hover_color',
			[
				'label' => esc_html__( 'Border Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-menu-cart__footer-buttons .elementor-button--checkout:hover' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'checkout_border_border!' => '',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'checkout_border',
				'selector' => '{{WRAPPER}} .elementor-button--checkout',
				'separator' => 'before',
				'condition' => [
					'checkout_button_show!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'view_checkout_button_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'normahana' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-menu-cart__footer-buttons a.elementor-button--checkout' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'checkout_button_show!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'view_checkout_button_box_shadow',
				'selector' => '{{WRAPPER}} .elementor-button--checkout',
				'condition' => [
					'checkout_button_show!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'view_checkout_button_padding',
			[
				'label' => esc_html__( 'Padding', 'normahana' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
				'selectors' => [
					'{{WRAPPER}}' => '--checkout-button-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'checkout_button_show!' => '',
				],
				'separator' => 'after',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_messages',
			[
				'label' => esc_html__( 'Messages', 'normahana' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'cart_empty_message_typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .woocommerce-mini-cart__empty-message',
			]
		);

		$this->add_control(
			'empty_message_color',
			[
				'label' => esc_html__( 'Empty Cart Message Color', 'normahana' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--empty-message-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'empty_message_alignment',
			[
				'label' => esc_html__( 'Alignment', 'normahana' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'normahana' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'normahana' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'normahana' ),
						'icon' => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justified', 'normahana' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--empty-message-alignment: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		if ( ! wp_script_is( 'wc-cart-fragments' ) ) {
			wp_enqueue_script( 'wc-cart-fragments' );
		}

		$this->nh_render_menu_cart( $settings );
	}

	public function render_plain_content() {}

	/**
	 * Render the main menu cart wrapper.
	 */
	private function nh_render_menu_cart( $settings ) {
		if ( null === WC()->cart ) {
			return;
		}

		$widget_cart_is_hidden = apply_filters( 'woocommerce_widget_cart_is_hidden', false );
		$is_edit_mode = \Elementor\Plugin::instance()->editor->is_edit_mode();
		?>
		<div class="elementor-menu-cart__wrapper">
			<?php if ( ! $widget_cart_is_hidden ) : ?>
				<div class="elementor-menu-cart__toggle_wrapper">
					<div class="elementor-menu-cart__container elementor-lightbox" aria-hidden="true">
						<div class="elementor-menu-cart__main" aria-hidden="true">
							<?php $this->nh_render_menu_cart_close_button( $settings ); ?>
							<div class="widget_shopping_cart_content">
								<?php if ( $is_edit_mode ) {
									woocommerce_mini_cart();
								} ?>
							</div>
						</div>
					</div>
					<?php $this->nh_render_menu_cart_toggle_button( $settings ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render the toggle button (cart icon + subtotal + count).
	 */
	private function nh_render_menu_cart_toggle_button( $settings ) {
		if ( null === WC()->cart ) {
			return;
		}
		$product_count = WC()->cart->get_cart_contents_count();
		$sub_total = WC()->cart->get_cart_subtotal();
		$icon = ! empty( $settings['icon'] ) ? $settings['icon'] : 'cart-medium';
		?>
		<div class="elementor-menu-cart__toggle elementor-button-wrapper">
			<a id="elementor-menu-cart__toggle_button" href="#" class="elementor-menu-cart__toggle_button elementor-button elementor-size-sm" aria-expanded="false">
				<span class="elementor-button-text"><?php echo $sub_total; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="elementor-button-icon">
					<span class="elementor-button-icon-qty" data-counter="<?php echo esc_attr( $product_count ); ?>"><?php echo $product_count; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<?php $this->nh_render_menu_icon( $settings, $icon ); ?>
					<span class="elementor-screen-only"><?php echo esc_html__( 'Cart', 'normahana' ); ?></span>
				</span>
			</a>
		</div>
		<?php
	}

	/**
	 * Render the close button for the cart panel.
	 */
	private function nh_render_menu_cart_close_button( $settings ) {
		$has_custom_icon = ! empty( $settings['close_cart_icon_svg']['value'] ) && 'yes' === $settings['close_cart_button_show'];
		$toggle_button_class = 'elementor-menu-cart__close-button';
		if ( $has_custom_icon ) {
			$toggle_button_class .= '-custom';
		}
		?>
		<div class="<?php echo sanitize_html_class( $toggle_button_class ); ?>">
			<?php
			if ( $has_custom_icon ) {
				Icons_Manager::render_icon( $settings['close_cart_icon_svg'], [
					'class' => 'e-close-cart-custom-icon',
					'aria-hidden' => 'true',
				] );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Render the cart icon (built-in or custom).
	 */
	private function nh_render_menu_icon( $settings, string $icon ) {
		if ( ! empty( $settings['icon'] ) && 'custom' === $settings['icon'] ) {
			if ( empty( $settings['menu_icon_svg'] ) ) {
				echo '<i class="fas fa-shopping-cart"></i>';
			} else {
				Icons_Manager::render_icon( $settings['menu_icon_svg'], [
					'class' => 'e-toggle-cart-custom-icon',
					'aria-hidden' => 'true',
				] );
			}
		} else {
			Icons_Manager::render_icon( [
				'library' => 'eicons',
				'value' => 'eicon-' . $icon,
			] );
		}
	}

	public function get_group_name() {
		return 'woocommerce';
	}
}
