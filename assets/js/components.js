/**
 * Components every demo can use, with no jQuery:
 * - Tabs: links in [data-wpe-tabs] (href="#panel-id") show one .wpe-tab-panel at a time; the first
 *   link with .is-on (or the URL hash) opens first. Without JavaScript every panel stays visible.
 * - Filter: links in [data-wpe-filter] (href="#category", "#all") hide .wpe-filter-item elements
 *   that lack the class wpe-cat-<category>.
 */
( function () {
	'use strict';

	// Tabs.
	document.querySelectorAll( '[data-wpe-tabs]' ).forEach( function ( toggle ) {
		var scope = toggle.closest( 'main, body' ) || document;
		var periods = scope.querySelectorAll( '.wpe-tab-panel' );
		if ( ! periods.length ) {
			return;
		}
		var links = toggle.querySelectorAll( 'a[href^="#"]' );
		toggle.setAttribute( 'role', 'tablist' );
		function show( id, focus ) {
			periods.forEach( function ( p ) {
				p.hidden = p.id !== id;
			} );
			links.forEach( function ( a ) {
				var on = a.getAttribute( 'href' ) === '#' + id;
				a.classList.toggle( 'is-on', on );
				a.setAttribute( 'aria-selected', on ? 'true' : 'false' );
				if ( on && focus ) {
					a.focus();
				}
			} );
		}
		links.forEach( function ( a ) {
			a.setAttribute( 'role', 'tab' );
			a.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				show( a.getAttribute( 'href' ).slice( 1 ) );
			} );
		} );
		var start = window.location.hash.slice( 1 );
		var first = toggle.querySelector( 'a.is-on' ) || links[ 0 ];
		show( start && scope.querySelector( '.wpe-tab-panel[id="' + start + '"]' ) ? start : first.getAttribute( 'href' ).slice( 1 ) );
	} );

	// Filter.
	document.querySelectorAll( '[data-wpe-filter]' ).forEach( function ( bar ) {
		var cards = document.querySelectorAll( '.wpe-filter-item' );
		var chips = bar.querySelectorAll( 'a[href^="#"]' );
		chips.forEach( function ( a ) {
			a.setAttribute( 'aria-pressed', a.classList.contains( 'is-on' ) ? 'true' : 'false' );
			a.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				var cat = a.getAttribute( 'href' ).slice( 1 );
				chips.forEach( function ( c ) {
					c.classList.toggle( 'is-on', c === a );
					c.setAttribute( 'aria-pressed', c === a ? 'true' : 'false' );
				} );
				cards.forEach( function ( card ) {
					card.hidden = 'all' !== cat && ! card.classList.contains( 'wpe-cat-' + cat );
				} );
			} );
		} );
	} );
}() );
