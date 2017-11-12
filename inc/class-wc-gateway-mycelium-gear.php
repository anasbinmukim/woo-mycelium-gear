<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Mycelium Gear Payment Gateway
 *
 * Provides a Mycelium Gear Payment Gateway, mainly for checkout using Mycelium Gear.
 *
 * @class 		WC_Gateway_MyceliumGear
 * @extends		WC_Payment_Gateway
 */
class WC_Gateway_MyceliumGear extends WC_Payment_Gateway {

  /**
   * Constructor for the gateway.
   */
	public function __construct() {
		$this->id                 = 'myceliumgear';
		$this->icon               = apply_filters( 'woo_mygear_logo', plugins_url( 'images/mycelium.png', dirname( __FILE__ ) ) );
		$this->has_fields         = true;
		$this->method_title       = __( 'Mycelium Gear', 'woo-mycelium-gear' );
		$this->method_description = __( 'Mycelium Gear sends customers to Mycelium Gear to complete payment from checkout page.', 'woo-mycelium-gear' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title        = $this->get_option( 'title' );
		$this->description  = $this->get_option( 'description' );
		$this->instructions = $this->get_option( 'instructions', $this->description );
		$this->default_order_status  = $this->get_option( 'default_order_status' );

		//Gateway API credentials
		$this->gateway_id  = $this->get_option( 'gateway_id' );
		$this->gateway_secret  = $this->get_option( 'gateway_secret' );


		// Actions
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		add_action( 'woocommerce_thankyou_invoiceme', array( $this, 'thankyou_page' ) );

		// Customer Emails
		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );

  }

    /**
     * Initialise Gateway Settings Form Fields
     */
    public function init_form_fields() {

		$userroles  = array();

		global $wp_roles;
		$all_roles = $wp_roles->roles;
		$editable_roles = apply_filters('editable_roles', $all_roles);
		foreach ($editable_roles as $role_name => $role_info){
			$userroles[ $role_name ] = $role_info['name'];
		}


    	$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'woo-mycelium-gear' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Mycelium Gear', 'woo-mycelium-gear' ),
				'default' => 'yes'
			),
			'title' => array(
				'title'       => __( 'Title', 'woo-mycelium-gear' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user see during checkout.', 'woo-mycelium-gear' ),
				'default'     => __( 'Mycelium Gear', 'woo-mycelium-gear' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Description', 'woo-mycelium-gear' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see on your checkout.', 'woo-mycelium-gear' ),
				'default'     => __( 'You can proceed payment using Mycelium Gear Wallet.', 'woo-mycelium-gear' ),
				'desc_tip'    => true,
			),
			'instructions' => array(
				'title'       => __( 'Instructions', 'woo-mycelium-gear' ),
				'type'        => 'textarea',
				'description' => __( 'Instructions message that display on checkout confirmation page.', 'woo-mycelium-gear' ),
				'default'     => __( 'Thank you for staying with us.', 'woo-mycelium-gear' ),
				'desc_tip'    => true,
			),
			'default_order_status' => array(
				'title'       => __( 'Order Status', 'woo-mycelium-gear' ),
				'type'        => 'select',
				'description' => __( 'Choose immediate order status at customer checkout.', 'woo-mycelium-gear' ),
				'default'     => 'sale',
				'desc_tip'    => true,
				'options'     => array(
					'on-hold'          => __( 'On Hold', 'woo-mycelium-gear' ),
					'processing' => __( 'Processing', 'woo-mycelium-gear' ),
					'completed' => __( 'Completed', 'woo-mycelium-gear' )
				)
			),
			'api_details' => array(
				'title'       => __( 'API credentials', 'woo-mycelium-gear' ),
				'type'        => 'title',
				'description' => sprintf( __( 'Enter your Mycelium Gear API credentials to process payment. Learn how to access your <a target="_blank" href="%s">Mycelium Gear</a>.', 'woo-mycelium-gear' ), 'https://admin.gear.mycelium.com/gateways' ),
			),
			'gateway_id' => array(
				'title'       => __( 'Gateway ID', 'woo-mycelium-gear' ),
				'type'        => 'text',
				'description' => __( 'Get your Gateway ID from Mycelium Gear.', 'woo-mycelium-gear' ),
				'default'     => '',
				'desc_tip'    => true,
				'placeholder' => __( 'Required', 'woo-mycelium-gear' ),
			),
			'gateway_secret' => array(
				'title'       => __( 'Gateway Secret', 'woo-mycelium-gear' ),
				'type'        => 'text',
				'description' => __( 'Get your Gateway Secret from Mycelium Gear.', 'woo-mycelium-gear' ),
				'default'     => '',
				'desc_tip'    => true,
				'placeholder' => __( 'Required', 'woo-mycelium-gear' ),
			)
		);
    }

    /**
     * Output for the order received page.
     */
		public function thankyou_page() {
			if ( $this->instructions )
	        	echo wpautop( wptexturize( $this->instructions ) );
		}


    /**
     * Add content to the WC emails.
     *
     * @access public
     * @param WC_Order $order
     * @param bool $sent_to_admin
     * @param bool $plain_text
     */
		public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
	    if ( $this->instructions && ! $sent_to_admin && 'myceliumgear' === $order->payment_method && $order->has_status( $this->default_order_status ) ) {
				echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
			}
		}

		/**
     * Add Mycelium Payment button
     *
     * @param int $order_id
     * @return array
     */
		public function payment_fields(){
			if ( $this->description )
						echo wpautop( wptexturize( $this->description ) );
			?>
			<script type="text/javascript">
					jQuery(document).ready(function($) {
						jQuery('.wc_payment_methods input[name="payment_method"]').change(function(){
								var current_button_text = jQuery('.woocommerce-checkout-payment #place_order').val();
				        if(jQuery('#payment_method_myceliumgear').prop('checked')){
										jQuery(".woocommerce-checkout-payment #place_order").val("Proceed To Mycelium Gear");
				        }
				    });
					});
			</script>
			<?php
		}

		/**
     * Process call back result after mycellium payment
     *
     * @return void
     */
		public function process_mycelium_order(){
			global $woocommerce;
			$woo_order_id = '';
			$mycelium_gear = new WC_Mycelium_Gear_API($this->gateway_id, $this->gateway_secret);
			$mycelium_check_order = $mycelium_gear->check_order_callback();
			if ($mycelium_check_order !== FALSE) {
				//If full paid
				if(isset($mycelium_check_order['status']) && ($mycelium_check_order['status'] == 2)){
						$gear_order_id = $mycelium_check_order['order_id'];
						$gear_callback = $mycelium_check_order['callback_data'];
						//if callback have data
						if($gear_callback != ''){
								$gear_callback_ar = explode('_', $gear_callback);
								if(isset($gear_callback_ar[1])){
									$woo_order_id = $gear_callback_ar[1];
								}
						}

						//If call back order ID and database store ID same
						if(($woo_order_id !== '')){
							$woo_order = new WC_Order( $woo_order_id );
							if($woo_order->payment_complete()){
									$woo_order->add_order_note( __('Mycelium gear payment completed', 'woo-mycelium-gear') );
							}
						}
				}
			}

			///echo 'Yes, I am alive...';

		}

    /**
     * Process the payment and return the result
     *
     * @param int $order_id
     * @return array
     */
		public function process_payment( $order_id ) {

			$order = wc_get_order( $order_id );

			$order_id    = (true === version_compare(WOOCOMMERCE_VERSION, '3.0', '<')) ? $order->id          : $order->get_id();
			$userID      = (true === version_compare(WOOCOMMERCE_VERSION, '3.0', '<')) ? $order->user_id     : $order->get_user_id();
			$order_total = (true === version_compare(WOOCOMMERCE_VERSION, '3.0', '<')) ? $order->order_total : $order->get_total();

			//woocommerce store order id
			$callback_data = 'wooorderid_'.$order_id;

			$mycelium_gear = new WC_Mycelium_Gear_API($this->gateway_id, $this->gateway_secret);
			$mycelium_order = $mycelium_gear->create_order($order_total, $order_id, $callback_data);
			//$order = $geary->check_order($payment_id);

			if ($mycelium_order->payment_id) {
				// Mark as on-hold (we're awaiting shop manager approval)
				$order->update_status( $this->default_order_status, __( 'Awaiting Mycelium Payment', 'woo-mycelium-gear' ) );

				// Reduce stock levels
				$order->reduce_order_stock();

				// Remove cart
				WC()->cart->empty_cart();

				//track order gear payment id
				update_post_meta( $order_id, '_mygear_order_id', $mycelium_order->id );
				update_post_meta( $order_id, '_mygear_address', $mycelium_order->address );

		    $redirect_to_payment_url = "https://gateway.gear.mycelium.com/pay/{$mycelium_order->payment_id}";

				// Return thankyou redirect
				return array(
					'result' 	=> 'success',
					'redirect'	=> $redirect_to_payment_url
				);
			}else{
				// Return thankyou redirect
				return array(
					'result' 	=> 'error',
					'redirect'	=> $this->get_return_url( $order )
				);
			}

		}
}
