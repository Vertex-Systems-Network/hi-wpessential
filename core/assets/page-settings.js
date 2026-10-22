/**
 * Page settings (WPEssential) in the block editor's document sidebar. The fields come from
 * PageSettings::fields() in PHP and are saved in one post meta object. No build step.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var useState = wp.element.useState;
	var __ = wp.i18n.__;
	var c = wp.components;
	var be = wp.blockEditor;
	var cfg = window.wpessentialPage;
	var Panel = ( wp.editor && wp.editor.PluginDocumentSettingPanel ) || ( wp.editPost && wp.editPost.PluginDocumentSettingPanel );
	if ( ! cfg || ! Panel ) {
		return;
	}

	function LengthField( p ) {
		var m = String( p.value || '' ).match( /^(-?[\d.]+)\s*([a-z%]*)$/i );
		var unit = useState( m && m[ 2 ] ? m[ 2 ] : 'px' );
		var u = m && m[ 2 ] ? m[ 2 ] : unit[ 0 ];
		return el( 'div', { className: 'wpep-field' },
			el( 'div', { className: 'wpep-length' },
				el( c.TextControl, { label: p.label, type: 'text', value: p.value || '', placeholder: '80px, 5rem, clamp(…)', onChange: p.onChange, __nextHasNoMarginBottom: true } ),
				el( 'div', { className: 'wpep-units', role: 'group', 'aria-label': __( 'Unit', 'hi-wpessential' ) }, cfg.units.map( function ( x ) {
					return el( 'button', { key: x, type: 'button', className: 'wpep-unit' + ( x === u ? ' is-active' : '' ), 'aria-pressed': x === u, onClick: function () {
						unit[ 1 ]( x );
						if ( m ) {
							p.onChange( m[ 1 ] + x );
						}
					} }, x );
				} ) )
			)
		);
	}

	function ColorField( p ) {
		var colors = Object.keys( p.palette || {} ).map( function ( slug ) {
			return { name: p.palette[ slug ], slug: slug, color: 'var(--wp--preset--color--' + slug + ')' };
		} );
		var isSlug = p.value && /^[a-z0-9-]+$/.test( p.value ) && ! /^[0-9a-f]{3,8}$/i.test( p.value );
		return el( 'div', { className: 'wpep-field' },
			el( 'span', { className: 'wpep-label' }, p.label ),
			el( 'div', { className: 'wpep-swatches' },
				colors.map( function ( col ) {
					return el( 'button', { key: col.slug, type: 'button', title: col.name, 'aria-label': col.name, 'aria-pressed': p.value === col.slug, className: 'wpep-swatch' + ( p.value === col.slug ? ' is-active' : '' ), style: { background: col.color }, onClick: function () {
						p.onChange( p.value === col.slug ? '' : col.slug );
					} } );
				} )
			),
			el( 'div', { className: 'wpep-custom-color' },
				el( 'input', { type: 'color', 'aria-label': __( 'Custom colour', 'hi-wpessential' ), value: ! isSlug && /^#[0-9a-f]{6}$/i.test( p.value || '' ) ? p.value : '#ec7d1a', onChange: function ( e ) {
					p.onChange( e.target.value );
				} } ),
				el( c.TextControl, { value: isSlug ? '' : p.value || '', placeholder: __( 'Custom: #ff6600, rgb(…)', 'hi-wpessential' ), onChange: p.onChange, __nextHasNoMarginBottom: true } ),
				p.value ? el( c.Button, { variant: 'link', isDestructive: true, onClick: function () {
					p.onChange( '' );
				} }, __( 'Clear', 'hi-wpessential' ) ) : null
			)
		);
	}

	function ImageField( p ) {
		var img = p.value && p.value.url ? p.value : null;
		return el( 'div', { className: 'wpep-field' },
			el( 'span', { className: 'wpep-label' }, p.label ),
			el( be.MediaUploadCheck, null, el( be.MediaUpload, {
				allowedTypes: [ 'image' ],
				value: img ? img.id : undefined,
				onSelect: function ( m ) {
					p.onChange( { id: m.id, url: m.url } );
				},
				render: function ( o ) {
					return img ? el( 'div', null,
						el( 'button', { type: 'button', className: 'wpep-thumb', onClick: o.open }, el( 'img', { src: img.url, alt: '' } ) ),
						el( 'div', { className: 'wpep-actions' },
							el( c.Button, { variant: 'secondary', onClick: o.open }, __( 'Replace', 'hi-wpessential' ) ),
							el( c.Button, { variant: 'link', isDestructive: true, onClick: function () {
								p.onChange( { id: 0, url: '' } );
							} }, __( 'Remove', 'hi-wpessential' ) )
						)
					) : el( 'button', { type: 'button', className: 'wpep-drop', onClick: o.open }, el( 'strong', null, __( 'Upload or choose an image', 'hi-wpessential' ) ), el( 'span', null, __( 'Opens the Media Library', 'hi-wpessential' ) ) );
				},
			} ) )
		);
	}

	function field( key, f, value, set ) {
		var onChange = function ( v ) {
			set( key, v );
		};
		switch ( f.type ) {
			case 'toggle':
				return el( c.ToggleControl, { key: key, label: f.label, checked: !! value, onChange: onChange, __nextHasNoMarginBottom: true } );
			case 'select':
				return el( c.SelectControl, { key: key, label: f.label, value: value || '', options: Object.keys( f.options ).map( function ( k ) {
					return { value: k, label: f.options[ k ] };
				} ), onChange: onChange, __nextHasNoMarginBottom: true } );
			case 'length':
				return el( LengthField, { key: key, label: f.label, value: value, onChange: onChange } );
			case 'color':
				return el( ColorField, { key: key, label: f.label, value: value, palette: f.palette, onChange: onChange } );
			case 'image':
				return el( ImageField, { key: key, label: f.label, value: value, onChange: onChange } );
			case 'code':
				return el( c.TextareaControl, { key: key, className: 'wpep-code', label: f.label, value: value || '', rows: 6, help: __( 'Example: selector .wpe-main { padding-top: 0; }', 'hi-wpessential' ), onChange: onChange, __nextHasNoMarginBottom: true } );
			default:
				return el( c.TextControl, { key: key, label: f.label, value: value || '', onChange: onChange, __nextHasNoMarginBottom: true } );
		}
	}

	function Settings() {
		var postType = wp.data.useSelect( function ( select ) {
			return select( 'core/editor' ).getCurrentPostType();
		}, [] );
		var entity = wp.coreData.useEntityProp( 'postType', postType, 'meta' );
		var meta = entity[ 0 ] || {};
		var s = meta[ cfg.meta ] && 'object' === typeof meta[ cfg.meta ] && ! Array.isArray( meta[ cfg.meta ] ) ? meta[ cfg.meta ] : {};
		var set = function ( key, v ) {
			var next = Object.assign( {}, s );
			next[ key ] = v;
			var m = Object.assign( {}, meta );
			m[ cfg.meta ] = next;
			entity[ 1 ]( m );
		};
		var count = Object.keys( s ).filter( function ( k ) {
			return s[ k ] && ! ( 'object' === typeof s[ k ] && ! s[ k ].url );
		} ).length;
		return Object.keys( cfg.groups ).map( function ( g, i ) {
			return el( Panel, { key: g, name: 'wpessential-page-' + g, title: ( 0 === i ? __( 'WPEssential · ', 'hi-wpessential' ) : '' ) + cfg.groups[ g ] + ( 0 === i && count ? ' (' + count + ')' : '' ), className: 'wpep-panel' },
				Object.keys( cfg.fields ).filter( function ( k ) {
					var f = cfg.fields[ k ];
					return f.group === g && ( ! f.show || s[ f.show[ 0 ] ] === f.show[ 1 ] );
				} ).map( function ( k ) {
					return el( 'div', { key: k, className: 'wpep-row' }, field( k, cfg.fields[ k ], s[ k ], set ) );
				} )
			);
		} );
	}

	wp.plugins.registerPlugin( 'wpessential-page-settings', { render: Settings, icon: null } );
}( window.wp ) );
