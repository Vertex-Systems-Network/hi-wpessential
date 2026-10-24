/* Dependency-free Table search and sorting interaction tests. */
'use strict';
const assert = require( 'node:assert/strict' );
const vm = require( 'node:vm' );
const fs = require( 'node:fs' );
const path = require( 'node:path' );
const script = fs.readFileSync( path.join( __dirname, '../../core/kit/table/script.js' ), 'utf8' );

function emitter( values = {} ) {
	return Object.assign( {
		listeners: {},
		addEventListener( name, fn ) { this.listeners[ name ] = fn; },
		fire( name ) { assert.ok( this.listeners[ name ], name + ' must be registered' ); this.listeners[ name ](); }
	}, values );
}

const headings = [ emitter(), emitter() ];
const buttons = headings.map( ( heading, index ) => emitter( {
	dataset: { wpeSort: String( index ) },
	closest: ( selector ) => selector === 'th' ? heading : null
} ) );
headings.forEach( ( heading ) => {
	heading.setAttribute = ( key, value ) => { heading[ key ] = value; };
} );
const createRow = ( name, score ) => ( {
	cells: [ { textContent: name }, { textContent: score } ],
	textContent: name + ' ' + score,
	hidden: false
} );
const first = createRow( 'Zebra', '2' );
const second = createRow( 'Alpha', '10' );
const third = createRow( 'Beta', '3' );
const children = [ first, second, third ];
const body = {
	querySelectorAll: () => children.slice(),
	appendChild( child ) {
		const index = children.indexOf( child );
		assert.notEqual( index, -1 );
		children.splice( index, 1 );
		children.push( child );
	}
};
const search = emitter( { value: '' } );
const empty = { hidden: true };
const status = { textContent: '' };
const table = {
	tBodies: [ body ],
	querySelectorAll: ( selector ) => selector === '[data-wpe-sort]' ? buttons : []
};
const root = {
	dataset: { rowSingular: '%d row shown', rowPlural: '%d rows shown' },
	querySelector( selector ) {
		return { '.wpe-table__table': table, '.wpe-table__search': search, '.wpe-table__no-results': empty, '.wpe-table__status': status }[ selector ] || null;
	}
};
const document = { readyState: 'complete', querySelectorAll: ( selector ) => selector === '[data-wpe-table]' ? [ root ] : [] };
vm.runInNewContext( script, { window: { Intl }, document }, { filename: 'table/script.js' } );
assert.equal( root.dataset.wpeTableReady, '1', 'Table is initialized only once' );
buttons[ 1 ].fire( 'click' );
assert.deepEqual( children.map( ( row ) => row.cells[ 1 ].textContent ), [ '2', '3', '10' ], 'Numeric-aware ascending sort' );
assert.equal( headings[ 1 ][ 'aria-sort' ], 'ascending', 'Active header has ascending aria-sort' );
buttons[ 1 ].fire( 'click' );
assert.deepEqual( children.map( ( row ) => row.cells[ 1 ].textContent ), [ '10', '3', '2' ], 'Second click toggles descending' );
assert.equal( headings[ 1 ][ 'aria-sort' ], 'descending' );
buttons[ 0 ].fire( 'click' );
assert.deepEqual( children.map( ( row ) => row.cells[ 0 ].textContent ), [ 'Alpha', 'Beta', 'Zebra' ], 'Different column starts with ascending order' );
assert.equal( headings[ 1 ][ 'aria-sort' ], 'none', 'Inactive column resets aria-sort' );

search.value = 'alpha';
search.fire( 'input' );
assert.equal( second.hidden, false, 'Matching row remains visible' );
assert.equal( first.hidden, true, 'Unmatched row hidden' );
assert.equal( third.hidden, true, 'Other unmatched row hidden' );
assert.equal( empty.hidden, true, 'No-results message stays hidden for matches' );
assert.equal( status.textContent, '1 row shown', 'Singular live announcement' );

search.value = 'no results';
search.fire( 'input' );
assert.equal( empty.hidden, false, 'No-results hint appears when all filtered' );
assert.equal( status.textContent, '0 rows shown', 'Zero-results plural announcement' );
search.value = '';
search.fire( 'input' );
assert.equal( children.every( ( row ) => ! row.hidden ), true, 'Clearing search reveals all rows' );
assert.equal( status.textContent, '3 rows shown', 'Plural live announcement' );
console.log( 'Table search, sort and accessibility behavior tests passed.' );
