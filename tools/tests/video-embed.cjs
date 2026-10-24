/* Dependency-free tests for opt-in third-party video embeds. */
'use strict';
const assert = require( 'node:assert/strict' );
const fs = require( 'node:fs' );
const path = require( 'node:path' );
const vm = require( 'node:vm' );
const code = fs.readFileSync( path.join( __dirname, '../../core/kit/video/script.js' ), 'utf8' );

function mock( url ) {
	let creates = 0;
	let attached = null;
	const button = {
		onclick: null,
		addEventListener( type, callback ) {
			assert.equal( type, 'click' );
			this.onclick = callback;
		},
		replaceWith( newNode ) { attached = newNode; }
	};
	const root = {
		dataset: { wpeEmbed: url, title: 'Product video' },
		querySelector( query ) { return query === '.wpe-video__play' ? button : null; }
	};
	const document = {
		readyState: 'complete',
		querySelectorAll( query ) { return query === '.wpe-video__embed[data-wpe-embed]' ? [ root ] : []; },
		createElement( tag ) {
			assert.equal( tag, 'iframe' );
			creates++;
			return { focus() { this.focused = true; } };
		}
	};
	vm.runInNewContext( code, { document, window: {} } );
	return { root, button, get creates() { return creates; }, get attached() { return attached; } };
}

const youtube = mock( 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?autoplay=1' );
assert.equal( youtube.creates, 0, 'No third-party iframe before explicit click' );
assert.equal( typeof youtube.button.onclick, 'function', 'Valid embed gets a click handler' );
youtube.button.onclick();
assert.equal( youtube.creates, 1, 'Iframe created only after click' );
assert.equal( youtube.attached.src, 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?autoplay=1' );
assert.equal( youtube.attached.title, 'Product video', 'Iframe has accessible title' );
assert.equal( youtube.attached.referrerPolicy, 'strict-origin-when-cross-origin', 'Referrer policy applied' );
assert.equal( youtube.attached.focused, true, 'New player gets keyboard focus' );
youtube.button.onclick();
assert.equal( youtube.creates, 1, 'Repeated click cannot create duplicate embeds' );

const vimeo = mock( 'https://player.vimeo.com/video/12345678?dnt=1&autoplay=1' );
assert.equal( vimeo.creates, 0 );
vimeo.button.onclick();
assert.equal( vimeo.attached.src.includes( 'dnt=1' ), true, 'Vimeo DNT iframe created on click' );

for ( const url of [
	'https://www.youtube-nocookie.com.evil.test/embed/dQw4w9WgXcQ?autoplay=1',
	'javascript:alert(1)',
	'https://player.vimeo.com/video/not-an-id?dnt=1&autoplay=1'
] ) {
	const invalid = mock( url );
	assert.equal( invalid.creates, 0, 'Invalid player URL never creates iframe' );
	assert.equal( invalid.button.onclick, null, 'Invalid player URL does not attach embed listener' );
}
console.log( 'Video click-to-load privacy and iframe tests passed.' );
