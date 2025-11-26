<?php
/**
 * The common utility functionalities for the plugin.
 *
 * @link       https://themehigh.com
 * @since      2.0.0
 *
 * @package    order-delivery-date-and-time
 * @subpackage order-delivery-date-and-time/includes
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWDTP_Block')):

class THWDTP_Block {

    private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		//add_action('after_setup_theme', array($this, 'define_block_hooks'));
        add_action('woocommerce_blocks_loaded', array($this, 'load_plugin_blocks'));
	}

    public function load_plugin_blocks(){

        require_once __DIR__ . '/class-fields-block-integration.php';
        add_action('woocommerce_blocks_checkout_block_registration' , array($this, 'register_block_integration'));

        require_once __DIR__ . '/class-fields-extend-store-endpoint.php';
        Fields_Extend_Store_Endpoint::init();

        add_action('woocommerce_store_api_checkout_update_order_from_request', array($this, 'store_api_checkout_update_order_from_request'),10,2);
    }

    public function register_block_integration($integration_registry){

       $integration_registry->register( new Fields_Block_Integration() );
    }

    public function store_api_checkout_update_order_from_request( \WC_Order $order, \WP_REST_Request $request ){
       
        $request_data  = isset($request['extensions']['order-delivery-fields-block']) ? $request['extensions']['order-delivery-fields-block'] : array();
        $delivery_date = isset($request_data['orderDeliveryDate']) ? $request_data['orderDeliveryDate'] : '';
        $delivery_time = isset($request_data['orderDeliveryTime']) ? $request_data['orderDeliveryTime'] : '';
        $pickup_date   = isset($request_data['orderPickupDate']) ? $request_data['orderPickupDate'] : '';
        $pickup_time   = isset($request_data['orderPickupTime']) ? $request_data['orderPickupTime'] : '';

        $shipping_method = $this->get_shipping_method( $order );
        
        if(apply_filters('thwdtp_convert_utc_to_wp_timezone', true)){
            $delivery_date = $delivery_date ? $this->convert_to_wp_timezone($delivery_date) : '';
            $pickup_date = $pickup_date ? $this->convert_to_wp_timezone($pickup_date) : '';
        }

        if($shipping_method === 'pickup_location' || $shipping_method === 'local_pickup'){
            if($pickup_date || $pickup_time){

                $order->update_meta_data('thwdtp_order_type', 'pickup');
                $order->update_meta_data('thwdtp_pickup_datepicker', $pickup_date);
                $order->update_meta_data('thwdtp_pickup_time', $pickup_time);
            }
        }else{
            if($delivery_date || $delivery_time){

                $order->update_meta_data('thwdtp_order_type', 'delivery');
                // $delivery_date = $delivery_date ? $delivery_date ; '';
                $order->update_meta_data('thwdtp_delivery_datepicker', $delivery_date);
                $order->update_meta_data('thwdtp_delivery_time', $delivery_time);
    
            }
        }
        $order->save();
    }

    private function convert_to_wp_timezone($utc_time_str){
        $datetime = new DateTime($utc_time_str, new DateTimeZone('UTC'));
        $site_timezone = wp_timezone();
        $datetime->setTimezone($site_timezone);
        $site_time_str = wp_date('Y-m-d H:i:s', $datetime->getTimestamp());
        return $site_time_str;
    }

    public function get_shipping_method( $order ){
        $names = array();
        foreach ( $order->get_shipping_methods() as $shipping_method ) {
			$names[] = $shipping_method->get_method_id();
		}
        return implode( ', ', $names );
    }

}

endif;