/* No-dependency behavior tests for Tooltip and two-tab Switcher. */
'use strict';
const assert = require( 'node:assert/strict' );
const fs = require( 'node:fs' );
const vm = require( 'node:vm' );
const path = require( 'node:path' );
const source = fs.readFileSync( path.join( __dirname, '../../core/kit/tooltip/script.js' ), 'utf8' );

function node( extra = {} ) {
	return Object.assign( {
		listeners: {},
		attributes: {},
		addEventListener( type, fn ) { this.listeners[ type ] = fn; },
		setAttribute( name, value ) { this.attributes[ name ] = value; },
		fire( name, details = {} ) {
			const ev = Object.assign( { key: '', relatedTarget: null, prevented: false, preventDefault() { this.prevented = true; } }, details );
			assert.equal( typeof this.listeners[ name ], 'function', name + ' handler installed' );
			this.listeners[ name ]( ev );
			return ev;
		},
		focus() { this.focused = true; },
		dataset: {}
	}, extra );
}
function classes() {
	const current = new Set();
	return {
		add( name ) { current.add( name ); },
		remove( name ) { current.delete( name ); },
		contains( name ) { return current.has( name ); },
		toggle( name, enabled ) {
			if ( enabled ) { current.add( name ); } else { current.delete( name ); }
		}
	};
}
function run( root, dir = 'ltr' ) {
	const document = {
		readyState: 'complete',
		documentElement: { dir },
		querySelectorAll( selector ) { return selector === '[data-wpe-interactive]' ? [ root ] : []; }
	};
	vm.runInNewContext( source, { window: {}, document }, { filename: 'tooltip/script.js' } );
}

const trigger = node();
const bubble = node();
const wrap = node( {
	classList: classes(),
	querySelector( selector ) {
		return { '.wpe-tooltip__trigger': trigger, '.wpe-tooltip__bubble': bubble }[ selector ];
	},
	contains( value ) { return trigger === value || bubble === value; }
} );
const tooltip = node( {
	dataset: { wpeInteractive: 'tooltip' },
	querySelector( selector ) { return selector === '.wpe-tooltip__tip-wrap' ? wrap : null; }
} );
run( tooltip );
assert.equal( tooltip.dataset.wpeInteractiveReady, '1', 'Tooltip initialized' );
trigger.fire( 'click' );
assert.equal( wrap.classList.contains( 'is-open' ), true, 'Tap pins tooltip open' );
trigger.fire( 'click' );
assert.equal( wrap.classList.contains( 'is-dismissed' ), true, 'Second tap dismisses tooltip even while focused' );
trigger.fire( 'click' );
assert.equal( wrap.classList.contains( 'is-open' ), true, 'Tooltip can reopen' );
const escapeEvent = trigger.fire( 'keydown', { key: 'Escape' } );
assert.equal( escapeEvent.prevented, true, 'Escape is consumed' );
assert.equal( wrap.classList.contains( 'is-dismissed' ), true, 'Escape dismisses tooltip' );
wrap.fire( 'mouseleave' );
assert.equal( wrap.classList.contains( 'is-dismissed' ), false, 'Pointer leaving resets dismissal' );
wrap.fire( 'focusout', { relatedTarget: null } );
assert.equal( wrap.classList.contains( 'is-open' ), false, 'Leaving the widget clears pinned state' );

function switchRoot() {
	const buttons = [ node( { dataset: { switchTo: 'first' } } ), node( { dataset: { switchTo: 'second' } } ) ];
	const panels = [ node( { dataset: { switchPanel: 'first' }, hidden: false } ), node( { dataset: { switchPanel: 'second' }, hidden: false } ) ];
	const nav = node( {
		dataset: { switchLabel: 'Content options' },
		querySelectorAll: ( selector ) => selector === '[data-switch-to]' ? buttons : []
	} );
	const root = node( {
		dataset: { wpeInteractive: 'switcher', initial: 'second' },
		querySelector: ( selector ) => selector === '[data-switch-buttons]' ? nav : null,
		querySelectorAll: ( selector ) => selector === '[data-switch-panel]' ? panels : []
	} );
	return { root, nav, buttons, panels };
}
const tabs = switchRoot();
assert.deepEqual( tabs.panels.map( ( item ) => item.hidden ), [ false, false ], 'Before JS both panels remain visible' );
run( tabs.root );
assert.equal( tabs.nav.attributes.role, 'tablist', 'Switcher gains semantic tablist role after JS initializes' );
assert.equal( tabs.nav.attributes[ 'aria-label' ], 'Content options', 'Tablist has accessible label' );
assert.equal( tabs.buttons[ 1 ].attributes[ 'aria-selected' ], 'true', 'Second tab is selected by default' );
assert.deepEqual( tabs.panels.map( ( item ) => item.hidden ), [ true, false ], 'After JS only selected panel remains visible' );
tabs.buttons[ 0 ].fire( 'click' );
assert.deepEqual( tabs.panels.map( ( item ) => item.hidden ), [ false, true ], 'Click switches visible panel' );
assert.equal( tabs.buttons[ 0 ].tabIndex, 0, 'Selected tab is keyboard focusable' );
assert.equal( tabs.buttons[ 1 ].tabIndex, -1, 'Inactive tab participates in roving tab index' );
tabs.buttons[ 0 ].fire( 'keydown', { key: 'ArrowRight' } );
assert.equal( tabs.buttons[ 1 ].attributes[ 'aria-selected' ], 'true', 'ArrowRight selects next tab' );
assert.equal( tabs.buttons[ 1 ].focused, true, 'Keyboard selection moves focus' );
tabs.buttons[ 1 ].fire( 'keydown', { key: 'Home' } );
assert.equal( tabs.buttons[ 0 ].attributes[ 'aria-selected' ], 'true', 'Home selects first tab' );
tabs.buttons[ 0 ].fire( 'keydown', { key: 'End' } );
assert.equal( tabs.buttons[ 1 ].attributes[ 'aria-selected' ], 'true', 'End selects last tab' );
const rtl = switchRoot();
run( rtl.root, 'rtl' );
rtl.buttons[ 1 ].fire( 'keydown', { key: 'ArrowRight' } );
assert.equal( rtl.buttons[ 0 ].attributes[ 'aria-selected' ], 'true', 'RTL ArrowRight selects previous tab' );
console.log( 'Interactive Tooltip and Switcher keyboard tests passed.' );
