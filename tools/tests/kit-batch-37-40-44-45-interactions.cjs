/* Alerts and image comparison — behavior tests with no browser dependencies. */
'use strict';
const assert = require( 'node:assert/strict' );
const fs = require( 'node:fs' );
const path = require( 'node:path' );
const vm = require( 'node:vm' );
const alertSource = fs.readFileSync( path.join( __dirname, '../../core/kit/alert/script.js' ), 'utf8' );
const comparisonSource = fs.readFileSync( path.join( __dirname, '../../core/kit/image-comparison/script.js' ), 'utf8' );

function button() {
	return {
		hidden: true, events: {},
		addEventListener( event, callback ) { this.events[ event ] = callback; },
		click() { assert.ok( this.events.click ); this.events.click(); }
	};
}
function testAlert( remember, storageOk = true, preset = false ) {
	const dismiss = button();
	const messages = new Map( preset ? [ [ 'wpe-alert:demo', 'dismissed' ] ] : [] );
	const storage = {
		getItem( key ) { if ( ! storageOk ) { throw Error( 'Blocked' ); } return messages.get( key ); },
		setItem( key, value ) { if ( ! storageOk ) { throw Error( 'Blocked' ); } messages.set( key, value ); }
	};
	const root = {
		dataset: { remember: remember ? '1' : '0', alertKey: 'demo' },
		hidden: false,
		querySelector( selector ) { return selector === '.wpe-alert__dismiss' ? dismiss : null; }
	};
	const document = { readyState: 'complete', querySelectorAll: () => [ root ] };
	vm.runInNewContext( alertSource, { window: { sessionStorage: storage }, document } );
	return { root, dismiss, messages };
}
const notice = testAlert( true );
assert.equal( notice.root.hidden, false, 'Alert visible before user dismisses' );
assert.equal( notice.dismiss.hidden, false, 'Dismiss button revealed after JS initializes' );
notice.dismiss.click();
assert.equal( notice.root.hidden, true, 'Click hides notice' );
assert.equal( notice.messages.get( 'wpe-alert:demo' ), 'dismissed', 'Opt-in dismissal persisted in browser session' );
assert.equal( testAlert( true, true, true ).root.hidden, true, 'Previously dismissed session notice stays hidden' );
const privateNotice = testAlert( true, false );
privateNotice.dismiss.click();
assert.equal( privateNotice.root.hidden, true, 'Alert remains dismissible when sessionStorage blocked' );
const temporary = testAlert( false );
temporary.dismiss.click();
assert.equal( temporary.messages.size, 0, 'Nonpersistent dismissal does not write storage' );

const range = {
	hidden: true, value: '60', attrs: {}, events: {}, labels: [ { textContent: 'Before and after' } ],
	setAttribute( key, value ) { this.attrs[ key ] = value; },
	addEventListener( event, callback ) { this.events[ event ] = callback; },
	input( value ) { this.value = String( value ); this.events.input(); }
};
const applied = [];
const comparison = {
	dataset: {},
	style: { setProperty( name, value ) { applied.push( { name, value } ); } },
	querySelector( selector ) { return selector === '.wpe-image-comparison__slider' ? range : null; }
};
const document = { readyState: 'complete', querySelectorAll: () => [ comparison ] };
vm.runInNewContext( comparisonSource, { window: {}, document } );
assert.equal( range.hidden, false, 'Image comparison range enables after JS' );
assert.equal( applied.at( -1 ).value, '60%', 'Image comparison initial split respects configured value' );
range.input( 86 );
assert.equal( applied.at( -1 ).value, '86%', 'Native range input moves comparison split' );
assert.equal( range.attrs[ 'aria-valuetext' ], '86% Before and after', 'Accessible range value updated' );
range.input( 500 );
assert.equal( applied.at( -1 ).value, '100%', 'Slider clamps malformed input to 100%' );
console.log( 'Alert dismissal and Image Comparison keyboard-range interactions passed.' );
