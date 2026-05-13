<?php

namespace CustomWooAjaxFilter\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {
	public function init(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'register' ) );
	}

	public function register(): void {
		wp_register_style( 'cwaf-style', CWAF_URL . 'assets/css/cwaf.css', array(), CWAF_VERSION );
		wp_register_script( 'cwaf-script', CWAF_URL . 'assets/js/cwaf.js', array(), CWAF_VERSION, true );
		wp_localize_script(
			'cwaf-script',
			'cwafData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'cwaf_nonce' ),
			)
		);
	}
}
