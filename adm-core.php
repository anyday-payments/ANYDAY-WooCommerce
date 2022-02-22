<?php
/*
Plugin Name: Anyday WooCommerce
Plugin URI: https://www.anyday.io
Description: A fair and transparent online payment solution for you and your customers
Version: 1.7.3
Requires at least: 5.2
Requires PHP: 7.1.33
Author: Anyday
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

	define( 'ADM_VERSION', '1.7.3' );
	define( 'ADM_PATH', plugin_dir_path( __FILE__ ) );
	define( 'ADM_URL', plugin_dir_url( __FILE__ ) );
	define( 'ADM_PLUGIN_SLUG', "am-wordpress" );
	define( 'ADM_API_BASE_URL', "https://my.anyday.io" );
	define( 'ADM_PLUGIN_BASE_NAME', plugin_basename(__FILE__) );
	define( 'ADM_PLUGIN_PATH', plugin_dir_path(__FILE__) );
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
			require_once( ADM_PATH . '/includes/events/AnydayEvent.php');
			require_once( ADM_PATH . '/includes/events/AnydayEventCapture.php');
			require_once( ADM_PATH . '/includes/events/AnydayEventCancel.php');
			require_once( ADM_PATH . '/includes/events/AnydayEventRefund.php');
			require_once( ADM_PATH . '/includes/events/AnydayEventAuthorize.php');
		});
	}

    /**
     * Load the plugin languages
     */
    add_action( 'plugins_loaded', function(){
		load_plugin_textdomain( 'adm', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	});

	add_action( 'upgrader_process_complete', 'de_upgrader_process_complete', 10, 2 );

	function de_upgrader_process_complete( $upgrader_object, $options ) {
		if ( isset( $options['plugins'] ) && is_array( $options['plugins'] ) ) {
			foreach ( $options['plugins'] as $index => $plugin ) {
					if ( strpos( $plugin, 'adm-core.php' ) !== false ) {
						update_option( 'adm_pricetag_js_version', '' );
						break;
					}
			}
		}
	}

	add_filter( 'woocommerce_can_reduce_order_stock', 'adm_do_not_reduce_stock', 10, 2 );

	/**
	 * do not reduce the stock if order status is pending or on-hold.
	 */
	function adm_do_not_reduce_stock( $reduce_stock, $order ) {
			if ( ( $order->has_status( 'pending' ) || $order->has_status( 'on-hold' ) ) && $order->get_payment_method() == 'anyday_payment_gateway' ) {
					$reduce_stock = false;
			}
			return $reduce_stock;
	}

	add_action( 'woocommerce_order_status_changed', 'adm_order_stock_reduction_based_on_status', 20, 4 );

	/**
	 * add stock back whenever order is cancelled or refunded.
	 */
	function adm_order_stock_reduction_based_on_status( $order_id, $old_status, $new_status, $order ){
		if($order->get_payment_method() !== 'anyday_payment_gateway')
			return;
		$stock_reduced = get_post_meta( $order_id, '_order_stock_reduced', true );
		if(empty($stock_reduced) ){
			if ( $new_status == 'processing' || $new_status == 'completed' ){
				wc_reduce_stock_levels($order_id);
			}
		} else {
			if ( $new_status == 'refunded' || $new_status == 'cancelled' ){
				wc_increase_stock_levels($order_id);
			}
		}
	}

	/**
	 * @param string $number
	 * @return float
	 */
	function format_amount($number) {
		$number_string = str_replace(',', '', str_replace('.', '', $number));
		$number = floatval(substr($number_string, 0, strlen($number_string) - 2) . "." . substr($number_string, strlen($number_string) - 2 , strlen($number_string)));
		return $number;
	}
}
