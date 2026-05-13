<?php

namespace CustomWooAjaxFilter\Core;

use CustomWooAjaxFilter\Admin\FilterSets;
use CustomWooAjaxFilter\Ajax\FilterEndpoint;
use CustomWooAjaxFilter\Elementor\WidgetRegistrar;
use CustomWooAjaxFilter\Frontend\Assets;
use CustomWooAjaxFilter\Frontend\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {
	public function init(): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		( new FilterSets() )->init();
		( new Assets() )->init();
		( new Shortcode() )->init();
		( new FilterEndpoint() )->init();
		( new WidgetRegistrar() )->init();

		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );
	}

	public function declare_hpos_compatibility(): void {
		if ( class_exists( '\\Automattic\\WooCommerce\\Utilities\\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', CWAF_FILE, true );
		}
	}
}
