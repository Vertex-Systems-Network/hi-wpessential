/* Dependency-free Progress Bar animation tests (Node VM). */
'use strict';
const assert = require( 'node:assert/strict' );
const fs = require( 'node:fs' );
const vm = require( 'node:vm' );
const path = require( 'node:path' );
const code = fs.readFileSync( path.join( __dirname, '../../core/kit/progress-bar/script.js' ), 'utf8' );

function simulate( { animate = true, reduced = false, intersection = true, targets = [ 75, 25 ] } = {} ) {
	const frames = [];
	const items = targets.map( ( target ) => {
		const styles = {};
		return {
			dataset: { target: String( target ) },
			style: {
				setProperty: ( key, value ) => { styles[ key ] = value; },
				removeProperty: ( key ) => { delete styles[ key ]; },
				get: ( key ) => styles[ key ]
			},
			classList: { add() {}, remove() {} }
		};
	} );
	let observed = false;
	let disconnected = false;
	let onIntersection = null;
	const root = {
		dataset: { animate: animate ? '1' : '0' },
		style: { getPropertyValue: () => '1100ms' },
		querySelectorAll: () => items,
		isConnected: true
	};
	const window = {
		matchMedia: () => ( { matches: reduced } ),
		requestAnimationFrame: ( callback ) => frames.push( callback ),
		IntersectionObserver: class {
			constructor( callback ) { onIntersection = callback; }
			observe() { observed = true; }
			disconnect() { disconnected = true; }
		}
	};
	const document = { readyState: 'complete', querySelectorAll: () => [ root ] };
	vm.runInNewContext( code, { window, document }, { filename: 'progress-bar/script.js' } );
	if ( intersection && onIntersection ) {
		onIntersection( [ { isIntersecting: true } ] );
	}
	return {
		root, items, frames, observed: () => observed, disconnected: () => disconnected,
		flush: ( time ) => {
			const frame = frames.shift();
			assert.ok( frame, 'Expected a queued frame' );
			frame( time );
		}
	};
}

const animation = simulate( { intersection: false } );
assert.equal( animation.observed(), true, 'Animated item observed for intersection' );
assert.equal( animation.items[ 0 ].style.get( '--wpek-active-progress' ), '0%', 'Reset the visual to zero before entry' );
animation.flush = animation.flush; // Keep the stored instance alive until intersection.
const running = simulate();
assert.equal( running.disconnected(), true, 'Observer disconnects after entering viewport' );
running.flush( 0 );
assert.equal( running.items[ 0 ].style.get( '--wpek-active-progress' ), '0.00%', 'Progress starts at zero' );
running.flush( 550 );
assert.equal( running.items[ 0 ].style.get( '--wpek-active-progress' ), '65.63%', 'Easing animates first indicator' );
assert.equal( running.items[ 1 ].style.get( '--wpek-active-progress' ), '21.88%', 'Multiple indicators animate together' );
running.flush( 1100 );
assert.equal( running.items[ 0 ].style.get( '--wpek-active-progress' ), undefined, 'At completion, remove override to display server final value' );
assert.equal( running.frames.length, 0, 'No frames scheduled after completing animation' );

const staticOne = simulate( { animate: false } );
assert.equal( staticOne.observed(), false, 'Disabled animation never observes or changes styles' );
assert.equal( staticOne.items[ 0 ].style.get( '--wpek-active-progress' ), undefined, 'Disabled animation preserves server final' );
const motion = simulate( { reduced: true } );
assert.equal( motion.observed(), false, 'Reduced-motion users never animate' );
assert.equal( motion.items[ 0 ].style.get( '--wpek-active-progress' ), undefined, 'Reduced-motion users see final value' );
console.log( 'Progress Bar animations and reduced-motion tests passed.' );
