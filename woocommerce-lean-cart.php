<?php
/**
 * Plugin Name: WooCommerce Lean Cart
 * Plugin URI: https://jellum.net
 * Description: A minimal, modular WooCommerce cart built on the Store API. Replaces legacy cart fragments with a vanilla JS state layer and event-driven UI.
 * Version: 0.1.0
 * Author: Lasse Jellum
 * Author URI: https://jellum.net
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * WC requires at least: 8.0
 * Text Domain: lean-cart
 * Domain Path: /languages
 * License: GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

define( 'LEAN_CART_VERSION', '0.1.0' );
define( 'LEAN_CART_PATH', plugin_dir_path( __FILE__ ) );
define( 'LEAN_CART_URL', plugin_dir_url( __FILE__ ) );

/**
 * Boot the plugin after WooCommerce is loaded.
 */
add_action( 'plugins_loaded', function () {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	require_once LEAN_CART_PATH . 'includes/class-lean-cart.php';
	Lean_Cart::get_instance();
} );
