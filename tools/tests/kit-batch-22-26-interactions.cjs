/* Node VM tests: countdown time calculations, expiry, SVG visibility and reduced motion. */
'use strict';
const assert = require( 'node:assert/strict' );
const fs = require( 'node:fs' );
const path = require( 'node:path' );
const vm = require( 'node:vm' );
const countdown = fs.readFileSync( path.join( __dirname, '../../core/kit/countdown/script.js' ), 'utf8' );
const svg = fs.readFileSync( path.join( __dirname, '../../core/kit/svg/script.js' ), 'utf8' );

function countdownTest( end, start ) {
	const nodes = {};
	for ( const name of [ 'days', 'hours', 'minutes', 'seconds' ] ) {
		const number = { textContent: '--' };
		nodes[ name ] = { dataset: { unit: name }, querySelector: () => number, number };
	}
	const completed = { hidden: true };
	let current = start;
	let callback = null, cleared = false;
	const root = {
		isConnected: true,
		dataset: { end: String( end ) },
		querySelectorAll: () => Object.values( nodes ),
		querySelector: ( selector ) => selector === '.wpe-countdown__completed' ? completed : null
	};
	const document = { readyState: 'complete', querySelectorAll: () => [ root ] };
	const window = {
		setInterval( fn, ms ) { assert.equal( ms, 1000 ); callback = fn; return 1; },
		clearInterval( handle ) { assert.equal( handle, 1 ); cleared = true; }
	};
	vm.runInNewContext( countdown, { document, window, Date: { now: () => current } } );
	return {
		root, nodes, completed,
		advance( ms ) { current += ms; if ( callback ) { callback(); } },
		get cleared() { return cleared; },
		get intervalCreated() { return typeof callback === 'function'; }
	};
}

const now = 2000000000000;
const counter = countdownTest( now + ( 86400 + 3600 + 120 + 5 ) * 1000, now );
assert.equal( counter.nodes.days.number.textContent, '01', 'Days computed from epoch milliseconds' );
assert.equal( counter.nodes.hours.number.textContent, '01', 'Hours computed from remainder' );
assert.equal( counter.nodes.minutes.number.textContent, '02', 'Minutes computed from remainder' );
assert.equal( counter.nodes.seconds.number.textContent, '05', 'Seconds computed from remainder' );
assert.equal( counter.root.dataset.ready, '1', 'Countdown enhanced only once' );
counter.advance( 5000 );
assert.equal( counter.nodes.seconds.number.textContent, '00', 'Countdown decrements without accumulating tick drift' );
assert.equal( counter.cleared, false );
counter.advance( 90120000 );
assert.equal( counter.root.dataset.expired, '1', 'Countdown marks expiry' );
assert.equal( counter.completed.hidden, false, 'Accessible completion message revealed' );
assert.equal( counter.cleared, true, 'Countdown interval cleared on completion' );

const expired = countdownTest( now - 1000, now );
assert.equal( expired.intervalCreated, false, 'Expired countdown creates no interval' );
assert.equal( expired.root.dataset.expired, '1', 'Expired countdown immediately finishes' );

function svgTest( { reduced = false, intersection = true } = {} ) {
	let callback;
	let disconnected = false, observed = false;
	const root = { dataset: {} };
	const window = {
		matchMedia: () => ( { matches: reduced } ),
		IntersectionObserver: class {
			constructor( fn ) { callback = fn; }
			observe() { observed = true; }
			disconnect() { disconnected = true; }
		}
	};
	const document = { readyState: 'complete', querySelectorAll: () => [ root ] };
	vm.runInNewContext( svg, { document, window } );
	if ( intersection && callback ) { callback( [ { isIntersecting: true } ] ); }
	return { root, observed, disconnected };
}
const animated = svgTest();
assert.equal( animated.observed, true, 'SVG waits for viewport entry' );
assert.equal( animated.root.dataset.visible, '1', 'SVG enables animation when visible' );
assert.equal( animated.disconnected, true, 'SVG animates on first intersection only' );
const still = svgTest( { reduced: true } );
assert.equal( still.observed, false, 'Reduced-motion SVG never registers animator' );
assert.equal( still.root.dataset.visible, undefined, 'Reduced-motion SVG remains static' );
const below = svgTest( { intersection: false } );
assert.equal( below.root.dataset.visible, undefined, 'Below-viewport SVG remains static' );
console.log( 'Countdown and SVG animation tests passed.' );
