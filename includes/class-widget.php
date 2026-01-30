<?php
namespace Stock_Image_Fetcher_Pro;

if (!defined('ABSPATH')) {
    exit;
}

class Widget extends \Elementor\Widget_Base
{

    public function get_name()
    {
        return 'stock_image_fetcher_pro_widget';
    }

    public function get_title()
    {
        return esc_html__('Stock Image Fetcher Pro', 'stock-image-fetcher-pro');
    }

    public function get_icon()
    {
        return 'eicon-image-rollover';
    }

    public function get_categories()
    {
        return ['general'];
    }

    public function get_style_depends()
    {
        return ['stock-image-fetcher-pro-frontend'];
    }

    public function get_keywords()
    {
        return ['image', 'stock', 'pexels', 'fetcher', 'seo', 'optimize'];
    }

    protected function register_controls()
    {

        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__('Stock Image Search', 'stock-image-fetcher-pro'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'selected_image_id',
            [
                'label' => esc_html__('Stock Image Search', 'stock-image-fetcher-pro'),
                'type' => 'stock_image_fetcher_pro_control',
                'label_block' => true,
            ]
        );

        $this->add_control(
            'dynamic_image',
            [
                'label' => esc_html__('Fallback / Dynamic Image', 'stock-image-fetcher-pro'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'dynamic' => [
                    'active' => true,
                ],
                'description' => esc_html__('Use this if you want to override the stock image with a local one or dynamic tag.', 'stock-image-fetcher-pro'),
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'section_style',
            [
                'label' => esc_html__('Image Style', 'stock-image-fetcher-pro'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'image_width',
            [
                'label' => esc_html__('Width', 'stock-image-fetcher-pro'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['%', 'px', 'vw'],
                'range' => [
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => 0,
                        'max' => 2000,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}} .sifp-stock-image' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_height',
            [
                'label' => esc_html__('Height', 'stock-image-fetcher-pro'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'vh', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 2000,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .sifp-stock-image' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'image_object_fit',
            [
                'label' => esc_html__('Object Fit', 'stock-image-fetcher-pro'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '' => esc_html__('Default', 'stock-image-fetcher-pro'),
                    'fill' => esc_html__('Fill', 'stock-image-fetcher-pro'),
                    'cover' => esc_html__('Cover', 'stock-image-fetcher-pro'),
                    'contain' => esc_html__('Contain', 'stock-image-fetcher-pro'),
                ],
                'default' => 'cover',
                'selectors' => [
                    '{{WRAPPER}} .sifp-stock-image' => 'object-fit: {{VALUE}};',
                ],
                'condition' => [
                    'image_height[size]!' => '',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_border_radius',
            [
                'label' => esc_html__('Border Radius', 'stock-image-fetcher-pro'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .sifp-stock-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'image_box_shadow',
                'selector' => '{{WRAPPER}} .sifp-stock-image',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Css_Filter::get_type(),
            [
                'name' => 'image_filters',
                'selector' => '{{WRAPPER}} .sifp-stock-image',
            ]
        );

        $this->start_controls_tabs('image_effects');

        $this->start_controls_tab('normal', [
            'label' => esc_html__('Normal', 'stock-image-fetcher-pro'),
        ]);

        $this->add_control(
            'opacity',
            [
                'label' => esc_html__('Opacity', 'stock-image-fetcher-pro'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 1,
                        'min' => 0.1,
                        'step' => 0.01,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .sifp-stock-image' => 'opacity: {{SIZE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('hover', [
            'label' => esc_html__('Hover', 'stock-image-fetcher-pro'),
        ]);

        $this->add_control(
            'opacity_hover',
            [
                'label' => esc_html__('Opacity', 'stock-image-fetcher-pro'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 1,
                        'min' => 0.1,
                        'step' => 0.01,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}:hover .sifp-stock-image' => 'opacity: {{SIZE}};',
                ],
            ]
        );

        $this->add_control(
            'hover_animation_scale',
            [
                'label' => esc_html__('Hover Scale', 'stock-image-fetcher-pro'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 1.5,
                        'min' => 0.5,
                        'step' => 0.01,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}:hover .sifp-stock-image' => 'transform: scale({{SIZE}});',
                ],
            ]
        );

        $this->add_control(
            'hover_transition',
            [
                'label' => esc_html__('Transition Duration', 'stock-image-fetcher-pro'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 3,
                        'min' => 0,
                        'step' => 0.1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .sifp-stock-image' => 'transition: all {{SIZE}}s ease;',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'glassmorphism_heading',
            [
                'label' => esc_html__('Glassmorphism (2026)', 'stock-image-fetcher-pro'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'backdrop_blur',
            [
                'label' => esc_html__('Backdrop Blur', 'stock-image-fetcher-pro'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 50,
                        'min' => 0,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .sifp-stock-image' => 'backdrop-filter: blur({{SIZE}}px); -webkit-backdrop-filter: blur({{SIZE}}px);',
                ],
            ]
        );

        $this->add_control(
            'overlay_color',
            [
                'label' => esc_html__('Overlay Color', 'stock-image-fetcher-pro'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sifp-image-wrapper' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $image_id = $settings['selected_image_id'];
        $dynamic_image = $settings['dynamic_image'];

        // Use dynamic image if fetcher is empty
        if (empty($image_id) && !empty($dynamic_image['id'])) {
            $image_id = $dynamic_image['id'];
        }

        if (empty($image_id)) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<div class="sifp-placeholder" style="padding: 40px; text-align: center; background: #f9f9f9; border: 2px dashed #ddd; border-radius: 8px;">';
                echo '<i class="eicon-image-rollover" style="font-size: 48px; color: #bbb;"></i>';
                echo '<p style="margin-top: 10px; color: #777;">' . esc_html__('Search and select a stock image', 'stock-image-fetcher-pro') . '</p>';
                echo '</div>';
            }
            return;
        }

        $image_html = wp_get_attachment_image($image_id, 'full', false, [
            'class' => 'sifp-stock-image',
            'loading' => 'lazy'
        ]);

        if ($image_html) {
            echo '<div class="sifp-image-wrapper" style="font-size: 0; line-height: 0;">';
            echo $image_html;
            echo '</div>';
        }
    }
}
