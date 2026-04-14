<?php
/**
 * Script and style registration, localization.
 *
 * Enqueues the cart JS entry point as a native ES module and
 * injects the leanCartConfig object with Store API nonce,
 * currency settings, active modules, and i18n strings.
 */

defined( 'ABSPATH' ) || exit;

class Lean_Cart_Assets {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
		add_filter( 'script_loader_tag', [ $this, 'add_module_type' ], 10, 2 );
	}

	public function enqueue(): void {
		$this->enqueue_styles();
		$this->enqueue_scripts();
	}

	private function enqueue_styles(): void {
		/**
		 * Filter: lean_cart_disable_styles
		 *
		 * Return true to prevent the plugin from enqueuing its CSS.
		 * Useful when the theme provides its own cart styles.
		 *
		 * @param bool $disable Default false.
		 */
		if ( apply_filters( 'lean_cart_disable_styles', false ) ) {
			return;
		}

		$css_file = LEAN_CART_PATH . 'assets/css/lean-cart.css';

		wp_enqueue_style(
			'lean-cart-styles',
			LEAN_CART_URL . 'assets/css/lean-cart.css',
			[],
			file_exists( $css_file ) ? filemtime( $css_file ) : LEAN_CART_VERSION
		);
	}

	private function enqueue_scripts(): void {
		$js_file = LEAN_CART_PATH . 'assets/js/cart-init.js';

		wp_enqueue_script(
			'lean-cart-init',
			LEAN_CART_URL . 'assets/js/cart-init.js',
			[],
			file_exists( $js_file ) ? filemtime( $js_file ) : LEAN_CART_VERSION,
			[ 'strategy' => 'defer', 'in_footer' => true ]
		);

		$config = $this->build_config();

		wp_add_inline_script(
			'lean-cart-init',
			'window.leanCartConfig = ' . wp_json_encode( $config ) . ';',
			'before'
		);

		/**
		 * Action: lean_cart_enqueue_scripts
		 *
		 * Fires after lean cart scripts are enqueued.
		 * Use this to enqueue additional scripts that depend on the cart.
		 */
		do_action( 'lean_cart_enqueue_scripts' );
	}

	private function build_config(): array {
		$wc_price_format = get_woocommerce_price_format();

		$config = [
			'storeApiBase' => esc_url_raw( rest_url( 'wc/store/v1/' ) ),
			'nonce'        => wp_create_nonce( 'wc_store_api' ),
			'checkoutUrl'  => wc_get_checkout_url(),
			'cartUrl'      => wc_get_cart_url(),
			'currency'     => [
				'code'      => get_woocommerce_currency(),
				'symbol'    => html_entity_decode( get_woocommerce_currency_symbol() ),
				'minorUnit' => wc_get_price_decimals(),
				'locale'    => str_replace( '_', '-', get_locale() ),
			],
			'modules'      => [
				'subscriptions'    => class_exists( 'WC_Subscriptions' ),
				'mixAndMatch'      => class_exists( 'WC_Mix_and_Match' ),
				'allProductsSubs'  => class_exists( 'WCS_ATT' ),
			],
			'i18n'         => apply_filters( 'lean_cart_i18n', [
				'emptyCart'    => __( 'Handlekurven er tom', 'lean-cart' ),
				'item'         => __( 'vare', 'lean-cart' ),
				'items'        => __( 'varer', 'lean-cart' ),
				'remove'       => __( 'Fjern', 'lean-cart' ),
				'subtotal'     => __( 'Delsum', 'lean-cart' ),
				'total'        => __( 'Totalt', 'lean-cart' ),
				'shipping'     => __( 'Frakt', 'lean-cart' ),
				'freeShipping' => __( 'Gratis', 'lean-cart' ),
				'updateError'  => __( 'Kunne ikke oppdatere', 'lean-cart' ),
			] ),
		];

		/**
		 * Filter: lean_cart_config
		 *
		 * Modify the configuration object passed to the JS cart.
		 * Extension modules use this to add their own config keys.
		 *
		 * @param array $config The configuration array.
		 */
		return apply_filters( 'lean_cart_config', $config );
	}

	/**
	 * Add type="module" to the cart entry-point script tag.
	 *
	 * WordPress outputs type="text/javascript" by default.
	 * We must replace it (not append) to avoid a duplicate type attribute,
	 * which would cause the browser to use the first one (text/javascript)
	 * and silently break ES module imports.
	 */
		public function add_module_type( string $tag, string $handle ): string {
			if ( 'lean-cart-init' === $handle ) {
				// Replace existing type attribute or add type="module" for ES module support
				if ( strpos( $tag, 'type=' ) !== false ) {
					$tag = preg_replace( '/type="[^"]*"/', 'type="module"', $tag );
				} else {
					$tag = str_replace( '<script ', '<script type="module" ', $tag );
				}
			}
			return $tag;
		}
}
