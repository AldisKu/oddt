<?php

/**
 * The public-facing functionality of the plugin.

 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    order-delivery-date-and-time
 * @subpackage order-delivery-date-and-time/includes
 */

if(!defined('WPINC')){	die; }

if(!class_exists('THWDTP_Public')):
class THWDTP_Public {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		add_action('init', array($this, 'define_public_hooks'),20);
	}

	public function enqueue_styles_and_scripts() {

		global $wp_scripts;
		if(is_checkout()  || has_block( 'woocommerce/checkout')){
			$debug_mode = apply_filters('thwdtp_debug_mode', false);
			$suffix     = $debug_mode ? '' : '.min';

			$this->enqueue_styles($suffix);
			$this->enqueue_scripts($suffix);
		}
	}

	public function enqueue_styles($suffix) {
	
		wp_enqueue_style('thwdtp_flatpickr_styles', THWDTP_URL.'includes/assets/flat-pickr.min.css');
		wp_enqueue_style('thwdtp-public-style', THWDTP_ASSETS_URL_PUBLIC.'css/thwdtp-public'. $suffix .'.css');	
	}

	public function enqueue_scripts($suffix) {

		wp_enqueue_script( 'thwdtp-flatpickr-script', THWDTP_URL .'includes/assets/flat-pickr.min.js',array('jquery'));
		$deps = array('jquery','selectWoo','thwdtp-flatpickr-script');

		wp_register_script('thwdtp-public-script', THWDTP_ASSETS_URL_PUBLIC . 'js/thwdtp-public'. $suffix .'.js', $deps, $this->version, true );
		wp_enqueue_script('thwdtp-public-script');

		$settings             = THWDTP_Utils::get_general_settings();
		$settings             = $settings ? $settings : THWDTP_Utils::get_default_settings();
		$holidays             = isset($settings['holidays']) ? $settings['holidays'] : '';
		$specific_days        = isset($settings['specific_dates']) ? $settings['specific_dates'] : '';

		$common_settings      = isset($settings['common_fields']) ? $settings['common_fields'] : '';
		$based_on_shipping_method = isset($common_settings['enable_on_shipping_method']) ? $common_settings['enable_on_shipping_method'] : '';
		$time_format          = isset($common_settings['time_formats']) ? $common_settings['time_formats'] : '';

		$delivery_date_props  = isset($settings['delivery_date']) ? $settings['delivery_date'] : '';
		$enable_delivery_date = isset($delivery_date_props['enable_delivery_date']) ? $delivery_date_props['enable_delivery_date'] : '';
		$delivery_time        = isset($settings['delivery_time']) ? $settings['delivery_time'] : '';
		$delivery_time_props  = isset($delivery_time['time_settings']) ? $delivery_time['time_settings'] : '';
		$enable_delivery_time = isset($delivery_time_props['enable_delivery_time']) ? $delivery_time_props['enable_delivery_time'] : '';
		$delivery_time_slots  = $this->get_available_time_slots('delivery_time', $time_format );

		$pickup_date_props    = isset($settings['pickup_date']) ? $settings['pickup_date'] : '';
		$enable_pickup_date   = isset($pickup_date_props['enable_pickup_date']) ? $pickup_date_props['enable_pickup_date'] : '';
		$pickup_time          = isset($settings['pickup_time']) ? $settings['pickup_time'] : '';
		$pickup_time_props    = isset($pickup_time['time_settings']) ? $pickup_time['time_settings'] : '';
		$enable_pickup_time   = isset($pickup_time_props['enable_pickup_time']) ? $pickup_time_props['enable_pickup_time'] : '';
		$pickup_time_slots    = $this->get_available_time_slots('pickup_time', $time_format );
		
		$wp_now   = apply_filters('thwdtp_current_datetime',current_datetime());
		$wdtp_var = array(
			'ajax_url'             => admin_url( 'admin-ajax.php' ),
			'current_date'         => $wp_now->format('Y-m-d\TH:i:s'),
			'current_time'         => $wp_now->format('H:i:s'),
			'delivery_date_props'  => $delivery_date_props,
			'holidays'             => $holidays,
			'specific_dates'       => $specific_days,
			'delivery_time_props'  => $delivery_time_props,
			'delivery_time_slots'  => $delivery_time_slots,
			'pickup_date_props'    => $pickup_date_props,
			'pickup_time_props'    => $pickup_time_props,
			'pickup_time_slots'    => $pickup_time_slots,

			'enable_delivery_date' => $enable_delivery_date,
			'enable_delivery_time' => $enable_delivery_time,
			'enable_pickup_date'   => $enable_pickup_date,
			'enable_pickup_time'   => $enable_pickup_time,
			'based_on_shipping'    => $based_on_shipping_method,
			'today_date_str'       => $wp_now->format('Y-m-d'),
			'enable_specific_dates_only' =>  apply_filters('thwdtp_delivery_date_disabled_dates', false),
			'default_locale'               => apply_filters('thwdtp_react_date_picker_locale','en'),
			'include_current_time_slot' =>  apply_filters('thwdtp_include_current_time_slot', false),
		);

		wp_localize_script('thwdtp-public-script','thwdtp_public_var', $wdtp_var);	
	}


	public function define_public_hooks(){
		//Show  Fields in Checkout Page
		$fields_settings = THWDTP_UTILS::get_settings_by_section('common_fields');

		$position_hook = isset($fields_settings['field_positions']) ? $fields_settings['field_positions'] : 'after_order_notes' ;
		$position_hook = apply_filters('thwdtp_position_hook', "woocommerce_{$position_hook}");

		add_action($position_hook,array($this,'th_custom_fields'));
		//Checkout Init
		add_filter('woocommerce_checkout_fields', array($this, 'woo_checkout_fields'),10,1);
		
		//Checkout Process(Validate Date and time, save order meta
		add_action('woocommerce_checkout_process', array($this, 'woo_checkout_process'));
		add_action('woocommerce_checkout_create_order', array($this, 'woo_update_order_meta_data'), 10, 2);

		//Order table customer
		add_action('woocommerce_order_details_after_order_table', array($this, 'display_custom_order_fields_in_order_details_page_customer'), 20, 1);

		//Order meta fields in Email
		add_filter('woocommerce_email_order_meta_fields', array($this, 'add_meta_fields_in_email'), 10, 3);
	}

	public function th_custom_fields($checkout){
		
		$settings                = THWDTP_UTILS::get_general_settings();
		$settings                = $settings ? $settings : THWDTP_Utils::get_default_settings();
		$delivery_date           = ((isset($settings['delivery_date'])) && (is_array($settings['delivery_date']))) ? $settings['delivery_date'] : '';
		$is_enable_delivery_date =  $delivery_date && isset($delivery_date['enable_delivery_date']) ? $delivery_date['enable_delivery_date'] : '';
		$delivery_time           = ((isset($settings['delivery_time'])) && (is_array($settings['delivery_time']))) ? $settings['delivery_time'] : '';
		$time_settings           = ((isset($delivery_time['time_settings'])) && (is_array($delivery_time['time_settings']))) ? $delivery_time['time_settings'] : '';
		$is_enable_delivery_time =  $time_settings && isset($time_settings['enable_delivery_time']) ? $time_settings['enable_delivery_time'] : '';
		$pickup_date           = ((isset($settings['pickup_date'])) && (is_array($settings['pickup_date']))) ? $settings['pickup_date'] : '';
		$is_enable_pickup_date =  is_array($pickup_date)  && isset($pickup_date ['enable_pickup_date']) ? $pickup_date['enable_pickup_date'] : '';
		$pickup_time           = ((isset($settings['pickup_time'])) && (is_array($settings['pickup_time']))) ? $settings['pickup_time'] : '';
		$pickup_time_settings  = ((isset($pickup_time['time_settings'])) && (is_array($pickup_time['time_settings']))) ? $pickup_time['time_settings'] : '';
		$is_enable_pickup_time =  $pickup_time_settings && isset($pickup_time_settings['enable_pickup_time']) ? $pickup_time_settings['enable_pickup_time'] : '';

		if($is_enable_pickup_date || $is_enable_pickup_time){
			$common_fields = isset($settings['common_fields']) ? $settings['common_fields'] : '';
			$section       = ($common_fields && isset($common_fields['section_name'])) ? $common_fields['section_name'] : '';
			if($section){
				?>
				<h3><?php esc_html_e( $section, 'order-delivery-date-and-time' ); ?></h3>
				<?php
			}
		}

		if($is_enable_delivery_date ){
			$this->render_delivery_date_fields($delivery_date);
		}

		if($is_enable_delivery_time){
			$this->render_delivery_time_slots_fields($delivery_time);
		}

		if($is_enable_pickup_date){

			$this->render_pickup_date_fields($pickup_date);
		}

		if($is_enable_pickup_time){

			$this->render_pickup_time_slots_fieds($pickup_time );
		}
	}

	public function render_delivery_date_fields($settings){

		$del_date_label = (isset($settings['delivery_date_label'] ) && $settings['delivery_date_label']) ? $settings['delivery_date_label'] : esc_html__('Delivery Date','order-delivery-date-and-time');  
		$required       = isset($settings['set_date_as_required_delivery']) ? $settings['set_date_as_required_delivery'] : '';

		$delivery_date_fields =	array(

			'type'     => 'text',
			//'class' => array('flatpickr','flatpickr-input','active'),
			'name'     => 'thwdtp_delivery_datepicker',
			'label'    => $del_date_label,	
			'required' => $required,		
		);

		$name  = 'thwdtp_delivery_datepicker';
    	$value = '';
		woocommerce_form_field($name, $delivery_date_fields, $value );	
	}

	public function render_delivery_time_slots_fields($settings){
		
		$time_settings = is_array($settings) && isset($settings['time_settings']) ? $settings['time_settings'] : array();
		$required      = isset($time_settings['mandatory_delivery_time']) ? $time_settings['mandatory_delivery_time'] : '';
		$label         = (isset($time_settings['delivery_time_label']) && $time_settings['delivery_time_label']) ? $time_settings['delivery_time_label'] : esc_html__('Delivery Time','order-delivery-date-and-time');     

		$options = array(

    		''	=> __('Select Time Slot','order-delivery-date-and-time')
		);
		
		$field = array(

    		'type'        => 'select',
    		'name'        => 'thwdtp_delivery_time',
    		'label'       => $label,
    		'required'    => $required,
    		'class'       => array('form-row-wide','thwdtp-input-field-wrapper',),
    		'input_class' => array('thwdtp-input-field','thwdtp-enhanced-select'),
    		'options'     => $options
    	);

    	$name  = 'thwdtp_delivery_time';
    	$value = '';
		woocommerce_form_field($name, $field, $value);
	}

	public function render_pickup_date_fields($settings){

		$label    = (isset($settings['pickup_date_label']) && $settings['pickup_date_label']) ? $settings['pickup_date_label'] : esc_html__('Pickup Date','order-delivery-date-and-time'); 
		$required = isset($settings['set_date_as_required_pickup']) ? $settings['set_date_as_required_pickup'] : '';
		$pickup_date_fields =	array(

			'type'     => 'text',
			//'class' => array('flatpickr','flatpickr-input','active'),
			'name'     => 'thwdtp_pickup_datepicker',
			'label'    => $label,	
			'required' => $required,		
		);

		$name  = 'thwdtp_pickup_datepicker';
    	$value ='';
		woocommerce_form_field($name, $pickup_date_fields, $value );
	}

	public function render_pickup_time_slots_fieds($settings){

		$time_settings = is_array($settings) && isset($settings['time_settings']) ? $settings['time_settings'] : array();
		$required      = isset($time_settings['mandatory_pickup_time']) ? $time_settings['mandatory_pickup_time'] : '';
		$label         = (isset($time_settings['pickup_time_label']) && $time_settings['pickup_time_label']) ? $time_settings['pickup_time_label'] : esc_html__('Pickup Time', 'order-delivery-date-and-time');    

		$options = array(

    		''	=> __('Select Time Slot','order-delivery-date-and-time')
		);

		$field = array(

    		'type'        => 'select',
    		'name'        => 'thwdtp_delivery_time',
    		'label'       => $label,
    		'required'    => $required,
    		'class'       => array('form-row-wide','thwdtp-input-field-wrapper',),
    		'input_class' => array('thwdtp-input-field','thwdtp-enhanced-select'),
    		'options'     => $options
    	);

		$name  = 'thwdtp_pickup_time';
    	$value = '';
		woocommerce_form_field($name, $field, $value);
	}

	public function get_available_time_slots( $type, $time_format){

		$time_slot_settings = THWDTP_UTILS::get_settings_by_section($type);
		$all_time_slots     = array();

		if(is_array($time_slot_settings) && !empty($time_slot_settings) ){	

			foreach ($time_slot_settings as $slot_key => $slot_values) {

				$time_slots = array();

				if($slot_key == 'time_settings')
					continue;

				$general_settings = isset($slot_values['general_settings']) ? $slot_values['general_settings'] : false;
				
				$is_enable = isset($general_settings['enable_delivery_time_slot']) ? $general_settings['enable_delivery_time_slot']: false;

				$time_slot_for = isset($general_settings['time_slot_for']) ? $general_settings['time_slot_for'] : '';

				if($time_slot_for == 'week_days'){
					$time_slot_days = isset($general_settings['time_slot_type_week_days']) ? $general_settings['time_slot_type_week_days']: false;
				}else{

					$time_slot_days = isset($general_settings['time_slot_type_specific_date']) ? $general_settings['time_slot_type_specific_date']: false;
				}
				
				if($is_enable){

					$slot_add_method = $general_settings['time_slot_add_method'];

					if($slot_add_method == 'individual_time_slot'){

						$time_slots_sett = isset($slot_values['time_slots']) ? $slot_values['time_slots'] : false;

						if($time_slots_sett && is_array($time_slots_sett)){

							$time_slot_ranges = array();

							foreach ($time_slots_sett as $t_key => $t_value) {

								$time_slot = '';
								
								$from_hrs = $t_value['from_hrs'] ? $t_value['from_hrs'] : '00';
								$from_mins = $t_value['from_mins'] ? $t_value['from_mins'] : '00';
								$from_format = $t_value['from_format'] ? $t_value['from_format'] : 'am';
								$to_hrs = $t_value['to_hrs'] ? $t_value['to_hrs'] : '00';
								$to_mins = $t_value['to_mins'] ? $t_value['to_mins'] : '00';
								$to_format = $t_value['to_format'] ? $t_value['to_format'] : 'am';

								$from_time = $this->setup_time_format( $from_hrs, $from_mins,$from_format, $time_format);
								$to_time = $this->setup_time_format($to_hrs,$to_mins,$to_format, $time_format);

								$time_slot = $from_time." - ".$to_time;
								$time_slot_ranges[] = $time_slot;
							}
						}

						$time_slots['days']     = $time_slot_days;
						$time_slots['slots']    = $time_slot_ranges;
						$time_slots['day_type'] = $time_slot_for;

					}elseif($slot_add_method == 'bulk_time_slot'){

						$time_slots  = array();

						$slot_from_hrs    = $general_settings['order_slot_from_hrs'] ? $general_settings['order_slot_from_hrs'] : '';
						$slot_from_mins   =  $general_settings['order_slot_from_mins'] ? $general_settings['order_slot_from_mins'] : 0;
						$slot_from_mins   = strlen($slot_from_mins) == 1 ? '0'.$slot_from_mins : $slot_from_mins;
						$slot_from_format = $general_settings['order_slot_from_format'];
						$start_time       = $slot_from_hrs.':'.$slot_from_mins.' '.$slot_from_format;
						$StartTime        = strtotime ($start_time);
						
						$slot_end_hrs    = $general_settings['order_slot_end_hrs'] ? $general_settings['order_slot_end_hrs'] : '';
						$slot_end_mins   = $general_settings['order_slot_end_mins'] ? $general_settings['order_slot_end_mins'] : 0;
						$slot_end_mins   = strlen($slot_end_mins) == 1 ? '0'.$slot_end_mins : $slot_end_mins;
						$slot_end_format = $general_settings['order_slot_end_format'];

						$end_time = $slot_end_hrs .':'.$slot_end_mins.' '.$slot_end_format;
						$EndTime = strtotime ($end_time);
						$slot_duration_hrs = $general_settings['order_slot_duration_hrs'] ? $general_settings['order_slot_duration_hrs'] : 0 ;
						$slot_duration_mins = $general_settings['order_slot_duration_mins'] ? $general_settings['order_slot_duration_mins'] : 0;
						$duration = ($slot_duration_hrs*60 + $slot_duration_mins )*60;

						$slot_interval_hrs = $general_settings['order_slot_interval_hrs'] ?  $general_settings['order_slot_interval_hrs'] : 0;
						$slot_interval_mins = $general_settings['order_slot_interval_mins'] ? $general_settings['order_slot_interval_mins'] : 0;

						$interval = ($slot_interval_hrs*60 + $slot_interval_mins)*60;	
						$slots = array();
						if($StartTime && $EndTime && $duration){

							while ($StartTime < $EndTime) {

						        $slot_start_time = $StartTime;
						        $slot_end_time   = $slot_start_time + $duration; 
						        $StartTime       = $slot_end_time+$interval;
						        if( $slot_end_time <= $EndTime){
						        	$slots[]     = ($time_format === 'twenty_four_hour') ?  date ("H:i",$slot_start_time) ." - ". date ("H:i",$slot_end_time) : date ("h:i A",$slot_start_time) ." - ". date ("h:i A",$slot_end_time);
						        }
							}
										
							$time_slots['days'] = $time_slot_days;
							$time_slots['slots'] = $slots;
							$time_slots['day_type'] = $time_slot_for;
						}
					}

				}

				$all_time_slots[] = $time_slots;
			}	
		}
		return $all_time_slots;
	}

	private function setup_time_format( $_hrs, $_mins, $_format, $time_format){
		$_hrs    = ((strlen((string)$_hrs)) == 1) ? "0".$_hrs : $_hrs;
		$_mins   = ((strlen((string)$_mins)) == 1) ? "0".$_mins : $_mins;
		$_format = $_format === 'pm' ? 'PM' : 'AM';
		$time    = $_hrs.":".$_mins." ".$_format;

		$time    =  ($time_format === 'twenty_four_hour') ? (date("H:i", strtotime($time))) : $time;

		return  $time;
	}

	public function get_available_specific_days(){

		$time_slot_settings = THWDTP_UTILS::get_settings_by_section('delivery_time');
		$all_time_slots = array();
		$specific_days  = '';

		if(is_array($time_slot_settings) && !empty($time_slot_settings) ){	

			$specific_days = array();

			foreach ($time_slot_settings as $slot_key => $slot_values) {

				$time_slots = array();

				if($slot_key == 'time_settings')
					continue;

				$general_settings = isset($slot_values['general_settings']) ? $slot_values['general_settings'] : false;

				$is_enable = isset($general_settings['enable_delivery_time_slot']) ? $general_settings['enable_delivery_time_slot']: false;
				if($is_enable){

					$time_slot_for = $general_settings['time_slot_for'];

					$time_slot_days  = array();

					if($time_slot_for == 'specific_date'){
					
						$time_slot_days = isset($general_settings['time_slot_type_specific_date']) ? $general_settings['time_slot_type_specific_date']: false;
					}
				}

				$specific_days =  array_merge($specific_days,$time_slot_days);
			}
		}
				
		return $specific_days;
	}
	public function woo_checkout_fields( $checkout_fields ) {

		if(is_checkout() || is_account_page() || is_admin() || apply_filters('thwdtp_is_checkout', false)){
			$fieldset = THWDTP_Utils::get_checkout_fieldset();
		    $checkout_fields['order_specification'] = $fieldset;
		}
		return $checkout_fields;
	}    

	public function woo_checkout_process(){
		$checkout_fields = WC()->checkout->checkout_fields;
		$checkout_fields = $this->filter_order_specification_field($checkout_fields);
		if($checkout_fields){
			WC()->checkout->checkout_fields = $checkout_fields;
		}
	}

	public function filter_order_specification_field($checkout_fields){

		$shipping_method = isset($_POST['shipping_method'][0]) ? sanitize_text_field( $_POST['shipping_method'][0]) : '';

		if(!$shipping_method){
			$chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');
			$shipping_method = isset($chosen_shipping_methods[0]) ? sanitize_text_field( $chosen_shipping_methods[0]) : '';
		}
		
		if ($shipping_method && strpos($shipping_method, 'local_pickup') !== false ) {
    		
    		unset($checkout_fields['order_specification']['thwdtp_delivery_datepicker']);
    		unset($checkout_fields['order_specification']['thwdtp_delivery_time']);
		}else{
			unset($checkout_fields['order_specification']['thwdtp_pickup_datepicker']);
			unset($checkout_fields['order_specification']['thwdtp_pickup_time']);
		}
		return $checkout_fields;
	}

	public function woo_update_order_meta_data($order, $posted){

		//$selected_shipping_method = @array_shift($order->get_shipping_methods());
		//$shipping_method          = $selected_shipping_method['method_id'];

		$shipping_methods = $order->get_shipping_methods();
		$shipping_method  = '';
		foreach ( $order->get_shipping_methods() as $shipping_method ) {
			$shipping_method = $shipping_method->get_method_id();
		}

		$pickup_method = apply_filters('default_pickup_shipping_method','local_pickup');
		$order_type    = '';

		if($shipping_method === $pickup_method){
			$custom_fields = THWDTP_Utils::get_checkout_fieldset('pickup');
			$order_type    = 'pickup';
		}else{
			$custom_fields = THWDTP_Utils::get_checkout_fieldset('delivery');
			$order_type    = 'delivery';
		} 

		$order->update_meta_data('thwdtp_order_type', $order_type);

		foreach ($custom_fields as $field_key => $field){
			
			$value =  isset($posted[$field_key]) && !empty($posted[$field_key]) ? sanitize_text_field($posted[$field_key]) : '';
			if($value){
				$order->update_meta_data($field_key, $value);
			}
		}
	}

	public function woo_datepicker_field(){
		?>

		<p><input id="thwdtp_date_datepicker" class="flatpickr flatpickr-input active" type="text" placeholder=<?php esc_attr_e('Select Date..','order-delivery-date-and-time' ) ?> readonly="readonly"></p>
		<?php
	}

	public function display_custom_order_fields_in_order_details_page_customer($order){
	
		$e_fields = $this->get_additional_order_fields($order);
		$html     = '';
		if($e_fields){
			?>
			<table class="woocommerce-table woocommerce-table--custom-fields custom-fields thwdtp-custom-fields"> <?php
				foreach ($e_fields as $key => $fields){ 
					if($fields){?>
						<tr>
							<td><?php echo esc_html($fields['label']); ?> </td>
							<td><?php echo esc_html($fields['value']); ?></td>
						</tr>
						<?php
					}
				} ?>
			</table>
			<?php
		}
	}

	public function add_meta_fields_in_email($o_fields, $sent_to_admin, $order){

		$e_fields = $this->get_additional_order_fields($order);
		return array_merge($o_fields, $e_fields);
	}

	public function get_additional_order_fields($order){

		$order_id        = $order->get_id();
		$checkout_fields = WC()->checkout->checkout_fields;
		$custom_fields   = isset($checkout_fields['order_specification']) ? $checkout_fields['order_specification'] : THWDTP_Utils::get_checkout_fieldset();

		$e_fields      = array();
		foreach ($custom_fields as $field_key => $field){
			
			$label      =  $field['label'];
			//$value      = get_post_meta( $order_id, $field_key, true );
			$value      = $order->get_meta($field_key);
			$field_data = array();
			if(!empty($label) && !empty($value)){
				if($field_key === 'thwdtp_delivery_datepicker'){
					$date_format  = THWDTP_Utils::get_time_format('delivery_date');
					$value        = $date_format ? date($date_format, strtotime($value)) : $value;
				}else if($field_key === 'thwdtp_pickup_datepicker'){
					$date_format = THWDTP_Utils::get_time_format('pickup_date');
					$value       = $date_format ? date($date_format, strtotime($value)) : $value;
				}

				$field_data['label'] = $label;
				$field_data['value'] = $value;
			}
			$e_fields[$field_key] = $field_data;
		}
		return $e_fields;
	}

}
endif;
