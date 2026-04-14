<?php
/**
 * Mix and Match Products integration module.
 *
 * Loaded only when WC_Mix_and_Match is active.
 * The Store API extension is already provided by WC_MNM_Store_API.
 * This module adds i18n strings and any extra config the JS
 * normalizer needs for parent-child grouping.
 */

defined( 'ABSPATH' ) || exit;

class Lean_Cart_Mix_And_Match {

	public function __construct() {
		add_filter( 'lean_cart_config', [ $this, 'extend_config' ] );
	}

	public function extend_config( array $config ): array {
		$config['i18n']['mnmContainer']   = __( 'Sammensetning', 'lean-cart' );
		$config['i18n']['mnmItemsSelected'] = __( '%d valgt', 'lean-cart' );

		return $config;
	}
}
