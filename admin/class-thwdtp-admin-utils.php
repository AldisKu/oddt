<?php
/**
 * The admin settings page common utility functionalities.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package   order-delivery-date-and-time
 * @subpackage order-delivery-date-and-time/admin
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWDTP_Admin_Utils')):

class THWDTP_Admin_Utils {

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

	public static function get_time_slot_settings($section=null){

		$all_settings = THWDTP_Utils::get_general_settings();
		if(!empty($all_settings )){
			$time_settings = isset($all_settings [$section]) ? $all_settings [$section] : '';
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


}

endif;