<?php
/*
Plugin Name: WooCommerce EasyPayWay A Bangladeshi Payment Gateway
Plugin URI: http://www.easypayway.com/
Description: Extends WooCommerce EasyPayWay A Bangladeshi Payment Gateway.
Version: 1.4.0
Author: Jm Redwan


    Copyright: © 2009-2013 JMRedwan.
    License: GNU General Public License v3.0
    License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

add_action('plugins_loaded', 'woocommerce_gateway_easypayway_init', 0);
define('easypayway_IMG', WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)) . '/assets/img/');

function woocommerce_gateway_easypayway_init() {
	if ( !class_exists( 'WC_Payment_Gateway' ) ) return;

	/**
 	 * Gateway class
 	 */
	class WC_Gateway_easypayway extends WC_Payment_Gateway {

	     /**
         * Make __construct()
         **/	
		public function __construct(){
		
			$this->id 					= 'easypayway'; // ID for WC to associate the gateway values
			$this->method_title 		= 'EasyPayWay'; // Gateway Title as seen in Admin Dashboad
			$this->method_description	= 'EasyPayWay A Bangladeshi Payment Gateway'; // Gateway Description as seen in Admin Dashboad
			$this->has_fields 			= false; // Inform WC if any fileds have to be displayed to the visitor in Frontend 
			
			$this->init_form_fields();	// defines your settings to WC
			$this->init_settings();		// loads the Gateway settings into variables for WC
						
			// Special settigns if gateway is on Test Mode
			if ( $this->settings['test_mode'] == 'test' ) {
				$test_title 		= ' [TEST MODE]';
				$test_description 	= '<br/><br/><u>This Is Test. Any Order Placed will not Accepted';
				$key_URL				= 'http://sandbox.easypayway.com/payment/index.php';	
				$key_secret			=  $this->settings['key_secret'];
			} else {
				$test_ttitle		= '';
				$test_description	= '';
				$key_URL				= 'http://securepay.easypayway.com/payment/index.php';	
				$key_secret			= $this->settings['key_secret'];
			} //END-{else}-test_mode=yes

			$this->title 			= $this->settings['title'].$test_title; // Title as displayed on Frontend
			$this->description 		= $this->settings['description'].$test_description; // Description as displayed on Frontend
			if ( $this->settings['show_logo'] != "no" ) { // Check if Show-Logo has been allowed
				$this->icon 		= easypayway_IMG . 'logo_' . $this->settings['show_logo'] . '.png';
			}
			$this->merchant_id = $this->settings['merchant_id'];
			 
            $this->key_secret 		= $key_secret;
			$this->liveurl 			= $key_URL;
			
            $this->msg['message']	= '';
            $this->msg['class'] 	= '';
			
			add_action('init', array(&$this, 'check_easypayway_response'));
            add_action('woocommerce_api_' . strtolower(get_class($this)), array($this, 'check_easypayway_response')); //update for woocommerce >2.0

            if ( version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
                    add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) ); //update for woocommerce >2.0
                 } else {
                    add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) ); // WC-1.6.6
                }
            add_action('woocommerce_receipt_easypayway', array(&$this, 'receipt_page'));
		} //END-__construct
		
        /**
         * Initiate Form Fields in the Admin Backend
         **/
		function init_form_fields(){

			$this->form_fields = array(
				// Activate the Gateway
				'enabled' => array(
					'title' 			=> __('Enable/Disable:', 'woo_easypayway'),
					'type' 			=> 'checkbox',
					'label' 			=> __('Enable EasyPayWay', 'woo_easypayway'),
					'default' 		=> 'no',
					'description' 	=> 'Show in the Payment List as a payment option'
				),
				// Title as displayed on Frontend
      			'title' => array(
					'title' 			=> __('Title:', 'woo_easypayway'),
					'type'			=> 'text',
					'default' 		=> __('Online Payments', 'woo_easypayway'),
					'description' 	=> __('This controls the title which the user sees during checkout.', 'woo_easypayway'),
					'desc_tip' 		=> true
				),
				// Description as displayed on Frontend
      			'description' => array(
					'title' 			=> __('Description:', 'woo_easypayway'),
					'type' 			=> 'textarea',
					'default' 		=> __('Pay securely by Credit or Debit card or internet banking through easypayway.', 'woo_easypayway'),
					'description' 	=> __('This controls the description which the user sees during checkout.', 'woo_easypayway'),
					'desc_tip' 		=> true
				),
				// EasyPayWay Merhcant ID
				'merchant_id' => array(
                    'title' => __('Merchant ID', 'Redwan'),
                    'type' => 'text',
                    'description' => __('This id(USER ID) available at EasyPayWay of "email at support@easypayway.com"')),
  				// LIVE Key-Secret
    			'key_secret' => array(
					'title' 			=> __('EasyPayWay Signature Key:', 'woo_easypayway'),
					'type' 			=> 'text',
					'description' 	=> __('Given to Merchant by EasyPayWay'),
					'desc_tip' 		=> true
                ),
  				// Mode of Transaction
      			'test_mode' => array(
					'title' 			=> __('Mode:', 'woo_easypayway'),
					'type' 			=> 'select',
					'label' 			=> __('easypayway Tranasction Mode.', 'woo_easypayway'),
					'options' 		=> array('test'=>'Test Mode','secure'=>'Live Mode'),
					'default' 		=> 'test',
					'description' 	=> __('Mode of easypayway activities'),
					'desc_tip' 		=> true
                ),
  				// Page for Redirecting after Transaction
      			'redirect_page' => array(
					'title' 			=> __('Return Page'),
					'type' 			=> 'select',
					'options' 		=> $this->easypayway_get_pages('Select Page'),
					'description' 	=> __('URL of success page', 'woo_easypayway'),
					'desc_tip' 		=> true
                ),
  				// Show Logo on Frontend
      			'show_logo' => array(
					'title' 			=> __('Show Logo:', 'woo_easypayway'),
					'type' 			=> 'select',
					'label' 			=> __('Enable easypayway TEST Transactions.', 'woo_easypayway'),
					'options' 		=> array('no'=>'No Logo','icon-light'=>'Light - Icon','icon'=>'Dark'),
					'default' 		=> 'no',
					'description' 	=> __('<strong>EasyPayWay (Light)</strong> | Icon: <img src="'. easypayway_IMG . 'logo_icon-light.png" height="24px" /> | Logo: <img src="'. easypayway_IMG . 'logo-light.png" height="24px" /><br/>' . "\n"
										 .'<strong>EasyPayWay Dark&nbsp;&nbsp;</strong> | Icon: <img src="'. easypayway_IMG . 'logo.png" height="24px" /> | Logo: <img src="'. easypayway_IMG . 'logo.png" height="24px" /> | Logo (Full): <img src="'. easypayway_IMG . 'logo.png" height="24px" />', 'woo_easypayway'),
					'desc_tip' 		=> false
                )
			);

		} //END-init_form_fields
		
        /**
         * Admin Panel Options
         * - Show info on Admin Backend
         **/
		public function admin_options(){
			echo '<h3>'.__('EasyPayWay', 'woo_easypayway').'</h3>';
			echo '<p>'.__('Please make a note if you are using ', 'woo_easypayway').'<strong>'.__('"EasyPayWay"', 'woo_easypayway').'</strong>'.__(' or ', 'woo_easypayway').'<strong>'.__('"EasyPayWay"', 'woo_easypayway').'</strong>'.__(' as you main account.', 'woo_easypayway').'</p>';
			echo '<p><small><strong>'.__('Confirm your Mode: Is it LIVE or TEST.').'</strong></small></p>';
			echo '<table class="form-table">';
			// Generate the HTML For the settings form.
			$this->generate_settings_html();
			echo '</table>';
		} //END-admin_options

        /**
         *  There are no payment fields, but we want to show the description if set.
         **/
		function payment_fields(){
			if( $this->description ) {
				echo wpautop( wptexturize( $this->description ) );
			}
		} //END-payment_fields
		
        /**
         * Receipt Page
         **/
		function receipt_page($order){			
			echo '<p><strong>' . __('Thank you for your order.', 'woo_easypayway').'</strong><br/>' . __('The payment page will open soon.', 'woo_easypayway').'</p>';
			echo $this->generate_easypayway_form($order);
		} //END-receipt_page
    
        /**
         * Generate button link
         **/
		function generate_easypayway_form($order_id){
			global $woocommerce;
			$order = new WC_Order( $order_id );

			// Redirect URL
			if ( $this->redirect_page_id == '' || $this->redirect_page == 0 ) {
				$redirect_url = get_site_url() . "/";
			} else {
				$redirect_url = get_permalink( $this->redirect_page );
			}
			// Redirect URL : For WooCoomerce 2.0
			if ( version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
				$redirect_url = add_query_arg( 'wc-api', get_class( $this ), esc_url($this->get_return_url($order)) );
			}

            $productinfo = "Order $order_id";

			$txnid = $order_id;
			
			$current_currency = get_option('woocommerce_currency');
			if ( class_exists( 'Woo_Multi_Currency' && isset($_COOKIE['wmc_current_currency']) ) ) {
				$current_currency = $_COOKIE['wmc_current_currency'];
				/*echo "<pre>" . $current_currency ; die("</pre>");*/
			}

			$easypayway_args = array(
				'store_id' => $this->merchant_id,
                'amount' => $order -> order_total,
				'currency' =>	$current_currency,
				/*'currency' =>	get_option('woocommerce_currency'),	commented by rony*/			
                'tran_id' => $txnid,
                'success_url' => $redirect_url,
				'fail_url' => $redirect_url,
				'ipn_url' => $redirect_url,
				'cancel_url' => $redirect_url,
                'cus_name' => $order -> billing_first_name .' '. $order -> billing_last_name,
                'cus_add1' => $order -> billing_address_1,
                'cus_country' => $order -> billing_country,
                'cus_state' => $order -> billing_state,
                'cus_city' => $order -> billing_city,
                'cus_postcode' => $order -> billing_postcode,
                'cus_phone'=> $order -> billing_phone,
                'cus_email' => $order -> billing_email,
                'ship_name' => $order -> shipping_first_name .' '. $order -> shipping_last_name,
                'ship_add1' => $order -> shipping_address_1,
                'ship_country' => $order -> shipping_country,
                'ship_state' => $order -> shipping_state,
                'delivery_cust_tel' => '',
                'desc' =>  $productinfo,
                'ship_city' => $order -> shipping_city,
                'ship_postcode' => $order -> shipping_postcode,
                'signature_key' =>$this->key_secret
			);
			$easypayway_args_array = array();
			foreach($easypayway_args as $key => $value){
				$easypayway_args_array[] = "<input type='hidden' name='$key' value='$value'/>";
			}
			
			return '	<form action="'.$this->liveurl.'" method="post" id="easypayway_payment_form">
  				' . implode('', $easypayway_args_array) . '
				<input type="submit" class="button-alt" id="submit_easypayway_payment_form" value="'.__('Pay via EasyPayWay', 'woo_easypayway').'" /> <a class="button cancel" href="'.$order->get_cancel_order_url().'">'.__('Cancel order &amp; restore cart', 'woo_easypayway').'</a>
					<script type="text/javascript">
					jQuery(function(){
					jQuery("body").block({
						message: "'.__('Thank you for your order. We are now redirecting you to Payment Gateway to make payment.', 'woo_easypayway').'",
						overlayCSS: {
							background		: "#fff",
							opacity			: 0.6
						},
						css: {
							padding			: 20,
							textAlign		: "center",
							color			: "#555",
							border			: "3px solid #aaa",
							backgroundColor	: "#fff",
							cursor			: "wait",
							lineHeight		: "32px"
						}
					});
					jQuery("#submit_easypayway_payment_form").click();});
					</script>
				</form>';		
		
		} //END-generate_easypayway_form

        /**
         * Process the payment and return the result
         **/
        function process_payment($order_id){
			global $woocommerce;
            $order = new WC_Order($order_id);
			
			if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '>=' ) ) { // For WC 2.1.0
			  $checkout_payment_url = $order->get_checkout_payment_url( true );
			} else {
				$checkout_payment_url = get_permalink( get_option ( 'woocommerce_pay_page_id' ) );
			}
			//echo '<pre>'.$order_id; print_r($order->get_checkout_payment_url( true )); die();
			return array(
				'result' => 'success', 
				'redirect' => add_query_arg(
					'order', 
					$order->id, 
					add_query_arg(
						'key', 
						$order->order_key, 
						$checkout_payment_url						
					)
				)
			);
		} //END-process_payment

        /**
         * Check for valid gateway server callback
         **/
        function check_easypayway_response(){
            global $woocommerce;

			if(isset($_POST['mer_txnid']) && isset($_POST['store_id']))
			{
				$order_id = $_REQUEST['mer_txnid'];
				if($order_id != ''){
					try{
						$order = new WC_Order( $order_id );
						$status = $_REQUEST['pay_status'];
						$risk_level =  $_REQUEST['epw_card_risklevel'];
						$trans_authorised = false;
						
						if( $order->status !=='completed' ){
							
								$status = strtolower($status);
								if($status=="successful" && $risk_level==0){
									$trans_authorised = true;
									$this->msg['message'] = "Thank you for shopping with us. Your account has been charged and your transaction is successful.";
									$this->msg['class'] = 'woocommerce-message';
									if($order->status == 'processing'){
										$order->add_order_note('EasyPayWay ID: '.$_REQUEST['epw_txnid'].' ('.$_REQUEST['mer_txnid'].')<br/>Card Type: '.$_REQUEST['card_type'].'('.$_REQUEST['card_number'].')<br/>Risk Level: '.$risk_level.'');
									}else{
										$order->payment_complete();
										$order->add_order_note('EasyPayWay payment successful.<br/>EasyPayWay ID: '.$_REQUEST['epw_txnid'].' ('.$_REQUEST['mer_txnid'].')<br/>Card Type: '.$_REQUEST['card_type'].'('.$_REQUEST['card_number'].')<br/>Risk Level: '.$risk_level.'');
										$order->update_status('processing');
										$woocommerce->cart->empty_cart();
									}
								}else if($status=="successful" && $risk_level==1){
									$trans_authorised = true;
									$this->msg['message'] = "Thank you for shopping with us. Right now your payment status is pending. EasyPayWay will keep you posted regarding the status of your order through eMail. Please Co-Operate With EasyPayaWay.";
									$this->msg['class'] = 'woocommerce-info';
									$order->add_order_note('EasyPayWay payment On Hold<br/>EasyPayWay ID: '.$_REQUEST['epw_txnid'].' ('.$_REQUEST['mer_txnid'].')<br/>Card Type: '.$_REQUEST['card_type'].'('.$_REQUEST['card_number'].')<br/>Risk Level: '.$risk_level.'');
									$order->update_status('on-hold');
									$woocommerce -> cart -> empty_cart();
								}else{
									$this->msg['class'] = 'woocommerce-error';
									$this->msg['message'] = "Thank you for shopping with us. However, the transaction has been declined.";
									$order->add_order_note('Transaction ERROR: '.$_REQUEST['error'].'<br/>EasyPayWay ID: '.$_REQUEST['epw_txnid'].' ('.$_REQUEST['mer_txnid'].')<br/>Card Type: '.$_REQUEST['card_type'].'('.$_REQUEST['card_number'].')<br/>Risk Level: '.$risk_level.'');
								}
							
							header("Location: ".esc_url($this->get_return_url($order)));
							if($trans_authorised==false){
								$order->update_status('failed');
							}

							//removed for WooCommerce 2.0
							//add_action('the_content', array(&$this, 'easypayway_showMessage'));
						}
					}catch(Exception $e){
                        // $errorOccurred = true;
                        $msg = "Error";
					}
				}

				if ( $this->redirect_page_id == '' || $this->redirect_page_id == 0 ) {
					$redirect_url = esc_url($this->get_return_url($order));
				} else {
					$redirect_url = esc_url($this->get_return_url($order));
				}

				return array(
					'result' => 'success', 
					'redirect' => $redirect_url
				);
	
			}

        } //END-check_easypayway_response





        /**
         * Get Page list from WordPress
         **/
		function easypayway_get_pages($title = false, $indent = true) {
			$wp_pages = get_pages('sort_column=menu_order');
			$page_list = array();
			if ($title) $page_list[] = $title;
			foreach ($wp_pages as $page) {
				$prefix = '';
				// show indented child pages?
				if ($indent) {
                	$has_parent = $page->post_parent;
                	while($has_parent) {
                    	$prefix .=  ' - ';

                    	$next_page = get_post($has_parent);
                    	$has_parent = $next_page->post_parent;
                	}
            	}
            	// add to page list array array
            	$page_list[$page->ID] = $prefix . $page->post_title;
        	}
        	return $page_list;
		} //END-easypayway_get_pages

	} //END-class
	
	/**
 	* Add the Gateway to WooCommerce
 	**/
	function woocommerce_add_gateway_easypayway_gateway($methods) {
		$methods[] = 'WC_Gateway_easypayway';
		return $methods;
	}//END-wc_add_gateway
	
	add_filter('woocommerce_payment_gateways', 'woocommerce_add_gateway_easypayway_gateway' );
	
} //END-init

/**
* 'Settings' link on plugin page
**/
add_filter( 'plugin_action_links', 'easypayway_add_action_plugin', 10, 5 );
function easypayway_add_action_plugin( $actions, $plugin_file ) {
	static $plugin;

	if (!isset($plugin))
		$plugin = plugin_basename(__FILE__);
	if ($plugin == $plugin_file) {

			$settings = array('settings' => '<a href="admin.php?page=wc-settings&tab=checkout&section=wc_gateway_easypayway">' . __('Settings') . '</a>');
		
    			$actions = array_merge($settings, $actions);
			
		}
		
		return $actions;
}//END-settings_add_action_link