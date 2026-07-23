<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class NH_Product_Sorting_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'nh_product_sorting_widget';
	}

	public function get_title() {
		return esc_html__( 'NH Ordenar Productos', 'hello-elementor-child' );
	}

	public function get_icon() {
		return 'eicon-sort';
	}

	public function get_categories() {
		return [ 'nh-widgets' ];
	}

	public function get_keywords() {
		return [ 'sort', 'orderby', 'woocommerce', 'ajax', 'norma hana' ];
	}

	protected function register_controls() {

		// ==========================================
		// PESTAÑA: CONTENIDO
		// ==========================================
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Configuración', 'hello-elementor-child' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'label',
			[
				'label' => esc_html__( 'Etiqueta del Ordenador', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Ordenar por:', 'hello-elementor-child' ),
			]
		);

		$this->add_control(
			'options',
			[
				'label' => esc_html__( 'Opciones de Orden', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => [
					[
						'name' => 'value',
						'label' => esc_html__( 'Valor de Orden (Query Param)', 'hello-elementor-child' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'default' => 'menu_order',
						'options' => [
							'menu_order' => esc_html__( 'Por defecto (Orden de menú)', 'hello-elementor-child' ),
							'popularity' => esc_html__( 'Popularidad (Ventas)', 'hello-elementor-child' ),
							'rating'     => esc_html__( 'Puntuación Media', 'hello-elementor-child' ),
							'date'       => esc_html__( 'Últimos lanzamientos', 'hello-elementor-child' ),
							'price'      => esc_html__( 'Precio: bajo a alto', 'hello-elementor-child' ),
							'price-desc' => esc_html__( 'Precio: alto a bajo', 'hello-elementor-child' ),
						],
					],
					[
						'name' => 'label',
						'label' => esc_html__( 'Texto Visible', 'hello-elementor-child' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => esc_html__( 'Opción', 'hello-elementor-child' ),
					]
				],
				'default' => [
					[ 'value' => 'menu_order', 'label' => esc_html__( 'Por defecto', 'hello-elementor-child' ) ],
					[ 'value' => 'price', 'label' => esc_html__( 'Precio: menor a mayor', 'hello-elementor-child' ) ],
					[ 'value' => 'price-desc', 'label' => esc_html__( 'Precio: mayor a menor', 'hello-elementor-child' ) ],
					[ 'value' => 'date', 'label' => esc_html__( 'Novedades', 'hello-elementor-child' ) ],
					[ 'value' => 'popularity', 'label' => esc_html__( 'Más vendidos', 'hello-elementor-child' ) ],
				],
				'title_field' => '{{{ label }}}',
			]
		);

		$this->end_controls_section();

		// ==========================================
		// PESTAÑA: ESTILO - ETIQUETA
		// ==========================================
		$this->start_controls_section(
			'style_label_section',
			[
				'label' => esc_html__( 'Etiqueta', 'hello-elementor-child' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'label_color',
			[
				'label' => esc_html__( 'Color del Texto', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-sort-label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'label_typography',
				'selector' => '{{WRAPPER}} .nh-sort-label',
			]
		);

		$this->add_responsive_control(
			'label_spacing',
			[
				'label' => esc_html__( 'Espacio a la Derecha', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nh-sort-label' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// PESTAÑA: ESTILO - SELECT / DROPDOWN
		// ==========================================
		$this->start_controls_section(
			'style_select_section',
			[
				'label' => esc_html__( 'Selector (Dropdown)', 'hello-elementor-child' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'select_text_color',
			[
				'label' => esc_html__( 'Color de Texto', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-sort-select' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'select_bg_color',
			[
				'label' => esc_html__( 'Color de Fondo', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-sort-select' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'select_typography',
				'selector' => '{{WRAPPER}} .nh-sort-select',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'select_border',
				'selector' => '{{WRAPPER}} .nh-sort-select',
			]
		);

		$this->add_control(
			'select_border_radius',
			[
				'label' => esc_html__( 'Radio de Borde', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .nh-sort-select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'select_padding',
			[
				'label' => esc_html__( 'Padding', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nh-sort-select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'select_width',
			[
				'label' => esc_html__( 'Ancho', 'hello-elementor-child' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 500,
					],
					'%' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nh-sort-select' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<div class="nh-product-sorting-container">
			<?php if ( ! empty( $settings['label'] ) ) : ?>
				<span class="nh-sort-label"><?php echo esc_html( $settings['label'] ); ?></span>
			<?php endif; ?>
			
			<select id="nh-product-orderby" class="nh-sort-select">
				<?php foreach ( $settings['options'] as $option ) : ?>
					<option value="<?php echo esc_attr( $option['value'] ); ?>">
						<?php echo esc_html( $option['label'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<style>
		.nh-product-sorting-container {
			display: flex;
			align-items: center;
			padding: 10px 0;
			font-family: inherit;
		}
		.nh-sort-label {
			font-size: 0.9rem;
			font-weight: 500;
			margin-right: 10px;
			color: #333;
		}
		.nh-sort-select {
			height: 38px;
			border: 1px solid #dcdcdc;
			border-radius: 4px;
			padding: 0 30px 0 10px;
			font-size: 0.9rem;
			outline: none;
			cursor: pointer;
			background-color: #fff;
			transition: border-color 0.2s, background-color 0.2s, color 0.2s;
			box-sizing: border-box;
			/* Estilo flecha nativo */
			appearance: none;
			background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 8.825L1.175 4 2.59 2.59 6 6l3.41-3.41L10.825 4z'/%3E%3C/svg%3E");
			background-repeat: no-repeat;
			background-position: right 10px center;
		}
		.nh-sort-select:focus {
			border-color: #000;
		}
		</style>

		<script>
		(function() {
			function initProductSorting() {
				const orderbySelect = document.getElementById('nh-product-orderby');
				if (!orderbySelect) return;

				// Leer el parámetro orderby activo del navegador
				const urlParams = new URLSearchParams(window.location.search);
				const currentOrderby = urlParams.get('orderby');
				if (currentOrderby) {
					orderbySelect.value = currentOrderby;
				}

				function loadSortedContent(url) {
					// Elementos a animar opacidad durante la carga
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

							// Actualizar URL y re-enlazar eventos de paginación
							history.pushState(null, '', url);
							bindPaginationLinks();
						})
						.catch(err => {
							console.error('AJAX Sorting error:', err);
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
					loadSortedContent(this.href);
				}

				bindPaginationLinks();

				orderbySelect.addEventListener('change', function() {
					const selectedVal = this.value;
					const currentUrl = new URL(window.location.href);
					
					if (selectedVal && selectedVal !== 'menu_order') {
						currentUrl.searchParams.set('orderby', selectedVal);
					} else {
						currentUrl.searchParams.delete('orderby');
					}

					loadSortedContent(currentUrl.toString());
				});
			}

			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', initProductSorting);
			} else {
				initProductSorting();
			}
		})();
		</script>
		<?php
	}
}
