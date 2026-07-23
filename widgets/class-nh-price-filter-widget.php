<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class NH_Price_Filter_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'nh_price_filter_widget';
	}

	public function get_title() {
		return esc_html__( 'NH Filtro de Precio', 'hello-elementor-child' );
	}

	public function get_icon() {
		return 'eicon-filter-search';
	}

	public function get_categories() {
		return [ 'nh-widgets' ];
	}

	public function get_keywords() {
		return [ 'price', 'filter', 'woocommerce', 'ajax', 'norma hana' ];
	}

	protected function register_controls() {

		// ==========================================
		// PESTAÑA: CONTENIDO (Tab Content)
		// ==========================================
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Configuración del Filtro', 'hello-elementor-child' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'title',
			[
				'label' => esc_html__( 'Título del Filtro', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Filtrar por Precio', 'hello-elementor-child' ),
				'placeholder' => esc_html__( 'Escribe un título', 'hello-elementor-child' ),
			]
		);

		$this->add_control(
			'min_label',
			[
				'label' => esc_html__( 'Etiqueta Mínimo', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Mín ($)', 'hello-elementor-child' ),
			]
		);

		$this->add_control(
			'min_placeholder',
			[
				'label' => esc_html__( 'Placeholder Mínimo', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( '0', 'hello-elementor-child' ),
			]
		);

		$this->add_control(
			'max_label',
			[
				'label' => esc_html__( 'Etiqueta Máximo', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Máx ($)', 'hello-elementor-child' ),
			]
		);

		$this->add_control(
			'max_placeholder',
			[
				'label' => esc_html__( 'Placeholder Máximo', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Max', 'hello-elementor-child' ),
			]
		);

		$this->add_control(
			'btn_text',
			[
				'label' => esc_html__( 'Texto del Botón', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Filtrar', 'hello-elementor-child' ),
			]
		);

		$this->add_control(
			'clear_btn_text',
			[
				'label' => esc_html__( 'Texto del Botón Limpiar', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Limpiar', 'hello-elementor-child' ),
			]
		);

		$this->end_controls_section();

		// ==========================================
		// PESTAÑA: ESTILO - TÍTULO (Style Title)
		// ==========================================
		$this->start_controls_section(
			'style_title_section',
			[
				'label' => esc_html__( 'Título', 'hello-elementor-child' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Color del Título', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-filter-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .nh-filter-title',
			]
		);

		$this->add_responsive_control(
			'title_align',
			[
				'label' => esc_html__( 'Alineación', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Izquierda', 'hello-elementor-child' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Centro', 'hello-elementor-child' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Derecha', 'hello-elementor-child' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nh-filter-title' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'title_margin',
			[
				'label' => esc_html__( 'Margen Inferior', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nh-filter-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// PESTAÑA: ESTILO - ETIQUETAS E INPUTS (Style Inputs)
		// ==========================================
		$this->start_controls_section(
			'style_inputs_section',
			[
				'label' => esc_html__( 'Campos de Entrada (Inputs)', 'hello-elementor-child' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'label_heading',
			[
				'label' => esc_html__( 'Etiquetas', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'label_color',
			[
				'label' => esc_html__( 'Color de Etiqueta', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-price-field span' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'label_typography',
				'selector' => '{{WRAPPER}} .nh-price-field span',
			]
		);

		$this->add_control(
			'input_heading',
			[
				'label' => esc_html__( 'Inputs', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'input_text_color',
			[
				'label' => esc_html__( 'Color de Texto', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-price-field input' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'input_placeholder_color',
			[
				'label' => esc_html__( 'Color de Placeholder', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-price-field input::placeholder' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'input_bg_color',
			[
				'label' => esc_html__( 'Color de Fondo', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-price-field input' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'input_typography',
				'selector' => '{{WRAPPER}} .nh-price-field input',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'input_border',
				'selector' => '{{WRAPPER}} .nh-price-field input',
			]
		);

		$this->add_control(
			'input_border_radius',
			[
				'label' => esc_html__( 'Radio de Borde', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .nh-price-field input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'input_padding',
			[
				'label' => esc_html__( 'Relleno (Padding)', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nh-price-field input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'input_focus_border_color',
			[
				'label' => esc_html__( 'Borde al Enfocar (Focus)', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-price-field input:focus' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'separator_color',
			[
				'label' => esc_html__( 'Color del Separador (—)', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-price-separator' => 'color: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		// ==========================================
		// PESTAÑA: ESTILO - BOTÓN FILTRAR (Style Filter Button)
		// ==========================================
		$this->start_controls_section(
			'style_filter_btn_section',
			[
				'label' => esc_html__( 'Botón Filtrar', 'hello-elementor-child' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'btn_typography',
				'selector' => '{{WRAPPER}} .nh-filter-btn',
			]
		);

		$this->start_controls_tabs( 'tabs_btn_style' );

		// Normal
		$this->start_controls_tab(
			'tab_btn_normal',
			[
				'label' => esc_html__( 'Normal', 'hello-elementor-child' ),
			]
		);

		$this->add_control(
			'btn_bg_color',
			[
				'label' => esc_html__( 'Color de Fondo', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-filter-btn' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'btn_text_color',
			[
				'label' => esc_html__( 'Color de Texto', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-filter-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'btn_border',
				'selector' => '{{WRAPPER}} .nh-filter-btn',
			]
		);

		$this->end_controls_tab();

		// Hover
		$this->start_controls_tab(
			'tab_btn_hover',
			[
				'label' => esc_html__( 'Hover', 'hello-elementor-child' ),
			]
		);

		$this->add_control(
			'btn_bg_hover_color',
			[
				'label' => esc_html__( 'Color de Fondo Hover', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-filter-btn:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'btn_text_hover_color',
			[
				'label' => esc_html__( 'Color de Texto Hover', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-filter-btn:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'btn_border_hover_color',
			[
				'label' => esc_html__( 'Color de Borde Hover', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-filter-btn:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'btn_border_radius',
			[
				'label' => esc_html__( 'Radio de Borde', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .nh-filter-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'btn_padding',
			[
				'label' => esc_html__( 'Padding', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nh-filter-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// PESTAÑA: ESTILO - BOTÓN LIMPIAR (Style Clear Button)
		// ==========================================
		$this->start_controls_section(
			'style_clear_btn_section',
			[
				'label' => esc_html__( 'Botón Limpiar', 'hello-elementor-child' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'clear_btn_typography',
				'selector' => '{{WRAPPER}} .nh-filter-clear-btn',
			]
		);

		$this->start_controls_tabs( 'tabs_clear_btn_style' );

		// Normal
		$this->start_controls_tab(
			'tab_clear_btn_normal',
			[
				'label' => esc_html__( 'Normal', 'hello-elementor-child' ),
			]
		);

		$this->add_control(
			'clear_btn_bg_color',
			[
				'label' => esc_html__( 'Color de Fondo', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-filter-clear-btn' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'clear_btn_text_color',
			[
				'label' => esc_html__( 'Color de Texto', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-filter-clear-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'clear_btn_border',
				'selector' => '{{WRAPPER}} .nh-filter-clear-btn',
			]
		);

		$this->end_controls_tab();

		// Hover
		$this->start_controls_tab(
			'tab_clear_btn_hover',
			[
				'label' => esc_html__( 'Hover', 'hello-elementor-child' ),
			]
		);

		$this->add_control(
			'clear_btn_bg_hover_color',
			[
				'label' => esc_html__( 'Color de Fondo Hover', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-filter-clear-btn:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'clear_btn_text_hover_color',
			[
				'label' => esc_html__( 'Color de Texto Hover', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-filter-clear-btn:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'clear_btn_border_hover_color',
			[
				'label' => esc_html__( 'Color de Borde Hover', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-filter-clear-btn:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'clear_btn_border_radius',
			[
				'label' => esc_html__( 'Radio de Borde', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .nh-filter-clear-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'clear_btn_padding',
			[
				'label' => esc_html__( 'Padding', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nh-filter-clear-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<div class="nh-price-filter-container">
			<?php if ( ! empty( $settings['title'] ) ) : ?>
				<h4 class="nh-filter-title"><?php echo esc_html( $settings['title'] ); ?></h4>
			<?php endif; ?>
			
			<div class="nh-price-inputs">
				<div class="nh-price-field">
					<span><?php echo esc_html( $settings['min_label'] ); ?></span>
					<input type="number" id="nh-min-price" placeholder="<?php echo esc_attr( $settings['min_placeholder'] ); ?>" min="0">
				</div>
				<div class="nh-price-separator">—</div>
				<div class="nh-price-field">
					<span><?php echo esc_html( $settings['max_label'] ); ?></span>
					<input type="number" id="nh-max-price" placeholder="<?php echo esc_attr( $settings['max_placeholder'] ); ?>" min="0">
				</div>
			</div>
			
			<div class="nh-filter-actions">
				<button type="button" id="nh-submit-price-filter" class="nh-filter-btn"><?php echo esc_html( $settings['btn_text'] ); ?></button>
				<button type="button" id="nh-clear-price-filter" class="nh-filter-clear-btn" style="display: none;"><?php echo esc_html( $settings['clear_btn_text'] ); ?></button>
			</div>
		</div>

		<style>
		.nh-price-filter-container {
			padding: 10px 0;
			font-family: inherit;
		}
		.nh-filter-title {
			font-size: 1.1rem;
			font-weight: 600;
			margin-bottom: 15px;
			color: #333;
		}
		.nh-price-inputs {
			display: flex;
			align-items: center;
			gap: 8px;
			margin-bottom: 15px;
		}
		.nh-price-field {
			display: flex;
			flex-direction: column;
			flex: 1;
		}
		.nh-price-field span {
			font-size: 0.75rem;
			color: #666;
			margin-bottom: 4px;
		}
		.nh-price-field input {
			width: 100%;
			height: 38px;
			border: 1px solid #dcdcdc;
			border-radius: 4px;
			padding: 0 10px;
			font-size: 0.9rem;
			outline: none;
			box-sizing: border-box;
			transition: border-color 0.2s, background-color 0.2s, color 0.2s;
		}
		.nh-price-field input:focus {
			border-color: #000;
		}
		.nh-price-separator {
			color: #aaa;
			margin-top: 18px;
		}
		.nh-filter-actions {
			display: flex;
			gap: 8px;
		}
		.nh-filter-btn {
			flex: 2;
			height: 38px;
			background-color: #000;
			color: #fff;
			border: none;
			border-radius: 4px;
			font-weight: 500;
			font-size: 0.9rem;
			cursor: pointer;
			transition: background-color 0.2s, color 0.2s, border-color 0.2s;
		}
		.nh-filter-btn:hover {
			background-color: #333;
		}
		.nh-filter-clear-btn {
			flex: 1;
			height: 38px;
			background-color: #f5f5f7;
			color: #333;
			border: 1px solid #dcdcdc;
			border-radius: 4px;
			font-size: 0.85rem;
			cursor: pointer;
			transition: background-color 0.2s, color 0.2s, border-color 0.2s;
		}
		.nh-filter-clear-btn:hover {
			background-color: #e5e5e7;
		}
		</style>

		<script>
		(function() {
			function initPriceFilter() {
				const minInput = document.getElementById('nh-min-price');
				const maxInput = document.getElementById('nh-max-price');
				const submitBtn = document.getElementById('nh-submit-price-filter');
				const clearBtn = document.getElementById('nh-clear-price-filter');

				if (!submitBtn) return;

				const urlParams = new URLSearchParams(window.location.search);
				const currentMin = urlParams.get('min_price');
				const currentMax = urlParams.get('max_price');

				if (currentMin) minInput.value = currentMin;
				if (currentMax) maxInput.value = currentMax;
				if (currentMin || currentMax) {
					if (clearBtn) clearBtn.style.display = 'block';
				}

				function loadFilteredContent(url) {
					const targets = document.querySelectorAll('.elementor-widget-loop-grid, .elementor-widget-jet-listing-grid, .woocommerce-pagination, .jet-filters-pagination, .woocommerce-info');
					targets.forEach(t => t.style.opacity = '0.5');

					fetch(url)
						.then(response => response.text())
						.then(html => {
							const parser = new DOMParser();
							const doc = parser.parseFromString(html, 'text/html');
							
							const selectors = [
								'.elementor-widget-loop-grid', 
								'.elementor-widget-jet-listing-grid', 
								'.woocommerce-pagination', 
								'.jet-filters-pagination',
								'.woocommerce-info'
							];
							
							selectors.forEach(selector => {
								const currentElements = document.querySelectorAll(selector);
								const newElements = doc.querySelectorAll(selector);
								
								currentElements.forEach((el, index) => {
									if (newElements[index]) {
										el.innerHTML = newElements[index].innerHTML;
									} else {
										el.innerHTML = '';
									}
									el.style.opacity = '1';
								});
							});

							history.pushState(null, '', url);
							bindPaginationLinks();
						})
						.catch(err => {
							console.error('AJAX Filter error:', err);
							targets.forEach(t => t.style.opacity = '1');
						});
				}

				function bindPaginationLinks() {
					const pageLinks = document.querySelectorAll('.woocommerce-pagination a, .jet-filters-pagination a');
					pageLinks.forEach(link => {
						link.removeEventListener('click', handlePaginationClick);
						link.addEventListener('click', handlePaginationClick);
					});
				}

				function handlePaginationClick(e) {
					e.preventDefault();
					loadFilteredContent(this.href);
				}

				bindPaginationLinks();

				submitBtn.addEventListener('click', function() {
					const minVal = minInput.value.trim();
					const maxVal = maxInput.value.trim();
					const currentUrl = new URL(window.location.href);
					
					if (minVal !== '' && parseInt(minVal) >= 0) {
						currentUrl.searchParams.set('min_price', minVal);
					} else {
						currentUrl.searchParams.delete('min_price');
					}
					
					if (maxVal !== '' && parseInt(maxVal) >= 0) {
						currentUrl.searchParams.set('max_price', maxVal);
					} else {
						currentUrl.searchParams.delete('max_price');
					}

					loadFilteredContent(currentUrl.toString());
					if (clearBtn) clearBtn.style.display = (minVal || maxVal) ? 'block' : 'none';
				});

				if (clearBtn) {
					clearBtn.addEventListener('click', function() {
						minInput.value = '';
						maxInput.value = '';
						const currentUrl = new URL(window.location.href);
						currentUrl.searchParams.delete('min_price');
						currentUrl.searchParams.delete('max_price');

						loadFilteredContent(currentUrl.toString());
						clearBtn.style.display = 'none';
					});
				}
			}

			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', initPriceFilter);
			} else {
				initPriceFilter();
			}
		})();
		</script>
		<?php
	}
}
