/* Privacy-first Map interaction tests: no iframe until explicit click. */
'use strict';
const assert = require( 'node:assert/strict' );
const fs = require( 'node:fs' );
const vm = require( 'node:vm' );
const path = require( 'node:path' );
const source = fs.readFileSync( path.join( __dirname, '../../core/kit/map/script.js' ), 'utf8' );

function run( url ) {
	const button = {
		dataset: { mapSrc: url, mapTitle: 'Test location' },
		listeners: {},
		addEventListener( name, callback ) { this.listeners[ name ] = callback; }
	};
	const frame = { replaced: [], replaceChildren( node ) { this.replaced.push( node ); } };
	const root = {
		dataset: {},
		querySelector( key ) {
			return { '.wpe-map__load': button, '.wpe-map__frame': frame }[ key ] || null;
		}
	};
	const elements = [];
	const document = {
		readyState: 'complete',
		querySelectorAll: () => [ root ],
		createElement( tag ) {
			elements.push( tag );
			return {};
		}
	};
	vm.runInNewContext( source, { window: {}, document } );
	return { root, button, frame, elements };
}
const trusted = run( 'https://www.openstreetmap.org/export/embed.html?bbox=0&layer=mapnik' );
assert.equal( trusted.elements.length, 0, 'Third-party iframe never created before user consent' );
assert.equal( trusted.frame.replaced.length, 0, 'Initial frame preserves consent explanation' );
trusted.button.listeners.click();
assert.equal( trusted.frame.replaced.length, 1, 'Click-to-load replaces placeholder with iframe' );
assert.equal( trusted.frame.replaced[ 0 ].title, 'Test location', 'Iframe has accessible title' );
assert.equal( trusted.frame.replaced[ 0 ].referrerPolicy, 'no-referrer', 'Iframe suppresses referrer' );
assert.equal( trusted.frame.replaced[ 0 ].src, trusted.button.dataset.mapSrc, 'Trusted OpenStreetMap URL loads' );

const evil = run( 'https://attacker.example/export/embed.html?bbox=1' );
evil.button.listeners.click();
assert.equal( evil.elements.length, 0, 'Untrusted provider rejected even after user clicks' );
const downgrade = run( 'http://www.openstreetmap.org/export/embed.html?bbox=1' );
downgrade.button.listeners.click();
assert.equal( downgrade.elements.length, 0, 'HTTP downgrade rejected' );
console.log( 'Map consent and URL safety tests passed.' );
