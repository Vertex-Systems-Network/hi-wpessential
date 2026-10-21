/**
 * WPEssential.org marketplace: pricing period tabs and demo category filters.
 * Without JavaScript the monthly plans and every demo stay visible.
 */
( function () {
	'use strict';

	// Pricing: one period at a time, chosen by the toggle; #yearly / #lifetime links open that tab.
	document.querySelectorAll( '[data-ess-toggle]' ).forEach( function ( toggle ) {
		var scope = toggle.closest( 'main, body' ) || document;
		var periods = scope.querySelectorAll( '.ess-period' );
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
		show( scope.querySelector( '.ess-period[id="' + start + '"]' ) ? start : 'monthly' );
	} );

	// Demo library: category chips hide the cards of other categories.
	document.querySelectorAll( '[data-ess-filter]' ).forEach( function ( bar ) {
		var cards = document.querySelectorAll( '.ess-demo' );
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
					card.hidden = 'all' !== cat && ! card.classList.contains( 'ess-cat-' + cat );
				} );
			} );
		} );
	} );
}() );
