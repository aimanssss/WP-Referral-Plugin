<?php
/**
 * Plugin Name: Kibgim Referrals
 * Description: Complete referral/affiliate system with seasonal campaigns and WooCommerce integration.
 * Version: 1.0.0
 * Author: OpenAI ChatGPT
 * Text Domain: kibgim-referrals
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// PSR-4 Autoloader using composer or fallback.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    spl_autoload_register( function ( $class ) {
        if ( 0 !== strpos( $class, 'Kibgim\\Referrals\\' ) ) {
            return;
        }
        $path = __DIR__ . '/src/' . str_replace( 'Kibgim\\Referrals\\', '', $class );
        $path = str_replace( '\\', '/', $path ) . '.php';
        if ( file_exists( $path ) ) {
            require_once $path;
        }
    } );
}

use Kibgim\Referrals\Install\Activator;
use Kibgim\Referrals\Services\Plugin;

// Activation hook - install tables.
register_activation_hook( __FILE__, function () {
    Activator::activate();
} );

// Bootstrap plugin.
add_action( 'plugins_loaded', function() {
    Plugin::instance();
} );

