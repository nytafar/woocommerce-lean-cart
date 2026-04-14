/**
 * All Products for WooCommerce Subscriptions extension module.
 *
 * Registers a normalizer that reads APfS scheme data from the
 * custom Store API extension (lean_cart_apfs) and sets the
 * purchaseMode on each cart item.
 */

const config = window.leanCartConfig;

export function init( store ) {
	store.addNormalizer( ( item, raw ) => {
		const ext = raw.extensions?.lean_cart_apfs;
		if ( ! ext ) return item;

		if ( ext.is_subscription ) {
			item.purchaseMode = 'subscription';
			if ( ext.scheme_description ) {
				item.subscriptionSummary = item.subscriptionSummary || ext.scheme_description;
			}
			if ( ! item.badges.includes( config.i18n?.purchaseSubscribe || 'Abonnement' ) ) {
				item.badges.push( config.i18n?.purchaseSubscribe || 'Abonnement' );
			}
		} else {
			item.purchaseMode = 'one-time';
		}

		return item;
	} );
}
