/**
 * Cart Init — entry point.
 *
 * Bootstraps the cart system: initializes store and UI,
 * applies cold-fetch guard, sets up event delegation,
 * bridges to theme navigation, and loads extension modules.
 */

import * as store from './cart-store.js';
import * as ui from './cart-ui.js';
import * as add from './cart-add.js';

const config = window.leanCartConfig;

// ── 1. Initialize UI (cache mount points, subscribe to events) ──

ui.init();
add.init();

// ── 2. Cold-fetch guard ─────────────────────────────────────────
//
// Only fetch the cart if WooCommerce has set a session cookie.
// This avoids a round-trip on first visit for new users.

const hasSession =
	document.cookie.includes( 'wp_woocommerce_session' ) ||
	document.cookie.includes( 'woocommerce_items_in_cart' );

if ( hasSession ) {
	store.fetchCart();
}

// ── 3. Event delegation for cart actions ────────────────────────
//
// All interactive cart elements use data-cart-action attributes.
// A single delegated listener on document handles everything.

document.addEventListener( 'click', ( e ) => {
	const btn = e.target.closest( '[data-cart-action]' );
	if ( ! btn ) return;

	const action = btn.dataset.cartAction;
	const row = btn.closest( '[data-cart-item-key]' );
	const key = row?.dataset.cartItemKey;

	switch ( action ) {
		case 'remove':
			if ( key ) store.removeItem( key, { trigger: btn } );
			break;

		case 'increase':
			if ( key ) store.changeQuantity( key, 1 );
			break;

		case 'decrease':
			if ( key ) store.changeQuantity( key, -1 );
			break;

		case 'add':
			// Add-to-cart from archive/product pages (Stage 1.5).
			{
				const id = Number( btn.dataset.productId );
				const qty = Number( btn.dataset.cartQty || 1 );
				if ( id ) {
					e.preventDefault();
					store.addItem( { id, quantity: qty }, { trigger: btn, openDrawer: true } );
				}
			}
			break;
	}
} );

// ── 4. Bridge to theme navigation ──────────────────────────────
//
// The lean-cart:open event requests the cart drawer to open.
// If the theme's numenu system is available, bridge to it.
// Otherwise, themes can listen to this event directly.

document.addEventListener( 'lean-cart:open', () => {
	if ( window.numenu ) {
		window.numenu.open( 'cart' );
	}
} );

// ── 5. Load extension modules conditionally ─────────────────────

if ( config.modules.subscriptions ) {
	import( './modules/subscriptions.js' ).then( m => m.init( store ) );
}

if ( config.modules.mixAndMatch ) {
	import( './modules/mix-and-match.js' ).then( m => m.init( store ) );
}

if ( config.modules.allProductsSubs ) {
	import( './modules/all-products-subs.js' ).then( m => m.init( store ) );
}

// ── 6. Public API ───────────────────────────────────────────────

window.leanCart = {
	store,
	refresh: () => store.fetchCart(),
	open:    () => document.dispatchEvent( new CustomEvent( 'lean-cart:open' ) ),
	close:   () => document.dispatchEvent( new CustomEvent( 'lean-cart:close' ) ),
};
