<?php

namespace CustomWooAjaxFilter\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Autoloader {
	public static function register(): void {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	private static function autoload( string $class ): void {
		$prefix = 'CustomWooAjaxFilter\\';
		if ( strpos( $class, $prefix ) !== 0 ) {
			return;
		}

		$relative = str_replace( $prefix, '', $class );
		$path     = CWAF_PATH . 'includes/' . str_replace( '\\', '/', $relative ) . '.php';

		if ( file_exists( $path ) ) {
			require_once $path;
		}
	}
}
