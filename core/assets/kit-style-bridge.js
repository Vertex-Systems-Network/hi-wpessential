( function () {
	'use strict';
	var storageKey = 'wpessential.kit.style-presets.v1';
	var format = 'wpessential-kit-style-v1';

	function allPresets() { try { return JSON.parse( window.localStorage.getItem( storageKey ) || '{}' ); } catch ( e ) { return {}; } }
	function savePresets( value ) { window.localStorage.setItem( storageKey, JSON.stringify( value ) ); }
	function panelOf( root ) { return root.closest( '.elementor-panel' ) || document.querySelector( '.elementor-panel' ); }
	function settingNode( panel, key ) {
		try { return panel.querySelector( '[data-setting="' + CSS.escape( key ) + '"]' ); } catch ( e ) { return null; }
	}
	function controlValue( panel, key ) {
		var wrapper = settingNode( panel, key );
		if ( ! wrapper ) return undefined;
		var root = settingRoot( wrapper );
		var inputs = root.querySelectorAll( 'input' );
		if ( inputs.length >= 4 && /dimensions/i.test( root.className || '' ) ) {
			var sides = { top: inputs[ 0 ].value, right: inputs[ 1 ].value, bottom: inputs[ 2 ].value, left: inputs[ 3 ].value };
			var unit = root.querySelector( '.elementor-control-units select, select' );
			if ( unit && unit.value ) sides.unit = unit.value;
			return sides;
		}
		var field = wrapper.matches( 'input,select,textarea' ) ? wrapper : wrapper.querySelector( 'input,select,textarea' );
		if ( ! field ) return undefined;
		if ( field.type === 'checkbox' ) return field.checked ? 'yes' : '';
		var value = field.value;
		var unit = wrapper.querySelector( '[data-unit]' );
		if ( unit && value !== '' && ! isNaN( Number( value ) ) ) value += unit.getAttribute( 'data-unit' );
		return value;
	}
	function settingRoot( node ) { return node.closest( '.elementor-control' ) || node; }
	function dimensionScalar( value ) {
		if ( ! value || typeof value !== 'object' ) return scalar( value );
		var sides = [ 'top', 'right', 'bottom', 'left' ].map( function ( side ) { return scalar( value[ side ] ); } );
		if ( sides.some( function ( side ) { return side === undefined; } ) || sides.some( function ( side ) { return side !== sides[ 0 ]; } ) ) return undefined;
		return sides[ 0 ] + ( value.unit && ! String( sides[ 0 ] ).match( /[a-z%]+$/i ) ? value.unit : '' );
	}
	function dimensionsFromScalar( value ) {
		var match = String( value ).match( /^(-?[\d.]+)\s*([a-z%]*)$/i );
		if ( ! match ) return null;
		return { top: match[ 1 ], right: match[ 1 ], bottom: match[ 1 ], left: match[ 1 ], unit: match[ 2 ] || 'px' };
	}
	function setControl( panel, key, value ) {
		var wrapper = settingNode( panel, key );
		if ( ! wrapper ) return false;
		var root = settingRoot( wrapper );
		var field = root.matches( 'input,select,textarea' ) ? root : root.querySelector( 'input,select,textarea' );
		if ( ! field || value === undefined || value === null ) return false;
		if ( typeof value === 'object' ) {
			var names = [ 'top', 'right', 'bottom', 'left' ];
			if ( names.every( function ( side ) { return value[ side ] !== undefined; } ) ) {
				var inputs = root.querySelectorAll( 'input' );
				if ( inputs.length < 4 ) return false;
				var units = root.querySelector( '.elementor-control-units select, select' );
				if ( units && value.unit ) { units.value = value.unit; units.dispatchEvent( new Event( 'change', { bubbles: true } ) ); }
				names.forEach( function ( side, index ) {
					var raw = String( value[ side ] );
					var match = raw.match( /^(-?[\d.]+)\s*([a-z%]*)$/i );
					inputs[ index ].value = match ? match[ 1 ] : raw;
					if ( units && match && match[ 2 ] ) units.value = match[ 2 ];
					inputs[ index ].dispatchEvent( new Event( 'input', { bubbles: true } ) ); inputs[ index ].dispatchEvent( new Event( 'change', { bubbles: true } ) );
				} );
				return true;
			}
			if ( value.size !== undefined ) value = value.size + ( value.unit || '' );
			else return false;
		}
		if ( field.type === 'checkbox' ) field.checked = !! value && 'no' !== value;
		else field.value = value;
		field.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		field.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		return true;
	}
	function numberValue( value ) {
		if ( value && typeof value === 'object' && value.size !== undefined ) return value.size;
		if ( typeof value === 'string' ) { var match = value.match( /^(-?[\d.]+)/ ); return match ? match[ 1 ] : undefined; }
		return value;
	}
	function scalar( value ) {
		if ( value === undefined || value === null || value === '' ) return undefined;
		if ( typeof value === 'object' ) {
			if ( value.size !== undefined && value.size !== '' ) return String( value.size ) + ( value.unit || '' );
			return undefined;
		}
		return value;
	}
	function normalizeGroup( type, prefix, raw ) {
		var out = {};
		var aliases = {
			typography: { family: [ '_font_family' ], size: [ '_font_size' ], weight: [ '_font_weight' ], transform: [ '_text_transform' ], style: [ '_font_style' ], decoration: [ '_text_decoration' ], lineHeight: [ '_line_height' ], letterSpacing: [ '_letter_spacing' ], wordSpacing: [ '_word_spacing' ] },
			spacing: { top: [ '_top' ], right: [ '_right' ], bottom: [ '_bottom' ], left: [ '_left' ] },
			border: { style: [ '_border' ], width: [ '_width' ], color: [ '_color' ], hoverColor: [ '_hover_color' ], radius: [ '_radius' ] },
			shadow: { custom: [ '_box_shadow' ], hoverPreset: [ '_hover_box_shadow' ] },
			background: { color: [ '_color' ], image: [ '_image' ], position: [ '_position' ], size: [ '_size' ], repeat: [ '_repeat' ], attachment: [ '_attachment' ] },
			colors: { normal: [ '' ], hover: [ '_hover' ], focus: [ '_focus' ], active: [ '_active' ], dark: [ '_dark' ], darkHover: [ '_dark_hover' ] },
			textEffects: { shadow: [ '_text_shadow' ], hoverShadow: [ '_hover_text_shadow' ], strokeWidth: [ '_stroke_text_stroke' ], strokeColor: [ '_stroke_stroke_color' ], blend: [ '_blend' ] },
			filters: { blur: [ '_blur' ], brightness: [ '_brightness' ], contrast: [ '_contrast' ], saturate: [ '_saturate' ], hue: [ '_hue' ], grey: [ '_grayscale' ], opacity: [ '_opacity' ] }
		};
		var map = aliases[ type ] || {};
		Object.keys( map ).forEach( function ( field ) {
			for ( var i = 0; i < map[ field ].length; i++ ) {
				var rawValue = raw[ prefix + map[ field ][ i ] ];
				var value = type === 'filters' ? numberValue( rawValue ) : ( ( type === 'spacing' || ( type === 'border' && ( field === 'width' || field === 'radius' ) ) ) ? dimensionScalar( rawValue ) : scalar( rawValue ) );
				if ( value !== undefined ) { out[ field ] = value; break; }
			}
		} );
		if ( type === 'spacing' && raw[ prefix ] && typeof raw[ prefix ] === 'object' ) {
			[ 'top', 'right', 'bottom', 'left' ].forEach( function ( side ) { var v = scalar( raw[ prefix ][ side ] ); if ( v !== undefined ) out[ side ] = v + ( raw[ prefix ].unit || '' ); } );
		}
		if ( type === 'border' ) {
			[ 'width', 'radius' ].forEach( function ( field ) { var value = dimensionScalar( raw[ prefix + ( field === 'width' ? '_width' : '_radius' ) ] ); if ( value !== undefined ) out[ field ] = value; } );
		}
		if ( type === 'border' && out.style && typeof out.style === 'object' ) out.style = out.style.style || '';
		if ( type === 'shadow' ) {
			var shadow = raw[ prefix + '_box_shadow' ] || raw[ prefix ];
			if ( shadow && typeof shadow === 'object' ) {
				var css = [ shadow.horizontal || shadow.x || 0, shadow.vertical || shadow.y || 0, shadow.blur || 0, shadow.spread || 0, shadow.color || '' ].join( ' ' ).trim();
				if ( css.replace( /[0\s]/g, '' ) ) out.custom = css;
			}
		}
		return out;
	}
	function payloadFor( root, panel ) {
		var keys = []; try { keys = JSON.parse( root.getAttribute( 'data-wpek-style-keys' ) || '[]' ); } catch ( e ) {}
		var attrs = {};
		keys.forEach( function ( key ) { var value = controlValue( panel, key ); if ( value !== undefined ) attrs[ key ] = value; } );
		var raw = {};
		var prefixes = []; try { prefixes = JSON.parse( root.getAttribute( 'data-wpek-group-prefixes' ) || '[]' ); } catch ( e ) {}
		Array.prototype.forEach.call( panel.querySelectorAll( '[data-setting]' ), function ( node ) {
			var key = node.getAttribute( 'data-setting' );
			if ( ! prefixes.some( function ( prefix ) { return key === prefix || key.indexOf( prefix + '_' ) === 0; } ) ) return;
			var value = controlValue( panel, key );
			if ( value !== undefined ) raw[ key ] = value;
		} );
		var groupMap = {}; try { groupMap = JSON.parse( root.getAttribute( 'data-wpek-group-map' ) || '{}' ); } catch ( e ) {}
		var groups = {};
		Object.keys( groupMap ).forEach( function ( prefix ) {
			var normalized = normalizeGroup( groupMap[ prefix ], prefix, raw );
			if ( Object.keys( normalized ).length ) groups[ prefix ] = normalized;
		} );
		return { format: format, attributes: attrs, groups: groups, groupTypes: groupMap, elementorControls: raw };
	}
	function applyGroups( panel, groups, groupTypes ) {
		var applied = 0;
		Object.keys( groups || {} ).forEach( function ( prefix ) {
			var group = groups[ prefix ] || {};
			var type = ( groupTypes || {} )[ prefix ] || '';
			var names = {
				typography: { family: '_font_family', size: '_font_size', weight: '_font_weight', transform: '_text_transform', style: '_font_style', decoration: '_text_decoration', lineHeight: '_line_height', letterSpacing: '_letter_spacing', wordSpacing: '_word_spacing' },
				spacing: { top: '_top', right: '_right', bottom: '_bottom', left: '_left' },
				border: { style: '_border', width: '_width', color: '_color', hoverColor: '_hover_color', radius: '_radius' },
				background: { color: '_color', image: '_image', position: '_position', size: '_size', repeat: '_repeat', attachment: '_attachment' },
				colors: { normal: '', hover: '_hover', focus: '_focus', active: '_active', dark: '_dark', darkHover: '_dark_hover' },
				textEffects: { shadow: '_text_shadow', hoverShadow: '_hover_text_shadow', strokeWidth: '_stroke_text_stroke', strokeColor: '_stroke_stroke_color', blend: '_blend' },
				filters: { blur: '_blur', brightness: '_brightness', contrast: '_contrast', saturate: '_saturate', hue: '_hue', grey: '_grayscale', hoverBlur: '_hover_blur', hoverBrightness: '_hover_brightness', hoverContrast: '_hover_contrast', hoverSaturate: '_hover_saturate', hoverHue: '_hover_hue', hoverGrey: '_hover_grayscale', opacity: '_opacity', hoverOpacity: '_opacity_hover' }
			};
			var map = names[ type ] || {};
			if ( type === 'spacing' ) {
				var spacing = {};
				[ 'top', 'right', 'bottom', 'left' ].forEach( function ( side ) { if ( group[ side ] !== undefined ) spacing[ side ] = group[ side ]; } );
				if ( Object.keys( spacing ).length ) {
					var parsed = dimensionsFromScalar( spacing.top || spacing.right || spacing.bottom || spacing.left );
					if ( parsed ) [ 'top', 'right', 'bottom', 'left' ].forEach( function ( side ) { if ( spacing[ side ] !== undefined ) parsed[ side ] = spacing[ side ]; } );
					else Object.assign( parsed || ( parsed = {} ), spacing );
					if ( setControl( panel, prefix, parsed ) ) applied++;
				}
			}
			if ( type === 'border' ) {
				[ 'width', 'radius' ].forEach( function ( field ) { var parsed = dimensionsFromScalar( group[ field ] ); if ( parsed && setControl( panel, prefix + ( field === 'width' ? '_width' : '_radius' ), parsed ) ) applied++; } );
			}
			Object.keys( map ).forEach( function ( field ) {
				if ( group[ field ] !== undefined ) {
					var value = group[ field ];
					if ( value && typeof value === 'object' && value.size !== undefined ) value = value.size + ( value.unit || '' );
					if ( setControl( panel, prefix + map[ field ], value ) ) applied++;
				}
			} );
			if ( type === 'shadow' && group.custom ) {
				var shadow = group.custom;
				if ( typeof shadow === 'string' && setControl( panel, prefix + '_box_shadow', shadow ) ) applied++;
			}
			if ( type === 'background' && group.image && group.image.url ) {
				if ( setControl( panel, prefix + '_background_image[url]', group.image.url ) ) applied++;
			}
		} );
		return applied;
	}
	function status( root, text ) { var node = root.querySelector( '.wpek-style-status' ); if ( node ) node.textContent = text; }
	function applyPayload( panel, payload ) {
		var applied = 0;
		Object.keys( payload.attributes || {} ).forEach( function ( key ) { if ( setControl( panel, key, payload.attributes[ key ] ) ) applied++; } );
		Object.keys( payload.elementorControls || {} ).forEach( function ( key ) { if ( setControl( panel, key, payload.elementorControls[ key ] ) ) applied++; } );
		applied += applyGroups( panel, payload.groups || {}, payload.groupTypes || JSON.parse( root.getAttribute( 'data-wpek-group-map' ) || '{}' ) );
		return applied;
	}
	function transfer( root, action ) {
		var panel = panelOf( root );
		if ( ! panel ) return;
		if ( action === 'copy' ) {
			navigator.clipboard && navigator.clipboard.writeText ? navigator.clipboard.writeText( JSON.stringify( payloadFor( root, panel ) ) ).then( function () { status( root, 'Style copied, including shared style groups.' ); } ).catch( function () { status( root, 'Clipboard permission was denied.' ); } ) : status( root, 'Clipboard is unavailable in this browser.' );
		} else if ( action === 'paste' ) {
			if ( ! navigator.clipboard || ! navigator.clipboard.readText ) { status( root, 'Clipboard is unavailable in this browser.' ); return; }
			navigator.clipboard.readText().then( function ( raw ) {
				try { var payload = JSON.parse( raw ); if ( payload.format !== format ) { status( root, 'Clipboard does not contain Kit styles.' ); return; }
					var applied = applyPayload( panel, payload );
					status( root, applied ? 'Style applied to ' + applied + ' settings.' : 'No matching style settings found.' );
				} catch ( e ) { status( root, 'Clipboard data is invalid.' ); }
			} ).catch( function () { status( root, 'Clipboard permission was denied.' ); } );
		} else if ( action === 'save' ) {
			var name = root.querySelector( '.wpek-style-name' ).value.trim();
			if ( ! name ) { status( root, 'Enter a preset name first.' ); return; }
			var presets = allPresets(); presets[ name ] = payloadFor( root, panel ); savePresets( presets ); populate( root ); root.querySelector( '.wpek-style-select' ).value = name; status( root, 'Preset saved in this browser.' );
		} else if ( action === 'apply' ) {
			var preset = allPresets()[ root.querySelector( '.wpek-style-select' ).value ];
			if ( ! preset || preset.format !== format ) { status( root, 'Choose a valid preset.' ); return; }
			var count = applyPayload( panel, preset );
			status( root, count ? 'Preset applied to ' + count + ' settings.' : 'No matching preset settings found.' );
		}
	}
	function populate( root ) {
		var select = root.querySelector( '.wpek-style-select' ); if ( ! select ) return;
		var selected = select.value; select.innerHTML = '<option value="">Choose a saved preset</option>';
		Object.keys( allPresets() ).sort().forEach( function ( name ) { var option = document.createElement( 'option' ); option.value = name; option.textContent = name; select.appendChild( option ); } );
		select.value = selected;
	}
	document.addEventListener( 'click', function ( event ) {
		var root = event.target.closest( '.wpek-style-bridge' ); if ( ! root ) return;
		var button = event.target.closest( 'button' ); if ( ! button ) return;
		event.preventDefault();
		if ( button.classList.contains( 'wpek-style-copy' ) ) transfer( root, 'copy' );
		if ( button.classList.contains( 'wpek-style-paste' ) ) transfer( root, 'paste' );
		if ( button.classList.contains( 'wpek-style-save' ) ) transfer( root, 'save' );
		if ( button.classList.contains( 'wpek-style-apply' ) ) transfer( root, 'apply' );
	} );
	var observer = new MutationObserver( function () { document.querySelectorAll( '.wpek-style-bridge' ).forEach( populate ); } );
	observer.observe( document.documentElement, { childList: true, subtree: true } );
	document.addEventListener( 'DOMContentLoaded', function () { document.querySelectorAll( '.wpek-style-bridge' ).forEach( populate ); } );
}() );
