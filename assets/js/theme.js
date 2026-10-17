/**
 * WPEssential front-end: sticky header shadow, back-to-top, Woo quantity steppers.
 * No dependencies. Loaded with the `defer` strategy.
 */
( function () {
	'use strict';

	var header = document.querySelector( '.wpe-header' );
	var toTop = document.querySelector( '.wpe-back-to-top' );

	function onScroll() {
		var y = window.scrollY || 0;
		if ( header ) {
			header.classList.toggle( 'is-scrolled', y > 8 );
		}
		if ( toTop ) {
			toTop.classList.toggle( 'is-visible', y > 600 );
		}
	}
	window.addEventListener( 'scroll', onScroll, { passive: true } );
	onScroll();

	if ( toTop ) {
		toTop.addEventListener( 'click', function () {
			window.scrollTo( { top: 0, behavior: 'smooth' } );
		} );
	}

	// Quantity +/- buttons around WooCommerce .qty inputs (classic templates).
	function enhanceQty( root ) {
		( root || document ).querySelectorAll( '.quantity:not(.wpe-qty-ready)' ).forEach( function ( wrap ) {
			var input = wrap.querySelector( 'input.qty' );
			if ( ! input || input.type === 'hidden' ) {
				return;
			}
			wrap.classList.add( 'wpe-qty-ready' );
			[ [ '-', -1 ], [ '+', 1 ] ].forEach( function ( def ) {
				var btn = document.createElement( 'button' );
				btn.type = 'button';
				btn.className = 'wpe-qty-btn';
				btn.textContent = def[ 0 ] === '-' ? '−' : '+';
				btn.setAttribute( 'aria-label', def[ 1 ] < 0 ? 'Decrease quantity' : 'Increase quantity' );
				btn.addEventListener( 'click', function () {
					var step = parseFloat( input.step ) || 1;
					var min = input.min !== '' ? parseFloat( input.min ) : 0;
					var max = input.max !== '' ? parseFloat( input.max ) : Infinity;
					var next = ( parseFloat( input.value ) || 0 ) + def[ 1 ] * step;
					input.value = Math.min( max, Math.max( min, next ) );
					input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
				} );
				if ( def[ 1 ] < 0 ) {
					wrap.insertBefore( btn, input );
				} else {
					wrap.appendChild( btn );
				}
			} );
		} );
	}
	enhanceQty();
	// WooCommerce re-renders the cart table (jQuery events) after AJAX updates.
	if ( window.jQuery ) {
		window.jQuery( document.body ).on( 'updated_cart_totals updated_wc_div', function () {
			enhanceQty();
		} );
	}
}() );

// Light / dark toggle: flips <html data-wpe-scheme> and remembers the choice.
( function () {
	var root = document.documentElement;
	function sync() {
		var dark = root.getAttribute( 'data-wpe-scheme' ) === 'dark';
		document.querySelectorAll( '.wpe-theme-toggle' ).forEach( function ( btn ) {
			btn.setAttribute( 'aria-pressed', dark ? 'true' : 'false' );
			btn.setAttribute( 'aria-label', btn.getAttribute( dark ? 'data-label-light' : 'data-label-dark' ) || '' );
		} );
	}
	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest && e.target.closest( '.wpe-theme-toggle' );
		if ( ! btn ) {
			return;
		}
		var next = root.getAttribute( 'data-wpe-scheme' ) === 'dark' ? 'light' : 'dark';
		root.setAttribute( 'data-wpe-scheme', next );
		try {
			window.localStorage.setItem( 'wpe-scheme', next );
		} catch ( err ) {}
		sync();
	} );
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', sync );
	} else {
		sync();
	}
} )();
