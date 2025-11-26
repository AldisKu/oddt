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

        public static function get_pickup_schedule_from_settings($time_format){
                $settings     = THWDTP_UTILS::get_general_settings();
                $settings     = $settings ? $settings : THWDTP_Utils::get_default_settings();
                $pickup_date  = isset($settings['pickup_date']) ? $settings['pickup_date'] : array();
                $pickup_time  = isset($settings['pickup_time']) ? $settings['pickup_time'] : array();
                $time_props   = isset($pickup_time['time_settings']) ? $pickup_time['time_settings'] : array();
                $time_slots   = self::get_available_time_slots_for_settings($pickup_time, $time_format);

                return array(
                        'date_props' => $pickup_date,
                        'time_props' => $time_props,
                        'time_slots' => $time_slots,
                );
        }

        public static function get_available_time_slots_for_settings($time_settings, $time_format){
                $time_slot_settings = isset($time_settings['time_slots']) ? $time_settings['time_slots'] : $time_settings;
                $all_time_slots     = array();

                if(is_array($time_slot_settings) && !empty($time_slot_settings) ){

                        foreach ($time_slot_settings as $slot_key => $slot_values) {

                                $time_slots = array();

                                if($slot_key == 'time_settings'){
                                        continue;
                                }

                                $general_settings = isset($slot_values['general_settings']) ? $slot_values['general_settings'] : false;

                                $is_enable = isset($general_settings['enable_delivery_time_slot']) ? $general_settings['enable_delivery_time_slot']: false;

                                $time_slot_for = isset($general_settings['time_slot_for']) ? $general_settings['time_slot_for'] : '';

                                if($time_slot_for == 'week_days'){
                                        $time_slot_days = isset($general_settings['time_slot_type_week_days']) ? $general_settings['time_slot_type_week_days']: false;
                                }else{

                                        $time_slot_days = isset($general_settings['time_slot_type_specific_date']) ? $general_settings['time_slot_type_specific_date']: false;
                                }

                                if($is_enable){

                                        $slot_add_method = isset($general_settings['time_slot_add_method']) ? $general_settings['time_slot_add_method'] : '';

                                        if($slot_add_method == 'individual_time_slot'){

                                                $time_slots_sett = isset($slot_values['time_slots']) ? $slot_values['time_slots'] : false;

                                                if($time_slots_sett && is_array($time_slots_sett)){

                                                        $time_slot_ranges = array();

                                                        foreach ($time_slots_sett as $t_value) {

                                                                $from_hrs = isset($t_value['from_hrs']) && $t_value['from_hrs'] ? $t_value['from_hrs'] : '00';
                                                                $from_mins = isset($t_value['from_mins']) && $t_value['from_mins'] ? $t_value['from_mins'] : '00';
                                                                $from_format = isset($t_value['from_format']) && $t_value['from_format'] ? $t_value['from_format'] : 'am';
                                                                $to_hrs = isset($t_value['to_hrs']) && $t_value['to_hrs'] ? $t_value['to_hrs'] : '00';
                                                                $to_mins = isset($t_value['to_mins']) && $t_value['to_mins'] ? $t_value['to_mins'] : '00';
                                                                $to_format = isset($t_value['to_format']) && $t_value['to_format'] ? $t_value['to_format'] : 'am';

                                                                $from_time = self::setup_time_format( $from_hrs, $from_mins,$from_format, $time_format);
                                                                $to_time = self::setup_time_format($to_hrs,$to_mins,$to_format, $time_format);

                                                                $time_slot_ranges[] = $from_time." - ".$to_time;
                                                        }
                                                }

                                                $time_slots['days']     = $time_slot_days;
                                                $time_slots['slots']    = isset($time_slot_ranges) ? $time_slot_ranges : array();
                                                $time_slots['day_type'] = $time_slot_for;

                                        }elseif($slot_add_method == 'bulk_time_slot'){

                                                $slot_from_hrs    = isset($general_settings['order_slot_from_hrs']) && $general_settings['order_slot_from_hrs'] ? $general_settings['order_slot_from_hrs'] : '';
                                                $slot_from_mins   =  isset($general_settings['order_slot_from_mins']) ? $general_settings['order_slot_from_mins'] : 0;
                                                $slot_from_mins   = strlen($slot_from_mins) == 1 ? '0'.$slot_from_mins : $slot_from_mins;
                                                $slot_from_format = isset($general_settings['order_slot_from_format']) ? $general_settings['order_slot_from_format'] : 'am';
                                                $start_time       = $slot_from_hrs.':'.$slot_from_mins.' '.$slot_from_format;
                                                $StartTime        = strtotime ($start_time);

                                                $slot_end_hrs    = isset($general_settings['order_slot_end_hrs']) && $general_settings['order_slot_end_hrs'] ? $general_settings['order_slot_end_hrs'] : '';
                                                $slot_end_mins   = isset($general_settings['order_slot_end_mins']) ? $general_settings['order_slot_end_mins'] : 0;
                                                $slot_end_mins   = strlen($slot_end_mins) == 1 ? '0'.$slot_end_mins : $slot_end_mins;
                                                $slot_end_format = isset($general_settings['order_slot_end_format']) ? $general_settings['order_slot_end_format'] : 'am';

                                                $end_time = $slot_end_hrs .':'.$slot_end_mins.' '.$slot_end_format;
                                                $EndTime = strtotime ($end_time);
                                                $slot_duration_hrs = isset($general_settings['order_slot_duration_hrs']) ? $general_settings['order_slot_duration_hrs'] : 0 ;
                                                $slot_duration_mins = isset($general_settings['order_slot_duration_mins']) ? $general_settings['order_slot_duration_mins'] : 0;
                                                $duration = ($slot_duration_hrs*60 + $slot_duration_mins )*60;

                                                $slot_interval_hrs = isset($general_settings['order_slot_interval_hrs']) ?  $general_settings['order_slot_interval_hrs'] : 0;
                                                $slot_interval_mins = isset($general_settings['order_slot_interval_mins']) ? $general_settings['order_slot_interval_mins'] : 0;

                                                $interval = ($slot_interval_hrs*60 + $slot_interval_mins)*60;
                                                $slots = array();
                                                if($StartTime && $EndTime && $duration){

                                                        while ($StartTime < $EndTime) {

                                                                $slot_start_time = $StartTime;
                                                                $slot_end_time   = $slot_start_time + $duration;
                                                                $StartTime       = $slot_end_time+$interval;
                                                                if( $slot_end_time <= $EndTime){
                                                                        $slots[]     = ($time_format === 'twenty_four_hour') ?  date("H:i",$slot_start_time) ." - ". date("H:i",$slot_end_time) : date("h:i A",$slot_start_time) ." - ". date("h:i A",$slot_end_time);
                                                                }
                                                        }

                                                        $time_slots['days'] = $time_slot_days;
                                                        $time_slots['slots'] = $slots;
                                                        $time_slots['day_type'] = $time_slot_for;
                                                }
                                        }

                                }

                                if($time_slots){
                                        $all_time_slots[] = $time_slots;
                                }
                        }
                }
                return $all_time_slots;
        }

        private static function setup_time_format( $_hrs, $_mins, $_format, $time_format){
                $_hrs    = ((strlen((string)$_hrs)) == 1) ? "0".$_hrs : $_hrs;
                $_mins   = ((strlen((string)$_mins)) == 1) ? "0".$_mins : $_mins;
                $_format = $_format === 'pm' ? 'PM' : 'AM';
                $time    = $_hrs.":".$_mins." ".$_format;

                $time    =  ($time_format === 'twenty_four_hour') ? (date("H:i", strtotime($time))) : $time;

                return  $time;
        }

        public static function build_custom_time_slots($slot_strings, $time_format){
                $slots = array();
                if(!is_array($slot_strings)){
                        return $slots;
                }

                $clean_slots = array();
                foreach ($slot_strings as $slot){
                        $slot = trim($slot);
                        if(!empty($slot)){
                                $clean_slots[] = $slot;
                        }
                }

                if(!empty($clean_slots)){
                        $slots[] = array(
                                'days'     => array(0,1,2,3,4,5,6),
                                'slots'    => $clean_slots,
                                'day_type' => 'week_days',
                        );
                }

                return $slots;
        }

        public static function merge_pickup_schedules($base_schedule, $incoming_schedule){
                if(empty($base_schedule)){
                        return $incoming_schedule;
                }

                $base = $base_schedule;
                $inc  = $incoming_schedule;

                $base['date_props']['min_preperation_time_pickup'] = max(
                        intval(isset($base['date_props']['min_preperation_time_pickup']) ? $base['date_props']['min_preperation_time_pickup'] : 0),
                        intval(isset($inc['date_props']['min_preperation_time_pickup']) ? $inc['date_props']['min_preperation_time_pickup'] : 0)
                );

                $base_allowable = isset($base['date_props']['allowable_days_pickup']) ? intval($base['date_props']['allowable_days_pickup']) : 0;
                $inc_allowable  = isset($inc['date_props']['allowable_days_pickup']) ? intval($inc['date_props']['allowable_days_pickup']) : 0;
                if($base_allowable && $inc_allowable){
                        $base['date_props']['allowable_days_pickup'] = min($base_allowable, $inc_allowable);
                }elseif(!$base_allowable){
                        $base['date_props']['allowable_days_pickup'] = $inc_allowable;
                }

                $base_off_days = isset($base['date_props']['pickup_off_days']) && is_array($base['date_props']['pickup_off_days']) ? $base['date_props']['pickup_off_days'] : array();
                $inc_off_days  = isset($inc['date_props']['pickup_off_days']) && is_array($inc['date_props']['pickup_off_days']) ? $inc['date_props']['pickup_off_days'] : array();
                $base['date_props']['pickup_off_days'] = array_values(array_unique(array_merge($base_off_days, $inc_off_days)));

                $base_time_enabled = isset($base['time_props']['enable_pickup_time']) ? $base['time_props']['enable_pickup_time'] : '';
                $inc_time_enabled  = isset($inc['time_props']['enable_pickup_time']) ? $inc['time_props']['enable_pickup_time'] : '';
                $base['time_props']['enable_pickup_time'] = wc_string_to_bool($base_time_enabled) && wc_string_to_bool($inc_time_enabled);

                $base_slots_map = self::index_time_slots($base['time_slots']);
                $inc_slots_map  = self::index_time_slots($inc['time_slots']);
                $merged_slots   = array();

                foreach ($base_slots_map as $key => $slots){
                        if(isset($inc_slots_map[$key])){
                                $overlap = array_values(array_intersect($slots, $inc_slots_map[$key]));
                                if(!empty($overlap)){
                                        $merged_slots[$key] = $overlap;
                                }
                        }
                }

                $base['time_slots'] = self::expand_time_slot_index($merged_slots);

                return $base;
        }

        private static function index_time_slots($time_slots){
                $indexed = array();
                if(!is_array($time_slots)){
                        return $indexed;
                }

                foreach ($time_slots as $slot){
                        $day_type = isset($slot['day_type']) ? $slot['day_type'] : 'week_days';
                        $days     = isset($slot['days']) ? $slot['days'] : array();
                        $slots    = isset($slot['slots']) ? $slot['slots'] : array();

                        if($day_type === 'week_days'){
                                foreach ($days as $day){
                                        $key = 'd_'.$day;
                                        $indexed[$key] = isset($indexed[$key]) ? array_unique(array_merge($indexed[$key], $slots)) : $slots;
                                }
                        }else{
                                foreach ($days as $day){
                                        $key = 's_'.$day;
                                        $indexed[$key] = isset($indexed[$key]) ? array_unique(array_merge($indexed[$key], $slots)) : $slots;
                                }
                        }
                }

                return $indexed;
        }

        private static function expand_time_slot_index($indexed){
                $output = array();
                foreach ($indexed as $key => $slots){
                        if(empty($slots)){
                                continue;
                        }

                        if(strpos($key, 'd_') === 0){
                                $day = intval(substr($key, 2));
                                $output[] = array(
                                        'days'     => array($day),
                                        'slots'    => array_values($slots),
                                        'day_type' => 'week_days',
                                );
                        }elseif(strpos($key, 's_') === 0){
                                $day = substr($key, 2);
                                $output[] = array(
                                        'days'     => array($day),
                                        'slots'    => array_values($slots),
                                        'day_type' => 'specific_date',
                                );
                        }
                }

                return $output;
        }

        public static function get_pickup_schedule_for_product($product_id, $time_format){
                $global_schedule = self::get_pickup_schedule_from_settings($time_format);
                $product_schedule = get_post_meta($product_id, '_thwdtp_pickup_schedule', true);

                if(isset($product_schedule['enabled']) && $product_schedule['enabled']){
                        return self::normalize_pickup_schedule($product_schedule, $global_schedule, $time_format);
                }

                $term_schedules = array();
                $terms = get_the_terms($product_id, 'product_cat');
                if($terms && !is_wp_error($terms)){
                        foreach ($terms as $term){
                                $schedule = get_term_meta($term->term_id, 'thwdtp_pickup_schedule', true);
                                if(isset($schedule['enabled']) && $schedule['enabled']){
                                        $term_schedules[] = self::normalize_pickup_schedule($schedule, $global_schedule, $time_format);
                                }
                        }
                }

                if(!empty($term_schedules)){
                        $final = array_shift($term_schedules);
                        foreach ($term_schedules as $sched){
                                $final = self::merge_pickup_schedules($final, $sched);
                        }
                        return $final;
                }

                return $global_schedule;
        }

        private static function normalize_pickup_schedule($raw_schedule, $fallback, $time_format){
                $schedule = $fallback;

                if(isset($raw_schedule['min_time'])){
                        $schedule['date_props']['min_preperation_time_pickup'] = absint($raw_schedule['min_time']);
                }

                if(isset($raw_schedule['allowable_days'])){
                        $schedule['date_props']['allowable_days_pickup'] = absint($raw_schedule['allowable_days']);
                }

                if(isset($raw_schedule['off_days']) && is_array($raw_schedule['off_days'])){
                        $schedule['date_props']['pickup_off_days'] = array_map('absint', $raw_schedule['off_days']);
                }

                if(isset($raw_schedule['enable_time'])){
                        $schedule['time_props']['enable_pickup_time'] = wc_string_to_bool($raw_schedule['enable_time']);
                }

                if(isset($raw_schedule['time_slots'])){
                        $schedule['time_slots'] = self::build_custom_time_slots($raw_schedule['time_slots'], $time_format);
                }

                return $schedule;
        }

        public static function get_cart_pickup_schedule($time_format){
                $cart = WC()->cart;
                if(!$cart){
                        return self::get_pickup_schedule_from_settings($time_format);
                }

                $final_schedule = array();

                foreach ($cart->get_cart() as $cart_item){
                        if(!isset($cart_item['product_id'])){
                                continue;
                        }
                        $product_id = $cart_item['product_id'];
                        $schedule   = self::get_pickup_schedule_for_product($product_id, $time_format);

                        if(empty($final_schedule)){
                                $final_schedule = $schedule;
                        }else{
                                $final_schedule = self::merge_pickup_schedules($final_schedule, $schedule);
                        }
                }

                if(empty($final_schedule)){
                        $final_schedule = self::get_pickup_schedule_from_settings($time_format);
                }

                return $final_schedule;
        }

}

endif;