/**
 * Block editor side of the WPEssential kit. Every kit element is registered from its
 * element.json (passed in window.wpessentialKit): the sidebar panels and controls are built from
 * the options, responsive options get a breakpoint picker, and the preview is the same PHP
 * render the front end and Elementor use. No build step, no JSX.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useState = wp.element.useState;
	var __ = wp.i18n.__;
	var be = wp.blockEditor;
	var c = wp.components;
	var ServerSideRender = wp.serverSideRender;
	var kit = window.wpessentialKit || { elements: {}, breakpoints: [ 'base' ], palette: {} };

	var BP_LABEL = { base: '1440 · desktop', 1920: '1920+', 1280: '1280', 1024: '1024', 768: '768', 560: '560', 480: '480', 360: '360', 320: '320' };

	function options( map ) {
		return Object.keys( map || {} ).map( function ( k ) {
			return { value: k, label: map[ k ] };
		} );
	}

	function control( key, opt, value, onChange ) {
		var label = opt.label || key;
		switch ( opt.type ) {
			case 'textarea':
				return el( c.TextareaControl, { label: label, value: value || '', onChange: onChange, rows: 2, __nextHasNoMarginBottom: true } );
			case 'select':
				return el( c.SelectControl, { label: label, value: value, options: options( opt.options ), onChange: onChange, __nextHasNoMarginBottom: true } );
			case 'color':
				return el( c.SelectControl, { label: label, value: value || '', options: [ { value: '', label: __( 'Default', 'hi-wpessential' ) } ].concat( options( kit.palette ) ), onChange: onChange, __nextHasNoMarginBottom: true } );
			case 'toggle':
				return el( c.ToggleControl, { label: label, checked: !! value, onChange: onChange, __nextHasNoMarginBottom: true } );
			case 'range':
				return el( c.RangeControl, { label: label + ( opt.unit ? ' (' + opt.unit + ')' : '' ), value: '' === value || undefined === value ? undefined : Number( value ), min: opt.min, max: opt.max, step: opt.step || 1, allowReset: true, onChange: function ( v ) {
					onChange( undefined === v ? '' : v );
				}, __nextHasNoMarginBottom: true } );
			default:
				return el( c.TextControl, { label: label, value: value || '', type: 'url' === opt.type ? 'url' : 'text', onChange: onChange, __nextHasNoMarginBottom: true } );
		}
	}

	Object.keys( kit.elements ).forEach( function ( name ) {
		var def = kit.elements[ name ];
		var panels = def.panels || { content: __( 'Content', 'hi-wpessential' ) };
		wp.blocks.registerBlockType( 'wpessential/' + name, {
			apiVersion: 3,
			title: def.title,
			icon: def.icon || 'star-filled',
			category: 'wpessential',
			edit: function ( props ) {
				var bp = useState( 'base' );
				var device = bp[ 0 ];
				var setDevice = bp[ 1 ];
				var blockProps = be.useBlockProps();
				var a = props.attributes;
				var picker = el( c.SelectControl, {
					label: __( 'Editing breakpoint', 'hi-wpessential' ),
					help: __( 'Responsive options below save a value for this screen width.', 'hi-wpessential' ),
					value: device,
					options: kit.breakpoints.map( function ( b ) {
						return { value: b, label: BP_LABEL[ b ] || b };
					} ),
					onChange: setDevice,
					__nextHasNoMarginBottom: true,
				} );
				var sections = Object.keys( panels ).map( function ( panel, i ) {
					var rows = [];
					var hasResponsive = false;
					Object.keys( def.attributes ).forEach( function ( key ) {
						var opt = def.attributes[ key ];
						if ( ( opt.panel || 'content' ) !== panel ) {
							return;
						}
						if ( opt.responsive ) {
							hasResponsive = true;
							var obj = a[ key ] || {};
							rows.push( el( 'div', { key: key, style: { marginBottom: 16 } }, control( key, Object.assign( {}, opt, { label: ( opt.label || key ) + ' · ' + ( BP_LABEL[ device ] || device ) } ), obj[ device ], function ( v ) {
								var next = Object.assign( {}, obj );
								next[ device ] = v;
								var change = {};
								change[ key ] = next;
								props.setAttributes( change );
							} ) ) );
						} else {
							rows.push( el( 'div', { key: key, style: { marginBottom: 16 } }, control( key, opt, a[ key ], function ( v ) {
								var change = {};
								change[ key ] = v;
								props.setAttributes( change );
							} ) ) );
						}
					} );
					return el( c.PanelBody, { key: panel, title: panels[ panel ], initialOpen: 0 === i }, hasResponsive ? el( 'div', { style: { marginBottom: 16 } }, picker ) : null, rows );
				} );
				return el( Fragment, null,
					el( be.InspectorControls, null, sections ),
					el( 'div', blockProps, el( ServerSideRender, { block: 'wpessential/' + name, attributes: a, skipBlockSupportAttributes: true } ) )
				);
			},
			save: function () {
				return null;
			},
		} );
	} );
}( window.wp ) );
