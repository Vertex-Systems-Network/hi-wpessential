/* Dependency-free native dialog + carousel behavior regression tests. */
'use strict';
const assert = require( 'node:assert/strict' );
const fs = require( 'node:fs' );
const vm = require( 'node:vm' );
const path = require( 'node:path' );
const modalCode = fs.readFileSync( path.join( __dirname, '../../core/kit/modal/script.js' ), 'utf8' );
const carouselCode = fs.readFileSync( path.join( __dirname, '../../core/kit/image-carousel/script.js' ), 'utf8' );

function element( values = {} ) {
	return Object.assign( {
		handlers: {},
		addEventListener( name, callback ) { this.handlers[ name ] = callback; },
		fire( name, values = {} ) {
			assert.equal( typeof this.handlers[ name ], 'function', 'Handler registered: ' + name );
			this.handlers[ name ]( values );
		},
		focus() { this.focused = true; },
		setAttribute( name, value ) { ( this.attrs ||= {} )[ name ] = value; },
		removeAttribute( name ) { if ( this.attrs ) { delete this.attrs[ name ]; } }
	}, values );
}

function modal( hasDialog = true, backdrop = '1' ) {
	const dialog = element( { open: false } );
	if ( hasDialog ) {
		dialog.showModal = () => { dialog.open = true; };
		dialog.close = () => { dialog.open = false; dialog.fire( 'close' ); };
	}
	const fallback = { hidden: false };
	const trigger = element( { hidden: true, isConnected: true } );
	const close = element();
	const bottom = element();
	const components = {
		'.wpe-modal__dialog': dialog,
		'.wpe-modal__fallback': fallback,
		'.wpe-modal__open': trigger,
		'.wpe-modal__close': close,
		'.wpe-modal__close-bottom': bottom
	};
	const root = {
		dataset: { closeBackdrop: backdrop },
		querySelector: ( sel ) => components[ sel ] || null
	};
	const document = { readyState: 'complete', querySelectorAll: () => [ root ] };
	vm.runInNewContext( modalCode, { document, window: {} } );
	return { dialog, fallback, trigger, close, bottom, root };
}

const m = modal();
assert.equal( m.trigger.hidden, false, 'Dialog trigger enabled after enhancement' );
assert.equal( m.fallback.hidden, true, 'No-JS details hidden only when dialog supported' );
m.trigger.fire( 'click' );
assert.equal( m.dialog.open, true, 'Dialog opens from explicit click' );
assert.equal( m.close.focused, true, 'Open dialog focuses close button' );
m.close.fire( 'click' );
assert.equal( m.dialog.open, false, 'Close button dismisses dialog' );
assert.equal( m.trigger.focused, true, 'Dismissal restores trigger focus' );
m.trigger.fire( 'click' );
m.dialog.fire( 'click', { target: m.dialog } );
assert.equal( m.dialog.open, false, 'Backdrop click closes when enabled' );
m.trigger.fire( 'click' );
m.bottom.fire( 'click' );
assert.equal( m.dialog.open, false, 'Footer close button works' );
const noBackdrop = modal( true, '0' );
noBackdrop.trigger.fire( 'click' );
noBackdrop.dialog.fire( 'click', { target: noBackdrop.dialog } );
assert.equal( noBackdrop.dialog.open, true, 'Optional backdrop dismissal can be disabled' );
const noDialog = modal( false );
assert.equal( noDialog.fallback.hidden, false, 'Native details fallback remains when dialog API unavailable' );
assert.equal( noDialog.trigger.hidden, true, 'Unsupported dialog does not reveal inert trigger' );

function carousel( options = {} ) {
	const slides = [ 0, 300, 600 ].map( offset => ( { offsetLeft: offset } ) );
	const scrolls = [];
	const viewport = { scrollTo: ( opts ) => scrolls.push( opts ) };
	const previous = element( { disabled: false } ), next = element( { disabled: false } );
	const dots = slides.map( () => element( { attrs: {} } ) );
	const controls = { hidden: true };
	const root = element( {
		dataset: { loop: options.loop ? '1' : '0', autoplay: options.autoplay ? '1' : '0', pauseHover: '1', interval: '3000' },
		isConnected: true,
		querySelector( name ) {
			return { '.wpe-image-carousel__viewport': viewport, '.wpe-image-carousel__controls': controls, '[data-carousel-prev]': previous, '[data-carousel-next]': next }[ name ] || null;
		},
		querySelectorAll( name ) {
			return { '.wpe-image-carousel__card': slides, '[data-carousel-dot]': dots }[ name ] || [];
		},
		contains( other ) { return other === next || other === previous || dots.includes( other ); }
	} );
	let timerCallback = null, intervals = 0, cleared = 0;
	const document = element( { readyState: 'complete', hidden: false, querySelectorAll: () => [ root ] } );
	const window = {
		matchMedia: () => ( { matches: !!options.reduced } ),
		setInterval( fn, ms ) { assert.equal( ms, 3000 ); timerCallback = fn; intervals++; return intervals; },
		clearInterval( id ) { assert.ok( id > 0 ); cleared++; }
	};
	vm.runInNewContext( carouselCode, { document, window } );
	return { slides, viewport, scrolls, previous, next, dots, controls, root, document, get intervals() { return intervals; }, get cleared() { return cleared; }, tick() { assert.ok( timerCallback ); timerCallback(); } };
}

const c = carousel();
assert.equal( c.controls.hidden, false, 'Carousel controls shown only after initialization' );
assert.equal( c.scrolls[ 0 ].left, 0, 'Initial carousel position is first slide' );
assert.equal( c.previous.disabled, true, 'Previous disabled at start with looping off' );
c.next.fire( 'click' );
assert.equal( c.scrolls.at( -1 ).left, 300, 'Next advances by one slide' );
assert.equal( c.dots[ 1 ].attrs[ 'aria-current' ], 'true', 'Active dot ARIA updated' );
c.next.fire( 'click' );
assert.equal( c.next.disabled, true, 'Next disabled at final slide' );
c.previous.fire( 'click' );
assert.equal( c.scrolls.at( -1 ).left, 300, 'Previous navigates backward' );
c.dots[ 0 ].fire( 'click' );
assert.equal( c.scrolls.at( -1 ).left, 0, 'Dot navigation selects requested slide' );
const looping = carousel( { loop: true } );
looping.previous.fire( 'click' );
assert.equal( looping.scrolls.at( -1 ).left, 600, 'Loop mode wraps previous from first to last' );
const playing = carousel( { autoplay: true } );
assert.equal( playing.intervals, 1, 'Opt-in autoplay starts when reduced motion is off' );
playing.tick();
assert.equal( playing.scrolls.at( -1 ).left, 300, 'Autoplay progresses slides' );
playing.root.fire( 'mouseenter' );
assert.ok( playing.cleared >= 1, 'Hover stops autoplay' );
playing.root.fire( 'mouseleave' );
assert.equal( playing.intervals, 2, 'Leaving hover resumes autoplay' );
playing.document.hidden = true;
playing.document.fire( 'visibilitychange' );
assert.ok( playing.cleared >= 2, 'Page visibility pauses autoplay' );
const reduced = carousel( { autoplay: true, reduced: true } );
assert.equal( reduced.intervals, 0, 'Reduced motion never autoplays' );
assert.equal( reduced.scrolls[ 0 ].behavior, 'auto', 'Reduced motion uses nonanimated navigation' );
console.log( 'Modal and image carousel interactive regression tests passed.' );
