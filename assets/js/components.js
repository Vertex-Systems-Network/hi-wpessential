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
			// "Load more": only the first [data-wpe-more] matching cards show until the button is pressed.
			var more = scope.querySelector( '[data-wpe-more]' );
			var moreCount = scope.querySelector( '[data-wpe-more-count]' );
			var limit = more ? parseInt( more.dataset.wpeMore, 10 ) * ( parseInt( more.dataset.page || '1', 10 ) ) : Infinity;
			var visible = cards.filter( function ( c ) { return ! c.hidden; } ).sort( function ( x, y ) {
				return Array.prototype.indexOf.call( x.parentNode.children, x ) - Array.prototype.indexOf.call( y.parentNode.children, y );
			} );
			visible.forEach( function ( c, i ) { c.hidden = i >= limit; } );
			if ( moreCount ) {
				moreCount.textContent = 'Showing ' + Math.min( limit, visible.length ) + ' of ' + visible.length + ' demos';
			}
			if ( more ) {
				more.hidden = visible.length <= limit;
			}
		};
		var moreBtn = scope.querySelector( '[data-wpe-more]' );
		if ( moreBtn ) {
			moreBtn.addEventListener( 'click', function () {
				moreBtn.dataset.page = String( parseInt( moreBtn.dataset.page || '1', 10 ) + 1 );
				apply();
			} );
		}
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
	// Multi-step form: <p data-wpe-steps="Step:First field label|..."> before a form splits its fields into steps with tabs,
	// a progress bar and Back / Next. Required fields are checked before moving on; the last step shows submit.
	document.querySelectorAll( '[data-wpe-steps]' ).forEach( function ( cfg ) {
		var box = cfg.closest( '.wpe-ess-stepper' ) || cfg.parentNode;
		var form = box.querySelector( 'form' );
		var fields = form ? Array.prototype.slice.call( form.querySelectorAll( '.wpforms-field-container > .wpforms-field' ) ) : [];
		if ( ! fields.length ) {
			return;
		}
		// Each step starts at the field whose label begins with the text after the colon.
		var label = function ( f ) { var l = f.querySelector( '.wpforms-field-label, h3' ); return l ? l.textContent.trim() : ''; };
		var steps = cfg.dataset.wpeSteps.split( '|' ).map( function ( s ) {
			var p = s.split( ':' );
			var start = fields.findIndex( function ( f ) { return 0 === label( f ).indexOf( p[ 1 ] ); } );
			return { label: p[ 0 ], start: start < 0 ? fields.length : start };
		} );
		steps[ 0 ].start = 0;
		steps.forEach( function ( s, k ) { s.fields = fields.slice( s.start, k + 1 < steps.length ? steps[ k + 1 ].start : fields.length ); } );
		var nav = document.createElement( 'ol' );
		nav.className = 'wpe-steps__nav';
		var bar = document.createElement( 'div' );
		bar.className = 'wpe-steps__bar';
		bar.innerHTML = '<span></span>';
		var title = document.createElement( 'h3' );
		title.className = 'wpe-steps__title';
		var foot = document.createElement( 'div' );
		foot.className = 'wpe-steps__foot';
		foot.innerHTML = '<button type="button" class="wpe-steps__back">← Back</button><button type="button" class="wpe-steps__next"></button>';
		var note = document.createElement( 'p' );
		note.className = 'wpe-steps__note';
		note.textContent = 'Fields marked * are required.';
		var submit = form.querySelector( '.wpforms-submit-container' );
		form.insertBefore( title, form.firstChild );
		form.insertBefore( bar, title );
		form.insertBefore( nav, bar );
		( submit || form ).parentNode.insertBefore( foot, submit );
		form.appendChild( note );
		var cur = 0;
		var show = function ( i ) {
			cur = i;
			steps.forEach( function ( s, k ) { s.fields.forEach( function ( f ) { f.hidden = k !== i; } ); } );
			nav.querySelectorAll( 'li' ).forEach( function ( li, k ) { li.classList.toggle( 'is-on', k === i ); li.classList.toggle( 'is-done', k < i ); } );
			bar.firstChild.style.width = ( ( i + 1 ) / steps.length * 100 ) + '%';
			title.textContent = 'Step ' + ( i + 1 ) + ' of ' + steps.length + ' · ' + steps[ i ].label;
			foot.querySelector( '.wpe-steps__back' ).hidden = 0 === i;
			var last = i === steps.length - 1;
			foot.querySelector( '.wpe-steps__next' ).hidden = last;
			foot.querySelector( '.wpe-steps__next' ).textContent = last ? '' : 'Next: ' + steps[ i + 1 ].label + ' →';
			if ( submit ) {
				submit.hidden = ! last;
			}
		};
		var valid = function () {
			var ok = true;
			steps[ cur ].fields.forEach( function ( f ) {
				f.querySelectorAll( 'input, select, textarea' ).forEach( function ( el ) {
					if ( ok && el.willValidate && ! el.checkValidity() ) {
						el.reportValidity();
						ok = false;
					}
				} );
			} );
			return ok;
		};
		steps.forEach( function ( s, k ) {
			var li = document.createElement( 'li' );
			li.innerHTML = '<button type="button"><b>' + ( k + 1 ) + '</b> ' + s.label + '</button>';
			li.firstChild.addEventListener( 'click', function () { if ( k <= cur || valid() ) { show( k ); } } );
			nav.appendChild( li );
		} );
		foot.querySelector( '.wpe-steps__back' ).addEventListener( 'click', function () { show( cur - 1 ); } );
		foot.querySelector( '.wpe-steps__next' ).addEventListener( 'click', function () { if ( valid() ) { show( cur + 1 ); } } );
		box.classList.add( 'is-stepped' );
		show( 0 );
	} );
}() );
