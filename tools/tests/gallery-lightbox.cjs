/* Dependency-free interaction tests for the Gallery dialog. */
'use strict';
const assert = require( 'node:assert/strict' );
const fs = require( 'node:fs' );
const path = require( 'node:path' );
const vm = require( 'node:vm' );
const code = fs.readFileSync( path.join( __dirname, '../../core/kit/gallery/script.js' ), 'utf8' );

function elem( extra = {} ) {
	return Object.assign( {
		events: {},
		addEventListener( type, fn ) { this.events[ type ] = fn; },
		fire( type, props = {} ) {
			const event = Object.assign( { target: this, key: null, prevented: false, preventDefault() { this.prevented = true; } }, props );
			if ( this.events[ type ] ) {
				this.events[ type ]( event );
			}
			return event;
		},
		focus() { this.focused = true; }
	}, extra );
}

function simulate( { disabledDialog = false, captions = true, num = 2 } = {} ) {
	const opener1 = elem( { dataset: { full: 'https://example.org/one.jpg', alt: 'First', caption: 'First caption' }, href: 'https://example.org/one.jpg', isConnected: true } );
	const opener2 = elem( { dataset: { full: 'https://example.org/two.jpg', alt: 'Second', caption: 'Second caption' }, href: 'https://example.org/two.jpg', isConnected: true } );
	const links = num === 1 ? [ opener1 ] : [ opener1, opener2 ];
	const full = elem( { src: '', alt: '', removeAttribute( key ) { if ( key === 'src' ) { this.src = ''; } } } );
	const caption = elem( { textContent: '', hidden: false } );
	const prev = elem(), next = elem(), close = elem();
	const controls = {
		'.wpe-gallery__full': full,
		'.wpe-gallery__viewer-caption': caption,
		'[data-action="prev"]': prev,
		'[data-action="next"]': next,
		'[data-action="close"]': close
	};
	const dialog = elem( {
		dataset: { showCaptions: captions ? '1' : '0' },
		showModal: disabledDialog ? undefined : function () { this.open = true; },
		close() { this.open = false; this.fire( 'close' ); },
		querySelector( key ) { return controls[ key ] || null; }
	} );
	const root = { dataset: {}, querySelector: ( k ) => k === '.wpe-gallery__dialog' ? dialog : null,
		querySelectorAll: ( k ) => k === '[data-wpe-gallery-open]' ? links : [] };
	const document = { readyState: 'complete', querySelectorAll: ( key ) => key === '[data-wpe-gallery]' ? [ root ] : [] };
	vm.runInNewContext( code, { document, window: {} }, { filename: 'gallery/script.js' } );
	return { opener1, opener2, dialog, full, caption, prev, next, close, root };
}

const test = simulate();
const click = test.opener1.fire( 'click' );
assert.equal( click.prevented, true, 'Lightbox prevents direct navigation only when supported' );
assert.equal( test.dialog.open, true, 'Opens native dialog' );
assert.equal( test.full.src, 'https://example.org/one.jpg', 'Loads the correct full image' );
assert.equal( test.full.alt, 'First', 'Sets the image alt text' );
assert.equal( test.caption.textContent, 'First caption', 'Shows safe text caption' );
assert.equal( test.close.focused, true, 'Close control receives keyboard focus' );

test.next.fire( 'click' );
assert.equal( test.full.src, 'https://example.org/two.jpg', 'Next control changes image' );
test.dialog.fire( 'keydown', { key: 'ArrowRight' } );
assert.equal( test.full.src, 'https://example.org/one.jpg', 'Keyboard next wraps around' );
test.dialog.fire( 'keydown', { key: 'ArrowLeft' } );
assert.equal( test.full.src, 'https://example.org/two.jpg', 'Keyboard previous wraps around' );
test.close.fire( 'click' );
assert.equal( test.dialog.open, false, 'Close control dismisses dialog' );
assert.equal( test.full.src, '', 'Closing clears large image source' );
assert.equal( test.opener1.focused, true, 'Closing restores opener focus' );

const single = simulate( { num: 1, captions: false } );
single.opener1.fire( 'click' );
assert.equal( single.prev.hidden, true, 'Single image has no Previous button' );
assert.equal( single.next.hidden, true, 'Single image has no Next button' );
assert.equal( single.caption.hidden, true, 'Captions option disables the dialog caption' );

const fallback = simulate( { disabledDialog: true } );
const fallbackClick = fallback.opener1.fire( 'click' );
assert.equal( fallbackClick.prevented, false, 'Unsupported dialog falls back to normal image hyperlink' );

console.log( 'Gallery JavaScript lightbox tests passed.' );
