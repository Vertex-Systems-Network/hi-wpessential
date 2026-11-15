/**
 * Hi WPEssential motion: reveals elements as they scroll into view.
 * Opt in with the block class `wpe-anim` (+ `wpe-anim--fade|fade-up|zoom|slide-left|slide-right|mask|stagger|parallax|counter`);
 * common theme blocks are animated automatically.
 */
( function () {
	'use strict';

	var cfg = window.wpessentialMotion || {};
	var html = document.documentElement;
	if ( ! html.classList.contains( 'wpe-motion' ) ) {
		return;
	}

	var auto = cfg.auto ? document.querySelectorAll( cfg.auto ) : [];
	auto.forEach( function ( el ) {
		if ( ! el.closest( '.wpe-anim' ) && ! el.classList.contains( 'wpe-anim' ) ) {
			el.classList.add( 'wpe-anim', 'wpe-anim--auto' );
		}
	} );

	// Stagger children of grids.
	document.querySelectorAll( '.wpe-anim--stagger' ).forEach( function ( grid ) {
		Array.prototype.forEach.call( grid.children, function ( child, i ) {
			child.style.setProperty( '--wpe-delay', ( i * 80 ) + 'ms' );
		} );
	} );
	document.querySelectorAll( 'ul.products, .wp-block-post-template, .wc-block-grid__products' ).forEach( function ( grid ) {
		Array.prototype.forEach.call( grid.children, function ( child, i ) {
			child.style.setProperty( '--wpe-delay', ( ( i % 4 ) * 90 ) + 'ms' );
		} );
	} );

	function count( el ) {
		var target = parseFloat( ( el.textContent || '' ).replace( /[^0-9.]/g, '' ) );
		if ( ! target || el.dataset.wpeCounted ) {
			return;
		}
		el.dataset.wpeCounted = '1';
		var original = el.textContent;
		var decimals = ( original.split( '.' )[ 1 ] || '' ).replace( /[^0-9]/g, '' ).length;
		var start = null;
		function step( t ) {
			start = start || t;
			var p = Math.min( 1, ( t - start ) / 1400 );
			var eased = 1 - Math.pow( 1 - p, 3 );
			el.textContent = original.replace( /[0-9][0-9.,]*/, ( target * eased ).toFixed( decimals ) );
			if ( p < 1 ) {
				window.requestAnimationFrame( step );
			} else {
				el.textContent = original;
			}
		}
		window.requestAnimationFrame( step );
	}

	var targets = document.querySelectorAll( '.wpe-anim' );
	if ( ! ( 'IntersectionObserver' in window ) ) {
		targets.forEach( function ( el ) {
			el.classList.add( 'is-in' );
		} );
		return;
	}
	var io = new IntersectionObserver( function ( entries ) {
		entries.forEach( function ( entry ) {
			if ( entry.isIntersecting ) {
				entry.target.classList.add( 'is-in' );
				if ( entry.target.classList.contains( 'wpe-anim--counter' ) ) {
					count( entry.target );
				}
				io.unobserve( entry.target );
			}
		} );
	}, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 } );
	targets.forEach( function ( el ) {
		io.observe( el );
	} );

	// Dynamic extras without a library: parallax, marquee and counters.
	if ( cfg.dynamic ) {
		var layers = Array.prototype.slice.call( document.querySelectorAll( '.wpe-anim--parallax, .wpe-motion--dynamic .wpe-hero-shot, .wpe-motion--dynamic .wp-block-cover__image-background' ) );
		var ticking = false;
		var paint = function () {
			ticking = false;
			var vh = window.innerHeight || 1;
			layers.forEach( function ( el ) {
				var box = ( el.parentElement || el ).getBoundingClientRect();
				if ( box.bottom < 0 || box.top > vh ) {
					return;
				}
				// -6% at the bottom of the viewport to +6% at the top, like the old scrub.
				var t = ( vh - box.top ) / ( vh + box.height );
				el.style.transform = 'translate3d(0,' + ( ( t * 12 ) - 6 ).toFixed( 2 ) + '%,0)';
			} );
		};
		if ( layers.length ) {
			window.addEventListener( 'scroll', function () {
				if ( ! ticking ) {
					ticking = true;
					window.requestAnimationFrame( paint );
				}
			}, { passive: true } );
			paint();
		}
		document.querySelectorAll( '.wpe-anim--marquee' ).forEach( function ( el ) {
			el.classList.add( 'wpe-anim--marquee-run' );
		} );
		document.querySelectorAll( '.wpe-motion--dynamic .wpe-stat strong, .wpe-motion--dynamic .wpe-stat .wp-block-heading' ).forEach( function ( el ) {
			el.classList.add( 'wpe-anim--counter' );
			io.observe( el );
		} );
	}
}() );
