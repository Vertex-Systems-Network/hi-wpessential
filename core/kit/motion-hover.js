/**
 * Pointer-follow Kit effects. Loaded only when a rendered element opts into one.
 */
( function () {
	'use strict';
	var selector = '.wpek-hp-magnetic,.wpek-hp-tilt-follow,.wpek-hp-ripple,.wpek-hp-cursor,.wpek-hp-spotlight';
	var reduced = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	function setup( node ) {
		if ( node.dataset.wpekPointerReady || reduced ) return;
		node.dataset.wpekPointerReady = '1';
		var update = function ( event ) {
			var rect = node.getBoundingClientRect();
			if ( ! rect.width || ! rect.height ) return;
			var x = Math.max( 0, Math.min( 100, ( event.clientX - rect.left ) / rect.width * 100 ) );
			var y = Math.max( 0, Math.min( 100, ( event.clientY - rect.top ) / rect.height * 100 ) );
			node.style.setProperty( '--wpek-pointer-x', x + '%' );
			node.style.setProperty( '--wpek-pointer-y', y + '%' );
			if ( node.classList.contains( 'wpek-hp-magnetic' ) ) {
				node.style.setProperty( '--wpek-magnetic-x', Math.round( ( x - 50 ) * .16 ) + 'px' );
				node.style.setProperty( '--wpek-magnetic-y', Math.round( ( y - 50 ) * .16 ) + 'px' );
			}
			if ( node.classList.contains( 'wpek-hp-tilt-follow' ) ) {
				node.style.setProperty( '--wpek-tilt-x', ( ( 50 - y ) * .12 ).toFixed( 2 ) + 'deg' );
				node.style.setProperty( '--wpek-tilt-y', ( ( x - 50 ) * .12 ).toFixed( 2 ) + 'deg' );
			}
		};
		node.addEventListener( 'pointermove', update, { passive: true } );
		node.addEventListener( 'pointerleave', function () {
			node.style.removeProperty( '--wpek-magnetic-x' );
			node.style.removeProperty( '--wpek-magnetic-y' );
			node.style.removeProperty( '--wpek-tilt-x' );
			node.style.removeProperty( '--wpek-tilt-y' );
		} );
		if ( node.classList.contains( 'wpek-hp-ripple' ) ) {
			node.addEventListener( 'pointerdown', function ( event ) {
				update( event );
				node.classList.remove( 'is-wpek-ripple-active' );
				void node.offsetWidth;
				node.classList.add( 'is-wpek-ripple-active' );
			} );
		}
	}
	function boot( root ) {
		( root || document ).querySelectorAll( selector ).forEach( setup );
	}
	if ( document.readyState === 'loading' ) document.addEventListener( 'DOMContentLoaded', function () { boot(); } );
	else boot();
	new MutationObserver( function ( records ) {
		records.forEach( function ( record ) {
			record.addedNodes.forEach( function ( node ) {
				if ( node.nodeType === 1 ) {
					if ( node.matches( selector ) ) setup( node );
					boot( node );
				}
			} );
		} );
	} ).observe( document.documentElement, { childList: true, subtree: true } );
}() );
