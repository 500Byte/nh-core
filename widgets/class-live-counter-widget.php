<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Elementor_Live_Counter_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'nh_live_counter';
    }

    public function get_title() {
        return esc_html__( 'NH - Contador en Vivo', 'normahana' );
    }

    public function get_icon() {
        return 'eicon-time-line';
    }

    public function get_categories() {
        return [ 'nh-widgets' ];
    }

    public function get_keywords() {
        return [ 'contador', 'vivo', 'viewers', 'personas', 'norma hana', 'live' ];
    }

    public function get_style_depends() {
        return [ 'nh-live-counter' ];
    }

    public function get_script_depends() {
        return [ 'nh-live-counter' ];
    }

    protected function register_controls() {

        // ==========================================
        // TAB: CONTENT
        // ==========================================
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__( 'Configuración', 'normahana' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // --- Number mode (3 options) ---
        $this->add_control(
            'number_mode',
            [
                'label'   => esc_html__( 'Modo de números', 'normahana' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'real'      => esc_html__( 'Real (visitantes activos)', 'normahana' ),
                    'simulated' => esc_html__( 'Simulado (aleatorio)', 'normahana' ),
                    'fixed'     => esc_html__( 'Fijo (estático)', 'normahana' ),
                ],
                'default' => 'real',
            ]
        );

        $this->add_control(
            'fixed_number',
            [
                'label'     => esc_html__( 'Número fijo', 'normahana' ),
                'type'      => \Elementor\Controls_Manager::NUMBER,
                'min'       => 1,
                'max'       => 999,
                'step'      => 1,
                'default'   => 12,
                'condition' => [
                    'number_mode' => 'fixed',
                ],
            ]
        );

        $this->add_control(
            'min_viewers',
            [
                'label'       => esc_html__( 'Mínimo de personas', 'normahana' ),
                'type'        => \Elementor\Controls_Manager::NUMBER,
                'min'         => 1,
                'max'         => 100,
                'step'        => 1,
                'default'     => 5,
                'description' => esc_html__( 'Valor mínimo del contador aleatorio.', 'normahana' ),
                'condition'   => [
                    'number_mode' => 'simulated',
                ],
            ]
        );

        $this->add_control(
            'max_viewers',
            [
                'label'       => esc_html__( 'Máximo de personas', 'normahana' ),
                'type'        => \Elementor\Controls_Manager::NUMBER,
                'min'         => 1,
                'max'         => 100,
                'step'        => 1,
                'default'     => 18,
                'description' => esc_html__( 'Valor máximo del contador aleatorio.', 'normahana' ),
                'condition'   => [
                    'number_mode' => 'simulated',
                ],
            ]
        );

        $this->add_control(
            'fallback_text',
            [
                'label'       => esc_html__( 'Texto mientras carga', 'normahana' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__( 'personas están viendo este producto', 'normahana' ),
                'placeholder' => esc_html__( 'Frase antes de recibir el conteo real...', 'normahana' ),
                'description' => esc_html__( 'Se muestra antes de que el servidor responda con el conteo real.', 'normahana' ),
                'condition'   => [
                    'number_mode' => 'real',
                ],
            ]
        );

        $this->add_control(
            'counter_text',
            [
                'label'       => esc_html__( 'Texto descriptivo', 'normahana' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__( 'personas están viendo este producto', 'normahana' ),
                'placeholder' => esc_html__( 'Escribe la frase...', 'normahana' ),
            ]
        );

        $this->add_control(
            'pulse_color',
            [
                'label'     => esc_html__( 'Color del punto de actividad', 'normahana' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#2ec4b6',
                'selectors' => [
                    '{{WRAPPER}} .nh-live-dot' => 'background-color: {{VALUE}}; color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // ==========================================
        // TAB: STYLE
        // ==========================================
        $this->start_controls_section(
            'section_style',
            [
                'label' => esc_html__( 'Estilo', 'normahana' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // --- Alignment ---
        $this->add_responsive_control(
            'alignment',
            [
                'label'   => esc_html__( 'Alineación', 'normahana' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => esc_html__( 'Izquierda', 'normahana' ),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Centro', 'normahana' ),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'flex-end' => [
                        'title' => esc_html__( 'Derecha', 'normahana' ),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'default'   => 'flex-start',
                'selectors' => [
                    '{{WRAPPER}} .nh-live-counter-wrapper' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        // --- Text color ---
        $this->add_control(
            'text_color',
            [
                'label'     => esc_html__( 'Color de texto', 'normahana' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#555555',
                'selectors' => [
                    '{{WRAPPER}} .nh-live-counter-wrapper' => 'color: {{VALUE}};',
                ],
            ]
        );

        // --- Typography ---
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'text_typography',
                'selector' => '{{WRAPPER}} .nh-live-counter-text',
            ]
        );

        // --- Dot size ---
        $this->add_control(
            'dot_size',
            [
                'label'   => esc_html__( 'Tamaño del punto', 'normahana' ),
                'type'    => \Elementor\Controls_Manager::SLIDER,
                'range'   => [
                    'px' => [
                        'min'  => 4,
                        'max'  => 20,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 8,
                ],
                'selectors' => [
                    '{{WRAPPER}} .nh-live-dot' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // --- Gap between dot and text ---
        $this->add_control(
            'item_gap',
            [
                'label'      => esc_html__( 'Espacio entre punto y texto', 'normahana' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range'      => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 50,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 8,
                ],
                'selectors' => [
                    '{{WRAPPER}} .nh-live-counter-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'divider_style',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
            ]
        );

        // --- Padding ---
        $this->add_responsive_control(
            'widget_padding',
            [
                'label'      => esc_html__( 'Relleno (Padding)', 'normahana' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .nh-live-counter-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // --- Margin ---
        $this->add_responsive_control(
            'widget_margin',
            [
                'label'      => esc_html__( 'Margen (Margin)', 'normahana' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .nh-live-counter-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings  = $this->get_settings_for_display();
        $mode      = $settings['number_mode'] ?? 'real';
        $fixed     = intval( $settings['fixed_number'] ?? 12 );
        $min       = intval( $settings['min_viewers'] ?? 5 );
        $max       = intval( $settings['max_viewers'] ?? 18 );
        $text      = esc_html( $settings['counter_text'] );
        $fallback  = esc_html( $settings['fallback_text'] ?? $text );
        ?>
        <div class="nh-live-counter-wrapper">
            <span class="nh-live-dot"></span>
            <span class="nh-live-counter-text livecounter"
                  data-mode="<?php echo esc_attr( $mode ); ?>"
                  data-fixed="<?php echo esc_attr( $fixed ); ?>"
                  data-min="<?php echo esc_attr( $min ); ?>"
                  data-max="<?php echo esc_attr( $max ); ?>"
                  data-fallback="<?php echo esc_attr( $fallback ); ?>"
            ><?php echo $text; ?></span>
        </div>
        <?php
    }
}
