<?php
use Automattic\WooCommerce\Blocks\Package;
use Automattic\WooCommerce\Blocks\StoreApi\Schemas\CartSchema;
use Automattic\WooCommerce\Blocks\StoreApi\Schemas\CheckoutSchema;

/**
 * Shipping Workshop Extend Store API.
 */
class Fields_Extend_Store_Endpoint{
	/**
	 * Stores Rest Extending instance.
	 *
	 * @var ExtendRestApi
	 */
	private static $extend;

	/**
	 * Plugin Identifier, unique to each plugin.
	 *
	 * @var string
	 */
	const IDENTIFIER = 'order-delivery-fields-block';

	/**
	 * Bootstraps the class and hooks required data.
	 *
	 */
	public static function init() {
		self::$extend = Automattic\WooCommerce\StoreApi\StoreApi::container()->get( Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema::class );
		self::extend_store();
	}

	/**
	 * Registers the actual data into each endpoint.
	 */
	public static function extend_store() {

		if ( is_callable( [ self::$extend, 'register_endpoint_data' ] ) ) {
			self::$extend->register_endpoint_data(
				[
					'endpoint'        => CheckoutSchema::IDENTIFIER,
					'namespace'       => self::IDENTIFIER,
					'schema_callback' => ['Fields_Extend_Store_Endpoint', 'extend_checkout_schema' ],
					'schema_type'     => ARRAY_A,
				]
			);
		}

	}


	/**
	 * Register shipping workshop schema into the Checkout endpoint.
	 *
	 * @return array Registered schema.
	 *
	 */
	public static function extend_checkout_schema() {
        /**
         *
         * We need to describe the shape of the data we're adding to the Checkout endpoint.
         *
         * This function should return an array. Since we're adding two keys on the client, this function should
         * return an array with  keys. Each key describes the shape of the data for each field coming from the client.
         *
         */

		// can add optional

        return [

            'orderDeliveryDate'   => [
            'description' => __('Allow users to use delivery date', ''),
                'type'        => [ 'string', 'null' ],
                'context'     => ['view','edit'],
                'readonly'    => true,
                'arg_options' => [
                    'validate_callback' => function( $value ) {
                        return ( $value );
                    },
                ]
            ],
			'orderDeliveryTime'   => [
			'description' => __('Allow users to use delivery Time', ''),
				'type'        => [ 'string', 'null' ],
				'context'     => ['view','edit'],
				'readonly'    => true,
				'arg_options' => [
					'validate_callback' => function( $value ) {
						return ( $value );
					},
				]
			],
			'orderPickupDate'   => [
			'description' => __('Allow users to use pickup date', ''),
				'type'        => [ 'string', 'null' ],
				'context'     => ['view','edit'],
				'readonly'    => true,
				'arg_options' => [
					'validate_callback' => function( $value ) {
						return ( $value );
					},
				]
			],
			'orderPickupTime'   => [
				'description' => __('Allow users to use pickup time', ''),
					'type'        => [ 'string', 'null' ],
					'context'     => ['view','edit'],
					'readonly'    => true,
					'arg_options' => [
						'validate_callback' => function( $value ) {
							return ( $value );
						},
					]
				],
        ];

    }
}
