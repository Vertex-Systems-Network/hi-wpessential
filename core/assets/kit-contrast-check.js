/**
 * Advisory WCAG contrast check for the selected Kit element's rendered preview.
 * It reports computed text/background pairs; it never changes author styles.
 */
( function () {
	'use strict';
	var noticeId = 'wpessential-kit-contrast';
	var lastMessage = '';
	function rgba( value ) {
		var m = String( value || '' ).match( /rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)(?:\s*,\s*([\d.]+))?\s*\)/i );
		return m ? [ Number( m[ 1 ] ), Number( m[ 2 ] ), Number( m[ 3 ] ), undefined === m[ 4 ] ? 1 : Number( m[ 4 ] ) ] : null;
	}
	function blend( fg, bg ) {
		var a = fg[ 3 ];
		return [ 0, 1, 2, 3 ].map( function ( i ) { return i === 3 ? 1 : Math.round( fg[ i ] * a + bg[ i ] * ( 1 - a ) ); } );
	}
	function luminance( c ) {
		var channels = c.slice( 0, 3 ).map( function ( v ) {
			v /= 255;
			return v <= .04045 ? v / 12.92 : Math.pow( ( v + .055 ) / 1.055, 2.4 );
		} );
		return .2126 * channels[ 0 ] + .7152 * channels[ 1 ] + .0722 * channels[ 2 ];
	}
	function contrast( a, b ) {
		var x = luminance( a ), y = luminance( b );
		return ( Math.max( x, y ) + .05 ) / ( Math.min( x, y ) + .05 );
	}
	function backgroundFor( node, root, doc ) {
		var current = node;
		while ( current && current.nodeType === 1 ) {
			var color = rgba( doc.defaultView.getComputedStyle( current ).backgroundColor );
			if ( color && color[ 3 ] > .01 ) {
				if ( color[ 3 ] < .99 ) {
					var parent = current.parentElement;
					while ( parent && parent.nodeType === 1 ) {
						var parentColor = rgba( doc.defaultView.getComputedStyle( parent ).backgroundColor );
						if ( parentColor && parentColor[ 3 ] > .01 ) { color = blend( color, parentColor ); break; }
						parent = parent.parentElement;
					}
					if ( color[ 3 ] < .99 ) color = blend( color, [ 255, 255, 255, 1 ] );
				}
				return color;
			}
			if ( current === root ) break;
			current = current.parentElement;
		}
		return [ 255, 255, 255, 1 ];
	}
	function inspect( root, doc ) {
		if ( ! root || ! root.querySelectorAll ) return null;
		var nodes = [ root ].concat( Array.prototype.slice.call( root.querySelectorAll( '*' ) ) );
		var lowest = Infinity;
		nodes.forEach( function ( node ) {
			if ( ! Array.prototype.some.call( node.childNodes || [], function ( child ) { return child.nodeType === 3 && child.nodeValue.trim(); } ) ) return;
			var styles = doc.defaultView.getComputedStyle( node );
			var fg = rgba( styles.color );
			if ( ! fg ) return;
			var bg = backgroundFor( node, root, doc );
			if ( fg[ 3 ] < .99 ) fg = blend( fg, bg );
			lowest = Math.min( lowest, contrast( fg, bg ) );
		} );
		return lowest < 4.5 ? lowest : null;
	}
	function report( ratio ) {
		var store = window.wp && wp.data && wp.data.dispatch( 'core/notices' );
		if ( store ) {
			store.removeNotice( noticeId );
			if ( ratio ) {
				store.createNotice( 'warning', 'This Kit preview may contain text below WCAG AA contrast (lowest estimated ratio ' + ratio.toFixed( 2 ) + ':1). Check text size, overlays, and the published page.', { id: noticeId, isDismissible: true } );
			}
		}
		var panel = document.querySelector( '.elementor-panel' );
		if ( panel ) {
			var notice = panel.querySelector( '.wpek-contrast-warning' );
			if ( ratio && ! notice ) {
				notice = document.createElement( 'div' );
				notice.className = 'wpek-contrast-warning';
				notice.style.cssText = 'margin:12px;padding:10px;border-left:4px solid #dba617;background:#fff8e5;color:#332600;font-size:12px;line-height:1.45';
				panel.prepend( notice );
			}
			if ( notice ) {
				notice.hidden = ! ratio;
				notice.textContent = ratio ? 'Possible WCAG AA contrast issue. Lowest estimated text ratio: ' + ratio.toFixed( 2 ) + ':1. Check text size, overlays, and the published page.' : '';
			}
		}
	}
	function check() {
		var ratio = null;
		var iframe = document.getElementById( 'elementor-preview-iframe' );
		if ( iframe && iframe.contentDocument ) {
			var selected = iframe.contentDocument.querySelector( '.elementor-element-edit-mode' );
			if ( selected && /wpe-|wpessential/i.test( selected.className ) ) ratio = inspect( selected, iframe.contentDocument );
		}
		if ( ! ratio && window.wp && wp.data ) {
			var editor = wp.data.select( 'core/block-editor' );
			var id = editor && editor.getSelectedBlockClientId();
			if ( id ) {
				var root = document.querySelector( '[data-block="' + CSS.escape( id ) + '"]' );
				if ( root && root.querySelector( '[class*="wpe-"]' ) ) ratio = inspect( root.querySelector( '.wpe-' ), document );
			}
		}
		var message = ratio ? ratio.toFixed( 2 ) : '';
		if ( message !== lastMessage ) { lastMessage = message; report( ratio ); }
	}
	window.setInterval( check, 1200 );
	document.addEventListener( 'DOMContentLoaded', check );
}() );
