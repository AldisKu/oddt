<?php

/**
 * The file that defines the core plugin class
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package   order-delivery-date-and-time
 * @subpackageorder-delivery-date-and-time/includes
 */

if(!defined('WPINC')){	die; }

if(!class_exists('THWDTP')):
class THWDTP {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      THWDTP_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	const TEXT_DOMAIN = 'order-delivery-date-and-time';
	public function __construct() {
		if ( defined( 'THWDTP_VERSION' ) ) {
			$this->version = THWDTP_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'order-delivery-date-and-time';

		$this->load_dependencies();
		$this->define_block_hooks();

		add_action( 'init', array($this, 'init' ));
	}

	public function init(){
		$this->set_locale();
		$this->define_constants();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}
	
	private function define_constants(){
		!defined('THWDTP_ASSETS_URL_ADMIN') && define('THWDTP_ASSETS_URL_ADMIN', THWDTP_URL . 'admin/assets/');
		!defined('THWDTP_ASSETS_URL_PUBLIC') && define('THWDTP_ASSETS_URL_PUBLIC', THWDTP_URL . 'public/assets/');
		!defined('THWDTP_WOO_ASSETS_URL') && define('THWDTP_WOO_ASSETS_URL', WC()->plugin_url() . '/assets/');
	}

	private function load_dependencies() {

		if(!function_exists('is_plugin_active')){
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-thwdtp-autoloader.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		//require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-thwdtp-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-thwdtp-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-thwdtp-public.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/utils/class-thwdtp-utils.php';

		//$this->loader = new THWDTP_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */

	private function set_locale() {
		add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
	}

	public function load_plugin_textdomain() {

		$locale = apply_filters('plugin_locale', get_locale(), self::TEXT_DOMAIN);
		
		load_textdomain(self::TEXT_DOMAIN, WP_LANG_DIR.'/order-delivery-date-and-time/'.self::TEXT_DOMAIN.'-'.$locale.'.mo');
		load_plugin_textdomain(self::TEXT_DOMAIN, false, dirname(THWDTP_BASE_NAME) . '/languages/');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new THWDTP_Admin( $this->get_plugin_name(), $this->get_version() );

		add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_styles_and_scripts' ));
		add_action( 'admin_menu', array( $plugin_admin, 'admin_menu'));
		add_filter( 'woocommerce_screen_ids', array( $plugin_admin, 'add_screen_id' ));
		add_filter( 'plugin_action_links_'.THWDTP_BASE_NAME, array( $plugin_admin, 'plugin_action_links' ));
		//$this->loader->add_filter( 'plugin_row_meta', $plugin_admin, 'plugin_row_meta', 10, 2 );
		$wdtp_data = THWDTP_Admin_Settings_General::instance();
		add_action( 'wp_ajax_thwdtp_save_settings', array( $wdtp_data, 'thwdtp_save_settings'));
		add_action( 'wp_ajax_thwdtp_get_specific_dates', array( $wdtp_data, 'get_specific_dates'));
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new THWDTP_Public( $this->get_plugin_name(), $this->get_version() );
		
		add_action( 'wp_enqueue_scripts', array( $plugin_public, 'enqueue_styles_and_scripts' ));
	}

	private function define_block_hooks(){

		$plugin_block = new THWDTP_Block( $this->get_plugin_name(), $this->get_version() );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Plugin_Name_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
endif;
