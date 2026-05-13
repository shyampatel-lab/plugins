<?php

namespace CustomWooAjaxFilter\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WidgetRegistrar {
	public function init(): void {
		if ( did_action( 'elementor/loaded' ) ) {
			add_action( 'elementor/widgets/register', array( $this, 'register_widget' ) );
		}
	}

	public function register_widget( $widgets_manager ): void {
		require_once CWAF_PATH . 'includes/Elementor/Widget.php';
		$widgets_manager->register( new Widget() );
	}
}
