<?php
/*
Plugin Name: Anyday WooCommerce
Plugin URI: https://www.anyday.io
Description: A fair and transparent online payment solution for you and your customers
Version: 1.0
Requires at least: 5.2
Requires PHP: 7.1.33
Author: ANYDAY
Author URI: https://github.com/anyday-payments/ANYDAY-WooCommerce/graphs/contributors
License: MIT
License URI: https://opensource.org/licenses/MIT
*/
require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

use Adm\Activator;
use Adm\Deactivator;
use Adm\Core;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	define( 'ADM_VERSION', '1.0' );
	define( 'ADM_PATH', plugin_dir_path( __FILE__ ) );
	define( 'ADM_URL', plugin_dir_url( __FILE__ ) );
	define( 'ADM_PLUGIN_SLUG', "am-wordpress" );
	define( 'ADM_API_BASE_URL', "https://my.anyday.io" );
	define( 'ADM_PLUGIN_BASE_NAME', plugin_basename(__FILE__) );
	define( 'ADM_CURRENCY', "DKK" );

	// Execute code upon plugin activation
	$activator = new Activator;
	register_activation_hook( __FILE__, array( $activator, 'activate' ) );

	// Execute code upon plugin uninstallation
	$deactivator = new Deactivator;
	register_deactivation_hook( __FILE__, array( $deactivator, 'deactivate' ) );

    new Core();

    /**
     * Load the Anyday Payment Gateway
     */
	if( get_option('woocommerce_currency') == ADM_CURRENCY ) {
		add_action( 'plugins_loaded', function() {
			require_once( ADM_PATH . '/includes/classes/WC_Gateway_Anyday_Payment.php');
		});
	}

    /**
     * Load the plugin languages
     */
    add_action( 'plugins_loaded', function(){
		load_plugin_textdomain( 'adm', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	});
}