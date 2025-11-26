<?php
use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

/**
 * Class for integrating with WooCommerce Blocks
 */
 class Fields_Block_Integration implements IntegrationInterface {

	/**
	 * The name of the integration.
	 *
	 * @return string
	 */
	protected $pickup_location_enable;
	public function get_name() {
		return 'order-delivery-date-and-time';
	}

	/**
	 * When called invokes any initialization/setup for the integration.
	 */
	public function initialize() {

		$pickup_location_settings = get_option( 'woocommerce_pickup_location_settings', [] );
		$this->pickup_location_enable = wc_string_to_bool( $pickup_location_settings['enabled'] ?? 'no' );

		$this->register_delivery_block_frontend_scripts();
		$this->register_delivery_block_frontend_styles();
		$this->register_delivery_block_editor_scripts();
		//$this->register_block_editor_styles();

		if($this->pickup_location_enable){

			$this->register_pickup_block_frontend_scripts();
			$this->register_pickup_block_editor_scripts();
			//$this->register_pickup_block_editor_styles();
			$this->register_pickup_block_frontend_styles();
		}

		$this->register_main_integration();
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		$script_handles = ['thwdtp-block-integration', 'delivery-fields-block-frontend'];
		if($this->pickup_location_enable){
			array_push($script_handles, 'pickup-fields-block-frontend');
		} 
		return $script_handles;
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		$script_edit_handles = [ 'thwdtp-block-integration', 'delivery-fields-block-editor'];
		if($this->pickup_location_enable){
			array_push($script_edit_handles, 'pickup-fields-block-editor');
		}	
		return $script_edit_handles;
	}

	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data() {
		$data = [
			'order-fields-block-active' => true,
			'th_local_pickup' => 'enabled',
		];

		return $data;

	}

	/**
	 * Registers the main JS file required to add filters and Slot/Fills.
	 */
	private function register_main_integration() {
		$script_path = '/assets/dist/index.js';
		$style_path  = '/assets/dist/style-index.css';

		$script_url = plugins_url( $script_path, __FILE__ );
		$style_url  = plugins_url( $style_path, __FILE__ );

		$script_asset_path = dirname( __FILE__ ) . '/assets/dist/index.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: [
				'dependencies' => [],
				'version'      => $this->get_file_version( $script_path ),
			];

		// wp_enqueue_style(
		// 	'thwdtp-block-integration',
		// 	$style_url,
		// 	[],
		// 	$this->get_file_version( $style_path )
		// );

		wp_register_script(
			'thwdtp-block-integration',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
		wp_set_script_translations(
			'thwdtp-block-integration',
			' order-delivery-date-and-time',
			dirname( __FILE__ ) . '/languages'
		);
	}

	public function register_block_editor_styles() {
		$style_path = '/assets/dist/style-delivery-fields.css';
		$style_url = plugins_url( $style_path, __FILE__ );
		wp_enqueue_style(
			'order-fields-block',
			$style_url,
			[],
			$this->get_file_version( $style_path )
		);
	}

	public function register_pickup_block_editor_styles() {
		$style_path = '/assets/dist/style-pickup-fields.css';

		$style_url = plugins_url( $style_path, __FILE__ );
		wp_enqueue_style(
			'pickup-fields-block',
			$style_url,
			[],
			$this->get_file_version( $style_path )
		);
	}

	public function register_delivery_block_editor_scripts() {
		$script_path       = '/assets/dist/delivery-fields.js';
		$script_url        = plugins_url( $script_path, __FILE__ );
		$script_asset_path = dirname( __FILE__ ) . '/assets/dist/delivery-fields.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: [
				'dependencies' => [],
				'version'      => $this->get_file_version( $script_asset_path ),
			];

		wp_register_script(
			'delivery-fields-block-editor',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_set_script_translations(
			'delivery-fields-block-editor',
			'order-delivery-date-and-time',
			dirname( __FILE__ ) . '/languages'
		);
	}

	public function register_pickup_block_editor_scripts() {
		$script_path       = '/assets/dist/pickup-fields.js';
		$script_url        = plugins_url( $script_path, __FILE__ );
		$script_asset_path = dirname( __FILE__ ) . '/assets/dist/pickup-fields.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: [
				'dependencies' => [],
				'version'      => $this->get_file_version( $script_asset_path ),
			];

		wp_register_script(
			'pickup-fields-block-editor',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_set_script_translations(
			'pickup-fields-block-editor',
			'order-delivery-date-and-time',
			dirname( __FILE__ ) . '/languages'
		);
	}

	public function register_delivery_block_frontend_styles(){
		// $style_path = '/assets/dist/style-delivery-fields-frontend.css';

		// $style_url = plugins_url( $style_path, __FILE__ );
		// wp_enqueue_style(
		// 	'delivery-fields-block-frontend',
		// 	$style_url,
		// 	[],
		// 	$this->get_file_version( $style_path )
		// );

		$dpicker_style_path = '/assets/dist/delivery-fields-frontend.css';
		$dpicker_style_url = plugins_url( $dpicker_style_path, __FILE__ );
		wp_enqueue_style(
			'delivery-fields-datepicker',
			$dpicker_style_url,
			[],
			$this->get_file_version( $dpicker_style_path)
		);
	}

	public function register_delivery_block_frontend_scripts() {

		$script_path       = '/assets/dist/delivery-fields-frontend.js';
		$script_url        = plugins_url( $script_path, __FILE__ );
		$script_asset_path = dirname( __FILE__ ) . '/assets/dist/delivery-fields-frontend.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: [
				'dependencies' => [],
				'version'      => $this->get_file_version( $script_asset_path ),
			];

		wp_register_script(
			'delivery-fields-block-frontend',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
		wp_set_script_translations(
			'delivery-fields-block-frontend',
			'order-delivery-date-and-time',
			dirname( __FILE__ ) . '/languages'
		);
		// $wdtp_var = array(

		// 	'ajax_url'             => admin_url( 'admin-ajax.php' ),
		// 	'holidays'             => 'holidays',
		// );
		// wp_localize_script('order-fields-block-frontend','thwdtp_public_var', $wdtp_var);
	}

	public function register_pickup_block_frontend_styles(){
		$style_path = '/assets/dist/style-pickup-fields-frontend.css';

		$style_url = plugins_url( $style_path, __FILE__ );
		wp_enqueue_style(
			'pickup-fields-block-frontend',
			$style_url,
			[],
			$this->get_file_version( $style_path )
		);

		$dpicker_style_path = '/assets/dist/pickup-fields-frontend.css';
		$dpicker_style_url = plugins_url( $dpicker_style_path, __FILE__ );
		wp_enqueue_style(
			'pickup-fields-datepicker',
			$dpicker_style_url,
			[],
			$this->get_file_version( $dpicker_style_path )
		);
	}

	public function register_pickup_block_frontend_scripts() {
		$script_path       = '/assets/dist/pickup-fields-frontend.js';
		$script_url        = plugins_url( $script_path, __FILE__ );
		$script_asset_path = dirname( __FILE__ ) . '/assets/dist/pickup-fields-frontend.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: [
				'dependencies' => [],
				'version'      => $this->get_file_version( $script_asset_path ),
			];

		wp_register_script(
			'pickup-fields-block-frontend',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
		wp_set_script_translations(
			'pickup-fields-block-frontend',
			'order-delivery-date-and-time',
			dirname( __FILE__ ) . '/languages'
		);
	}

	/**
	 * Get the file modified time as a cache buster if we're in dev mode.
	 *
	 * @param string $file Local path to the file.
	 * @return string The cache buster value to use for the given file.
	 */
	protected function get_file_version( $file ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
			return filemtime( $file );
		}
		return THWDTP_VERSION;
	}
}
