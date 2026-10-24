/* Unit-like Gutenberg editor registration tests with a minimal wp mock. */
'use strict';
const assert = require( 'node:assert/strict' );
const fs = require( 'node:fs' );
const path = require( 'node:path' );
const vm = require( 'node:vm' );
const src = fs.readFileSync( path.join( __dirname, '../../core/assets/kit-editor.js' ), 'utf8' );

const registered = {};
const InnerBlocks = function InnerBlocks() {};
InnerBlocks.ButtonBlockAppender = function ButtonBlockAppender() {};
InnerBlocks.Content = function InnerBlocksContent() {};
const InspectorControls = function InspectorControls() {};
const ServerSideRender = function ServerSideRender() {};

const wp = {
	element: {
		createElement: function ( type, props, ...children ) { return { type, props: props || {}, children: children.flat( Infinity ) }; },
		Fragment: function Fragment() {},
		useState: function ( initial ) { return [ initial, function () {} ]; }
	},
	blocks: {
		registerBlockType: function ( name, definition ) { registered[ name ] = definition; }
	},
	blockEditor: {
		useBlockProps: function ( props ) { return Object.assign( { className: 'editor-block' }, props ); },
		InnerBlocks,
		InspectorControls,
		MediaUploadCheck: function () {},
		MediaUpload: function () {}
	},
	components: {
		PanelBody: function () {},
		SelectControl: function () {}
	},
	serverSideRender: ServerSideRender,
	i18n: {
		__: function ( text ) { return text; }
	}
};
const defs = {
	container: { title: 'Container', icon: 'screenoptions', panels: { content: 'Content' }, attributes: {}, groups: {} },
	heading: { title: 'Heading', icon: 'heading', panels: { content: 'Content' }, attributes: {}, groups: {} }
};
const window = {
	wp,
	wpessentialKit: { elements: defs, breakpoints: [ 'base', '480' ], palette: {}, groupFields: {}, icons: {}, lengthUnits: [ 'px' ] }
};
vm.runInNewContext( src, { window }, { filename: 'kit-editor.js' } );
assert.ok( registered[ 'wpessential/container' ], 'Container block registered' );
assert.ok( registered[ 'wpessential/heading' ], 'Other Kit block remains registered' );

const saved = registered[ 'wpessential/container' ].save();
assert.equal( saved.type, InnerBlocks.Content, 'Container save persists native InnerBlocks content' );
assert.equal( registered[ 'wpessential/heading' ].save(), null, 'Other Kit blocks remain dynamic' );

function contains( node, type ) {
	return !!node && ( node.type === type || ( node.children || [] ).some( function ( child ) {
		return Array.isArray( child ) ? child.some( function ( item ) { return contains( item, type ); } ) : contains( child, type );
	} ) );
}
const props = {
	attributes: { layout: 'grid', columns: { base: 3 }, gap: { base: 20 } },
	setAttributes: function () {}
};
const editor = registered[ 'wpessential/container' ].edit( props );
assert.ok( contains( editor, InnerBlocks ), 'Container editor shows native nested blocks' );
assert.ok( ! contains( editor, ServerSideRender ), 'Container editor does not replace children with a server preview' );
const sibling = registered[ 'wpessential/heading' ].edit( { attributes: {}, setAttributes: function () {} } );
assert.ok( contains( sibling, ServerSideRender ), 'Existing Kit elements retain server preview' );
console.log( 'Container Gutenberg editor registration tests passed.' );
