<?php
/*
Plugin Name: WooCommerce Mycelium Gear
Plugin URI: http://rmweblab.com/
Description: WooCommerce Mycelium Gear Payment Gateway Extends WooCommerce Payment Gateway allow customer to pay using mycelium gear.
Author: Anas
Version: 1.0
Author URI: http://rmweblab.com
Text Domain: woo-mycelium-gear
Domain Path: /languages

Copyright: © 2017 RMWebLab.
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



/**
 * Main WC_MyceliumGear class which sets the gateway up for us
 */
class WC_MyceliumGear {

	/**
	 * Constructor
	 */
	public function __construct() {
		define( 'WC_MYGEAR_VERSION', '1.0' );
		define( 'WC_MYGEAR_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		define( 'WC_MYGEAR_MAIN_FILE', __FILE__ );

		// Actions
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		add_action( 'plugins_loaded', array( $this, 'init' ), 0 );
		add_filter( 'woocommerce_payment_gateways', array( $this, 'register_gateway' ) );
		add_action( 'woocommerce_order_status_on-hold_to_processing', array( $this, 'capture_order' ) );
		add_action( 'woocommerce_order_status_on-hold_to_completed', array( $this, 'capture_order' ) );
		add_action( 'woocommerce_order_status_on-hold_to_cancelled', array( $this, 'cancel_order' ) );
		add_action( 'woocommerce_order_status_on-hold_to_refunded', array( $this, 'cancel_order' ) );
	}

	/**
	 * Add relevant links to plugins page
	 * @param  array $links
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=myceliumgear' ) . '">' . __( 'Settings', 'woo-mycelium-gear' ) . '</a>',
			'<a href="http://rmweblab.com/support">' . __( 'Support', 'woo-mycelium-gear' ) . '</a>',
			'<a href="http://woocommerce.rmweblab.com/woocommerce-mycelium-gear">' . __( 'Docs', 'woo-mycelium-gear' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}

	/**
	 * Init localisations and files
	 */
	public function init() {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}

		// Includes
		require_once( 'inc/class-wc-mycelium-gear-api.php' );

		// Includes
		require_once( 'inc/gateway-mycelium-settings.php' );

		// Includes
		require_once( 'inc/class-wc-gateway-mycelium-gear.php' );

		// Localisation
		load_plugin_textdomain( 'woo-mycelium-gear', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}




	/**
	 * Register the gateway for use
	 */
	public function register_gateway( $methods ) {
			if(class_exists( 'WC_Gateway_MyceliumGear' )){
				$methods[] = 'WC_Gateway_MyceliumGear';
			}
		return $methods;
	}

	/**
	 * Capture payment when the order is changed from on-hold to complete or processing
	 *
	 * @param  int $order_id
	 */
	public function capture_order( $order_id ) {
		$order = new WC_Order( $order_id );

		if ( $order->payment_method == 'myceliumgear' ) {

			//Sent Invoice to customer
			//date_default_timezone_set("UTC");
			//update_post_meta( $order_id, '_invoice_me_status', 'sent' );
			//$date_time = date("Y-m-d H:i:s");
			//update_post_meta( $order_id, '_invoice_me_date', $date_time );

		}
	}

	/**
	 * Cancel pre-auth on cancellation
	 *
	 * @param  int $order_id
	 */
	public function cancel_order( $order_id ) {
		$order = new WC_Order( $order_id );

		if ( $order->payment_method == 'myceliumgear' ) {

			//if(get_post_meta( $order_id, '_invoice_me', true )){

				//clean up invoice me meta value for this order.
				//delete_post_meta($order_id, '_invoice_me_status', 'pending');
				//delete_post_meta($order_id, '_invoice_me', 'yes');
			//}

		}

	}

}

new WC_MyceliumGear();
