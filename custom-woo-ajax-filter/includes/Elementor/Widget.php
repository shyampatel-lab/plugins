<?php

namespace CustomWooAjaxFilter\Elementor;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Widget extends Widget_Base {
	public function get_name(): string {
		return 'cwaf_filter_widget';
	}

	public function get_title(): string {
		return __( 'CWAF Product Filter', 'custom-woo-ajax-filter' );
	}

	public function get_icon(): string {
		return 'eicon-filter';
	}

	public function get_categories(): array {
		return array( 'woocommerce-elements' );
	}

	protected function register_controls(): void {
		$this->start_controls_section( 'content_section', array( 'label' => __( 'Settings', 'custom-woo-ajax-filter' ) ) );
		$this->add_control(
			'filter_set_id',
			array(
				'label' => __( 'Filter Set ID', 'custom-woo-ajax-filter' ),
				'type'  => Controls_Manager::NUMBER,
			)
		);
		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		echo do_shortcode( '[custom_product_filter id="' . absint( $settings['filter_set_id'] ) . '"]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
