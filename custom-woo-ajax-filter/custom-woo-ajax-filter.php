<?php
/**
 * Plugin Name: Custom Woo AJAX Product Filter
 * Description: AJAX-based WooCommerce product filtering with shortcode and Elementor widget support.
 * Version: 1.0.0
 * Author: Custom Dev
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Text Domain: custom-woo-ajax-filter
 * WC requires at least: 8.0
 * WC tested up to: 9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CWAF_VERSION', '1.0.0' );
define( 'CWAF_FILE', __FILE__ );
define( 'CWAF_PATH', plugin_dir_path( __FILE__ ) );
define( 'CWAF_URL', plugin_dir_url( __FILE__ ) );

require_once CWAF_PATH . 'includes/Core/Autoloader.php';

CustomWooAjaxFilter\Core\Autoloader::register();

function cwaf_bootstrap() {
	$plugin = new CustomWooAjaxFilter\Core\Plugin();
	$plugin->init();
}
add_action( 'plugins_loaded', 'cwaf_bootstrap' );
