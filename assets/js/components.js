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

	// Facet filter (sidebar). Markup is links and spans (block HTML keeps no form controls):
	// a[data-facet][href="#value"] toggles a facet, [data-facet-search] / [data-facet-sort] become a search box / select,
	// [data-facet-reset] clears. Cards are .wpe-filter-item with classes wpe-<facet>-<value>; [data-facet-count] shows the total.
	document.querySelectorAll( '[data-wpe-facets]' ).forEach( function ( side ) {
		var scope = side.closest( '.wpe-ess-facet-layout' ) || document;
		var cards = Array.prototype.slice.call( scope.querySelectorAll( '.wpe-filter-item' ) );
		var count = scope.querySelector( '[data-facet-count]' );
		var links = side.querySelectorAll( 'a[data-facet]' );
		var search = document.createElement( 'input' );
		var sort = document.createElement( 'select' );
		var slot = side.querySelector( '[data-facet-search]' );
		var sortSlot = scope.querySelector( '[data-facet-sort]' );
		search.type = 'search';
		search.placeholder = slot ? slot.textContent : 'Search';
		search.setAttribute( 'aria-label', search.placeholder );
		if ( slot ) {
			slot.textContent = '';
			slot.appendChild( search );
		}
		if ( sortSlot ) {
			sortSlot.textContent.split( '|' ).forEach( function ( label, n ) {
				var o = document.createElement( 'option' );
				o.value = [ 'newest', 'az', 'za' ][ n ] || 'newest';
				o.textContent = label.trim();
				sort.appendChild( o );
			} );
			sort.setAttribute( 'aria-label', 'Sort demos' );
			sortSlot.textContent = '';
			sortSlot.appendChild( sort );
		}
		var title = function ( c ) { var h = c.querySelector( 'h2, h3' ); return h ? h.textContent.trim() : ''; };
		var apply = function () {
			var q = search.value.trim().toLowerCase();
			var on = {};
			links.forEach( function ( a ) {
				var v = a.getAttribute( 'href' ).slice( 1 );
				if ( a.classList.contains( 'is-on' ) && v ) {
					( on[ a.dataset.facet ] = on[ a.dataset.facet ] || [] ).push( v );
				}
			} );
			var shown = 0;
			cards.forEach( function ( card ) {
				var ok = ! q || card.textContent.toLowerCase().indexOf( q ) !== -1;
				Object.keys( on ).forEach( function ( g ) {
					ok = ok && on[ g ].some( function ( v ) { return card.classList.contains( 'wpe-' + g + '-' + v ); } );
				} );
				card.hidden = ! ok;
				shown += ok ? 1 : 0;
			} );
			if ( count ) {
				count.textContent = shown + ( 1 === shown ? ' demo' : ' demos' );
			}
			var dir = sort.value === 'za' ? -1 : 1;
			cards.slice().sort( function ( x, y ) {
				return sort.value === 'az' || sort.value === 'za' ? dir * title( x ).localeCompare( title( y ) ) : cards.indexOf( x ) - cards.indexOf( y );
			} ).forEach( function ( c ) { c.parentNode.appendChild( c ); } );
		};
		links.forEach( function ( a ) {
			a.setAttribute( 'role', a.dataset.single ? 'radio' : 'checkbox' );
			a.setAttribute( 'aria-checked', a.classList.contains( 'is-on' ) ? 'true' : 'false' );
			a.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				if ( a.dataset.single ) {
					side.querySelectorAll( 'a[data-facet="' + a.dataset.facet + '"]' ).forEach( function ( o ) {
						o.classList.toggle( 'is-on', o === a );
						o.setAttribute( 'aria-checked', o === a ? 'true' : 'false' );
					} );
				} else {
					a.classList.toggle( 'is-on' );
					a.setAttribute( 'aria-checked', a.classList.contains( 'is-on' ) ? 'true' : 'false' );
				}
				apply();
			} );
		} );
		var reset = side.querySelector( '[data-facet-reset]' );
		if ( reset ) {
			reset.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				search.value = '';
				links.forEach( function ( a ) {
					var keep = a.dataset.single && '#' === a.getAttribute( 'href' );
					a.classList.toggle( 'is-on', !! keep );
					a.setAttribute( 'aria-checked', keep ? 'true' : 'false' );
				} );
				apply();
			} );
		}
		search.addEventListener( 'input', apply );
		sort.addEventListener( 'change', apply );
		apply();
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
