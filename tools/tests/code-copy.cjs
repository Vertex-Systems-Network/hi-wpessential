/* Code copy behaviour, dependency-free Node test. */
'use strict';
const assert = require( 'node:assert/strict' );
const fs = require( 'node:fs' );
const vm = require( 'node:vm' );
const path = require( 'node:path' );
const code = fs.readFileSync( path.join( __dirname, '../../core/kit/code/script.js' ), 'utf8' );

function simulate( clipboard ) {
	const status = { textContent: '' };
	const button = {
		textContent: 'Copy snippet',
		click: null,
		addEventListener( event, fn ) {
			assert.equal( event, 'click' );
			this.click = fn;
		}
	};
	const source = { textContent: '<script>shown, not executed</script>' };
	const root = {
		dataset: {},
		querySelector( selector ) {
			return { '.wpe-code__copy': button, '.wpe-code__source': source, '.wpe-code__copy-status': status }[ selector ];
		}
	};
	const document = { readyState: 'complete', querySelectorAll: () => [ root ] };
	vm.runInNewContext( code, { window: {}, document, navigator: { clipboard } } );
	return { status, button, source, root };
}

( async function () {
	let copied = null;
	const valid = simulate( { writeText: ( text ) => { copied = text; return Promise.resolve(); } } );
	assert.equal( copied, null, 'No copy before click' );
	valid.button.click();
	await Promise.resolve();
	assert.equal( copied, valid.source.textContent, 'Copy action writes original source text' );
	assert.equal( valid.status.textContent, 'Code copied.', 'Accessible success status published' );
	assert.equal( valid.button.textContent, 'Copied', 'Button confirms successful copy' );

	const denied = simulate( { writeText: () => Promise.reject( new Error( 'denied' ) ) } );
	denied.button.click();
	await Promise.resolve();
	assert.equal( denied.status.textContent, 'Copy failed. Select and copy the code manually.', 'Failure does not claim success' );
	assert.equal( denied.button.textContent, 'Copy snippet', 'Failure preserves original button label' );

	const unsupported = simulate( null );
	unsupported.button.click();
	assert.equal( unsupported.status.textContent, 'Clipboard is unavailable in this browser.', 'Unsupported browser gets a meaningful message' );
	console.log( 'Code copy UI tests passed.' );
}() ).catch( ( error ) => { console.error( error ); process.exitCode = 1; } );
