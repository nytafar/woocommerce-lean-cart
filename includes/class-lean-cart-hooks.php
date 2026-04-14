<?php
/**
 * WooCommerce integration hooks.
 *
 * Disables legacy cart fragments and wires up the custom hook
 * registry that developers can use to extend the cart.
 */

defined( 'ABSPATH' ) || exit;

class Lean_Cart_Hooks {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'disable_legacy_fragments' ], 20 );
		add_filter( 'woocommerce_cart_fragment_refresh', '__return_false' );
	}

	/**
	 * Dequeue the legacy wc-cart-fragments script.
	 *
	 * This system sends PHP-rendered HTML blobs via AJAX and holds
	 * a PHP session lock that blocks concurrent WP requests.
	 * Replaced entirely by Store API JSON fetches.
	 */
	public function disable_legacy_fragments(): void {
		wp_dequeue_script( 'wc-cart-fragments' );
	}
}
