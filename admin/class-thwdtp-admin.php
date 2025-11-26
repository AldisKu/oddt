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
                $this->pickup_schedule_admin_hooks();
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

        private function pickup_schedule_admin_hooks(){
                add_filter('woocommerce_product_data_tabs', array($this, 'add_pickup_schedule_product_tab'));
                add_action('woocommerce_product_data_panels', array($this, 'render_pickup_schedule_product_panel'));
                add_action('woocommerce_admin_process_product_object', array($this, 'save_pickup_schedule_product_panel'));

                add_action('product_cat_add_form_fields', array($this, 'render_pickup_schedule_category_fields'));
                add_action('product_cat_edit_form_fields', array($this, 'render_pickup_schedule_category_fields'));
                add_action('created_product_cat', array($this, 'save_pickup_schedule_category_fields'));
                add_action('edited_product_cat', array($this, 'save_pickup_schedule_category_fields'));
        }

        private function get_days_map(){
                return array(
                        0 => __('Sunday','order-delivery-date-and-time'),
                        1 => __('Monday','order-delivery-date-and-time'),
                        2 => __('Tuesday','order-delivery-date-and-time'),
                        3 => __('Wednesday','order-delivery-date-and-time'),
                        4 => __('Thursday','order-delivery-date-and-time'),
                        5 => __('Friday','order-delivery-date-and-time'),
                        6 => __('Saturday','order-delivery-date-and-time'),
                );
        }

        public function add_pickup_schedule_product_tab($tabs){
                $tabs['thwdtp_pickup_schedule'] = array(
                        'label'    => __('Pickup Schedule','order-delivery-date-and-time'),
                        'target'   => 'thwdtp_pickup_schedule_data',
                        'priority' => 80,
                );

                return $tabs;
        }

        public function render_pickup_schedule_product_panel(){
                global $post;

                $product_id = $post ? $post->ID : 0;
                $schedule   = get_post_meta($product_id, '_thwdtp_pickup_schedule', true);
                $enabled    = isset($schedule['enabled']) ? wc_string_to_bool($schedule['enabled']) : false;
                $min_time   = isset($schedule['min_time']) ? intval($schedule['min_time']) : '';
                $allowable  = isset($schedule['allowable_days']) ? intval($schedule['allowable_days']) : '';
                $off_days   = isset($schedule['off_days']) && is_array($schedule['off_days']) ? $schedule['off_days'] : array();
                $enable_time = isset($schedule['enable_time']) ? wc_string_to_bool($schedule['enable_time']) : false;
                $time_slots  = isset($schedule['time_slots']) && is_array($schedule['time_slots']) ? implode("\n", $schedule['time_slots']) : '';

                $days_map = $this->get_days_map();
                ?>
                <div id="thwdtp_pickup_schedule_data" class="panel woocommerce_options_panel">
                        <div class="options_group">
                                <?php
                                woocommerce_wp_checkbox(array(
                                        'id'          => '_thwdtp_pickup_schedule_enabled',
                                        'label'       => __('Use custom pickup schedule','order-delivery-date-and-time'),
                                        'value'       => $enabled ? 'yes' : 'no',
                                        'description' => __('Override the global pickup rules for this product.','order-delivery-date-and-time'),
                                ));

                                woocommerce_wp_text_input(array(
                                        'id'                => '_thwdtp_pickup_schedule_min_time',
                                        'label'             => __('Preparation time (minutes)','order-delivery-date-and-time'),
                                        'type'              => 'number',
                                        'custom_attributes' => array('min' => 0),
                                        'value'             => $min_time,
                                ));

                                woocommerce_wp_text_input(array(
                                        'id'                => '_thwdtp_pickup_schedule_allowable_days',
                                        'label'             => __('Valid days','order-delivery-date-and-time'),
                                        'type'              => 'number',
                                        'custom_attributes' => array('min' => 1),
                                        'value'             => $allowable,
                                ));
                                ?>

                                <p class="form-field">
                                        <label for="_thwdtp_pickup_schedule_off_days"><?php esc_html_e('Off days','order-delivery-date-and-time'); ?></label>
                                        <select id="_thwdtp_pickup_schedule_off_days" name="_thwdtp_pickup_schedule_off_days[]" class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e('Select off days','order-delivery-date-and-time');?>">
                                                <?php foreach ($days_map as $day_key => $day_label){ ?>
                                                        <option value="<?php echo esc_attr($day_key); ?>" <?php selected(in_array($day_key, $off_days, true)); ?>><?php echo esc_html($day_label); ?></option>
                                                <?php } ?>
                                        </select>
                                        <?php echo wc_help_tip(__('Days on which pickup is not available for this product.','order-delivery-date-and-time')); ?>
                                </p>

                                <?php
                                woocommerce_wp_checkbox(array(
                                        'id'          => '_thwdtp_pickup_schedule_enable_time',
                                        'label'       => __('Override pickup time slots','order-delivery-date-and-time'),
                                        'value'       => $enable_time ? 'yes' : 'no',
                                        'description' => __('Provide custom pickup time slots for this product.','order-delivery-date-and-time'),
                                ));
                                ?>

                                <p class="form-field">
                                        <label for="_thwdtp_pickup_schedule_time_slots"><?php esc_html_e('Pickup time slots','order-delivery-date-and-time'); ?></label>
                                        <textarea class="short" rows="4" cols="20" id="_thwdtp_pickup_schedule_time_slots" name="_thwdtp_pickup_schedule_time_slots" placeholder="09:00 - 11:00&#10;14:00 - 17:00"><?php echo esc_textarea($time_slots); ?></textarea>
                                        <?php echo wc_help_tip(__('Enter one time slot per line using the site time format. All slots apply to every pickup day.','order-delivery-date-and-time')); ?>
                                </p>
                        </div>
                </div>
                <?php
        }

        public function save_pickup_schedule_product_panel($product){
                $enabled   = isset($_POST['_thwdtp_pickup_schedule_enabled']) ? 'yes' === wc_clean($_POST['_thwdtp_pickup_schedule_enabled']) : false;
                $min_time  = isset($_POST['_thwdtp_pickup_schedule_min_time']) ? absint($_POST['_thwdtp_pickup_schedule_min_time']) : '';
                $allowable = isset($_POST['_thwdtp_pickup_schedule_allowable_days']) ? absint($_POST['_thwdtp_pickup_schedule_allowable_days']) : '';
                $off_days  = isset($_POST['_thwdtp_pickup_schedule_off_days']) ? array_map('absint', (array) wp_unslash($_POST['_thwdtp_pickup_schedule_off_days'])) : array();
                $enable_time = isset($_POST['_thwdtp_pickup_schedule_enable_time']) ? 'yes' === wc_clean($_POST['_thwdtp_pickup_schedule_enable_time']) : false;
                $time_slots  = isset($_POST['_thwdtp_pickup_schedule_time_slots']) ? explode("\n", wp_unslash($_POST['_thwdtp_pickup_schedule_time_slots'])) : array();

                $slots = array();
                if($time_slots){
                        foreach ($time_slots as $slot){
                                $slot = trim($slot);
                                if(!empty($slot)){
                                        $slots[] = $slot;
                                }
                        }
                }

                $schedule = array(
                        'enabled'       => $enabled,
                        'min_time'      => $min_time,
                        'allowable_days'=> $allowable,
                        'off_days'      => $off_days,
                        'enable_time'   => $enable_time,
                        'time_slots'    => $slots,
                );

                if($enabled){
                        $product->update_meta_data('_thwdtp_pickup_schedule', $schedule);
                }else{
                        $product->delete_meta_data('_thwdtp_pickup_schedule');
                }
        }

        public function render_pickup_schedule_category_fields($term = false){
                $editing = is_object($term);
                $schedule = $editing ? get_term_meta($term->term_id, 'thwdtp_pickup_schedule', true) : array();

                $enabled     = isset($schedule['enabled']) ? wc_string_to_bool($schedule['enabled']) : false;
                $min_time    = isset($schedule['min_time']) ? intval($schedule['min_time']) : '';
                $allowable   = isset($schedule['allowable_days']) ? intval($schedule['allowable_days']) : '';
                $off_days    = isset($schedule['off_days']) && is_array($schedule['off_days']) ? $schedule['off_days'] : array();
                $enable_time = isset($schedule['enable_time']) ? wc_string_to_bool($schedule['enable_time']) : false;
                $time_slots  = isset($schedule['time_slots']) && is_array($schedule['time_slots']) ? implode("\n", $schedule['time_slots']) : '';

                $days_map = $this->get_days_map();

                if(!$editing){
                        echo '<div class="form-field">';
                }else{
                        echo '<tr class="form-field">';
                        echo '<th scope="row" valign="top"><label>'. esc_html__('Pickup Schedule','order-delivery-date-and-time') .'</label></th><td>';
                }
                ?>
                <p>
                        <label>
                                <input type="checkbox" name="thwdtp_pickup_schedule[enabled]" value="yes" <?php checked($enabled); ?> />
                                <?php esc_html_e('Use custom pickup schedule','order-delivery-date-and-time'); ?>
                        </label>
                </p>
                <p>
                        <label for="thwdtp_pickup_schedule_min_time"><?php esc_html_e('Preparation time (minutes)','order-delivery-date-and-time'); ?></label>
                        <input name="thwdtp_pickup_schedule[min_time]" id="thwdtp_pickup_schedule_min_time" type="number" min="0" value="<?php echo esc_attr($min_time); ?>" />
                </p>
                <p>
                        <label for="thwdtp_pickup_schedule_allowable_days"><?php esc_html_e('Valid days','order-delivery-date-and-time'); ?></label>
                        <input name="thwdtp_pickup_schedule[allowable_days]" id="thwdtp_pickup_schedule_allowable_days" type="number" min="1" value="<?php echo esc_attr($allowable); ?>" />
                </p>
                <p>
                        <label for="thwdtp_pickup_schedule_off_days"><?php esc_html_e('Off days','order-delivery-date-and-time'); ?></label>
                        <select name="thwdtp_pickup_schedule[off_days][]" id="thwdtp_pickup_schedule_off_days" multiple="multiple" class="wc-enhanced-select" data-placeholder="<?php esc_attr_e('Select off days','order-delivery-date-and-time');?>">
                                <?php foreach ($days_map as $day_key => $day_label){ ?>
                                        <option value="<?php echo esc_attr($day_key); ?>" <?php selected(in_array($day_key, $off_days, true)); ?>><?php echo esc_html($day_label); ?></option>
                                <?php } ?>
                        </select>
                </p>
                <p>
                        <label>
                                <input type="checkbox" name="thwdtp_pickup_schedule[enable_time]" value="yes" <?php checked($enable_time); ?> />
                                <?php esc_html_e('Override pickup time slots','order-delivery-date-and-time'); ?>
                        </label>
                </p>
                <p>
                        <label for="thwdtp_pickup_schedule_time_slots"><?php esc_html_e('Pickup time slots','order-delivery-date-and-time'); ?></label>
                        <textarea name="thwdtp_pickup_schedule[time_slots]" id="thwdtp_pickup_schedule_time_slots" rows="4" cols="40" placeholder="09:00 - 11:00&#10;14:00 - 17:00"><?php echo esc_textarea($time_slots); ?></textarea>
                </p>
                <?php
                if(!$editing){
                        echo '</div>';
                }else{
                        echo '</td></tr>';
                }
        }

        public function save_pickup_schedule_category_fields($term_id){
                if(!isset($_POST['thwdtp_pickup_schedule'])){
                        return;
                }

                $raw_schedule = wp_unslash($_POST['thwdtp_pickup_schedule']);
                $enabled      = isset($raw_schedule['enabled']) && 'yes' === $raw_schedule['enabled'];
                $min_time     = isset($raw_schedule['min_time']) ? absint($raw_schedule['min_time']) : '';
                $allowable    = isset($raw_schedule['allowable_days']) ? absint($raw_schedule['allowable_days']) : '';
                $off_days     = isset($raw_schedule['off_days']) ? array_map('absint', (array) $raw_schedule['off_days']) : array();
                $enable_time  = isset($raw_schedule['enable_time']) && 'yes' === $raw_schedule['enable_time'];
                $time_slot_str = isset($raw_schedule['time_slots']) ? (string) $raw_schedule['time_slots'] : '';

                $slots = array();
                if($time_slot_str){
                        foreach (explode("\n", $time_slot_str) as $slot){
                                $slot = trim($slot);
                                if($slot){
                                        $slots[] = $slot;
                                }
                        }
                }

                if($enabled){
                        $schedule = array(
                                'enabled'        => $enabled,
                                'min_time'       => $min_time,
                                'allowable_days' => $allowable,
                                'off_days'       => $off_days,
                                'enable_time'    => $enable_time,
                                'time_slots'     => $slots,
                        );
                        update_term_meta($term_id, 'thwdtp_pickup_schedule', $schedule);
                }else{
                        delete_term_meta($term_id, 'thwdtp_pickup_schedule');
                }
        }
}
