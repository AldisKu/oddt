<?php
/**
 * The file that defines the core plugin class
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    order-delivery-date-and-time
 * @subpackage order-delivery-date-and-time/admin
 */
if(!defined('WPINC')){	die; }


if(!class_exists('THWDTP_Admin_Settings_General')):

class THWDTP_Admin_Settings_General extends THWDTP_Admin_Settings{

	protected static $_instance = null;
    private $days_map       = array();

	private $delivery_date_fields = NULL;
	private $delivery_time_fields = NULL;
	private $pickup_date_fields   = NULL;
	private $pickup_time_fields   = NULL;
	private $time_slot_fields     = NULL;
	private $common_fields        = NULL;

	public function __construct() {
		parent::__construct('general_settings');
		$this->init_constants();
	}
	
	public static function instance() {
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	} 
	
	public function init_constants(){

		$this->days_map = array(
			0 => __('Sunday','order-delivery-date-and-time'),
			1 => __('Monday','order-delivery-date-and-time'),
			2 => __('Tuesday','order-delivery-date-and-time'),
			3 => __('Wednesday','order-delivery-date-and-time'),
			4 => __('Thursday','order-delivery-date-and-time'),
			5 => __('Friday','order-delivery-date-and-time'),
			6 => __('Saturday','order-delivery-date-and-time'),
		);
		
		$this->delivery_date_fields = $this->get_delivery_date_fields();
		$this->delivery_time_fields = $this->get_delivery_time_fields();
		$this->time_slot_fields     = $this->get_time_slot_fields();
		$this->common_fields        = $this->get_common_fields();
		$this->pickup_date_fields   = $this->get_pickup_date_fields();
		$this->pickup_time_fields   = $this->get_pickup_time_fields();
	}

	public function get_delivery_date_fields(){

		$date_formats = array(
			'Y-m-d'  => __('Y-m-d','order-delivery-date-and-time'),
			'd-m-Y'  => __('d-m-Y','order-delivery-date-and-time'),
			'm/d/y'  => __('m/d/y','order-delivery-date-and-time'),
			'd.m.y'  => __('d.m.y','order-delivery-date-and-time'),
			'F j, Y' => __('F j,Y','order-delivery-date-and-time')
		);

		return array(
			
			array( 'label'=> __('Delvery Date Settings', 'order-delivery-date-and-time'), 'type'=>'title', 'id' =>'delivery_date_settings'),

			array( 'id'=>'enable_delivery_date', 'label'=> __('Enable Delivery Date','order-delivery-date-and-time'), 'type'=>'checkbox', 'desc'=> __('Enable delivery date at Checkout page.','order-delivery-date-and-time') , 'desc_tip' => true,'default' => 'yes', 'value' => 1),

			array( 'id'=>'set_date_as_required_delivery', 'label'=> __('Make Delivery Date Mandatory','order-delivery-date-and-time'),'type'=>'checkbox', 'default' => 'no'),

			array( 'type'=>'text', 'id'=>'delivery_date_label','label'=>__('Delivery Date Label', 'order-delivery-date-and-time')), 

			array( 'type'=>'number', 'id'=>'min_preperation_days_delivery', 'label'=>__('Processing Time(min)','order-delivery-date-and-time'),'custom_attributes' => array('min'  => 0,)),

			array( 'type'=>'number', 'id'=>'allowable_days_delivery', 'label'=>__('Valid Days','order-delivery-date-and-time'), 'custom_attributes' => array('min'  => 0), 'desc'=> __('Default valid days is 365.','order-delivery-date-and-time'), 'desc_tip' => true,),
			// array( 'type'=>'number', 'id'=>'max_delivery_per_day', 'label'=>__('maximum Order Deliveries Per Day','order-delivery-date-and-time'), 'desc'=>'Maximum order deliveries per day.','desc_tip' => true,),

			array( 'type' => 'select', 'id'=>'week_start_date', 'label'=>__('Week Starts Date', 'order-delivery-date-and-time'),'value' => 'sunday', 'options'=>$this->days_map),

			array( 'type' => 'select', 'id'=>'delivery_date_format', 'label'=>__('Date Format', 'order-delivery-date-and-time') ,'value' => 'left', 'options'=>$date_formats),

			array( 'id'=>'auto_select_first_date', 'label'=> __('Auto Select First Available Date','order-delivery-date-and-time'), 'type'=>'checkbox', 'default' =>'no'),

			array( 'id'=>'delivery_off_days','type' => 'multiselect', 'label'=>__('Off Days', 'order-delivery-date-and-time'), 'options'=>$this->days_map, 'multiple_hidden' =>1, 'class'             => 'wc-enhanced-select',),
			array( 'type' => 'sectionend','id'   => 'delivery_date_settings',),
		
		);
	}

	public function get_delivery_time_fields(){
		return array(

			array('label'=>__('Delivery Time Settings', 'order-delivery-date-and-time'), 'type'=>'title', 'id' => 'delivery_time_settings'),

			array('id'=>'enable_delivery_time', 'label'=>__("Enable Delivery Time",'order-delivery-date-and-time'), 'type'=>'checkbox','desc'=> __('Enable delivery time at Checkout page.','order-delivery-date-and-time') ,'desc_tip' => true, 'default' => 'no'),

			array('id'=>'mandatory_delivery_time', 'label'=>__("Make Delivery Time Mandatory",'order-delivery-date-and-time'),'type'=>'checkbox', 'default' => 'no'),

			array('type'=>'text', 'id'=>'delivery_time_label','label'=>__('Delivery Time Label', 'order-delivery-date-and-time'),'value'=>''),
			'min_preperation_time_delivery' => array('type'=>'number', 'id'=>'min_preperation_time_delivery', 'label'=>__('Processing Time (min)', 'order-delivery-date-and-time'),'custom_attributes' => array('min'  => 0,),'desc'=> __('This field is applicable only if the Delivery Date option is disabled.','order-delivery-date-and-time'), 'desc_tip' => true,),
			array( 'type' => 'sectionend','id'   => 'delivery_time_settings',),
		);
	}

	public function get_time_slot_fields(){

		$time_slot_add_methods = array(
			'individual_time_slot' => __('Individual Time Slot','order-delivery-date-and-time'),
			'bulk_time_slot' => __('Bulk Time Slot','order-delivery-date-and-time')
		);

		$types = array(

			'week_days' => __('Week Days', 'order-delivery-date-and-time'),
			'specific_date' => __('Specific Dates','order-delivery-date-and-time'),
		);

		$days =  $this->days_map;

		$time_slot_type = array(

			'individual_time_slot' => __('Individual Time Slot' , 'order-delivery-date-and-time'),
			'bulk_time_slot' => __('Bulk Time Slot', 'order-delivery-date-and-time')
		);

		$available_specific_dates = array();

		$specific_dates = THWDTP_Utils::get_settings_by_section('specific_dates');

		$specific_dates = $specific_dates ? $specific_dates : array() ;

		foreach ($specific_dates as  $date) {

			$available_specific_dates[$date] = $date;
		}
		return array(

			array( 'id'=>'enable_delivery_time_slot', 'label'=>"Enable Delivery Time", 'type'=>'checkbox','desc'=> __('Enable this delivery time slot .' ,'order-delivery-date-and-time'), 'desc_tip' => true, 'default' => 'no'),

			array('type'=>'select', 'id'=>'time_slot_add_method', 'label'=>__('Time Slot Type', 'order-delivery-date-and-time'), 'options'=>$time_slot_add_methods, 'onchange'=>'thwdtpTimeSlotMethodChangeListner(this)'),

			array('type' => 'select', 'id'=>'time_slot_for', 'label'=>__('Time Slot For', 'order-delivery-date-and-time'), 'desc'=> __('Select Weekday option or Specific delivery dates option to create a time slot.','order-delivery-date-and-time') , 'desc_tip' => true,'value' => 'week_days', 'options'=>$types, 'onchange'=>'thwdtpTimeSlotTypeChangeListner(this)' ),

			array('type' => 'multiselect', 'id'=>'time_slot_type_week_days', 'label'=>__('Select Delivery Week Days', 'order-delivery-date-and-time'), 'desc'=> __('Select Delivery Days/Dates for which you want to create an exclusive Time Slot.', 'order-delivery-date-and-time') , 'desc_tip' => true, 'options'=>$days, 'class' => 'wc-enhanced-select',),

			array('type' => 'multiselect', 'id'=>'time_slot_type_specific_date', 'label'=>__('Select Specific Delivery Dates', 'order-delivery-date-and-time'), 'desc'=> __('Select Delivery Days/Dates for which you want to create an exclusive Time Slot. ','order-delivery-date-and-time'), 'desc_tip' => true,'options'=>$available_specific_dates, 'class' => 'wc-enhanced-select',),

			// array('type'=>'number', 'id'=>'order_deliveries_per_slot', 'label'=>__('Order Deliveries per Slot', 'order-delivery-date-and-time'),'desc'=>'A time slot will become unavailable for further deliveries once these many orders are placed for delivery for that time slot.
			// 	Note: If Max order deliveries is set, then that will get priority over time slot lockout.', 'desc_tip' => true,),
		);
	}

	public function get_pickup_date_fields(){

		$days = $this->days_map;

		$date_formats = array(

			'Y-m-d'  => __('Y-m-d','order-delivery-date-and-time'),
			'd-m-Y'  => __('d-m-Y','order-delivery-date-and-time'),
			'm/d/y'  => __('m/d/y','order-delivery-date-and-time'),
			'd.m.y'  => __('d.m.y','order-delivery-date-and-time'),
			'F j, Y' => __('F j,Y','order-delivery-date-and-time')
		);

		return array(

			array('label'=>__('Pickup Date Settings', 'order-delivery-date-and-time'), 'type'=>'title', 'id' => 'pickup_date_settings'),
			array('id'=>'enable_pickup_date', 'label'=>__("Enable Pickup Date", 'order-delivery-date-and-time'),'type'=>'checkbox','desc'=> __('Enable pickup date at Checkout page.','order-delivery-date-and-time') , 'desc_tip' => true, 'default' => 'no'),
			array('id'=>'set_date_as_required_pickup', 'label'=>__("Make Pickup Date Mandatory",'order-delivery-date-and-time'),'type'=>'checkbox', 'default' => 'no'),
			array('type'=>'text', 'id'=>'pickup_date_label','label'=>__('Pickup Date Label', 'order-delivery-date-and-time'),'value'=>''), 
			array('type'=>'number', 'id'=>'min_preperation_time_pickup', 'label'=>__('Processing Time(min)','order-delivery-date-and-time'), 'custom_attributes' => array('min'  => 0,)),
			array('type'=>'number', 'id'=>'allowable_days_pickup', 'label'=>__('Valid Days','order-delivery-date-and-time'), 'custom_attributes' => array('min'  => 0,),'desc'=> __('Default valid days is 365.', 'order-delivery-date-and-time'), 'desc_tip' => true,),
			array('type' => 'select', 'id'=>'week_start_date_pickup', 'label'=>__('Week Starts Date', 'order-delivery-date-and-time'),'value' => 'sunday', 'options'=>$days),
			array('type' => 'select', 'id'=>'pickup_date_format', 'label'=>__('Date Format', 'order-delivery-date-and-time'),'value' => 'left', 'options'=>$date_formats),
			array('id'=>'auto_select_first_date_pickup', 'label'=>__("Auto Select First Available Date",'order-delivery-date-and-time'), 'type'=>'checkbox', 'default' => 'no'),
			array( 'id'=>'pickup_off_days','type' => 'multiselect', 'label'=>__('Off Days', 'order-delivery-date-and-time'), 'options'=>$days, 'multiple_hidden' =>1, 'class' => 'wc-enhanced-select',),
			array( 'type' => 'sectionend','id' => 'pickup_date_settings',),
		);
	}

	public function get_pickup_time_fields(){
		return array(

			array('label'=>__('Pickup Time Settings', 'order-delivery-date-and-time'), 'type'=>'title', 'id' => 'pickup_time_settings'),
			array('id'=>'enable_pickup_time', 'label'=>__("Enable Pickup Time", 'order-delivery-date-and-time'),'type'=>'checkbox','desc'=> __('Enable pickup time at Checkout page.', 'order-delivery-date-and-time') , 'desc_tip' => true, 'default' => 'no'),
			array('id'=>'mandatory_pickup_time', 'label'=>__("Make Pickup Time Mandatory",'order-delivery-date-and-time'),'type'=>'checkbox', 'default' => 'no'),
			array('type'=>'text', 'id'=>'pickup_time_label','label'=>__('Pickup Time Label', ''),'value'=>''),
			array('type'=>'number', 'id'=>'min_preperation_time_pickup', 'label'=>__('Processing Time(min)','order-delivery-date-and-time'), 'custom_attributes' => array('min'  => 0,),'desc'=> __('This field is applicable only if the Pickup Date option is disabled.','order-delivery-date-and-time'), 'desc_tip' => true,),
			array( 'type' => 'sectionend','id' => 'pickup_time_settings',),
		);
	}

	public function get_common_fields(){

		$positions = array(
			
			'after_order_notes'                    => 'After order notes',
			'before_order_notes'                   => 'Before order_notes',
			'before_checkout_billing_form'         => 'Before billing form',
			'after_checkout_billing_form'          => 'After billing form',
			'before_checkout_shipping_form'        => 'Before shipping form',
			'after_checkout_shipping_form'         => 'After shipping form',
			'checkout_before_terms_and_conditions' => 'Before terms and conditions', 
			'checkout_after_terms_and_conditions'  => 'After terms and conditions',
		);

		if(apply_filters('thwdtpe_enable_review_order_section_positions', false)){

			$positions['before_cart_contents']        = 'Review Order - Before cart contents';
			$positions['after_cart_contents']         = 'Review Order - After cart contents';
			$positions['before_order_total']          = 'Review Order - Before order total';
			$positions['after_order_total']           = 'Review Order - After order total';
			$positions['before_order_review_heading'] = 'Before order review heading';
			$positions['before_order_review']         = 'Before order review wrapper';
			$positions['after_order_review']          = 'After order review wrapper';
			$positions['order_review_0']              = 'Before order review content';
			$positions['order_review_99']             = 'After order review content';

		}

		$time_formats = array(

			'twelve_hour'      => '12 Hours',
			'twenty_four_hour' => '24 Hours Format'
		);

		return array(
			

			array('label'=>__('Other Settings', 'order-delivery-date-and-time'), 'type'=>'title', 'id' => 'common_settings'),
			array('type' => 'select', 'id'=>'time_formats', 'label'=>__('Time Format', 'order-delivery-date-and-time'),'value' => 'twelve_hour', 'options'=>$time_formats ),
			array('description'=>__('The functions below are for the classic checkout and do not work with the WooCommerce block', 'order-delivery-date-and-time'), 'type'=>'description', 'id' => 'common_settings'),
			array('type'=>'text', 'id'=>'section_name', 'label'=>__('Section Heading', 'order-delivery-date-and-time'),),
			array('type' => 'select', 'id'=>'field_positions', 'label'=>__('Field Position', 'order-delivery-date-and-time'),'value' => '', 'options'=>$positions, ),

			array('id'=>'enable_on_shipping_method', 'label'=>__("Enable Fields Based on Shipping Method",'order-delivery-date-and-time'),'type'=>'checkbox', 'default' => 'no'),

			array( 'type' => 'sectionend','id' => 'common_settings',),
		);
	}

	public function get_time_slots_props_display(){
		return array(
			'days/dates'           => array('name'=>'days', 'type'=>'text', 'len'=>60),
			'time_slot_add_method' => array('name' => 'time_slot_add_method', 'type' => 'select'),
			//'title' => array('name'=>'title', 'type'=>'text', 'len'=>40),
			//'order_deliveries_per_slot'  => array('name'=>'order_deliveries_per_slot', 'type'=>'text'),
			'enable_delivery_time_slot' => array('name'=>'enable_delivery_time_slot', 'type'=>'checkbox', 'status'=>1),
		);
	}
	
	public function render_page(){
		$this->render_tabs();
		$this->render_content();		
	}
		
	public function save_general_settings($settings){
		$result = update_option(THWDTP_Utils::OPTION_KEY_DELIVERY_SETTINGS, $settings, 'no');
		return $result;
	}

	public function get_specific_dates(){

        if(check_ajax_referer('specific_dates', 'security')) {
        	$specific_dates = THWDTP_Utils::get_settings_by_section('specific_dates');
        	wp_send_json($specific_dates);
        }
	}

	/*
	 * E001: Success, valid settings.
	 * E002: Success, invalid settings.
	 * E101: Error, Unexpected.
	 */
	
	public function thwdtp_save_settings(){

		check_ajax_referer( 'thwdtp_save_settings', 'security' );
		$capability = THWDTP_Utils::wdtp_capability();
		if(!current_user_can($capability)){
			die();
		}
		$result = array(
			'code'    => 'E002',
			'message' => ''
		);

		$settings_section  = isset($_POST['section']) ? sanitize_text_field($_POST['section']) : '';
		$settings_key      = isset($_POST['key']) ? sanitize_text_field($_POST['key']) : '';
		$settings          = isset($_POST['settings']) ? wc_clean(json_decode(stripslashes(($_POST['settings'])),true)) : '';
	
		if(is_array($settings)){
			try{
				$settings         = $this->prepare_settings_field($settings, $settings_section, $settings_key);
				$settings_section = ($settings_section == 'delivery_time_general') ? 'delivery_time' : $settings_section;
				$settings_section = ($settings_section == 'pickup_time_general') ? 'pickup_time' : $settings_section;
				$settings         = $this->prepare_settings_for_save($settings, $settings_section);
				$result           = $this->save_settings($settings);
				$new_settings     = $this->get_settings_slots_populate_row($settings_section);

				 $result = array(
					'message' => $result,
					'settings' => $new_settings,
				);

			} catch (Exception $e) {
				$result = array(
					'code' => 'E101',
					'message' => $e->getMessage(),
				);
			}
		}
		wp_send_json($result);
	}

	public function prepare_settings_field($presettings, $section, $settings_key){

		$all_new_settings = array();
		$new_settings     = array();

		if($section === 'delivery_time' || $section === 'pickup_time'){
			
			$time_slot_settings    = $presettings['time_slots_settings'];
			$time_general_settings = $presettings['general_settings'];

			$new_settings['time_slots'] = $time_slot_settings;
			$new_settings['general_settings'] = $time_general_settings;
			
			$all_settings = THWDTP_Admin_Utils::get_time_slot_settings($section);
			
			if($all_settings && is_array($all_settings )){

				$new_key = '';

				if($settings_key){
					$new_key = $settings_key;
				}else{
					
					$array_keys = array_filter(array_keys($all_settings), 'is_int');
					$last_key   = ($array_keys && is_array($array_keys))  ? max($array_keys) : 0;
					$new_key    = $last_key+1;
				}

				$all_settings[$new_key] = $new_settings;
				$all_new_settings       = $all_settings;
				
			}else{

				$all_new_settings[1] = $new_settings;
			}
			
		}elseif($section === 'delivery_time_general' || $section === 'pickup_time_general'){

			$o_type           = $section == 'pickup_time_general' ? 'pickup_time' : 'delivery_time' ;
			$all_settings     = $this->prepare_delivery_time_settings($presettings, $o_type); 
			$all_new_settings = $all_settings;

		}else{

			$all_new_settings = $presettings;
		}

		return $all_new_settings ;
	}

	private function prepare_delivery_time_settings($settings, $section){

		$all_settings = array();
		$delete_keys  = $settings['deleted_keys'];
		$enable_disable_slots = $settings['enable_disable'];

		$time_settings = $settings['settings'];
		$all_settings  = THWDTP_Admin_Utils::get_time_slot_settings($section);
		$time_fields   = $section == 'delivery_time' ? $this->delivery_time_fields : $this->pickup_time_fields;

		$all_settings['time_settings'] = $time_settings;

		if($enable_disable_slots ){

			foreach ($enable_disable_slots as $stat_key => $stat_value ) {
				
				$state_change_slot = $all_settings[$stat_key];

				if(isset($state_change_slot['general_settings'])){

					$state_change_slot['general_settings']['enable_delivery_time_slot'] = $stat_value;
				}

				$all_settings[$stat_key] = $state_change_slot;
			}
		}

		if($delete_keys){

			foreach ($delete_keys as $key) {

				unset($all_settings[$key]);
			}
		}

		return $all_settings;
	}

	private function prepare_settings_for_save($settings, $settings_section){
		$all_settings = array();
		$all_settings = THWDTP_Utils::get_general_settings();
		if($all_settings && is_array($all_settings) && !empty($all_settings)){
			$all_settings[$settings_section] = $settings;
		}else{
			$all_settings[$settings_section] = $settings;
		}

		return $all_settings;
	}

	private function save_settings($settings){
				
		$result = update_option(THWDTP_Utils::OPTION_KEY_DELIVERY_SETTINGS, $settings, 'no');
		if ($result == true) {
			$result =  'success' ;
		} else {
			$result = 'error';
		}	

		return $result;
	}
	
	private function render_content(){

		?>
		<div style="padding-left: 30px;">          
		    <div class="thwdtp-wrap">
		    	<div class = "th-note">
	    			<p>Note: <a href="<?php echo esc_url(admin_url('options-general.php#WPLANG'));?>"> The time you set within your site </a> would be considered for the plugin settings, and not the default browser time.</p>
	    		</div>
	    		<div class = "th-success-msg success-msg" style="display: none;">
	    			<p><?php esc_html_e('Your changes were saved.','order-delivery-date-and-time') ; ?> </p>
	    		</div>
	    		<div class = "th-success-msg error-msg" style="display: none;">
	    			<p><?php esc_html_e('Your changes were not saved due to an error (or you made none!).','order-delivery-date-and-time') ; ?> </p>
	    		</div>
		    	<div id="thwdtpadmin_wrapper" class="thwdtpadmin-wrapper">
			    	<div class="th-col-3 thwdtpadmin-tabs-wrapper">
			    		<ul id="thwdtpadmin-tabs" class="thwdpadmin-tabs">
			    			
			    			<li class="thwdtpadmin-tab">
								<a href="javascript:void(0)" id="tab-0" data-tab_name="" data-tab="0" class="">
									<span class="thwdtpadmin-tab-label">
										<!-- <span class="dashicons dashicons-align-full-width"></span> -->
										<span class="dashicons dashicons-calendar-alt"></span>
										<?php esc_html_e('Delivery Date Settings', 'order-delivery-date-and-time'); ?>
									</span>
								</a>
							</li>

							<li class="thwdtpadmin-tab">
								<a href="javascript:void(0)" id="tab-5" data-tab_name="" data-tab="5" class="">
									<span class="thwdtpadmin-tab-label">
										<span class="dashicons dashicons-visibility"></span>
										<?php esc_html_e('Add Specific Dates', 'order-delivery-date-and-time'); ?>
									</span>
								</a>
							</li>

							<li class="thwdtpadmin-tab">
								<a href="javascript:void(0)" id="tab-2" data-tab_name="" data-tab="2" class="">
									<span class="thwdtpadmin-tab-label">
										<span class="dashicons dashicons-clock"></span>
										<?php esc_html_e('Delivery Time Settings', 'order-delivery-date-and-time'); ?>
									</span>
								</a>
							</li>

							<li class="thwdtpadmin-tab">
								<a href="javascript:void(0)" id="tab-6" data-tab_name="" data-tab="6" class="">
									<span class="thwdtpadmin-tab-label">
										<span class="dashicons dashicons-hidden"></span>
										<?php esc_html_e('Add Holidays', 'order-delivery-date-and-time'); ?>
									</span>
								</a>
							</li>

							<li class="thwdtpadmin-tab">
								<a href="javascript:void(0)" id="tab-1" data-tab_name="" data-tab="1" class="">
									<span class="thwdtpadmin-tab-label">
										<span class="dashicons dashicons-calendar-alt"></span>
										<?php esc_html_e('Pickup Date Settings', 'order-delivery-date-and-time') ?>
									</span>
								</a>
							</li>

							<li class="thwdtpadmin-tab">
								<a href="javascript:void(0)" id="tab-3" data-tab_name="" data-tab="3" class="">
									<span class="thwdtpadmin-tab-label">
										<span class="dashicons dashicons-clock"></span>
										<?php esc_html_e('Pickup Time Settings', 'order-delivery-date-and-time'); ?>
									</span>
								</a>
							</li>

							<li class="thwdtpadmin-tab">
								<a href="javascript:void(0)" id="tab-4" data-tab_name="" data-tab="4" class="">
									<span class="thwdtpadmin-tab-label">
										<span class="dashicons dashicons-admin-generic"></span>
										<?php esc_html_e('Other Settings', 'order-delivery-date-and-time') ?>
									</span>
								</a>
							</li>
			    		</ul>
			    	</div>

			    	<div id="ajax-loader-div">
        				<img id="loading-image" src= "<?php echo esc_url(THWDTP_ASSETS_URL_ADMIN.'/css/ajax-loader.gif') ?>" style="display:none;"/>
    				</div>

			    	<div class="th-col-9 thwdtpadmin-tab-panel-wrapper">
			    		<div id="thwdtpadmin-tab-panels" class="thwdtpadmin-tab-panels"> 
			    			<div class="thwdtpadmin-tab-panel" id="thwdtpadmin-tab-panel-0">
			    				<h3 class="thwdtpadmin-tab-content"><?php esc_html_e('Delivery Date Settings', 'order-delivery-date-and-time') ?></h3>
			    				<div class="thwdtpadmin-tab-content" id="thwdtp-tab-content-0">
			    					<?php $this->delivery_date_settings(); ?>
			    				</div>
			    			</div>
			    			<div class="thwdtpadmin-tab-panel" id="thwdtpadmin-tab-panel-5">
			    				<h3 class="thwdtpadmin-tab-content"><?php esc_html_e('Add Specific Dates', 'order-delivery-date-and-time') ?></h3>
			    				<div class="thwdtpadmin-tab-content" id="thwdtp-tab-content-5">
			    					<?php $this->add_specific_dates(); ?>
			    				</div>
			    			</div>

			    			<div class="thwdtpadmin-tab-panel" id="thwdtpadmin-tab-panel-2">
			    				<h3 class="thwdtpadmin-tab-content"><?php esc_html_e('Delivery Time Settings', 'order-delivery-date-and-time') ?></h3>
			    				<div class="thwdtpadmin-tab-content" id="thwdtp-tab-content-2">
			    					<?php $this->delivery_time_settings(); ?>
			    				</div>
			    			</div>

			    			<div class="thwdtpadmin-tab-panel" id="thwdtpadmin-tab-panel-6">
			    				<h3 class="thwdtpadmin-tab-content"><?php esc_html_e('Add Holidays', 'order-delivery-date-and-time') ?></h3>
			    				<div class="thwdtpadmin-tab-content" id="thwdtp-tab-content-6">
			    					<?php $this->add_holidays(); ?>
			    				</div>
			    			</div>

			    			<div class="thwdtpadmin-tab-panel" id="thwdtpadmin-tab-panel-1">
			    				<h3 class="thwdtpadmin-tab-content"><?php esc_html_e('Pickup Date Settings', 'order-delivery-date-and-time') ?></h3>
			    				    <h4> Based on shipping method - Local Pickup (<em> Enable Fields Based on Shipping Method </em>  in Other settings)</h4>
			    				<div class="thwdtpadmin-tab-content" id="thwdtp-tab-content-1">
			    					<?php $this->pickup_date_settings(); ?>
			    				</div>
			    			</div>

			    			<div class="thwdtpadmin-tab-panel" id="thwdtpadmin-tab-panel-3">
			    				<h3 class="thwdtpadmin-tab-content"><?php esc_html_e('Pickup Time Settings', 'order-delivery-date-and-time') ?></h3>
			    				<div class="thwdtpadmin-tab-content" id="thwdtp-tab-content-3">
			    					<?php $this->pickup_time_settings(); ?>
			    				</div>
			    			</div>

			    			<div class="thwdtpadmin-tab-panel" id="thwdtpadmin-tab-panel-4">
			    				<h3 class="thwdtpadmin-tab-content"><?php esc_html_e('Other Settings', 'order-delivery-date-and-time') ?></h3>
			    				<div class="thwdtpadmin-tab-content" id="thwdtp-tab-content-4">
			    					<?php $this->other_settings(); ?>
			    				</div>
			    			</div>
			    		</div>
		    		</div>
		    	</div>
		    	<div class="clear" style="clear: both"></div>
            </div>
    	</div>
		<?php     	
	}

	public function delivery_date_settings(){
		
		?>
		<form id="thwdtp_delivery_date_settings" method="post" action="">
			<?php wp_nonce_field('thwdtp_delivery_date_settings');
				
				$this->output_fields($this->delivery_date_fields, 'delivery_date');
			?>
			<div class="btn-toolbar">
				<button class="save-btn pull-right btn btn-primary" onclick="thwdtpSaveDeliveryDateSettings(this,event,'delivery_date')">
					<span><?php esc_html_e('Save Changes', 'order-delivery-date-and-time'); ?></span>
				</button>
									
			</div>
		</form>
		<?php
	}

	public function delivery_time_settings(){
	
		?>
		<form id="thwdtp_delivery_time_settings" method="post" action="">
			
			<?php 
				wp_nonce_field('thwdtp_delivery_time_settings');
				$this->output_fields($this->delivery_time_fields, 'delivery_time');
				$this->render_time_slot_content('delivery');
			?>
			<div class="btn-toolbar btn-time-save">
				<button class="save-btn pull-right btn btn-primary" onclick="thwdtpSaveDeliveryTimeSettings(this,event,'delivery-time')">
					<span><?php esc_html_e('Save Changes', 'order-delivery-date-and-time'); ?></span>
				</button>							
			</div>
		</form>
		<?php
		 
	}


	public function pickup_date_settings(){

		?>
		<form id="thwdtp_pickup_date_settings" method="post" action="">
			<?php wp_nonce_field('thwdtp_pickup_date_settings'); 
				$this->output_fields($this->pickup_date_fields, 'pickup_date');
			?>
			<div class="btn-toolbar">
				<button class="save-btn pull-right btn btn-primary" onclick="thwdtpSavePickupDateSettings(this,event,'pickup_date')">
					<span><?php esc_html_e('Save Changes', 'order-delivery-date-and-time'); ?></span>
				</button>
									
			</div>
		</form>
		<?php
	}


	public function pickup_time_settings(){
		?>
		<form id="thwdtp_pickup_time_settings" method="post" action="">
			
			<?php wp_nonce_field('thwdtp_pickup_time_settings'); 
			$this->output_fields($this->pickup_time_fields, 'pickup_time');
			$this->render_time_slot_content('pickup');
			?>
			<div class="btn-toolbar btn-time-save">
				<button class="save-btn pull-right btn btn-primary" onclick="thwdtpSavePickupTimeSettings(this,event,'pickup-time')">
					<span><?php esc_html_e('Save Changes', 'order-delivery-date-and-time'); ?></span>
				</button>							
			</div>
		</form>
		<?php
		
	}

	public function other_settings(){
		?>
		<form id="thwdtp_common_fields_form" method="post" action="">
			<?php wp_nonce_field('thwdtp_common_fields_settings'); 
			$this->output_fields($this->common_fields, 'common_fields');
			?>
			<div class="btn-toolbar">
				<button class="save-btn pull-right btn btn-primary" onclick="thwdtpSaveOtherSettings(this,event,'other_settings')">
					<span><?php esc_html_e('Save Changes', 'order-delivery-date-and-time'); ?></span>
				</button>
									
			</div>
		</form>
		<?php
	}

	public function render_time_slot_content($type){

		$section = $type === 'delivery' ? 'delivery_time' : 'pickup_time';
		$time_slots_property = THWDTP_Admin_Utils::get_time_slot_settings($section);
		
		?>
		<table id="thwdtp_time_slots_<?php echo esc_attr($type) ; ?>" class="wc_gateways widefat thpladmin_fields_table" cellspacing="0">
            <thead>
                <tr><?php $this->render_actions_row($type); ?></tr>
                <tr><?php $this->render_timeslots_table_heading($type); ?></tr>						
            </thead>
            <tfoot>
                <tr><?php $this->render_timeslots_table_heading($type); ?></tr>
                <tr><?php $this->render_actions_row($type); ?></tr>
            </tfoot>
            <tbody>
            <?php 
       
            if($time_slots_property && !empty($time_slots_property)){											
				foreach($time_slots_property  as $key => $time_slot ) {

					if(sizeof($time_slots_property) == 1 && ($key == 'time_settings')){

						echo '<tr><td colspan="10" class="empty-msg-row">'.esc_html__('No Time Slot Added.','order-delivery-date-and-time').'</td></tr>';
					}
					if($key == 'time_settings')
						continue;

					$i=$key;
					$time_slot_general       = isset($time_slot['general_settings']) ? $time_slot['general_settings'] : '';
					$is_enabled              = isset($time_slot_general['enable_delivery_time_slot']) ? $time_slot_general['enable_delivery_time_slot'] : '';
					$is_enabled              = $is_enabled ? $is_enabled : 0;
					$disable_class           = $is_enabled  ? '' : ' thpladmin-disabled';
					$time_slot_range         = isset($time_slot['time_slots']) ? $time_slot['time_slots'] : '';
					$time_slot_type          = isset($time_slot_general['time_slot_for'])? $time_slot_general['time_slot_for'] : '';
					$time_slot_method        = isset($time_slot_general['time_slot_add_method']) ? $time_slot_general['time_slot_add_method'] : '';
					$time_slot_week_days     = isset($time_slot_general['time_slot_type_week_days']) ? $time_slot_general['time_slot_type_week_days'] : '';
					$time_slot_week_days    =  $this->get_week_days_curresponding_number($time_slot_week_days);
					$time_slot_specific_days = isset($time_slot_general['time_slot_type_specific_date']) ? $time_slot_general['time_slot_type_specific_date'] : '';

					$props_json_general      = htmlspecialchars($this->get_property_set_json($time_slot_general));
					$props_json_slot_single  = htmlspecialchars($this->get_property_set_json_slot_single($time_slot_range));

					?>
					<tr class="row_<?php echo esc_attr($i); echo esc_attr($disable_class); ?>">
						<td>
							<input type="hidden" name="f_time_slot[<?php echo esc_attr($i); ?>]" class="f_slot_days" value="<?php echo esc_attr($key); ?>" />
							
							<input type="hidden" name="f_deleted[<?php echo esc_attr($i); ?>]" class="f_deleted" value="0" />
							<input type="hidden" name="f_enabled[<?php echo esc_attr($i); ?>]" class="f_enabled" value="<?php echo esc_attr($is_enabled); ?>" />
							
							 <input type="hidden" name="f_props_general[<?php echo esc_attr($i); ?>]" class="f_props" value="<?php echo esc_attr($props_json_general); ?>" />

							 <input type="hidden" name="f_props_slots[<?php echo esc_attr($i); ?>]" class="f_props_slots" value="<?php echo esc_attr($props_json_slot_single); ?>" />
						</td>

						<td class="td_select"><input type="checkbox" name="select_slot"/></td>
						<?php 
						$field_props_display = $this->get_time_slots_props_display();
						
						foreach( $field_props_display as $pname => $property ){

							if($pname == 'days/dates'){	
								if($time_slot_type == 'week_days'){

									$pvalue = is_array($time_slot_week_days) ? implode(',', $time_slot_week_days) : $time_slot_week_days;
								}else{

									$pvalue = is_array($time_slot_specific_days) ? implode(',', $time_slot_specific_days) : $time_slot_specific_days;
								}
							}else{
								if($pname == 'enable_delivery_time_slot'){
									$pvalue = $is_enabled;
								}else{
									$pvalue = isset($time_slot_general[$pname]) ? $time_slot_general[$pname] : '';
									
									$pvalue = is_array($pvalue) ? implode(',', $pvalue) : $pvalue;
									$pvalue = $pvalue === 'individual_time_slot' ? __('Individual Time Slot' , 'order-delivery-date-and-time') : $pvalue;
									$pvalue = $pvalue === 'bulk_time_slot' ? __('Bulk Time Slot', 'order-delivery-date-and-time'): $pvalue;
								}
							}

							if($property['type'] == 'checkbox'){
								
								$pvalue = $pvalue ? 1 : 0;
							}

							if(isset($property['status']) && $property['status'] == 1){
								$statusHtml = $pvalue == 1 ? '<span class="dashicons dashicons-yes tips" data-tip="'.__('Yes','order-delivery-date-and-time').'"></span>' : '-';
								?>
								<td class="td_<?php echo esc_attr($pname); ?> status"><?php echo  wp_kses_post($statusHtml); ?></td>
								<?php
							}else{
								
								$pvalue = stripslashes($pvalue);
								$tooltip = '';
								$len = isset($property['len']) ? $property['len'] : false;

								if(is_numeric($len) && $len > 0){
									$tooltip = $pvalue;
									$pvalue = $this->truncate_str($pvalue, $len);
								}

								?>
								<td class="td_<?php echo esc_attr($pname); ?>">
									<label title="<?php echo esc_attr($tooltip); ?>"><?php echo esc_html($pvalue); ?></label>
								</td>
								<?php
							}
						}
						?>
					
						<td class="td_actions" align="center">
							<?php if($is_enabled){ ?>
								<span class="f_edit_btn dashicons dashicons-edit tips" data-tip="<?php esc_attr_e('Edit Slot','order-delivery-date-and-time'); ?>"  
								onclick="thwdtpOpenEditTimeSlot(this, <?php echo esc_attr($i); ?>, '<?php echo esc_attr($type) ; ?>')"></span>
							<?php }else{ ?>
								<span class="f_edit_btn dashicons dashicons-edit disabled"></span>
							<?php } ?>
						</td>
					</tr>	

               	 <?php 
				}
					
			}else{
				echo '<tr><td colspan="10" class="empty-msg-row">'.esc_html__('No Time Slot Added.','order-delivery-date-and-time').'</td></tr>';
			} 
			?>
        </tbody>
        </table> 
        <?php
        $this->output_time_slot_form($type);
    }

    public function get_week_days_curresponding_number($week_days){

    	$days_map = $this->days_map ; 
    	$days = array();
    	if(is_array($week_days)){
    		foreach ($week_days as $key => $value) {
    			$days[$key] = $days_map[$value];
    		}
    	}
    	return $days;
    	
    }
	public function get_property_set_json($time_slot){
		if(is_array($time_slot) && !empty($time_slot)){
			$props_set = array();
			foreach( $time_slot as $pname => $pvalue ){
				//$pvalue = isset($time_slot[$property['id']]) ? $time_slot[$property['id']] : '';
				$pvalue = is_array($pvalue) ? implode(',', $pvalue) : $pvalue;
				$pvalue = esc_attr($pvalue);
				// if($property['type'] == 'checkbox'){
				// 	$pvalue = $pvalue ? 1 : 0;
				// }
				$props_set[$pname] = $pvalue;
			}
			return json_encode($props_set);
		}else{
			return '';
		}
	}

	public function get_property_set_json_slot_single($time_slot_range){

		if(is_array($time_slot_range) && !empty($time_slot_range)){
			$props_set = array();
			foreach( $time_slot_range as $slot_key => $tValues){
				$single_slot = array();
				foreach ($tValues as $sKey => $sValue) {
					
					$sValue = is_array($sValue) ? implode(',', $sValue) : $sValue;
					$sValue = esc_attr($sValue);
					$single_slot[$sKey] = $sValue ;
				}

				$props_set[$slot_key] =$single_slot;
			}
										
			return json_encode($props_set);
		}else{
			return '';
		}
	}

    private function output_time_slot_form($type){
		?>
        <div id="thwdtp_time_slot_pp_<?php echo esc_attr($type); ?>" class="thwdtpadmin-modal-mask thwdtp_time_slot_pp">
          <?php $this->output_popup_time_slot_fields($type); ?>
        </div>
        <?php
	}

	private function output_popup_time_slot_fields($type){
		?>

		<div class="thwdtpadmin-modal">
			<div class="modal-container">
				<span class="modal-close" onclick="thwdtpCloseModal(this)">×</span>
				<div class="modal-content">
					<div class="modal-body">
						<div class="form-wizard wizard">
							<main class="form-container main-full">
								<form method="post" id="thwdtp_time_slot_fields_<?php echo esc_attr($type); ?>" action="">
									<input type="hidden" name="f_action" value="" />
									<input type="hidden" name="i_options" value="" />

									<div class="slot-data-panel ">
										<?php $this->render_form_time_slot($type); ?>
									</div>
								</form>
							</main>
							<footer>
								<span class="Loader"></span>
								<div class="btn-toolbar">
									<button class="save-btn pull-right btn btn-primary" onclick="thwdtpSaveDeliveryTimeSlot(this,event,'time_slot', '<?php echo esc_attr($type) ?>')">
										<span><?php esc_html_e('Save & Close','order-delivery-date-and-time'); ?></span>
									</button>
									
								</div>
							</footer>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php
	}


	private function render_form_time_slot($type){
		?>
		
		<span class="wizard-title"  style="font-size: 30px" > <?php esc_html_e('New Time Slot','order-delivery-date-and-time'); ?></span>

		<div style="display: inherit;" class="data-panel-content">
			<div class="err_msgs timeslot-error"><?php esc_html_e('Input Time Slots','order-delivery-date-and-time'); ?></div>
			<table class="thwdtp_timeslot_pp_table form-table thpladmin-form-table">	
			
				<?php 
				$this->output_fields($this->time_slot_fields, '');
				$this->render_time_slot_method_fragments();

				?>
			</table>
			<table id="thwdtp_time_slot_add_methods" class="thwdtp_pp_table "></table>
		</div>
		<?php
	}

	public function add_specific_dates(){

		$specific_dates = THWDTP_Utils::get_settings_by_section('specific_dates');
		?>
		<form id="thwdtp_specific_delivery_date_form" method="post" action="">
			<table class="form-table thpladmin-form-table">
				<tbody>
					<tr>
						<td style = "width:200px;" > <?php esc_html_e('Add Specific Dates','order-delivery-date-and-time'); ?> </td>
						<td style = "width:200px;">	

							<input id="thwdtp_specific_date_datepicker" class="flatpickr flatpickr-input active" type="text" placeholder="Select Date.." readonly="readonly">

						</td>
						
						<td style = "width:125px;">

							<button type="button" class="btn btn-small btn-primary" onclick="thwdtpAddNewSpecificDate()">
								+ Add Date</button>
						</td>
						<td>	
							<div class="err_msgs err-specifictime"><?php esc_html_e('Select a Date','order-delivery-date-and-time'); ?></div>
						</td>
					</tr>

					<tr>
						<td colspan="4">
							
							<table id = "thwdtp_specific_dates_table" class="wc_gateways widefat thpladmin_fields_table" style="margin-top: 100px;">
								<thead>
									<tr>
										<th colspan="2"><?php esc_html_e('Specific Dates','order-delivery-date-and-time'); ?> </th>
									</tr>	
								</thead>
								<tbody>

									<?php 
									if($specific_dates && is_array($specific_dates)){

										foreach ($specific_dates as $specific_date) {
											?>
											<tr>
												<td> <span class="specific-dates"> <?php echo esc_html($specific_date); ?></span>
													<input type="hidden" name="thwdtp_specific_dates[]" value ="<?php echo esc_attr($specific_date); ?>" />
												</td>
												<td><button class="date-remove" onclick="thwdtpRemoveSpecificDeliveryDate(this)"><span class="dashicons dashicons-trash"></span></button></td>
												
											</tr>
											<?php	
										}
									}else{
										echo '<tr class="emty-row"><td colspan="10" class="empty-msg-row">'.esc_html__('No Specific Dates Added.','order-delivery-date-and-time').'</td></tr>';
									}

									?>
								</tbody>
								
								<tfoot></tfoot>
							</table>
						</td>	
					</tr>

					<tr>
						<td colspan="4">
							<button class="save-btn pull-right btn btn-primary" onclick="thwdtpSpecificDeliveryDates(this,event,'specific_dates')">
								<span><?php esc_html_e('Save Changes', 'order-delivery-date-and-time'); ?></span>
							</button>
						</td>
					</tr>
											
				</tbody>
			</table>
		</form>
		<?php
	}

	public function add_holidays(){

		$holidays = THWDTP_Utils::get_settings_by_section('holidays');
		?>
		<form id="thwdtp_delivery_holidays_form" method="post" action="">
			<table class="form-table thpladmin-form-table">
			
				<tbody>
					<tr>
						<td style = "width:200px;" > <?php esc_html_e('Add Holidays', 'order-delivery-date-and-time'); ?> </td>
						<td style = "width:200px;">	

							<input id="thwdtp_holiday_datepicker" class="flatpickr flatpickr-input active" type="text" placeholder="Select Date.." readonly="readonly">

						</td>
						<td style = "width:125px;">

							<button type="button" class="btn btn-small btn-primary" onclick="thwdtpAddNewHolidays()">
								<?php esc_html_e('+ Add Date', 'order-delivery-date-and-time'); ?> </button>
						</td>
						<td>	
							<div class="err_msgs err-holidays"><?php esc_html_e('Select a Date', 'order-delivery-date-and-time'); ?></div>
						</td>
					</tr>

					<tr>
						<td colspan="4">
							
							<table id = "thwdtp_holidays_table" class="wc_gateways widefat thpladmin_fields_table" style="margin-top: 100px;">
								<thead>
									<tr>
										<th colspan="2"> <?php esc_html_e('Holidays', 'order-delivery-date-and-time'); ?> </th>
									</tr>	
								</thead>
								<tbody>

									<?php 
									if($holidays && is_array($holidays)){

										foreach ($holidays as $holiday) {
											?>
											<tr>
												<td> <span class="holidays"> <?php echo esc_html($holiday); ?></span>
													<input type="hidden" name="thwdtp_holidays[]" value ="<?php echo esc_attr($holiday); ?>" />
												</td>
												<td><button class="date-remove" onclick="thwdtpRemoveHoliday(this)">
													<span class="dashicons dashicons-trash"></span></button></td>
												
											</tr>
											<?php	
										}
									}else{
										echo '<tr class="emty-row"><td colspan="10" class="empty-msg-row">'.esc_html__('No Holidays Added.','order-delivery-date-and-time').'</td></tr>';
									}

									?>
								</tbody>
								
								<tfoot></tfoot>
							</table>
						</td>	
					</tr>

					<tr>
						<td colspan="4">
							<button class="save-btn pull-right btn btn-primary" onclick="thwdtpSaveHolidays(this,event,'holidays')">
								<span><?php esc_html_e('Save Changes', 'order-delivery-date-and-time'); ?></span>
							</button>
						</td>
					</tr>
											
				</tbody>
			</table>
		</form>
		<?php
	}
	

	private function render_actions_row($type){
		?>
		<th colspan="6">
			<button type="button" class="btn btn-small btn-primary" onclick="thwdtpOpenNewTimeSlot('<?php echo esc_attr($type); ?>')">
				<?php esc_html_e('+ Add Time Slot','order-delivery-date-and-time'); ?>
			</button>
			<button type="button" class="btn btn-small" onclick="thwdtpRemoveSelectedTimeSlots('<?php echo esc_attr($type); ?>')"><?php  esc_html_e('Remove','order-delivery-date-and-time'); ?></button>
			<button type="button" class="btn btn-small" onclick="thwdtpEnableSelectedTimeSlots('<?php echo esc_attr($type); ?>')"><?php  esc_html_e('Enable','order-delivery-date-and-time'); ?></button>
			<button type="button" class="btn btn-small" onclick="thwdtpDisableSelectedTimeSlots('<?php echo esc_attr($type); ?>')"><?php esc_html_e('Disable','order-delivery-date-and-time'); ?></button>
		</th> 
		<?php 	
	}

	private function render_timeslots_table_heading($type){
		?>
		<th class="sort"></th>
		<th class="sort"></th>
		<th class="slot-days"><?php esc_html_e('Days','order-delivery-date-and-time'); ?></th>
		<th class="slot-label"><?php esc_html_e('Time Slot Method','order-delivery-date-and-time'); ?></th>
		<!-- <th class="max-order-slot"><?php //_e('Max Order/Slot',''); ?></th> -->
		<th class="status"><?php esc_html_e('Enabled','order-delivery-date-and-time'); ?></th>
		<th class="actions align-center"><?php esc_html_e('Actions','order-delivery-date-and-time'); ?></th>
        <?php
	}

	private function render_time_slot_method_fragments(){

		?>

		<table id="thwdtp_order_time_range_id_individual_time_slot" class="thwdtp_order_time_range_single_table" width="100%" style="display:none;">
			<tbody>
				<tr>
				<th class = "label"  colspan="2"> <?php esc_html_e('Time From - To','order-delivery-date-and-time'); ?> </th>

				</tr>
			<tr>
				<td colspan="3" class="p-0">
					<table border="0" cellpadding="0" cellspacing="0" class="thwdtp-time-slot-list thpladmin-dynamic-row-table">
						
						<tbody>
						
							<tr>
								
								<td ><input type="number" name="i_order_time_from_hrs[]" placeholder=00 class="time-slot" min="1" max="12" step="1" onchange="if(parseInt(this.value,10)<10)this.value='0'+this.value;"></td>
								<td><span> : </span> </td>
								<td ><input type="number" name="i_order_time_from_mins[]" min="1" max="59" placeholder=00 class="time-slot"></td>
								<td class="slot-time-format">    
									<select name="i_order_time_from_format[]" style="width: 60px;" class="time-slot">
										<option  value="am"> <?php esc_html_e('AM', 'order-delivery-date-and-time'); ?> </option>
										<option value="pm"> <?php esc_html_e('PM','order-delivery-date-and-time'); ?>  </option>
									</select>
								</td>
								<td> <span style="padding:10px;"> <?php esc_html_e('TO','order-delivery-date-and-time'); ?>  </span></td>

								<td ><input type="number" name="i_order_time_to_hrs[]" placeholder=00 class="time-slot" min="1" max="12"></td>
								<td><span> : </span> </td>
								<td ><input type="number" name="i_order_time_to_mins[]" placeholder=00 class="time-slot" min="1" max="59"></td>
								<td class="slot-time-format">    
									<select name="i_order_time_to_format[]" style="width: 60px;" class="time-slot">
										<option  value="am"> <?php esc_html_e('AM', 'order-delivery-date-and-time'); ?> </option>
										<option value="pm"> <?php esc_html_e('PM','order-delivery-date-and-time'); ?>  </option>
									</select>
								</td>
								<td class="action-cell">
									<a href="javascript:void(0)" onclick="thwdtpAddNewSlotRow(this)" class="btn btn-tiny btn-primary" title="Add new slot">+</a>
								</td>
								<td class="action-cell">
									<a href="javascript:void(0)" onclick="thwdtpRemoveNewSlotRow(this)" class="btn btn-tiny btn-danger" title="Remove option">x</a>
								</td>
							</tr>
						</tbody>
					</table>            	
				</td>
			</tr>
		</tbody>
		</table>

		<table id="thwdtp_order_time_range_id_bulk_time_slot" class="thwdtp_order_time_range_bulk_table" width="100%" style="display:none;">
            
			<tbody>
				<tr>
					<td class="label" style="width:170px;">
						<?php esc_html_e('Time Slot Start From','order-delivery-date-and-time'); ?>
					</td>
					<td ><input type="number" name="i_order_slot_from_hrs" placeholder=00  min =0 max= 12 class="time-slot"></td>
					<td><span> : </span> </td>
					<td ><input type="number" name="i_order_slot_from_mins" placeholder=00 min =0 max= 59 class="time-slot"></td>
					<td class="slot-time-format">    
						<select name="i_order_slot_from_format"  style="width: 60px;" class="time-slot">
							<option selected="am" value="am"> <?php esc_html_e('AM', 'order-delivery-date-and-time'); ?> </option>
							<option value="pm"> <?php esc_html_e('PM','order-delivery-date-and-time'); ?> </option>
						</select>
					</td>

				</tr>

				<tr>
					<td class="label">
						<?php esc_html_e('Time Slot Ends At','order-delivery-date-and-time'); ?>
					</td>
					<td ><input type="number" name="i_order_slot_end_hrs" placeholder=00 min =0 max= 12 class="time-slot"></td>
					<td><span> : </span> </td>
					<td ><input type="number" name="i_order_slot_end_mins" placeholder=00 min =0 max= 59 class="time-slot"></td>
					<td class="slot-time-format">    
						<select name="i_order_slot_end_format" style="width: 60px;" class="time-slot">
							<option selected="am" value="am"> <?php esc_html_e('AM', 'order-delivery-date-and-time'); ?>  </option>
							<option value="pm"> <?php esc_html_e('PM','order-delivery-date-and-time'); ?> </option>
						</select>
					</td>

				</tr>

				<tr>
					<td class="label">
						<?php esc_html_e('Each Time Slot Duration','order-delivery-date-and-time'); ?>
					</td>
					<td ><input type="number" name="i_order_slot_duration_hrs" placeholder=00 min =0 max= 12 class="time-slot"></td>
					<td><span> : </span> </td>
					<td ><input type="number" name="i_order_slot_duration_mins" placeholder=00 min =0 max= 59 class="time-slot"></td>
					
				</tr>

				<tr>
					<td class="label">
						<?php esc_html_e('Interval Between Time Slots','order-delivery-date-and-time'); ?>
					</td>
					<td ><input type="number" name="i_order_slot_interval_hrs" placeholder=00 min =0 max= 12 class="time-slot"></td>
					<td><span> : </span> </td>
					<td ><input type="number" name="i_order_slot_interval_mins" placeholder=00 min =0 max= 59 class="time-slot"></td>
					
				</tr>

			</tbody>   
        </table
		<?php
	}
	


	private function truncate_str($string, $offset){
		if($string && strlen($string) > $offset){
			$string = trim(substr($string, 0, $offset)).'...';
		}
		
		return $string;
	}

	private function get_settings_slots_populate_row($section){

		$all_settings = array();
		$all_settings = THWDTP_Admin_Utils::get_time_slot_settings($section);
		if($all_settings && is_array($all_settings) && !empty($all_settings)){

			$settings_key = '';

			foreach ($all_settings as $slot_key => $slot_values) {

				if($slot_key == 'time_settings'){
					unset($all_settings['time_settings']);
					continue;
				}

				$row_settings = array();

				$time_slot_general      = isset($slot_values['general_settings']) ? $slot_values['general_settings']: '';
				$time_slot_range        = isset($slot_values['time_slots']) ? $slot_values['time_slots'] : '';
				$props_json_general     = htmlspecialchars($this->get_property_set_json($time_slot_general));
				$props_json_slot_single = htmlspecialchars($this->get_property_set_json_slot_single($time_slot_range));

				$row_settings['settings_general'] = $time_slot_general;
				$row_settings['time_slot_range'] = $time_slot_range;	
				$row_settings['json_general'] = $props_json_general;
				$row_settings['json_slot_single'] = $props_json_slot_single;

				$all_settings[$slot_key] = $row_settings;

			}
			return $all_settings;	
		}
		
	}  
}

endif;