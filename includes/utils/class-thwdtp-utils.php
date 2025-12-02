<?php
/**
 * The common utility functionalities for the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    order-delivery-date-and-time
 * @subpackage order-delivery-date-and-time/includes
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWDTP_Utils')):

class THWDTP_Utils {
	 
	const OPTION_KEY_DELIVERY_SETTINGS = 'thwdtp_general_settings';

	public static function wdtp_capability() {
		$allowed    = array('manage_woocommerce', 'manage_options');
		$capability = apply_filters('thwdtp_required_capability', 'manage_woocommerce');

		if(!in_array($capability, $allowed)){
			$capability = 'manage_woocommerce';
		}
		return $capability;
	}

	public static function get_general_settings(){
		$settings = get_option(self::OPTION_KEY_DELIVERY_SETTINGS);
		return empty($settings) ? false : $settings;
	}

	public static function get_settings_by_section($section=null){
		$all_settings = self::get_general_settings();
		if(!empty($all_settings )){
			if($section){

				if(isset($all_settings[$section])){
					return $all_settings[$section];
				}
			}
			return false;
		}
	}

	public static function get_time_format($section){

		$settings = self::get_settings_by_section($section);
		$date_format = $settings && isset($settings[$section.'_format']) ? $settings[$section.'_format'] : '';
		return $date_format ;
	}

	public static function get_time_slot_settings($section=null){

		$all_settings = self::get_general_settings();
		if(!empty($all_settings )){
			$time_settings = $all_settings ['delivery_time'];
			if($time_settings && !empty($time_settings)){
				if($section && $section == 'time_slots'){
					return $time_settings['time_slots'];
				}elseif($section && $section == 'general_settings'){
					return $time_settings['general_settings'];
				}

				return $time_settings;
			}
			return false;
		}

		return false;
	}

	public static function get_default_settings(){

		$default_settings = array(

			'delivery_date' => array(

				'enable_delivery_date'          => 1,
				'set_date_as_required_delivery' => 0,
		    	'delivery_date_label'           => 'Delivery Date',
		    	'min_preperation_days_delivery' => 0,
		    	'allowable_days_delivery'       => 365,
		    	'max_delivery_per_day'          => '',
		    	'week_start_date'               => 0,
		    	'delivery_date_format'          => 'Y-m-d',
		        'auto_select_first_date'        => 0,
		    	'delivery_off_days'             => array(),
		    )
		);

		return $default_settings;
	}

	/*public static function get_delivery_date_json(){

		$settings  = self::get_settings_by_section('delivery_date');
		$props_set = array();
		foreach (self::$DELIVERY_PROPS as $p_name => $props) {
			$property = isset($settings[$p_name]) &&  $settings[$p_name] ? $settings[$p_name] : $props['value'];
			$pvalue   = is_array($property) ? implode(',', $property) : $property;
			$pvalue   = esc_attr($pvalue);
			$props_set[$p_name] = $pvalue;
		}
		return json_encode($props_set);
	}*/

	public static function get_checkout_fieldset($section = false){

		$settings = THWDTP_UTILS::get_general_settings();
		$delivery_date   = ((isset($settings['delivery_date'])) && (is_array($settings['delivery_date']))) ? $settings['delivery_date'] : '';
		$delivery_time           = ((isset($settings['delivery_time'])) && (is_array($settings['delivery_time']))) ? $settings['delivery_time'] : '';
		
		
		$pickup_date           = ((isset($settings['pickup_date'])) && (is_array($settings['pickup_date']))) ? $settings['pickup_date'] : '';
		$pickup_time           = ((isset($settings['pickup_time'])) && (is_array($settings['pickup_time']))) ? $settings['pickup_time'] : '';

		$del_date_label = isset($delivery_date['delivery_date_label'] ) ? $delivery_date['delivery_date_label'] : __('Delivery Date',''); 
		$del_date_label = $del_date_label ? $del_date_label : __('Delivery Date',''); 
		$del_date_required       = isset($delivery_date['set_date_as_required_delivery']) && (isset($delivery_date['enable_delivery_date']) &&  $delivery_date['enable_delivery_date'])? $delivery_date['set_date_as_required_delivery'] : '';

		$del_time_settings = is_array($delivery_time) && isset($delivery_time['time_settings']) ? $delivery_time['time_settings'] : array();
		$del_time_required      = isset($del_time_settings['mandatory_delivery_time']) && (isset($del_time_settings['enable_delivery_time']) && $del_time_settings['enable_delivery_time']) ? $del_time_settings['mandatory_delivery_time'] : '';
		$del_time_label         = isset($del_time_settings['delivery_time_label']) ? $del_time_settings['delivery_time_label'] : __('Delivery Time', '');
		$del_time_label         = $del_time_label ? $del_time_label :  __('Delivery Time', ''); 

		$pick_date_label    = isset($pickup_date['pickup_date_label'] ) ? $pickup_date['pickup_date_label'] : __('Pickup Date',''); 
		$pick_date_label    = $pick_date_label ? $pick_date_label : __('Pickup Date',''); 
		$pick_date_required = isset($pickup_date['set_date_as_required_pickup']) && ( isset($pickup_date['enable_pickup_date']) &&  $pickup_date['enable_pickup_date']) ? $pickup_date['set_date_as_required_pickup'] : '';


		$pick_time_settings = is_array($pickup_time ) && isset($pickup_time ['time_settings']) ? $pickup_time ['time_settings'] : array();
		$pick_time_required      = isset($pick_time_settings['mandatory_pickup_time']) && (isset($pick_time_settings['enable_pickup_time']) && $pick_time_settings['enable_pickup_time'] )? $pick_time_settings['mandatory_pickup_time'] : '';
		$pick_time_label         = isset($pick_time_settings['pickup_time_label']) ? $pick_time_settings['pickup_time_label'] : __('Pickup Time', '');
		$pick_time_label         = $pick_time_label ? $pick_time_label :  __('Pickup Time', ''); 

		$fields['thwdtp_delivery_datepicker'] =
			array(

			'type'     => 'text',
			'id'       => 'thwdtp_delivery_datepicker',
			//'class' => array('flatpickr','flatpickr-input','active'),
			'name'     => 'thwdtp_delivery_datepicker',
			'label'    => $del_date_label,	
			'required' => $del_date_required ,
			'value'    => '',		
		);

		$fields['thwdtp_delivery_time']	 = array(
    		'type'          => 'select',
    		'id'            => 'thwdtp_delivery_time',
    		'name'          => 'thwdtp_delivery_time',
    		'label'         => $del_time_label,
    		'required'      => $del_time_required ,
    		'options'       => '',
    		'value'         => '',
		);
		
		$fields['thwdtp_pickup_datepicker'] = array(

			'type'     => 'text',
			'id'       => 'thwdtp_pickup_datepicker',
			//'class' => array('flatpickr','flatpickr-input','active'),
			'name'     => 'thwdtp_pickup_datepicker',
			'label'    => $pick_date_label,	
			'required' => $pick_date_required,
			'value'	   => '',	
		);

		$fields['thwdtp_pickup_time'] = array(

    		'type'          => 'select',
    		'id'            => 'thwdtp_pickup_time',
    		'name'          => 'thwdtp_pickup_time',
    		'label'         => $pick_time_label,
    		'required'      => $pick_time_required,
    		//'class'       => array('form-row-wide','thwdtp-input-field-wrapper',),
    		//'input_class' => array('thwdtp-input-field','thwdtp-enhanced-select'),
    		'options'       => '',
    		'value'         => '',
		);

		return $fields;
	}

}

endif;