/**
 * Mix and Match extension module.
 *
 * Registers a normalizer that reads MnM parent-child data
 * from Store API extensions and restructures flat cart items
 * into nested groups.
 *
 * After all items are normalized, a post-processor groups
 * children under their parent containers.
 */

const config = window.leanCartConfig;

export function init( store ) {
	// Phase 1: Mark individual items as containers or children.
	store.addNormalizer( ( item, raw ) => {
		const ext = raw.extensions?.mix_and_match;
		if ( ! ext ) return item;

		// Container: has child_items array.
		if ( Array.isArray( ext.child_items ) && ext.child_items.length > 0 ) {
			item.type = 'mix-and-match';
			item.childKeys = ext.child_items;
			item.badges.push( config.i18n?.mnmContainer || 'Sammensetning' );
		}

		// Child: has a container reference.
		if ( ext.container ) {
			item.isChild = true;
			item.parentKey = ext.container;
		}

		return item;
	} );

	// Phase 2: After a cart:updated event, restructure items
	// to nest children under parents. This runs on the
	// already-normalized state.
	document.addEventListener( 'lean-cart:updated', ( e ) => {
		const state = e.detail.state;
		if ( ! state || ! state.items ) return;

		const containers = new Map();
		const orphans = [];

		for ( const item of state.items ) {
			if ( item.childKeys ) {
				item.children = [];
				containers.set( item.key, item );
			}
		}

		// Nothing to restructure if no MnM containers.
		if ( containers.size === 0 ) return;

		const restructured = [];

		for ( const item of state.items ) {
			if ( item.isChild && item.parentKey && containers.has( item.parentKey ) ) {
				containers.get( item.parentKey ).children.push( item );
			} else {
				restructured.push( item );
			}
		}

		state.items = restructured;
	} );
}
