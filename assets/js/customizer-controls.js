/**
 * WPEssential Site Settings controls (Customizer). Each control's markup comes from
 * WPEssential_Customize_Control; this builds the UI and writes the value into the control's
 * setting. Types: color, range, toggle, buttons, select, text, font, fonts.
 */
( function ( api ) {
	'use strict';

	var cfg = window.wpessentialCustomizer || {};
	var __ = window.wp.i18n.__;
	var googleList = null;
	var googleWaiters = [];
	var previewed = {};

	function h( tag, attrs, children ) {
		var el = document.createElement( tag );
		Object.keys( attrs || {} ).forEach( function ( k ) {
			if ( 'class' === k ) {
				el.className = attrs[ k ];
			} else if ( 'text' === k ) {
				el.textContent = attrs[ k ];
			} else if ( 0 === k.indexOf( 'on' ) ) {
				el.addEventListener( k.slice( 2 ), attrs[ k ] );
			} else if ( false !== attrs[ k ] && null !== attrs[ k ] && undefined !== attrs[ k ] ) {
				el.setAttribute( k, true === attrs[ k ] ? '' : attrs[ k ] );
			}
		} );
		( children || [] ).forEach( function ( c ) {
			if ( c ) {
				el.appendChild( 'string' === typeof c ? document.createTextNode( c ) : c );
			}
		} );
		return el;
	}

	function loadGoogle( done ) {
		if ( googleList ) {
			done( googleList );
			return;
		}
		googleWaiters.push( done );
		if ( googleWaiters.length > 1 ) {
			return;
		}
		window.fetch( cfg.googleList ).then( function ( r ) {
			return r.json();
		} ).then( function ( rows ) {
			googleList = rows;
			googleWaiters.forEach( function ( fn ) {
				fn( rows );
			} );
		} ).catch( function () {
			googleList = [];
		} );
	}

	// Load just the letters of a font name so the list shows each font in its own face.
	function preview( family, isGoogle ) {
		if ( previewed[ family ] ) {
			return;
		}
		previewed[ family ] = true;
		if ( isGoogle ) {
			document.head.appendChild( h( 'link', { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=' + encodeURIComponent( family ).replace( /%20/g, '+' ) + '&text=' + encodeURIComponent( family + 'Aa' ) + '&display=swap' } ) );
		} else if ( cfg.fontFiles && cfg.fontFiles[ family ] ) {
			var style = h( 'style' );
			style.textContent = '@font-face{font-family:"' + family + '";src:url("' + cfg.fontFiles[ family ] + '") format("woff2");font-weight:100 900;font-display:swap}';
			document.head.appendChild( style );
		}
	}

	function fontLabel( value ) {
		if ( ! value ) {
			return __( 'Theme default', 'hi-wpessential' );
		}
		return 0 === value.indexOf( 'google:' ) ? value.slice( 7 ) : value;
	}

	function fontStack( value ) {
		if ( ! value ) {
			return 'inherit';
		}
		if ( 0 === value.indexOf( 'google:' ) ) {
			return '"' + value.slice( 7 ) + '", system-ui, sans-serif';
		}
		return ( cfg.themeStacks && cfg.themeStacks[ value ] ) || value;
	}

	/**
	 * A searchable font list. onPick( value ) gets "Name" (theme font) or "google:Name".
	 */
	function fontPicker( current, onPick, opts ) {
		opts = opts || {};
		var CATS = [ [ 'all', __( 'All', 'hi-wpessential' ) ], [ 'theme', __( 'In theme', 'hi-wpessential' ) ], [ 'sans', 'Sans' ], [ 'serif', 'Serif' ], [ 'display', 'Display' ], [ 'hand', __( 'Handwriting', 'hi-wpessential' ) ], [ 'mono', 'Mono' ] ];
		var state = { q: '', cat: 'all', limit: 60 };
		var list = h( 'div', { class: 'wpec-font__list', role: 'listbox', 'aria-label': __( 'Fonts', 'hi-wpessential' ) } );
		var search = h( 'input', { type: 'search', class: 'wpec-font__search', placeholder: __( 'Search 1,700+ fonts…', 'hi-wpessential' ), 'aria-label': __( 'Search fonts', 'hi-wpessential' ) } );
		var chips = h( 'div', { class: 'wpec-font__cats' }, CATS.map( function ( c ) {
			return h( 'button', { type: 'button', class: 'wpec-chip' + ( 'all' === c[ 0 ] ? ' is-active' : '' ), 'data-cat': c[ 0 ], text: c[ 1 ], onclick: function ( e ) {
				state.cat = c[ 0 ];
				state.limit = 60;
				chips.querySelectorAll( '.wpec-chip' ).forEach( function ( b ) {
					b.classList.toggle( 'is-active', b === e.currentTarget );
				} );
				draw();
			} } );
		} ) );
		var panel = h( 'div', { class: 'wpec-font__panel' }, [ search, chips, list ] );
		var observer = 'IntersectionObserver' in window ? new window.IntersectionObserver( function ( entries ) {
			entries.forEach( function ( en ) {
				if ( en.isIntersecting ) {
					var b = en.target;
					preview( b.getAttribute( 'data-family' ), '1' === b.getAttribute( 'data-google' ) );
					observer.unobserve( b );
				}
			} );
		}, { root: list } ) : null;

		function row( value, family, meta, isGoogle ) {
			var b = h( 'button', { type: 'button', role: 'option', class: 'wpec-font__item' + ( value === current ? ' is-active' : '' ), 'aria-selected': value === current ? 'true' : 'false', 'data-family': family, 'data-google': isGoogle ? '1' : '0', onclick: function () {
				onPick( value );
			} }, [
				h( 'span', { class: 'wpec-font__name', style: 'font-family:' + ( isGoogle ? '"' + family + '", system-ui' : fontStack( value ) ), text: family } ),
				h( 'span', { class: 'wpec-font__meta', text: meta } ),
			] );
			if ( observer ) {
				observer.observe( b );
			} else {
				preview( family, isGoogle );
			}
			return b;
		}

		function draw() {
			var q = state.q.trim().toLowerCase();
			list.innerHTML = '';
			if ( ! opts.multi ) {
				list.appendChild( row( '', __( 'Theme default', 'hi-wpessential' ), __( 'From the demo', 'hi-wpessential' ), false ) );
			}
			if ( ! opts.multi && ( 'all' === state.cat || 'theme' === state.cat ) ) {
				( cfg.themeFonts || [] ).filter( function ( f ) {
					return ! q || f.toLowerCase().indexOf( q ) >= 0;
				} ).forEach( function ( f ) {
					list.appendChild( row( f, f, __( 'In theme', 'hi-wpessential' ), false ) );
				} );
			}
			if ( 'theme' === state.cat ) {
				return;
			}
			loadGoogle( function ( rows ) {
				var shown = 0;
				var total = 0;
				rows.forEach( function ( r ) {
					if ( ( 'all' !== state.cat && r[ 1 ] !== state.cat ) || ( q && r[ 0 ].toLowerCase().indexOf( q ) < 0 ) ) {
						return;
					}
					total++;
					if ( shown < state.limit ) {
						shown++;
						list.appendChild( row( 'google:' + r[ 0 ], r[ 0 ], 'Google · ' + ( r[ 4 ] ? __( 'variable', 'hi-wpessential' ) : r[ 2 ].length + ' ' + __( 'weights', 'hi-wpessential' ) ), true ) );
					}
				} );
				if ( total > shown ) {
					list.appendChild( h( 'button', { type: 'button', class: 'wpec-font__more', text: __( 'Show more', 'hi-wpessential' ) + ' (' + ( total - shown ) + ')', onclick: function () {
						state.limit += 120;
						draw();
					} } ) );
				}
				if ( ! total && ! list.children.length ) {
					list.appendChild( h( 'p', { class: 'wpec-font__empty', text: __( 'No font found.', 'hi-wpessential' ) } ) );
				}
			} );
		}
		search.addEventListener( 'input', function () {
			state.q = search.value;
			state.limit = 60;
			draw();
		} );
		draw();
		setTimeout( function () {
			search.focus();
		}, 0 );
		return panel;
	}

	function build( control ) {
		var root = control.container[ 0 ].querySelector( '.wpec' );
		if ( ! root || root.dataset.ready ) {
			return;
		}
		root.dataset.ready = '1';
		var opt = JSON.parse( root.getAttribute( 'data-wpec' ) || '{}' );
		var ui = root.querySelector( '.wpec__ui' );
		var reset = root.querySelector( '.wpec__reset' );
		var setting = control.setting;
		var get = function () {
			var v = setting.get();
			return null === v || undefined === v || false === v ? '' : String( v );
		};
		var set = function ( v ) {
			setting.set( v );
		};
		var refresh;

		switch ( opt.type ) {
			case 'color': {
				var swatches = h( 'div', { class: 'wpec-swatches' } );
				Object.keys( cfg.palette || {} ).forEach( function ( slug ) {
					var c = cfg.palette[ slug ];
					swatches.appendChild( h( 'button', { type: 'button', class: 'wpec-swatch', title: c[ 0 ] + ' ' + c[ 1 ], 'aria-label': c[ 0 ], style: 'background:' + c[ 1 ], 'data-color': c[ 1 ].toLowerCase(), onclick: function () {
						set( c[ 1 ] );
					} } ) );
				} );
				var picker = h( 'input', { type: 'color', class: 'wpec-color__picker', 'aria-label': __( 'Pick any colour', 'hi-wpessential' ), oninput: function ( e ) {
					set( e.target.value );
				} } );
				var hex = h( 'input', { type: 'text', class: 'wpec-input wpec-color__hex', placeholder: __( 'Theme default', 'hi-wpessential' ), maxlength: 9, spellcheck: 'false', onchange: function ( e ) {
					var v = e.target.value.trim();
					if ( ! v || /^#?[0-9a-f]{3}([0-9a-f]{3})?$/i.test( v ) ) {
						set( v && '#' !== v[ 0 ] ? '#' + v : v );
					}
				} } );
				var chip = h( 'span', { class: 'wpec-color__chip' }, [ picker ] );
				ui.appendChild( h( 'div', { class: 'wpec-color__row' }, [ chip, hex ] ) );
				ui.appendChild( swatches );
				refresh = function () {
					var v = get().toLowerCase();
					chip.style.background = v || 'repeating-conic-gradient(#ddd 0 25%, #fff 0 50%) 0 0 / 10px 10px';
					picker.value = /^#[0-9a-f]{6}$/.test( v ) ? v : '#ffffff';
					hex.value = v;
					swatches.querySelectorAll( '.wpec-swatch' ).forEach( function ( s ) {
						s.classList.toggle( 'is-active', s.getAttribute( 'data-color' ) === v );
					} );
				};
				break;
			}
			case 'range': {
				var slider = h( 'input', { type: 'range', class: 'wpec-range__slider', min: opt.min, max: opt.max, step: opt.step || 1, 'aria-label': control.params.label } );
				var num = h( 'input', { type: 'number', class: 'wpec-input wpec-range__num', min: opt.min, max: opt.max, step: opt.step || 1, placeholder: '–', 'aria-label': control.params.label } );
				slider.addEventListener( 'input', function () {
					set( slider.value );
				} );
				num.addEventListener( 'input', function () {
					set( num.value );
				} );
				ui.appendChild( h( 'div', { class: 'wpec-range__row' }, [ slider, h( 'span', { class: 'wpec-range__box' }, [ num, opt.unit ? h( 'span', { class: 'wpec-range__unit', text: opt.unit } ) : null ] ) ] ) );
				refresh = function () {
					var v = get();
					num.value = v;
					slider.value = '' === v ? ( Number( opt.min ) + Number( opt.max ) ) / 2 : v;
					slider.classList.toggle( 'is-empty', '' === v );
					var pct = '' === v ? 0 : ( ( Number( v ) - Number( opt.min ) ) / ( Number( opt.max ) - Number( opt.min ) ) ) * 100;
					slider.style.setProperty( '--wpec-fill', Math.max( 0, Math.min( 100, pct ) ) + '%' );
				};
				break;
			}
			case 'toggle': {
				var input = h( 'input', { type: 'checkbox', role: 'switch', class: 'wpec-switch__input', 'aria-label': control.params.label, onchange: function ( e ) {
					set( e.target.checked ? '1' : '' );
				} } );
				ui.appendChild( h( 'label', { class: 'wpec-switch' }, [ input, h( 'span', { class: 'wpec-switch__track', 'aria-hidden': 'true' } ) ] ) );
				root.querySelector( '.wpec__head' ).appendChild( ui );
				refresh = function () {
					input.checked = !! get() && '0' !== get();
				};
				break;
			}
			case 'buttons': {
				var group = h( 'div', { class: 'wpec-seg', role: 'radiogroup', 'aria-label': control.params.label } );
				Object.keys( opt.choices ).forEach( function ( k ) {
					group.appendChild( h( 'button', { type: 'button', role: 'radio', class: 'wpec-seg__btn', 'data-v': k, text: opt.choices[ k ], onclick: function () {
						set( k );
					} } ) );
				} );
				ui.appendChild( group );
				refresh = function () {
					var v = get();
					group.querySelectorAll( '.wpec-seg__btn' ).forEach( function ( b ) {
						var on = b.getAttribute( 'data-v' ) === v;
						b.classList.toggle( 'is-active', on );
						b.setAttribute( 'aria-checked', on ? 'true' : 'false' );
					} );
				};
				break;
			}
			case 'select': {
				var sel = h( 'select', { class: 'wpec-input wpec-select', 'aria-label': control.params.label, onchange: function ( e ) {
					set( e.target.value );
				} }, Object.keys( opt.choices ).map( function ( k ) {
					return h( 'option', { value: k, text: opt.choices[ k ] } );
				} ) );
				ui.appendChild( sel );
				refresh = function () {
					sel.value = get();
				};
				break;
			}
			case 'font': {
				var btn = h( 'button', { type: 'button', class: 'wpec-font__current', 'aria-expanded': 'false' } );
				var holder = h( 'div', { class: 'wpec-font__holder' } );
				var close = function () {
					holder.innerHTML = '';
					btn.setAttribute( 'aria-expanded', 'false' );
				};
				btn.addEventListener( 'click', function () {
					if ( holder.firstChild ) {
						close();
						return;
					}
					btn.setAttribute( 'aria-expanded', 'true' );
					holder.appendChild( fontPicker( get(), function ( v ) {
						set( v );
						close();
					} ) );
				} );
				ui.appendChild( btn );
				ui.appendChild( holder );
				refresh = function () {
					var v = get();
					var family = fontLabel( v );
					if ( v ) {
						preview( family, 0 === v.indexOf( 'google:' ) );
					}
					btn.innerHTML = '';
					btn.appendChild( h( 'span', { class: 'wpec-font__sample', style: 'font-family:' + fontStack( v ), text: 'Aa' } ) );
					btn.appendChild( h( 'span', { class: 'wpec-font__label' }, [ h( 'strong', { text: family } ), h( 'small', { text: ! v ? __( 'From the demo', 'hi-wpessential' ) : ( 0 === v.indexOf( 'google:' ) ? 'Google Fonts' : __( 'In theme', 'hi-wpessential' ) ) } ) ] ) );
					btn.appendChild( h( 'span', { class: 'wpec-font__caret', 'aria-hidden': 'true', text: '▾' } ) );
				};
				break;
			}
			case 'fonts': {
				var chipsBox = h( 'div', { class: 'wpec-fonts__chips' } );
				var add = h( 'button', { type: 'button', class: 'wpec-btn', text: '+ ' + __( 'Add a Google font', 'hi-wpessential' ) } );
				var hold = h( 'div', { class: 'wpec-font__holder' } );
				var list = function () {
					return get().split( '|' ).filter( Boolean );
				};
				add.addEventListener( 'click', function () {
					if ( hold.firstChild ) {
						hold.innerHTML = '';
						return;
					}
					hold.appendChild( fontPicker( '', function ( v ) {
						var f = v.replace( /^google:/, '' );
						if ( f && list().indexOf( f ) < 0 ) {
							set( list().concat( [ f ] ).join( '|' ) );
						}
						hold.innerHTML = '';
					}, { multi: true } ) );
				} );
				ui.appendChild( chipsBox );
				ui.appendChild( add );
				ui.appendChild( hold );
				refresh = function () {
					chipsBox.innerHTML = '';
					list().forEach( function ( f ) {
						preview( f, true );
						chipsBox.appendChild( h( 'span', { class: 'wpec-fonts__chip', style: 'font-family:"' + f + '", system-ui' }, [ f, h( 'button', { type: 'button', 'aria-label': __( 'Remove', 'hi-wpessential' ) + ' ' + f, text: '×', onclick: function () {
							set( list().filter( function ( x ) {
								return x !== f;
							} ).join( '|' ) );
						} } ) ] ) );
					} );
				};
				break;
			}
			default: {
				var text = h( 'input', { type: 'text', class: 'wpec-input', placeholder: __( 'Theme default', 'hi-wpessential' ), 'aria-label': control.params.label, onchange: function ( e ) {
					set( e.target.value );
				} } );
				ui.appendChild( text );
				refresh = function () {
					text.value = get();
				};
			}
		}

		reset.addEventListener( 'click', function () {
			set( '' );
		} );
		var sync = function () {
			refresh();
			var v = get();
			reset.hidden = '' === v;
			root.classList.toggle( 'is-set', '' !== v );
		};
		setting.bind( sync );
		sync();
	}

	api.bind( 'ready', function () {
		var init = function ( control ) {
			if ( 'wpessential' === control.params.type ) {
				control.deferred.embedded.done( function () {
					build( control );
				} );
			}
		};
		api.control.each( init );
		api.control.bind( 'add', init );
	} );
}( window.wp.customize ) );
