/* Node-only Counter animation behaviour tests; no browser or third-party dependencies. */
'use strict';

const assert = require( 'node:assert/strict' );
const fs = require( 'node:fs' );
const vm = require( 'node:vm' );
const path = require( 'node:path' );

const code = fs.readFileSync( path.join( __dirname, '../../core/kit/counter/script.js' ), 'utf8' );

function simulate( opts = {} ) {
	const digits = { textContent: opts.final || '1,234.5' };
	const root = {
		dataset: {
			wpeCounter: '1',
			wpeStart: opts.start || '0',
			wpeEnd: opts.end || '1234.5',
			wpeDecimals: '1',
			wpeThousands: ',',
			wpePoint: '.',
			wpeDuration: '200',
			wpeEasing: 'linear',
			wpeAnimate: opts.animate === false ? '0' : '1'
		},
		isConnected: true,
		querySelector: ( selector ) => selector === '.wpe-counter__digits' ? digits : null
	};
	const frames = [];
	let watched = false;
	let disconnected = false;
	const window = {
		matchMedia: () => ( { matches: !!opts.reduced } ),
		requestAnimationFrame: ( callback ) => frames.push( callback ),
		IntersectionObserver: class {
			constructor( callback ) { this.callback = callback; }
			observe() {
				watched = true;
				this.callback( [ { isIntersecting: true } ] );
			}
			disconnect() { disconnected = true; }
		}
	};
	const document = {
		readyState: 'complete',
		querySelectorAll: ( selector ) => selector === '[data-wpe-counter]' ? [ root ] : []
	};
	vm.runInNewContext( code, { window, document, Number, Math }, { filename: 'counter/script.js' } );
	return {
		digits,
		root,
		frames,
		watched: () => watched,
		disconnected: () => disconnected,
		flush: ( stamps ) => {
			for ( const stamp of stamps ) {
				const callback = frames.shift();
				assert.ok( callback, 'Expected an animation frame' );
				callback( stamp );
			}
		}
	};
}

const animated = simulate();
assert.equal( animated.watched(), true, 'Counter begins when intersecting' );
assert.equal( animated.disconnected(), true, 'Animation triggers only once' );
assert.equal( animated.digits.textContent, '0.0', 'Visible counter resets to the starting value' );
animated.flush( [ 0, 100, 200 ] );
assert.equal( animated.digits.textContent, '1,234.5', 'Animation reaches the formatted final value' );
assert.equal( animated.frames.length, 0, 'Animation stops at target' );

const reversed = simulate( { start: '1000', end: '-250.5', final: '-250.5' } );
reversed.flush( [ 0, 100, 200 ] );
assert.equal( reversed.digits.textContent, '-250.5', 'Counter handles descending and negative targets' );

const reduced = simulate( { reduced: true } );
assert.equal( reduced.watched(), false, 'Reduced-motion users do not trigger animation' );
assert.equal( reduced.digits.textContent, '1,234.5', 'Reduced-motion users retain final static value' );

const staticCounter = simulate( { animate: false } );
assert.equal( staticCounter.watched(), false, 'Animation disabled in settings remains static' );
assert.equal( staticCounter.digits.textContent, '1,234.5', 'Static counter retains the server-rendered final number' );

console.log( 'Counter JavaScript animation tests passed.' );
