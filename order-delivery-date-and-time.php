<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Order Delivery Date and Time
 * Plugin URI:        https://themehigh.com/order-delivery-date
 * Description:       Organize your order delivery with ease, with a simple order/pickup date-time planner for your store.
 * Version:           1.1.1
 * Author:            ThemeHigh
 * Author URI:        https://themehigh.com/
 * Text Domain:       order-delivery-date-and-time
 * Domain Path:       /languages
 * Requires Plugins: woocommerce
 * WC requires at least: 5.0.0
 * WC tested up to: 10.1
 */

if(!defined('WPINC')){	die; }

if (!function_exists('is_woocommerce_active')){
	function is_woocommerce_active(){
	    $active_plugins = (array) get_option('active_plugins', array());
	    if(is_multisite()){
		   $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
	    }
	    return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins) || class_exists('WooCommerce');
	}
}

if(is_woocommerce_active()) {

	define( 'THWDTP_VERSION', '1.1.1' );
	!defined('THWDTP_SOFTWARE_TITLE') && define('THWDTP_SOFTWARE_TITLE', 'Order Delivery Date and Time');
	!defined('THWDTP_FILE') && define('THWDTP_FILE', __FILE__);
	!defined('THWDTP_PATH') && define('THWDTP_PATH', plugin_dir_path( __FILE__ ));
	!defined('THWDTP_URL') && define('THWDTP_URL', plugins_url( '/', __FILE__ ));
	!defined('THWDTP_BASE_NAME') && define('THWDTP_BASE_NAME', plugin_basename( __FILE__ ));

	add_action( 'before_woocommerce_init', function() {
	    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
	        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	    }
	});

	/**
	 * The code that runs during plugin activation.
	 */
	function activate_thwdtp() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-thwdtp-activator.php';
		THWDTP_Activator::activate();
	}

	/**
	 * The code that runs during plugin deactivation.
	 */
	function deactivate_thwdtp() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-thwdtp-deactivator.php';
		THWDTP_Deactivator::deactivate();
	}

	register_activation_hook( __FILE__, 'activate_thwdtp' );
	register_deactivation_hook( __FILE__, 'deactivate_thwdtp' );

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-thwdtp.php';

	/**
	 * Begins execution of the plugin.
	 */
	function run_thwdtp() {

		$plugin = new THWDTP();

	}
	run_thwdtp();
}
