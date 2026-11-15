/**
 * Site Settings in the Customizer: each WPEssential_Customize_Control gets a control from the
 * wpeControls library (assets/js/wpe-controls.js), bound to its setting. Responsive controls
 * follow the Customizer's preview device, and the device buttons switch it.
 */
( function ( api, controls ) {
	'use strict';

	// Group headings in the top-level list: never open, and stay visible without controls.
	api.sectionConstructor.wpessential_group = api.Section.extend( {
		attachEvents: function () {},
		isContextuallyActive: function () {
			return true;
		},
	} );

	function build( control ) {
		var root = control.container[ 0 ].querySelector( '.wpec' );
		if ( ! root || root.dataset.ready || ! control.setting ) {
			return;
		}
		root.dataset.ready = '1';
		var opt = JSON.parse( root.getAttribute( 'data-wpec' ) || '{}' );
		var setting = control.setting;
		var get = function () {
			var v = setting.get();
			return null === v || undefined === v || false === v ? '' : String( v );
		};
		var ctl = controls.create( opt.type, {
			opt: opt,
			label: control.params.label,
			get: get,
			set: function ( v ) {
				setting.set( v );
			},
		} );
		var head = root.querySelector( '.wpec__head' );
		if ( ctl.devices ) {
			head.insertBefore( ctl.devices, head.querySelector( '.wpec__reset' ) );
		}
		if ( 'toggle' === opt.type ) {
			head.insertBefore( ctl.el, head.querySelector( '.wpec__reset' ) );
		} else {
			root.querySelector( '.wpec__ui' ).appendChild( ctl.el );
		}
		if ( opt.tab ) {
			root.classList.add( 'wpec--tab-' + opt.tab );
			control.container[ 0 ].setAttribute( 'data-wpec-tab', opt.tab );
			api.section( control.section(), function ( section ) {
				clearTimeout( section.wpecTabs );
				section.wpecTabs = setTimeout( function () {
					tabs( section );
				}, 50 );
			} );
		}
		var reset = root.querySelector( '.wpec__reset' );
		reset.addEventListener( 'click', function () {
			setting.set( '' );
		} );
		var sync = function () {
			ctl.refresh();
			reset.hidden = '' === get();
			root.classList.toggle( 'is-set', '' !== get() );
		};
		setting.bind( sync );
		sync();
	}

	// Normal / Hover tabs: controls marked with a tab are shown one tab at a time (like Elementor).
	function tabs( section ) {
		var list = section.contentContainer[ 0 ];
		var items = list.querySelectorAll( '[data-wpec-tab]' );
		var old = list.querySelector( '.wpec-statebar' );
		if ( ! items.length ) {
			return;
		}
		if ( old ) {
			old.remove();
		}
		var bar = controls.h( 'li', { class: 'wpec-statebar customize-control' } );
		var seg = controls.h( 'div', { class: 'wpec-seg', role: 'tablist' } );
		[ [ 'normal', 'Normal' ], [ 'hover', 'Hover' ] ].forEach( function ( t, i ) {
			seg.appendChild( controls.h( 'button', { type: 'button', role: 'tab', class: 'wpec-seg__btn' + ( 0 === i ? ' is-active' : '' ), text: t[ 1 ], onclick: function ( e ) {
				seg.querySelectorAll( 'button' ).forEach( function ( b ) {
					b.classList.toggle( 'is-active', b === e.currentTarget );
				} );
				show( t[ 0 ] );
			} } ) );
		} );
		bar.appendChild( seg );
		items[ 0 ].parentNode.insertBefore( bar, items[ 0 ] );
		function show( tab ) {
			list.querySelectorAll( '[data-wpec-tab]' ).forEach( function ( li ) {
				li.style.display = li.getAttribute( 'data-wpec-tab' ) === tab ? '' : 'none';
			} );
		}
		show( 'normal' );
	}

	api.bind( 'ready', function () {
		// WordPress' "your theme supports site editing" notice: our panel is the entry point here.
		if ( api.notifications ) {
			api.notifications.remove( 'site_editor_block_theme_notice' );
		}
		// A breakpoint button resizes the preview to that screen width (like Elementor's responsive mode).
		var WP_TO_BP = { desktop: 'desktop', tablet: '768', mobile: '560' };
		var preview = document.getElementById( 'customize-preview' );
		var resize = function ( d ) {
			var w = ( controls.breakpoints[ d ] || {} ).width;
			if ( ! preview ) {
				return;
			}
			if ( w && w < 1920 ) {
				preview.style.width = w + 'px';
				preview.style.left = '50%';
				preview.style.marginLeft = ( -w / 2 ) + 'px';
				preview.classList.add( 'wpec-preview-sized' );
			} else {
				preview.style.width = preview.style.left = preview.style.marginLeft = '';
				preview.classList.remove( 'wpec-preview-sized' );
			}
		};
		controls.onDevice = function ( d ) {
			if ( api.previewedDevice && 'desktop' !== api.previewedDevice.get() ) {
				api.previewedDevice.set( 'desktop' );
			}
			resize( d );
		};
		if ( api.previewedDevice ) {
			api.previewedDevice.bind( function ( d ) {
				var bp = WP_TO_BP[ d ] || 'desktop';
				if ( 'desktop' !== d && controls.currentDevice !== bp ) {
					// WordPress' own device buttons: show the matching breakpoint.
					resize( 'desktop' );
					controls.currentDevice = bp;
					controls.setDevice( bp );
				}
			} );
		}
		// The Breakpoints setting decides which breakpoints every responsive control shows.
		api( 'wpessential_site[breakpoints]', function ( setting ) {
			setting.bind( function ( v ) {
				controls.setBreakpoints( controls.parseOn( v ) );
			} );
		} );
		var init = function ( control ) {
			if ( 'wpessential' === control.params.type ) {
				control.deferred.embedded.done( function () {
					build( control );
				} );
			}
		};
		api.control.each( init );
		api.control.bind( 'add', init );
		api.section.each( function ( section ) {
			if ( 0 === section.id.indexOf( 'wpessential_site' ) ) {
				section.expanded.bind( function ( open ) {
					if ( open ) {
						setTimeout( function () {
							tabs( section );
						}, 0 );
					}
				} );
			}
		} );
	} );
}( window.wp.customize, window.wpeControls ) );
