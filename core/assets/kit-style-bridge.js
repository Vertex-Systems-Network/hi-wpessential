( function () {
	'use strict';
	var storageKey = 'wpessential.kit.style-presets.v1';
	var format = 'wpessential-kit-style-v1';
	function allPresets() { try { return JSON.parse( window.localStorage.getItem( storageKey ) || '{}' ); } catch ( e ) { return {}; } }
	function savePresets( value ) { window.localStorage.setItem( storageKey, JSON.stringify( value ) ); }
	function controlValue( panel, key ) {
		var wrapper = panel.querySelector( '[data-setting="' + CSS.escape( key ) + '"]' );
		if ( ! wrapper ) return undefined;
		var field = wrapper.matches( 'input,select,textarea' ) ? wrapper : wrapper.querySelector( 'input,select,textarea' );
		if ( ! field ) return undefined;
		return field.type === 'checkbox' ? ( field.checked ? 'yes' : '' ) : field.value;
	}
	function setControl( panel, key, value ) {
		var wrapper = panel.querySelector( '[data-setting="' + CSS.escape( key ) + '"]' );
		if ( ! wrapper ) return false;
		var field = wrapper.matches( 'input,select,textarea' ) ? wrapper : wrapper.querySelector( 'input,select,textarea' );
		if ( ! field ) return false;
		if ( field.type === 'checkbox' ) field.checked = !! value && 'no' !== value;
		else field.value = value;
		field.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		field.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		return true;
	}
	function panelOf( root ) { return root.closest( '.elementor-panel' ) || document.querySelector( '.elementor-panel' ); }
	function payloadFor( root, panel ) {
		var keys = []; try { keys = JSON.parse( root.getAttribute( 'data-wpek-style-keys' ) || '[]' ); } catch ( e ) {}
		var attrs = {};
		keys.forEach( function ( key ) { var value = controlValue( panel, key ); if ( undefined !== value ) attrs[ key ] = value; } );
		return { format: format, attributes: attrs, groups: {} };
	}
	function status( root, text ) { var node = root.querySelector( '.wpek-style-status' ); if ( node ) node.textContent = text; }
	function transfer( root, action ) {
		var panel = panelOf( root );
		if ( ! panel ) return;
		if ( 'copy' === action ) {
			var payload = JSON.stringify( payloadFor( root, panel ) );
			navigator.clipboard && navigator.clipboard.writeText ? navigator.clipboard.writeText( payload ).then( function () { status( root, 'Style copied.' ); } ).catch( function () { status( root, 'Clipboard permission was denied.' ); } ) : status( root, 'Clipboard is unavailable in this browser.' );
		} else if ( 'paste' === action ) {
			if ( ! navigator.clipboard || ! navigator.clipboard.readText ) { status( root, 'Clipboard is unavailable in this browser.' ); return; }
			navigator.clipboard.readText().then( function ( raw ) {
				try {
					var payload = JSON.parse( raw ); if ( payload.format !== format ) { status( root, 'Clipboard does not contain Kit styles.' ); return; }
					var applied = 0; Object.keys( payload.attributes || {} ).forEach( function ( key ) { if ( setControl( panel, key, payload.attributes[ key ] ) ) applied++; } );
					status( root, applied ? 'Style applied to ' + applied + ' settings.' : 'No matching style settings found.' );
				} catch ( e ) { status( root, 'Clipboard data is invalid.' ); }
			} ).catch( function () { status( root, 'Clipboard permission was denied.' ); } );
		} else if ( 'save' === action ) {
			var name = root.querySelector( '.wpek-style-name' ).value.trim();
			if ( ! name ) { status( root, 'Enter a preset name first.' ); return; }
			var presets = allPresets(); presets[ name ] = payloadFor( root, panel ); savePresets( presets ); populate( root ); root.querySelector( '.wpek-style-select' ).value = name; status( root, 'Preset saved in this browser.' );
		} else if ( 'apply' === action ) {
			var preset = allPresets()[ root.querySelector( '.wpek-style-select' ).value ];
			if ( ! preset || preset.format !== format ) { status( root, 'Choose a valid preset.' ); return; }
			var count = 0; Object.keys( preset.attributes || {} ).forEach( function ( key ) { if ( setControl( panel, key, preset.attributes[ key ] ) ) count++; } );
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
