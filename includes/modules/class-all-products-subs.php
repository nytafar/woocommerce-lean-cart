<?php
/**
 * All Products for WooCommerce Subscriptions integration module.
 *
 * Loaded only when WCS_ATT is active.
 * APfS does not extend the Store API cart item data natively,
 * so this module registers a custom extension to expose the
 * active subscription scheme on each cart item.
 */

defined( 'ABSPATH' ) || exit;

class Lean_Cart_All_Products_Subs {

	public function __construct() {
		add_filter( 'lean_cart_config', [ $this, 'extend_config' ] );
		add_action( 'woocommerce_blocks_loaded', [ $this, 'register_store_api_data' ] );
	}

	public function extend_config( array $config ): array {
		$config['i18n']['purchaseOneTime']    = __( 'Engangskjøp', 'lean-cart' );
		$config['i18n']['purchaseSubscribe']  = __( 'Abonnement', 'lean-cart' );

		return $config;
	}

	/**
	 * Register custom Store API extension data for APfS cart items.
	 *
	 * Exposes the active subscription scheme so the JS normalizer
	 * can display purchase mode (one-time vs subscription).
	 */
	public function register_store_api_data(): void {
		if ( ! function_exists( 'woocommerce_store_api_register_endpoint_data' ) ) {
			return;
		}

		woocommerce_store_api_register_endpoint_data( [
			'endpoint'        => \Automattic\WooCommerce\StoreApi\Schemas\V1\CartItemSchema::IDENTIFIER,
			'namespace'       => 'lean_cart_apfs',
			'data_callback'   => [ $this, 'cart_item_data' ],
			'schema_callback' => [ $this, 'cart_item_schema' ],
			'schema_type'     => ARRAY_A,
		] );
	}

	/**
	 * Add APfS scheme data to each cart item in the Store API response.
	 */
	public function cart_item_data(): array {
		return [
			'active_scheme_key'  => '',
			'is_subscription'    => false,
			'scheme_description' => '',
		];
	}

	/**
	 * Schema for the APfS extension data.
	 */
	public function cart_item_schema(): array {
		return [
			'active_scheme_key' => [
				'description' => __( 'Active subscription scheme key', 'lean-cart' ),
				'type'        => 'string',
				'context'     => [ 'view', 'edit' ],
				'readonly'    => true,
			],
			'is_subscription' => [
				'description' => __( 'Whether the item is in subscription mode', 'lean-cart' ),
				'type'        => 'boolean',
				'context'     => [ 'view', 'edit' ],
				'readonly'    => true,
			],
			'scheme_description' => [
				'description' => __( 'Human-readable plan description', 'lean-cart' ),
				'type'        => 'string',
				'context'     => [ 'view', 'edit' ],
				'readonly'    => true,
			],
		];
	}
}
