<?php

/**
 * The file that defines the core plugin class
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    order-delivery-date-and-time
 * @subpackage  order-delivery-date-and-time/includes
 */


class THWDTP_Admin {

	private $plugin_name;
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->order_meta_fields_admin_hooks();
	}

	public function enqueue_styles_and_scripts($hook) {

		if(strpos($hook, 'woocommerce_page_th_order-delivery-date-and-time') === false) {

			return;
		}
		$debug_mode = apply_filters('thwdtp_debug_mode', false);
		$suffix = $debug_mode ? '' : '.min';

		
		$this->enqueue_styles($suffix);
		$this->enqueue_scripts($suffix);
	}

	public function enqueue_styles($suffix) {

		wp_enqueue_style('thwdtp_flatpickr_styles', THWDTP_URL.'includes/assets/flat-pickr.min.css');
		wp_enqueue_style('woocommerce_admin_styles', THWDTP_WOO_ASSETS_URL.'css/admin.css');
		wp_enqueue_style('thwdtp-admin-style', THWDTP_ASSETS_URL_ADMIN . 'css/thwdtp-admin'. $suffix .'.css', $this->version);

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */

	public function enqueue_scripts($suffix) {
		wp_enqueue_script( 'thwdtp-flatpickr-script', THWDTP_URL .'includes/assets/flat-pickr.min.js');
		$deps = array('jquery', 'jquery-ui-dialog', 'jquery-tiptip', 'wc-enhanced-select', 'selectWoo','thwdtp-flatpickr-script');
		wp_enqueue_script( 'thwdtp-admin-script', THWDTP_ASSETS_URL_ADMIN . 'js/thwdtp-admin'. $suffix .'.js', $deps, $this->version, false );
		
		$wdtp_var = array(
            'ajax_url'  => admin_url('admin-ajax.php' ),
            'save_settings' => wp_create_nonce('thwdtp_save_settings'),
            'specific_dates_nonce' => wp_create_nonce('specific_dates'),
        );
		wp_localize_script('thwdtp-admin-script','wdtp_var',$wdtp_var);
	}
	
	public function admin_menu() {
		$capability = THWDTP_Utils::wdtp_capability();

		$screen_id = add_submenu_page('woocommerce', __('Delivery Date for Woocommerce','order-delivery-date-and-time'), __(' Date and Time Scheduler','order-delivery-date-and-time'), $capability, 'th_order-delivery-date-and-time', array($this, 'output_settings'));
	}

	public function add_screen_id($ids){
		$ids[] = 'woocommerce_page_th_order-delivery-date-and-time';
		$ids[] = strtolower(__('WooCommerce','order-delivery-date-and-time') ) .'_page_th_order-delivery-date-and-time';

		return $ids;
	}

	public function plugin_action_links($links) {
		$settings_link = '<a href="'.esc_url(admin_url('admin.php?page=th_order-delivery-date-and-time')).'">'. __('Settings','order-delivery-date-and-time') .'</a>';
		array_unshift($links, $settings_link);
		return $links;
	}
	
	/*public function plugin_row_meta( $links, $file ) {
		if(THWDTP_BASE_NAME == $file) {
			$doc_link = esc_url('https://www.themehigh.com/help-guides/woocommerce-checkout-field-editor/');
			$support_link = esc_url('https://www.themehigh.com/help-guides/');
				
			$row_meta = array(
				'docs' => '<a href="'.$doc_link.'" target="_blank" aria-label="'.esc_attr__('View plugin documentation','order-delivery-date-and-time').'">'.esc_html__('Docs','order-delivery-date-and-time').'</a>',
				'support' => '<a href="'.$support_link.'" target="_blank" aria-label="'. esc_attr__('Visit premium customer support' ,'order-delivery-date-and-time') .'">'. esc_html__('Premium support','order-delivery-date-and-time') .'</a>',
			);

			return array_merge( $links, $row_meta );
		}
		return (array) $links;
	}*/

	public function output_settings(){

		$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general_settings';
		$general_settings = THWDTP_Admin_Settings_General::instance();	
		$general_settings->render_page();
		
		/*if($tab === 'advanced_settings'){			
			$advanced_settings = THWDTP_Admin_Settings_Advanced::instance();	
			$advanced_settings->render_page();			
		}else if($tab === 'license_settings'){			
			$license_settings = THWDTP_Admin_Settings_Configuration::instance();	
			$license_settings->render_page();	
		}else{
			$general_settings = THWDTP_Admin_Settings_General::instance();	
			$general_settings->render_page();
		}*/
	}

	public function order_meta_fields_admin_hooks(){
		
		add_action('woocommerce_admin_order_data_after_order_details', array($this, 'display_custom_fields_in_admin_order_details'), 20, 1);
	}

	public function display_custom_fields_in_admin_order_details($order){
		
		$order_id      = $order->get_id();
		//$order_type  = get_post_meta( $order_id, 'thwdtp_order_type', true );
		$order_type  = $order->get_meta('thwdtp_order_type');
		$custom_fields =  THWDTP_Utils::get_checkout_fieldset();
		$html          = '';
		//$html         .= '<h3>Order Details</h3>';
		foreach ($custom_fields as $field_key => $field){
			
			$label =  $field['label'];
			//$value = get_post_meta( $order_id, $field_key, true );
			$value = $order->get_meta($field_key);
			if(!empty($label) && !empty($value)){

				if($field_key === 'thwdtp_delivery_datepicker'){
					$date_format  = THWDTP_Utils::get_time_format('delivery_date');
					$value        = $date_format ? date($date_format, strtotime($value)) : $value;
				}else if($field_key === 'thwdtp_pickup_datepicker'){
					$date_format = THWDTP_Utils::get_time_format('pickup_date');
					$value       = $date_format ? date($date_format, strtotime($value)) : $value;
				}
				
				$html .= '<p><strong>'. esc_html($label) .':</strong> '. esc_html($value) .'</p>';
			}
		}
		
		if($html){
			echo '<p style="clear: both; margin: 0 !important;"></p>';
			echo wp_kses_post($html);
		}
	}
}
