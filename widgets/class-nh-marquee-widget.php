<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NH_Marquee_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'nh_marquee_widget';
	}

	public function get_title() {
		return esc_html__( 'NH Marquesina', 'nh-core' );
	}

	public function get_icon() {
		return 'eicon-t-letter';
	}

	public function get_categories() {
		return [ 'nh-widgets' ];
	}

	public function get_keywords() {
		return [ 'marquee', 'scroll', 'banner', 'ticker', 'norma hana' ];
	}

	public function get_style_depends() {
		return [ 'nh-marquee-widget' ];
	}

	protected function register_controls() {

		// ─── CONTENIDO ────────────────────────────────────────
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Elementos', 'nh-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'items',
			[
				'label' => esc_html__( 'Elementos de la Marquesina', 'nh-core' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => [
					[
						'name' => 'icon',
						'label' => esc_html__( 'Icono', 'nh-core' ),
						'type' => \Elementor\Controls_Manager::ICONS,
						'default' => [
							'value' => 'eicon-globe',
							'library' => 'eicons',
						],
					],
					[
						'name' => 'text',
						'label' => esc_html__( 'Texto', 'nh-core' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => esc_html__( 'Envío gratuito por compras superiores a $280.000', 'nh-core' ),
						'label_block' => true,
					],
				],
				'default' => [
					[
						'icon' => [ 'value' => 'eicon-globe', 'library' => 'eicons' ],
						'text' => esc_html__( 'Envío gratuito por compras superiores a $280.000', 'nh-core' ),
					],
				],
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'separator',
			[
				'label'       => esc_html__( 'Separador', 'nh-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '✦',
				'placeholder' => esc_html__( 'Carácter entre elementos', 'nh-core' ),
			]
		);

		$this->add_control(
			'speed',
			[
				'label' => esc_html__( 'Velocidad (segundos)', 'nh-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'default' => [
					'size' => 25,
					'unit' => 's',
				],
				'range' => [
					's' => [
						'min' => 5,
						'max' => 60,
						'step' => 0.5,
					],
				],
			]
		);

		$this->add_control(
			'rotate_x',
			[
				'label' => esc_html__( 'Rotación 3D (X)', 'nh-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'default' => [
					'size' => 0,
					'unit' => 'deg',
				],
				'range' => [
					'deg' => [
						'min' => -45,
						'max' => 45,
						'step' => 1,
					],
				],
				'description' => esc_html__( 'Inclinación vertical de la marquesina (efecto 3D).', 'nh-core' ),
			]
		);

		$this->add_control(
			'rotate_y',
			[
				'label' => esc_html__( 'Rotación 3D (Y)', 'nh-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'default' => [
					'size' => 0,
					'unit' => 'deg',
				],
				'range' => [
					'deg' => [
						'min' => -45,
						'max' => 45,
						'step' => 1,
					],
				],
				'description' => esc_html__( 'Inclinación horizontal de la marquesina (efecto 3D).', 'nh-core' ),
			]
		);

		$this->end_controls_section();

		// ─── ESTILO: CONTENEDOR ──────────────────────────────
		$this->start_controls_section(
			'style_container_section',
			[
				'label' => esc_html__( 'Contenedor', 'nh-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'bg_color',
			[
				'label' => esc_html__( 'Color de Fondo', 'nh-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-marquee-container' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'padding',
			[
				'label' => esc_html__( 'Relleno', 'nh-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nh-marquee-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// ─── ESTILO: TEXTO ───────────────────────────────────
		$this->start_controls_section(
			'style_text_section',
			[
				'label' => esc_html__( 'Texto', 'nh-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => esc_html__( 'Color del Texto', 'nh-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-marquee-item' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nh-marquee-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'text_typography',
				'selector' => '{{WRAPPER}} .nh-marquee-item, {{WRAPPER}} .nh-marquee-text',
			]
		);

		$this->end_controls_section();

		// ─── ESTILO: SEPARADOR ───────────────────────────────
		$this->start_controls_section(
			'style_separator_section',
			[
				'label' => esc_html__( 'Separador', 'nh-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'separator_color',
			[
				'label' => esc_html__( 'Color del Separador', 'nh-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-marquee-separator' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'separator_size',
			[
				'label' => esc_html__( 'Tamaño del Separador', 'nh-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 12,
						'max' => 48,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nh-marquee-separator' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// ─── ESTILO: ICONO ───────────────────────────────────
		$this->start_controls_section(
			'style_icon_section',
			[
				'label' => esc_html__( 'Icono', 'nh-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label' => esc_html__( 'Color del Icono', 'nh-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nh-marquee-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nh-marquee-icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'icon_size',
			[
				'label' => esc_html__( 'Tamaño del Icono', 'nh-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 12,
						'max' => 48,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nh-marquee-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nh-marquee-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Renderiza un grupo de items con separadores entre ellos.
	 */
	private function render_group( $items, $separator ) {
		$count = count( $items );
		foreach ( $items as $i => $item ) :
			$icon = $item['icon'] ?? [];
			$text = $item['text'] ?? '';
			?>
			<span class="nh-marquee-item">
				<?php if ( ! empty( $icon['value'] ) ) : ?>
					<span class="nh-marquee-icon"><?php \Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] ); ?></span>
				<?php endif; ?>
				<?php if ( $text ) : ?>
					<span class="nh-marquee-text"><?php echo esc_html( $text ); ?></span>
				<?php endif; ?>
			</span>
			<?php if ( $separator !== '' ) : ?>
				<span class="nh-marquee-separator"><?php echo esc_html( $separator ); ?></span>
			<?php endif;
		endforeach;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$items    = $settings['items'] ?? [];
		$speed    = $settings['speed']['size'] ?? 25;
		$rotate_x = $settings['rotate_x']['size'] ?? 0;
		$rotate_y = $settings['rotate_y']['size'] ?? 0;
		$sep      = $settings['separator'] ?? '✦';

		if ( empty( $items ) ) {
			return;
		}
		?>
		<div class="nh-marquee-world" style="--rotate-x: <?php echo esc_attr( $rotate_x ); ?>deg; --rotate-y: <?php echo esc_attr( $rotate_y ); ?>deg;">
			<div class="nh-marquee-stage">
				<div class="nh-marquee-container">
					<div class="nh-marquee-track" style="--nh-marquee-duration: <?php echo esc_attr( $speed ); ?>s;">
						<div class="nh-marquee-group">
							<?php $this->render_group( $items, $sep ); ?>
						</div>
						<div class="nh-marquee-group">
							<?php $this->render_group( $items, $sep ); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	protected function content_template() {
		?>
		<#
		var items = settings.items || [];
		var speed = (settings.speed && settings.speed.size) ? settings.speed.size : 25;
		var rotateX = (settings.rotate_x && settings.rotate_x.size) ? settings.rotate_x.size : 0;
		var rotateY = (settings.rotate_y && settings.rotate_y.size) ? settings.rotate_y.size : 0;
		var sep = settings.separator || '✦';
		
		function renderGroup() {
			var html = '';
			for (var i = 0; i < items.length; i++) {
				html += '<span class="nh-marquee-item">';
				
				if (items[i].icon && items[i].icon.value && items[i].icon.library) {
					var iconClass = '';
					var lib = items[i].icon.library;
					var val = items[i].icon.value;
					
					if (lib === 'eicons') {
						iconClass = 'eicon ' + val;
					} else if (lib === 'fa-solid') {
						iconClass = 'fas ' + val;
					} else if (lib === 'fa-regular') {
						iconClass = 'far ' + val;
					} else if (lib === 'fa-brands') {
						iconClass = 'fab ' + val;
					} else {
						iconClass = val;
					}
					html += '<span class="nh-marquee-icon"><i class="' + iconClass + '"></i></span>';
				}
				
				if (items[i].text) {
					html += '<span class="nh-marquee-text">' + items[i].text + '</span>';
				}
				html += '</span>';
				if (sep) {
					html += '<span class="nh-marquee-separator">' + sep + '</span>';
				}
			}
			return html;
		}
		var groupHtml = renderGroup();
		#>
		<div class="nh-marquee-world" style="--rotate-x: {{rotateX}}deg; --rotate-y: {{rotateY}}deg;">
			<div class="nh-marquee-stage">
				<div class="nh-marquee-container">
					<div class="nh-marquee-track" style="--nh-marquee-duration: {{speed}}s;">
						<div class="nh-marquee-group">{{{ groupHtml }}}</div>
						<div class="nh-marquee-group">{{{ groupHtml }}}</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}