/* Social Feed progressive enhancement tests; no JS => all cards visible. */
'use strict';
const assert = require( 'node:assert/strict' );
const fs = require( 'node:fs' );
const vm = require( 'node:vm' );
const path = require( 'node:path' );
const source = fs.readFileSync( path.join( __dirname, '../../core/kit/social-feed/script.js' ), 'utf8' );
function run( initial, size ) {
	const items = Array.from( { length: size }, () => ( { hidden: false } ) );
	const button = { hidden: true, listeners: {}, addEventListener( name, callback ) { this.listeners[ name ] = callback; }, click() { assert.equal( typeof this.listeners.click, 'function' ); this.listeners.click(); } };
	const root = { dataset: { initial: String( initial ) }, querySelectorAll( sel ) { return sel === '[data-wpe-feed-item]' ? items : []; }, querySelector( sel ) { return sel === '.wpe-social-feed__more' ? button : null; } };
	assert.ok( items.every( item => ! item.hidden ), 'With no JS, all curated items remain visible' );
	const document = { readyState: 'complete', querySelectorAll: () => [ root ] };
	vm.runInNewContext( source, { document, window: {} } );
	return { root, items, button };
}
const feed = run( 2, 5 );
assert.deepEqual( feed.items.map( item => item.hidden ), [ false, false, true, true, true ], 'Initial visible limit applied when enhanced' );
assert.equal( feed.button.hidden, false, 'Load-more control appears for hidden content' );
feed.button.click();
assert.deepEqual( feed.items.map( item => item.hidden ), [ false, false, false, false, true ], 'Show more reveals another complete batch' );
feed.button.click();
assert.equal( feed.items.every( item => ! item.hidden ), true, 'All remaining items are revealed' );
assert.equal( feed.button.hidden, true, 'Show-more control disappears after last batch' );
const short = run( 6, 3 );
assert.equal( short.items.every( item => ! item.hidden ), true, 'Small feed not truncated' );
assert.equal( short.button.hidden, true, 'No unnecessary button on small feed' );
console.log( 'Social Feed load-more progressive enhancement checks passed.' );
