<?php
/**
 * WooCommerce Subscriptions integration module.
 *
 * Loaded only when WC_Subscriptions is active.
 * Adds subscription period strings and config to the JS layer.
 * The Store API extension is already provided by WC Subscriptions
 * via WC_Subscriptions_Extend_Store_Endpoint.
 */

defined( 'ABSPATH' ) || exit;

class Lean_Cart_Subscriptions {

	public function __construct() {
		add_filter( 'lean_cart_config', [ $this, 'extend_config' ] );
	}

	public function extend_config( array $config ): array {
		$config['subscriptionPeriods'] = [
			'day'   => __( 'dag', 'lean-cart' ),
			'week'  => __( 'uke', 'lean-cart' ),
			'month' => __( 'måned', 'lean-cart' ),
			'year'  => __( 'år', 'lean-cart' ),
		];

		$config['subscriptionIntervals'] = [
			/* translators: %s: period name (e.g., "måned") */
			'singular' => __( 'Hver %s', 'lean-cart' ),
			/* translators: %1$d: interval number, %2$s: period name */
			'plural'   => __( 'Hver %1$d. %2$s', 'lean-cart' ),
		];

		$config['i18n']['subscription']  = __( 'Abonnement', 'lean-cart' );
		$config['i18n']['signupFee']     = __( 'Oppstartsavgift', 'lean-cart' );
		$config['i18n']['freeTrial']     = __( 'Gratis prøveperiode', 'lean-cart' );

		return $config;
	}
}
