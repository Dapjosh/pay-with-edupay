<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       cloudpitcher.com
 * @since      1.0.0
 *
 * @package    Pay_With_Edupay
 * @subpackage Pay_With_Edupay/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Pay_With_Edupay
 * @subpackage Pay_With_Edupay/public
 * @author     CloudPitcher <support@cloudpitcher.com>
 */
if( ! defined( 'ABSPATH' ) ) { exit; }

require_once(FLW_WC_DIR_PATH. '.env.php');

define("BASEPATH", 1);
/* define('PUBLIC_KEY', '');
define('SECRET_KEY',''); */

require_once( FLW_WC_DIR_PATH . 'flutterwave-rave-php-sdk/lib/rave.php' );
require_once( FLW_WC_DIR_PATH . 'includes/eventHandler.php' );
    
use Flutterwave\Rave;
class Pay_With_Edupay_WooCommerce_Gateway extends WC_Payment_Gateway {
  

	public function __construct() {
        // $this->plugin_name = $plugin_name;
        // $this->version = $version;
        // $this->base_url = 'https://api.ravepay.co';
        $this->base_url = 'https://ravesandboxapi.flutterwave.com';
        $this->id = 'edupay';
        $this->country='';
        //$this->icon= plugins_url('public/img/edupay-logo.jpg', FLW_WC_PLUGIN_FILE);//URL to icon 
        $this->has_fields= true;
        
        $this->method_title= 'EduPay Gateway';
        $this->method_description = 'Students purchase from this store at a discounted price';
        $this->supports = array(
            'products'
        );
        $this->supports = array( 'default_credit_card_form' );
        $this->init_form_fields();

        $this->init_settings();
        $this->modal_logo=$this->get_option('modal_logo');
        $this->title= $this->get_option('title');
        $this->discount = $this->get_option('discount');
        $this->description = $this->get_option('description');
        $this->enabled= $this->get_option('enabled');
        $this->testmode = 'yes' === $this->get_option('testmode');
        $this->payment_options = $this->get_option( 'payment_options' );
        $this->public_key = PUBLIC_KEY;

        $this->secret_key= SECRET_KEY;

        //add_action( 'admin_notices', array( $this, 'admin_notices' ) );
        //add_action( 'woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
        //add_action( 'woocommerce_api_pay_with_edupay_woocommerce_gateway', array($this, 'edp_verify_payment'));

        // Webhook listener/API hook
        //add_action( 'woocommerce_api_edp_wc_payment_webhook', array($this, 'edp_rave_webhooks'));
      
        /* if ( is_admin() ) {
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }
        $this->load_scripts(); */
        // $this->load_scripts();
        //add_action( 'woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
        add_action('woocommerce_update_options_payment_gateways_'.$this->id, array($this,'process_admin_options'));
        add_action('wp_enqueue_scripts', array($this,'payment_scripts'));
    }
    public function edupay_gateway_class($methods){
        $methods[] = 'Pay_With_Edupay_WooCommerce_Gateway';
        return methods;
    }
    public function init_form_fields(){
 
        $this->form_fields = array(
            'enabled' => array(
                'title'       => __('Enable/Disable', 'edp_payments'),
                'label'       => __('Enable EduPay Gateway','edp_payments'),
                'type'        => 'checkbox',
                'description' => __('Students purchase from this store at a discounted price','edp_payments'),
                'default'     => 'no'
            ),
            'modal_logo' => array(
                'title' => __('Logo','edp_payments'),
                'type' => 'text',
                'description' => __('Please provide the link to your logo', 'edp_payments'),
            ),
            'discount' => array(
                'title' => __('Discount','edp_payments'),
                'type' => 'number',
                'description' => __('Please provide your discount in percentage(e.g 2 which represents 2%)', 'edp_payments'),
            ),
            'title' => array(
                'title'       => 'Payment Method Title',
                'type'        => 'text',
                'description' => 'This controls the title which the user sees during checkout.',
                'default'     => 'EduPay',
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => 'Payment method Description',
                'type'        => 'textarea',
                'description' => 'This controls the description which the user sees during checkout.',
                'default'     => 'Pay with your credit card via our super-cool payment gateway.',
            ),
            'testmode' => array(
                'title'       => 'Test mode',
                'label'       => 'Enable Test Mode',
                'type'        => 'checkbox',
                'description' => 'Place the payment gateway in test mode using test API keys.',
                'default'     => 'yes',
                'desc_tip'    => true,
            ),
            'webhook' => array(
                'title'       => __( 'Webhook Instruction', 'edp_payments' ),
                'type'        => 'hidden',
                'description' => __( 'Please copy this webhook URL and paste on the webhook section on your dashboard <strong style="color: red"><pre><code>'.WC()->api_request_url('Edp_WC_Payment_Webhook').'</code></pre></strong> (<a href="https://rave.flutterwave.com/dashboard/settings/webhooks" target="_blank">Rave Account</a>)', 'edp_payments' ),
              ),
            'payment_options' => array(
                'title'       => __( 'Payment Options', 'edp_payments' ),
                'type'        => 'select',
                'description' => __( 'Optional - Choice of payment method to use. Card, Account etc.', 'edp_payments' ),
                'options'     => array(
                  '' => esc_html_x( 'Default', 'payment_options', 'edp_payments' ),
                  'card'  => esc_html_x( 'Card Only',  'payment_options', 'edp_payments' ),
                  'account'  => esc_html_x( 'Account Only',  'payment_options', 'edp_payments' ),
                  'ussd'  => esc_html_x( 'USSD Only',  'payment_options', 'edp_payments' ),
                  'qr'  => esc_html_x( 'QR Only',  'payment_options', 'edp_payments' ),
                  'mpesa'  => esc_html_x( 'Mpesa Only',  'payment_options', 'edp_payments' ),
                  'mobilemoneyghana'  => esc_html_x( 'Ghana MM Only',  'payment_options', 'edp_payments' ),
                ),
                'default'     => ''
              ),
        );
    }
    public function payment_scripts(){
      if($this->id!='edupay'){
        $this->enabled = 'no';
      }
      
      if(!is_cart() && is_checkout() && isset($_GET['pay_for_order'])){
        return;
      }
      
      if('no'=== $this->enabled){
        return;
      }
      if(empty($this->public_key)|| empty($this->secret_key)){
        return;
      }
      if(!$this->testmode && !is_ssl()){
        return;
      }
    
    wp_register_script('woocommerce_edp',plugins_url( 'public/js/pay-with-edupay-public.js', FLW_WC_PLUGIN_FILE ),array( 'jquery' ), '1.0.0', true );
    wp_localize_script('woocommerce_edp','edp_payment_args',array('public_key'=>$this->public_key));
    wp_enqueue_script( 'woocommerce_edp' ); 
     
    } 
    public function getKey($seckey){
      $hashedkey = md5($seckey);
      $hashedkeylast12 = substr($hashedkey, -12);
    
      $seckeyadjusted = str_replace("FLWSECK-", "", $seckey);
      $seckeyadjustedfirst12 = substr($seckeyadjusted, 0, 12);
    
      $encryptionkey = $seckeyadjustedfirst12.$hashedkeylast12;
      return $encryptionkey;
  
    }
    public function encrypt3Des($data, $key)
    {
        $encData = openssl_encrypt($data, 'DES-EDE3', $key, OPENSSL_RAW_DATA);
        return base64_encode($encData);
    }
   
    public function process_payment($order_id) {
      global $woocommerce;
      
      $p_key= $this->public_key;
      $secret_key = $this->secret_key; 
      
  
      $order = wc_get_order( $order_id );
      $cb_url = WC()->api_request_url( 'Pay_With_Edupay_WooCommerce_Gateway' ).'?rave_id='.$order_id;
      $card_no = $_POST['edupay-card-number'];
      $card_cvv = $_POST['edupay-card-cvc'];
      $card_exp_date = $_POST['edupay-card-expiry'];
      $explode_date = explode("/",$card_exp_date);
      $card_exp_month = $explode_date[0];
      $card_exp_year = $explode_date[1]; 

      $txnref    = "WOOC_" . $order_id . '_' . time();
      $txnref    = filter_var($txnref, FILTER_SANITIZE_STRING);
      $amount    = $order->get_total();
      $first_name = $order->billing_first_name;
      $last_name = $order->billing_last_name;
      $email     = $order->get_billing_email();
      $currency     = $order->get_currency();
      $main_order_key = $order->get_order_key();
      switch ($currency) {
        case 'KES':
          $this->country = 'KE';
          break;
        case 'GHS':
          $this->country = 'GH';
          break;
        case 'ZAR':
          $this->country = 'ZA';
          break;
        case 'TZS':
          $this->country = 'TZ';
          break;
        
        default:
          $this->country = 'NG';
          break;
    }
    
    $country  = $this->country;
   
      $data = array(
      'PBFPubKey' => $p_key,
      'cardno' => $card_no,
      'currency' => $currency,
      'country' => $country,
      'cvv' => $card_cvv,
      'amount' => $amount,
      'expiryyear' => $card_exp_year,
      'expirymonth' => $card_exp_month,
      'email' => $email,
      'IP' => $_SERVER['REMOTE_ADDR'],
      'txRef' => $txnref);
      
      $key = $this->getKey($secret_key);
      $dataReq = json_encode($data);
      $post_enc = $this->encrypt3Des($dataReq,$key);
      $post_data = array(
        'PBFPubKey' => $p_key,
        'client' => $post_enc,
        'alg' => '3DES-24',
      );
      $response = wp_remote_post($this->base_url.'/flwv3-pug/getpaidx/api/charge', $post_data);
      var_dump($response);
      die();
      if( !is_wp_error( $response ) ) {
 
        $body = json_decode( $response['body'], true );
    
        // it could be different depending on your payment processor
        if ( $body['response']['responseCode'] == 'APPROVED' ) {
          
      
          var_dump($body['response']['responseCode']);
          die();
         // we received the payment
         //$order->payment_complete();
         //$order->reduce_order_stock();
    
         // some notes to customer (replace true with false to make it private)
         //$order->add_order_note( 'Hey, your order is paid! Thank you!', true );
    
         // Empty cart
         //$woocommerce->cart->empty_cart();
    
         // Redirect to the thank you page
         /* return array(
           'result' => 'success',
           'redirect' => $this->get_return_url( $order )
         );
     */
        } else {
         wc_add_notice(  'Please try again.', 'error' );
         return;
       }
    
     } else {
       wc_add_notice(  'Connection error.', 'error' );
       return;
     }
    }
    /* public function payment_fields() {
 
      // ok, let's display some description before the payment form
      if ( $this->description ) {
        // you can instructions for test mode, I mean test card numbers etc.
        if ( $this->testmode ) {
          $this->description .= ' TEST MODE ENABLED. In test mode, you can use the card numbers listed in <a href="#" target="_blank" rel="noopener noreferrer">documentation</a>.';
          $this->description  = trim( $this->description );
        }
        // display the description with <p> tags etc.
        echo wpautop( wp_kses_post( $this->description ) );
      }
     
      // I will echo() the form, but you can close PHP tags and print it directly in HTML
      echo '<fieldset id="wc-' . esc_attr( $this->id ) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';
     
      // Add this action hook if you want your custom payment gateway to support it
      do_action( 'woocommerce_credit_card_form_start', $this->id );
     
      // I recommend to use inique IDs, because other gateways could already use #ccNo, #expdate, #cvc
      echo '<div class="form-row form-row-wide"><label>Card Number <span class="required">*</span></label>
        <input id="edupay_ccNo" type="text" autocomplete="off">
        </div>
        <div class="form-row form-row-first">
          <label>Expiry Date <span class="required">*</span></label>
          <input id="edupay_expdate" type="text" autocomplete="off" placeholder="MM / YY">
        </div>
        <div class="form-row form-row-last">
          <label>Card Code (CVC) <span class="required">*</span></label>
          <input id="edupay_cvv" type="password" autocomplete="off" placeholder="CVC">
        </div>

        <div class="clear"></div>';
     
      do_action( 'woocommerce_credit_card_form_end', $this->id );
     
      echo '<div class="clear"></div></fieldset>';
     
    } */
    public function validate_fields(){
 
      if( empty( $_POST[ 'billing_first_name' ]) ) {
        wc_add_notice(  'First name is required!', 'error' );
        return false;
      }
      return true;
     
    }
    
      public function admin_notices() {

        if ( 'no' == $this->enabled ) {
          return;
        }
        if(!$this->testmode && ! is_ssl()){
            return;
        }
  
        /**
         * Check if public key is provided
         */
        if ( ! $this->public_key || ! $this->secret_key ) {
          $mode = ('yes' === $this->testmode) ? 'test' : 'live';
          echo '<div class="error"><p>';
          echo sprintf(
            'Provide your '.$mode .' public key and secret key <a href="%s">here</a> to be able to use the Rave Payment Gateway plugin. If you don\'t have one, kindly sign up at <a href="https://rave.flutterwave.com" target="_blank>https://rave.flutterwave.com</a>, navigate to the settings page and click on API.',
             admin_url( 'admin.php?page=wc-settings&tab=checkout&section=rave' )
           );
          echo '</p></div>';
          return;
        }
      }
      public function receipt_page( $order ) {

        $order = wc_get_order( $order );
        
        echo '<p>'.__( 'Thank you for your order, please click the <b>Make Payment</b> button below to make payment.Your student discount will automatically deducted. You will be redirected to a secure page where you can enter you card details or bank account details. <b>Please, do not close your browser at any point in this process.</b>', 'edp_payments' ).'</p>';
        echo '<a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">';
        echo __( 'Cancel order &amp; restore cart', 'edp_payments' ) . '</a> ';
        echo '<button class="button alt  wc-forward" id="edp-pay-now-button">Make Payment</button> ';
        
      }
    
      public function edp_verify_payment() {
           
        $publicKey = $this->public_key; 
        $secretKey = $this->secret_key; 
  
        // if($this->go_live === 'yes'){
        //   $env = 'live';
        // }else{
        //   $env = 'staging';
        // }
        $overrideRef = true;
          
        if(isset($_GET['rave_id']) && urldecode( $_GET['rave_id'] )){
          $order_id = urldecode( $_GET['rave_id'] );
          
          if(!$order_id){
            $order_id = urldecode( $_GET['order_id'] );
          }
          $order = wc_get_order( $order_id );
          
          $redirectURL =  WC()->api_request_url( 'Pay_With_Edupay_WooCommerce_Gateway' ).'?order_id='.$order_id;
          
          $ref = uniqid("WOOC_". $order_id."_".time()."_");
          
          $payment = new Rave($publicKey, $secretKey, $ref, $overrideRef);
          
          // if($this->modal_logo){
          //   $rave_m_logo = $this->modal_logo;
          // }
  
          //set variables
          $modal_desc = $this->description != '' ? filter_var($this->description, FILTER_SANITIZE_STRING) : "Payment for Order ID: $order_id on ". get_bloginfo('name');
          $modal_title = $this->title != '' ? filter_var($this->title, FILTER_SANITIZE_STRING) : get_bloginfo('name');
          
          // Make payment
          $payment
          ->eventHandler(new myEventHandler($order))
          ->setAmount($order->get_total())
          ->setPaymentOptions($this->payment_options) // value can be card, account or both
          ->setDescription($modal_desc)
          ->setTitle($modal_title)
          ->setCountry($this->country)
          ->setCurrency($order->get_currency())
          ->setEmail($order->get_billing_email())
          ->setFirstname($order->get_billing_first_name())
          ->setLastname($order->get_billing_last_name())
          ->setPhoneNumber($order->get_billing_phone())
          ->setRedirectUrl($redirectURL)
          // ->setMetaData(array('metaname' => 'SomeDataName', 'metavalue' => 'SomeValue')) // can be called multiple times. Uncomment this to add meta datas
          // ->setMetaData(array('metaname' => 'SomeOtherDataName', 'metavalue' => 'SomeOtherValue')) // can be called multiple times. Uncomment this to add meta datas
          ->initialize(); 
          die();
        }else{
          if(isset($_GET['cancelled']) && isset($_GET['order_id'])){
            if(!$order_id){
              $order_id = urldecode( $_GET['order_id'] );
            }
            $order = wc_get_order( $order_id );
            $redirectURL = $order->get_checkout_payment_url( true );
            header("Location: ".$redirectURL);
            die(); 
          }
          
          if ( isset( $_POST['txRef'] ) || isset($_GET['txref']) ) {
              $txn_ref = isset($_POST['txRef']) ? $_POST['txRef'] : urldecode($_GET['txref']);
              $o = explode('_', $txn_ref);
              $order_id = intval( $o[1] );
              $order = wc_get_order( $order_id );
              $payment = new Rave($publicKey, $secretKey, $txn_ref, $overrideRef);
          
              $payment->logger->notice('Payment completed. Now requerying payment.');
              
              $payment->eventHandler(new myEventHandler($order))->requeryTransaction(urldecode($txn_ref));
              
              $redirect_url = $this->get_return_url( $order );
              header("Location: ".$redirect_url);
              die(); 
          }else{
            $payment = new Rave($publicKey, $secretKey, $txn_ref, $overrideRef);
          
            $payment->logger->notice('Error with requerying payment.');
            
            $payment->eventHandler(new myEventHandler($order))->doNothing();
              die();
          }
        }
      }
      public function edp_rave_webhooks() {

        // Retrieve the request's body
        $body = @file_get_contents("php://input");
  
        // retrieve the signature sent in the request header's.
        $signature = (isset($_SERVER['HTTP_VERIF_HASH']) ? $_SERVER['HTTP_VERIF_HASH'] : '');
  
        /* It is a good idea to log all events received. Add code *
        * here to log the signature and body to db or file       */
  
        if (!$signature) {
            // only a post with rave signature header gets our attention
            exit();
        }
  
        // Store the same signature on your server as an env variable and check against what was sent in the headers
        $local_signature = $this->get_option('secret_hash');
  
        // confirm the event's signature
        if( $signature !== $local_signature ){
          // silently forget this ever happened
          exit();
        }
        sleep(10);
  
        http_response_code(200); // PHP 5.4 or greater
        // parse event (which is json string) as object
        // Give value to your customer but don't give any output
        // Remember that this is a call from rave's servers and 
        // Your customer is not seeing the response here at all
        $response = json_decode($body);
        if ($response->status == 'successful') {
  
          $getOrderId = explode('_', $response->txRef);
          $orderId = $getOrderId[1];
          $order = wc_get_order( $orderId );
        //   $order = new WC_Order($orderId);
  
          if ($order->status == 'pending') {
            $order->update_status('processing');
            $order->add_order_note('Payment was successful on Rave and verified via webhook');
            $customer_note  = 'Thank you for your order.<br>';
  
            $order->add_order_note( $customer_note, 1 );
        
            wc_add_notice( $customer_note, 'notice' );
          }
  
          
          // $order->payment_complete($order->id);
          // $order->add_order_note('Payment was successful on Rave and verified via webhook');
          // $order->add_order_note('Flutterwave transaction reference: '.$response->flwRef); 
          // $customer_note  = 'Thank you for your order.<br>';
          // $customer_note .= 'Your payment was successful, we are now <strong>processing</strong> your order.';
  
          // $order->add_order_note( $customer_note, 1 );
      
          // wc_add_notice( $customer_note, 'notice' );
          // $this->flw_verify_payment();
        }
        exit();    
  
      }
  
      /**
       * Save Customer Card Details
       */
      public static function save_card_details( $rave_response, $user_id, $order_id ) {
  
        if ( isset( $rave_response->card->card_tokens[0]->embedtoken ) ) {
          $token_code = $rave_response->card->card_tokens[0]->embedtoken;
        } else {
          $token_code = '';
        }
  
        // save payment token to the order
        self::save_subscription_payment_token( $order_id, $token_code );
        // $save_card = get_post_meta( $order_id, '_wc_rave_save_card', true );
  
        
      }
      /* public function save_subscription_payment_token( $order_id, $payment_token ) {

        if ( ! function_exists ( 'wcs_order_contains_subscription' ) ) {
          return;
        }
  
        if ( WC_Subscriptions_Order::order_contains_subscription( $order_id ) && ! empty( $payment_token ) ) {
  
          // Also store it on the subscriptions being purchased or paid for in the order
          if ( function_exists( 'wcs_order_contains_subscription' ) && wcs_order_contains_subscription( $order_id ) ) {
  
            $subscriptions = wcs_get_subscriptions_for_order( $order_id );
  
          } elseif ( function_exists( 'wcs_order_contains_renewal' ) && wcs_order_contains_renewal( $order_id ) ) {
  
            $subscriptions = wcs_get_subscriptions_for_renewal_order( $order_id );
  
          } else {
  
            $subscriptions = array();
  
          }
  
          foreach ( $subscriptions as $subscription ) {
  
            $subscription_id = $subscription->get_id();
  
            update_post_meta( $subscription_id, '_rave_wc_token', $payment_token );
  
          }
        }
      } */

}
