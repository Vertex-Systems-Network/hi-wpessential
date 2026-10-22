/**
 * WPEssential controls: Elementor-style form controls without a build step.
 *
 * Each control type is a builder: wpeControls.types[ type ]( ctx ) returns { el, refresh }.
 * ctx = { opt, label, get(), set( value ), device() }. Values are strings; composite controls
 * (dimensions, typography, shadow, gradient, background, responsive) store JSON strings.
 * Composite controls are made of the simple ones, so every control can be reused anywhere:
 * see docs/wiki/Controls.md.
 */
( function () {
	'use strict';

	var __ = window.wp && window.wp.i18n ? window.wp.i18n.__ : function ( s ) {
		return s;
	};
	var cfg = window.wpessentialControls || window.wpessentialCustomizer || {};
	var DEVICES = [ [ 'desktop', __( 'Desktop', 'hi-wpessential' ), '<rect x="2" y="4" width="20" height="13" rx="2"/><path d="M8 21h8M12 17v4"/>' ], [ 'tablet', __( 'Tablet (1023px and below)', 'hi-wpessential' ), '<rect x="5" y="2" width="14" height="20" rx="2"/><path d="M11 18h2"/>' ], [ 'mobile', __( 'Mobile (767px and below)', 'hi-wpessential' ), '<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>' ] ];
	var googleList = null;
	var googleWaiters = [];
	var previewed = {};
	var listeners = [];

	/* ---------------------------------------------------------------- helpers */

	function h( tag, attrs, children ) {
		var el = document.createElement( tag );
		Object.keys( attrs || {} ).forEach( function ( k ) {
			var v = attrs[ k ];
			if ( 'class' === k ) {
				el.className = v;
			} else if ( 'text' === k ) {
				el.textContent = v;
			} else if ( 'html' === k ) {
				el.innerHTML = v;
			} else if ( 0 === k.indexOf( 'on' ) ) {
				el.addEventListener( k.slice( 2 ), v );
			} else if ( false !== v && null !== v && undefined !== v ) {
				el.setAttribute( k, true === v ? '' : v );
			}
		} );
		( children || [] ).forEach( function ( c ) {
			if ( c ) {
				el.appendChild( 'string' === typeof c ? document.createTextNode( c ) : c );
			}
		} );
		return el;
	}

	function svg( body, size ) {
		return '<svg viewBox="0 0 24 24" width="' + ( size || 14 ) + '" height="' + ( size || 14 ) + '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + body + '</svg>';
	}

	function parse( v ) {
		if ( v && 'object' === typeof v ) {
			return v;
		}
		if ( 'string' === typeof v && '{' === v.charAt( 0 ) ) {
			try {
				return JSON.parse( v ) || {};
			} catch ( e ) {
				return {};
			}
		}
		return {};
	}

	function clean( obj ) {
		var out = {};
		Object.keys( obj ).forEach( function ( k ) {
			var v = obj[ k ];
			if ( v && 'object' === typeof v ) {
				v = clean( v );
				if ( Object.keys( v ).length ) {
					out[ k ] = v;
				}
			} else if ( '' !== v && null !== v && undefined !== v && false !== v ) {
				out[ k ] = v;
			}
		} );
		return out;
	}

	function stringify( obj ) {
		var c = clean( obj );
		return Object.keys( c ).length ? JSON.stringify( c ) : '';
	}

	// A child control that reads and writes one key of a JSON value.
	function child( ctx, key, opt, label ) {
		return {
			opt: opt || {},
			label: label,
			device: ctx.device,
			onDevice: ctx.onDevice,
			get: function () {
				var v = parse( ctx.get() )[ key ];
				if ( v && 'object' === typeof v ) {
					return JSON.stringify( v );
				}
				return undefined === v || null === v ? '' : String( v );
			},
			set: function ( v ) {
				var obj = parse( ctx.get() );
				var val = 'string' === typeof v && '{' === v.charAt( 0 ) ? parse( v ) : v;
				obj[ key ] = val;
				ctx.set( stringify( obj ) );
			},
		};
	}

	function field( label, ctl, extra ) {
		return h( 'div', { class: 'wpec-field' }, [ label ? h( 'span', { class: 'wpec-field__label', text: label } ) : null, extra || null, ctl.el ] );
	}

	function build( type, ctx ) {
		var fn = types[ type ] || types.text;
		return fn( ctx );
	}

	/* ---------------------------------------------------------------- fonts */

	function loadGoogle( done ) {
		if ( googleList ) {
			done( googleList );
			return;
		}
		googleWaiters.push( done );
		if ( googleWaiters.length > 1 || ! cfg.googleList ) {
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

	function preview( family, isGoogle ) {
		if ( ! family || previewed[ family ] ) {
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

	function fontLabel( v ) {
		return ! v ? __( 'Theme default', 'hi-wpessential' ) : v.replace( /^google:/, '' );
	}

	function fontStack( v ) {
		if ( ! v ) {
			return 'inherit';
		}
		return 0 === v.indexOf( 'google:' ) ? '"' + v.slice( 7 ) + '", system-ui' : ( ( cfg.themeStacks && cfg.themeStacks[ v ] ) || v );
	}

	function fontPicker( current, onPick, multi ) {
		var CATS = [ [ 'all', __( 'All', 'hi-wpessential' ) ], [ 'theme', __( 'In theme', 'hi-wpessential' ) ], [ 'sans', 'Sans' ], [ 'serif', 'Serif' ], [ 'display', 'Display' ], [ 'hand', __( 'Handwriting', 'hi-wpessential' ) ], [ 'mono', 'Mono' ] ];
		var state = { q: '', cat: 'all', limit: 60 };
		var list = h( 'div', { class: 'wpec-font__list', role: 'listbox', 'aria-label': __( 'Fonts', 'hi-wpessential' ) } );
		var search = h( 'input', { type: 'search', class: 'wpec-font__search', placeholder: __( 'Search 1,700+ fonts…', 'hi-wpessential' ), 'aria-label': __( 'Search fonts', 'hi-wpessential' ) } );
		var chips = h( 'div', { class: 'wpec-font__cats' } );
		CATS.forEach( function ( c ) {
			chips.appendChild( h( 'button', { type: 'button', class: 'wpec-chip' + ( 'all' === c[ 0 ] ? ' is-active' : '' ), text: c[ 1 ], onclick: function ( e ) {
				state.cat = c[ 0 ];
				state.limit = 60;
				chips.querySelectorAll( '.wpec-chip' ).forEach( function ( b ) {
					b.classList.toggle( 'is-active', b === e.currentTarget );
				} );
				draw();
			} } ) );
		} );
		var observer = 'IntersectionObserver' in window ? new window.IntersectionObserver( function ( entries ) {
			entries.forEach( function ( en ) {
				if ( en.isIntersecting ) {
					preview( en.target.getAttribute( 'data-family' ), '1' === en.target.getAttribute( 'data-google' ) );
					observer.unobserve( en.target );
				}
			} );
		}, { root: list } ) : null;
		function row( value, family, meta, isGoogle ) {
			var b = h( 'button', { type: 'button', role: 'option', class: 'wpec-font__item' + ( value === current ? ' is-active' : '' ), 'aria-selected': value === current ? 'true' : 'false', 'data-family': family, 'data-google': isGoogle ? '1' : '0', onclick: function () {
				onPick( value );
			} }, [ h( 'span', { class: 'wpec-font__name', style: 'font-family:' + ( isGoogle ? '"' + family + '", system-ui' : fontStack( value ) ), text: family } ), h( 'span', { class: 'wpec-font__meta', text: meta } ) ] );
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
			if ( ! multi ) {
				list.appendChild( row( '', __( 'Theme default', 'hi-wpessential' ), __( 'From the demo', 'hi-wpessential' ), false ) );
				if ( 'all' === state.cat || 'theme' === state.cat ) {
					( cfg.themeFonts || [] ).filter( function ( f ) {
						return ! q || f.toLowerCase().indexOf( q ) >= 0;
					} ).forEach( function ( f ) {
						list.appendChild( row( f, f, __( 'In theme', 'hi-wpessential' ), false ) );
					} );
				}
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
				if ( ! list.children.length ) {
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
		return h( 'div', { class: 'wpec-font__panel' }, [ search, chips, list ] );
	}

	/* ---------------------------------------------------------------- devices */

	// Device switcher shown next to a responsive control's label (like Elementor).
	function deviceSwitch( ctx ) {
		var box = h( 'span', { class: 'wpec-devices', role: 'group', 'aria-label': __( 'Device', 'hi-wpessential' ) } );
		DEVICES.forEach( function ( d ) {
			box.appendChild( h( 'button', { type: 'button', class: 'wpec-devices__btn', 'data-device': d[ 0 ], title: d[ 1 ], 'aria-label': d[ 1 ], html: svg( d[ 2 ], 13 ), onclick: function () {
				api.setDevice( d[ 0 ] );
			} } ) );
		} );
		var sync = function () {
			box.querySelectorAll( '.wpec-devices__btn' ).forEach( function ( b ) {
				b.classList.toggle( 'is-active', b.getAttribute( 'data-device' ) === ctx.device() );
			} );
		};
		listeners.push( sync );
		sync();
		return box;
	}

	// Wrap any control so it stores one value per device: { desktop, tablet, mobile }.
	function responsive( type, ctx ) {
		var inner = {
			opt: ctx.opt,
			label: ctx.label,
			device: ctx.device,
			get: function () {
				var all = parse( ctx.get() );
				var v = all[ ctx.device() ];
				// A plain old value counts as the desktop value.
				if ( undefined === v && 'desktop' === ctx.device() && '' !== ctx.get() && '{' !== String( ctx.get() ).charAt( 0 ) ) {
					v = ctx.get();
				}
				return v && 'object' === typeof v ? JSON.stringify( v ) : ( undefined === v ? '' : String( v ) );
			},
			set: function ( v ) {
				var all = parse( ctx.get() );
				if ( '' !== ctx.get() && '{' !== String( ctx.get() ).charAt( 0 ) ) {
					all = { desktop: ctx.get() };
				}
				all[ ctx.device() ] = 'string' === typeof v && '{' === v.charAt( 0 ) ? parse( v ) : v;
				ctx.set( stringify( all ) );
			},
		};
		var ctl = build( type, inner );
		listeners.push( ctl.refresh );
		return { el: ctl.el, refresh: ctl.refresh, devices: deviceSwitch( ctx ) };
	}

	/* ---------------------------------------------------------------- types */

	var types = {};

	types.text = function ( ctx ) {
		var input = h( 'input', { type: 'text', class: 'wpec-input', placeholder: ctx.opt.placeholder || __( 'Theme default', 'hi-wpessential' ), 'aria-label': ctx.label, onchange: function ( e ) {
			ctx.set( e.target.value );
		} } );
		return { el: input, refresh: function () {
			input.value = ctx.get();
		} };
	};

	types.color = function ( ctx ) {
		var wrap = h( 'div', { class: 'wpec-color' } );
		var chip = h( 'button', { type: 'button', class: 'wpec-color__chip', 'aria-haspopup': 'true', 'aria-label': ( ctx.label || '' ) + ': ' + __( 'choose colour', 'hi-wpessential' ) } );
		var hex = h( 'input', { type: 'text', class: 'wpec-input wpec-color__hex', placeholder: __( 'Theme default', 'hi-wpessential' ), spellcheck: 'false', 'aria-label': ctx.label, onchange: function ( e ) {
			var v = e.target.value.trim();
			if ( ! v || /^#?[0-9a-f]{3,8}$/i.test( v ) || /^(rgb|hsl)a?\(/i.test( v ) || /^var\(/.test( v ) ) {
				ctx.set( v && /^[0-9a-f]{3,8}$/i.test( v ) ? '#' + v : v );
			}
		} } );
		// Popover like Elementor's: Global colours and a custom picker with opacity.
		var pop = h( 'div', { class: 'wpec-pop', hidden: true } );
		var tabs = h( 'div', { class: 'wpec-tabs' } );
		var globals = h( 'div', { class: 'wpec-color__globals' } );
		var picker = h( 'input', { type: 'color', class: 'wpec-color__native', 'aria-label': __( 'Custom colour', 'hi-wpessential' ) } );
		var alpha = h( 'input', { type: 'range', min: 0, max: 100, step: 1, class: 'wpec-color__alpha', 'aria-label': __( 'Opacity', 'hi-wpessential' ) } );
		var custom = h( 'div', { class: 'wpec-color__custom', hidden: true }, [ picker, h( 'label', { class: 'wpec-color__alpha-row' }, [ h( 'span', { text: __( 'Opacity', 'hi-wpessential' ) } ), alpha ] ) ] );
		var setCustom = function () {
			var a = Math.round( Number( alpha.value ) * 2.55 );
			ctx.set( 255 === a ? picker.value : picker.value + ( a < 16 ? '0' : '' ) + a.toString( 16 ) );
		};
		picker.addEventListener( 'input', setCustom );
		alpha.addEventListener( 'input', setCustom );
		[ [ 'globals', __( 'Global', 'hi-wpessential' ) ], [ 'custom', __( 'Custom', 'hi-wpessential' ) ] ].forEach( function ( t, i ) {
			tabs.appendChild( h( 'button', { type: 'button', class: 'wpec-tabs__btn' + ( 0 === i ? ' is-active' : '' ), text: t[ 1 ], onclick: function ( e ) {
				tabs.querySelectorAll( '.wpec-tabs__btn' ).forEach( function ( b ) {
					b.classList.toggle( 'is-active', b === e.currentTarget );
				} );
				globals.hidden = 'globals' !== t[ 0 ];
				custom.hidden = 'custom' !== t[ 0 ];
			} } ) );
		} );
		Object.keys( cfg.palette || {} ).forEach( function ( slug ) {
			var c = cfg.palette[ slug ];
			globals.appendChild( h( 'button', { type: 'button', class: 'wpec-color__global', 'data-color': c[ 1 ].toLowerCase(), onclick: function () {
				ctx.set( c[ 1 ] );
				pop.hidden = true;
			} }, [ h( 'span', { class: 'wpec-color__dot', style: 'background:' + c[ 1 ] } ), h( 'span', { text: c[ 0 ] } ), h( 'code', { text: c[ 1 ] } ) ] ) );
		} );
		pop.appendChild( tabs );
		pop.appendChild( globals );
		pop.appendChild( custom );
		chip.addEventListener( 'click', function () {
			pop.hidden = ! pop.hidden;
		} );
		document.addEventListener( 'click', function ( e ) {
			if ( ! wrap.contains( e.target ) ) {
				pop.hidden = true;
			}
		} );
		wrap.appendChild( h( 'div', { class: 'wpec-color__row' }, [ chip, hex ] ) );
		wrap.appendChild( pop );
		return { el: wrap, refresh: function () {
			var v = ctx.get();
			chip.style.background = v ? v + ' padding-box' : '';
			chip.classList.toggle( 'is-empty', ! v );
			hex.value = v;
			var m = /^#([0-9a-f]{6})([0-9a-f]{2})?$/i.exec( v );
			if ( m ) {
				picker.value = '#' + m[ 1 ];
				alpha.value = m[ 2 ] ? Math.round( parseInt( m[ 2 ], 16 ) / 2.55 ) : 100;
			}
			globals.querySelectorAll( '.wpec-color__global' ).forEach( function ( g ) {
				g.classList.toggle( 'is-active', g.getAttribute( 'data-color' ) === v.toLowerCase() );
			} );
		} };
	};

	types.range = function ( ctx ) {
		var o = ctx.opt;
		var units = o.units || ( o.unit ? [ o.unit ] : [] );
		var withUnit = units.length > 1;
		var slider = h( 'input', { type: 'range', class: 'wpec-range__slider', min: o.min, max: o.max, step: o.step || 1, 'aria-label': ctx.label } );
		var num = h( 'input', { type: 'number', class: 'wpec-input wpec-range__num', min: o.min, max: o.max, step: o.step || 1, placeholder: '–', 'aria-label': ctx.label } );
		var unitSel = withUnit ? h( 'select', { class: 'wpec-range__units', 'aria-label': __( 'Unit', 'hi-wpessential' ) }, units.map( function ( u ) {
			return h( 'option', { value: u, text: u } );
		} ) ) : null;
		var read = function () {
			var v = ctx.get();
			var p = parse( v );
			return '{' === String( v ).charAt( 0 ) ? { size: p.size, unit: p.unit } : { size: v, unit: units[ 0 ] };
		};
		var write = function ( size ) {
			if ( '' === size ) {
				ctx.set( '' );
				return;
			}
			ctx.set( withUnit && unitSel.value !== units[ 0 ] ? JSON.stringify( { size: Number( size ), unit: unitSel.value } ) : String( size ) );
		};
		slider.addEventListener( 'input', function () {
			write( slider.value );
		} );
		num.addEventListener( 'input', function () {
			write( num.value );
		} );
		if ( unitSel ) {
			unitSel.addEventListener( 'change', function () {
				write( num.value );
			} );
		}
		var box = h( 'span', { class: 'wpec-range__box' + ( withUnit ? ' has-units' : '' ) }, [ num, withUnit ? unitSel : ( units[ 0 ] ? h( 'span', { class: 'wpec-range__unit', text: units[ 0 ] } ) : null ) ] );
		return { el: h( 'div', { class: 'wpec-range__row' }, [ slider, box ] ), refresh: function () {
			var r = read();
			var v = undefined === r.size || null === r.size ? '' : String( r.size );
			num.value = v;
			if ( unitSel ) {
				unitSel.value = r.unit || units[ 0 ];
			}
			slider.value = '' === v ? ( Number( o.min ) + Number( o.max ) ) / 2 : v;
			slider.classList.toggle( 'is-empty', '' === v );
			var pct = '' === v ? 0 : ( ( Number( v ) - Number( o.min ) ) / ( Number( o.max ) - Number( o.min ) ) ) * 100;
			slider.style.setProperty( '--wpec-fill', Math.max( 0, Math.min( 100, pct ) ) + '%' );
		} };
	};

	types.toggle = function ( ctx ) {
		var input = h( 'input', { type: 'checkbox', role: 'switch', class: 'wpec-switch__input', 'aria-label': ctx.label, onchange: function ( e ) {
			ctx.set( e.target.checked ? '1' : '' );
		} } );
		return { el: h( 'label', { class: 'wpec-switch' }, [ input, h( 'span', { class: 'wpec-switch__track', 'aria-hidden': 'true' } ) ] ), refresh: function () {
			var v = ctx.get();
			input.checked = !! v && '0' !== v && 'false' !== v;
		} };
	};

	types.buttons = function ( ctx ) {
		var group = h( 'div', { class: 'wpec-seg', role: 'radiogroup', 'aria-label': ctx.label } );
		Object.keys( ctx.opt.choices || {} ).forEach( function ( k ) {
			var c = ctx.opt.choices[ k ];
			var label = Array.isArray( c ) ? c[ 0 ] : c;
			var icon = Array.isArray( c ) ? c[ 1 ] : null;
			group.appendChild( h( 'button', { type: 'button', role: 'radio', class: 'wpec-seg__btn' + ( icon ? ' is-icon' : '' ), 'data-v': k, title: label, 'aria-label': label, text: icon || label, onclick: function () {
				ctx.set( k );
			} } ) );
		} );
		return { el: group, refresh: function () {
			var v = ctx.get();
			group.querySelectorAll( '.wpec-seg__btn' ).forEach( function ( b ) {
				var on = b.getAttribute( 'data-v' ) === v;
				b.classList.toggle( 'is-active', on );
				b.setAttribute( 'aria-checked', on ? 'true' : 'false' );
			} );
		} };
	};

	// Choose: icon buttons (Elementor's CHOOSE control). choices: { value: [ label, icon text or svg body ] }.
	types.choose = types.buttons;

	types.select = function ( ctx ) {
		var sel = h( 'select', { class: 'wpec-input wpec-select', 'aria-label': ctx.label, onchange: function ( e ) {
			ctx.set( e.target.value );
		} }, Object.keys( ctx.opt.choices || {} ).map( function ( k ) {
			var c = ctx.opt.choices[ k ];
			return h( 'option', { value: k, text: Array.isArray( c ) ? c[ 0 ] : c } );
		} ) );
		return { el: sel, refresh: function () {
			sel.value = ctx.get();
		} };
	};

	types.font = function ( ctx ) {
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
			holder.appendChild( fontPicker( ctx.get(), function ( v ) {
				ctx.set( v );
				close();
			} ) );
		} );
		return { el: h( 'div', null, [ btn, holder ] ), refresh: function () {
			var v = ctx.get();
			if ( v ) {
				preview( fontLabel( v ), 0 === v.indexOf( 'google:' ) );
			}
			btn.innerHTML = '';
			btn.appendChild( h( 'span', { class: 'wpec-font__sample', style: 'font-family:' + fontStack( v ), text: 'Aa' } ) );
			btn.appendChild( h( 'span', { class: 'wpec-font__label' }, [ h( 'strong', { text: fontLabel( v ) } ), h( 'small', { text: ! v ? __( 'From the demo', 'hi-wpessential' ) : ( 0 === v.indexOf( 'google:' ) ? 'Google Fonts' : __( 'In theme', 'hi-wpessential' ) ) } ) ] ) );
			btn.appendChild( h( 'span', { class: 'wpec-font__caret', 'aria-hidden': 'true', text: '▾' } ) );
		} };
	};

	types.fonts = function ( ctx ) {
		var chipsBox = h( 'div', { class: 'wpec-fonts__chips' } );
		var hold = h( 'div', { class: 'wpec-font__holder' } );
		var list = function () {
			return ctx.get().split( '|' ).filter( Boolean );
		};
		var add = h( 'button', { type: 'button', class: 'wpec-btn', text: '+ ' + __( 'Add a Google font', 'hi-wpessential' ), onclick: function () {
			if ( hold.firstChild ) {
				hold.innerHTML = '';
				return;
			}
			hold.appendChild( fontPicker( '', function ( v ) {
				var f = v.replace( /^google:/, '' );
				if ( f && list().indexOf( f ) < 0 ) {
					ctx.set( list().concat( [ f ] ).join( '|' ) );
				}
				hold.innerHTML = '';
			}, true ) );
		} } );
		return { el: h( 'div', null, [ chipsBox, add, hold ] ), refresh: function () {
			chipsBox.innerHTML = '';
			list().forEach( function ( f ) {
				preview( f, true );
				chipsBox.appendChild( h( 'span', { class: 'wpec-fonts__chip', style: 'font-family:"' + f + '", system-ui' }, [ f, h( 'button', { type: 'button', 'aria-label': __( 'Remove', 'hi-wpessential' ) + ' ' + f, text: '×', onclick: function () {
					ctx.set( list().filter( function ( x ) {
						return x !== f;
					} ).join( '|' ) );
				} } ) ] ) );
			} );
		} };
	};

	// Dimensions: top / right / bottom / left with a link toggle and a unit (Elementor's DIMENSIONS).
	types.dimensions = function ( ctx ) {
		var units = ctx.opt.units || [ 'px' ];
		var linked = true;
		var inputs = {};
		var row = h( 'div', { class: 'wpec-dims' } );
		var unitSel = h( 'select', { class: 'wpec-dims__unit', 'aria-label': __( 'Unit', 'hi-wpessential' ) }, units.map( function ( u ) {
			return h( 'option', { value: u, text: u } );
		} ) );
		var write = function () {
			var o = { unit: unitSel.value };
			var any = false;
			[ 'top', 'right', 'bottom', 'left' ].forEach( function ( s ) {
				if ( '' !== inputs[ s ].value ) {
					o[ s ] = Number( inputs[ s ].value );
					any = true;
				}
			} );
			ctx.set( any ? JSON.stringify( o ) : '' );
		};
		[ [ 'top', __( 'Top', 'hi-wpessential' ) ], [ 'right', __( 'Right', 'hi-wpessential' ) ], [ 'bottom', __( 'Bottom', 'hi-wpessential' ) ], [ 'left', __( 'Left', 'hi-wpessential' ) ] ].forEach( function ( s ) {
			inputs[ s[ 0 ] ] = h( 'input', { type: 'number', class: 'wpec-dims__input', 'aria-label': s[ 1 ], oninput: function ( e ) {
				if ( linked ) {
					Object.keys( inputs ).forEach( function ( k ) {
						inputs[ k ].value = e.target.value;
					} );
				}
				write();
			} } );
			row.appendChild( h( 'label', { class: 'wpec-dims__side' }, [ inputs[ s[ 0 ] ], h( 'span', { text: s[ 1 ] } ) ] ) );
		} );
		var link = h( 'button', { type: 'button', class: 'wpec-dims__link is-linked', 'aria-pressed': 'true', title: __( 'Link values together', 'hi-wpessential' ), 'aria-label': __( 'Link values together', 'hi-wpessential' ), html: svg( '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>', 14 ), onclick: function () {
			linked = ! linked;
			link.classList.toggle( 'is-linked', linked );
			link.setAttribute( 'aria-pressed', linked ? 'true' : 'false' );
		} } );
		unitSel.addEventListener( 'change', write );
		row.appendChild( link );
		return { el: h( 'div', null, [ h( 'div', { class: 'wpec-dims__head' }, [ unitSel ] ), row ] ), refresh: function () {
			var v = parse( ctx.get() );
			[ 'top', 'right', 'bottom', 'left' ].forEach( function ( s ) {
				inputs[ s ].value = undefined === v[ s ] ? '' : v[ s ];
			} );
			unitSel.value = v.unit || units[ 0 ];
			linked = v.top === v.right && v.right === v.bottom && v.bottom === v.left;
			link.classList.toggle( 'is-linked', linked );
		} };
	};

	// Popover helper: a pencil button that opens a group of controls (Elementor's POPOVER_TOGGLE).
	function popover( ctx, summary, makeBody ) {
		var body = null;
		var panel = h( 'div', { class: 'wpec-popover', hidden: true } );
		var label = h( 'span', { class: 'wpec-popover__summary' } );
		var btn = h( 'button', { type: 'button', class: 'wpec-popover__toggle', 'aria-expanded': 'false', 'aria-label': __( 'Edit', 'hi-wpessential' ) + ' ' + ( ctx.label || '' ), html: svg( '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4Z"/>', 14 ), onclick: function () {
			if ( ! body ) {
				body = makeBody();
				panel.appendChild( body.el );
			}
			panel.hidden = ! panel.hidden;
			btn.setAttribute( 'aria-expanded', panel.hidden ? 'false' : 'true' );
			if ( ! panel.hidden ) {
				body.refresh();
			}
		} } );
		return { el: h( 'div', { class: 'wpec-popover-wrap' }, [ h( 'div', { class: 'wpec-popover__bar' }, [ label, btn ] ), panel ] ), refresh: function () {
			label.textContent = summary();
			if ( body && ! panel.hidden ) {
				body.refresh();
			}
		} };
	}

	function group( parts ) {
		var el = h( 'div', { class: 'wpec-group' } );
		parts.forEach( function ( p ) {
			el.appendChild( p.node );
		} );
		return { el: el, refresh: function () {
			parts.forEach( function ( p ) {
				p.ctl.refresh();
			} );
		} };
	}

	function part( ctx, key, type, opt, label ) {
		var c = child( ctx, key, opt, label );
		var ctl;
		var devices = null;
		if ( opt && opt.responsive ) {
			ctl = responsive( type, c );
			devices = ctl.devices;
		} else {
			ctl = build( type, c );
		}
		var head = h( 'div', { class: 'wpec-field__head' }, [ h( 'span', { class: 'wpec-field__label', text: label } ), devices ] );
		return { node: h( 'div', { class: 'wpec-field' }, [ head, ctl.el ] ), ctl: ctl };
	}

	var WEIGHTS = { '': __( 'Default', 'hi-wpessential' ), 100: '100', 200: '200', 300: '300 · Light', 400: '400 · Regular', 500: '500 · Medium', 600: '600 · Semibold', 700: '700 · Bold', 800: '800', 900: '900 · Black' };

	// Typography: Elementor's typography popover, with per-device size, line height and spacing.
	types.typography = function ( ctx ) {
		return popover( ctx, function () {
			var t = parse( ctx.get() );
			var size = parse( t.size );
			var bits = [];
			if ( t.family ) {
				bits.push( fontLabel( t.family ) );
			}
			if ( size.desktop ) {
				bits.push( ( size.desktop.size || size.desktop ) + ( size.desktop.unit || 'px' ) );
			}
			if ( t.weight ) {
				bits.push( t.weight );
			}
			return bits.length ? bits.join( ' · ' ) : __( 'Theme default', 'hi-wpessential' );
		}, function () {
			return group( [
				part( ctx, 'family', 'font', {}, __( 'Family', 'hi-wpessential' ) ),
				part( ctx, 'size', 'range', { responsive: true, min: 8, max: 120, units: [ 'px', 'em', 'rem', 'vw' ] }, __( 'Size', 'hi-wpessential' ) ),
				part( ctx, 'weight', 'select', { choices: WEIGHTS }, __( 'Weight', 'hi-wpessential' ) ),
				part( ctx, 'transform', 'choose', { choices: { '': [ __( 'Default', 'hi-wpessential' ), '–' ], uppercase: [ 'UPPERCASE', 'AA' ], lowercase: [ 'lowercase', 'aa' ], capitalize: [ 'Capitalize', 'Aa' ], none: [ __( 'As typed', 'hi-wpessential' ), '✕' ] } }, __( 'Transform', 'hi-wpessential' ) ),
				part( ctx, 'style', 'choose', { choices: { '': [ __( 'Default', 'hi-wpessential' ), '–' ], normal: [ __( 'Normal', 'hi-wpessential' ), 'I' ], italic: [ __( 'Italic', 'hi-wpessential' ), '𝐼' ] } }, __( 'Style', 'hi-wpessential' ) ),
				part( ctx, 'decoration', 'choose', { choices: { '': [ __( 'Default', 'hi-wpessential' ), '–' ], none: [ __( 'None', 'hi-wpessential' ), '✕' ], underline: [ __( 'Underline', 'hi-wpessential' ), 'U̲' ], 'line-through': [ __( 'Line through', 'hi-wpessential' ), 'S̶' ] } }, __( 'Decoration', 'hi-wpessential' ) ),
				part( ctx, 'lineHeight', 'range', { responsive: true, min: 0.8, max: 3, step: 0.05, units: [ '', 'px', 'em' ] }, __( 'Line height', 'hi-wpessential' ) ),
				part( ctx, 'letterSpacing', 'range', { responsive: true, min: -5, max: 10, step: 0.1, units: [ 'px', 'em' ] }, __( 'Letter spacing', 'hi-wpessential' ) ),
			] );
		} );
	};

	// Box shadow: Elementor's box-shadow popover.
	types.shadow = function ( ctx ) {
		return popover( ctx, function () {
			var s = parse( ctx.get() );
			return undefined === s.y ? ( ctx.get() && '{' !== ctx.get().charAt( 0 ) ? ctx.get() : __( 'Theme default', 'hi-wpessential' ) ) : ( s.inset ? 'inset ' : '' ) + ( s.x || 0 ) + ' ' + ( s.y || 0 ) + ' ' + ( s.blur || 0 ) + ' ' + ( s.spread || 0 );
		}, function () {
			return group( [
				part( ctx, 'color', 'color', {}, __( 'Colour', 'hi-wpessential' ) ),
				part( ctx, 'x', 'range', { min: -100, max: 100, unit: 'px' }, __( 'Horizontal', 'hi-wpessential' ) ),
				part( ctx, 'y', 'range', { min: -100, max: 100, unit: 'px' }, __( 'Vertical', 'hi-wpessential' ) ),
				part( ctx, 'blur', 'range', { min: 0, max: 200, unit: 'px' }, __( 'Blur', 'hi-wpessential' ) ),
				part( ctx, 'spread', 'range', { min: -100, max: 100, unit: 'px' }, __( 'Spread', 'hi-wpessential' ) ),
				part( ctx, 'inset', 'choose', { choices: { '': [ __( 'Outline', 'hi-wpessential' ), __( 'Outline', 'hi-wpessential' ) ], 1: [ __( 'Inset', 'hi-wpessential' ), __( 'Inset', 'hi-wpessential' ) ] } }, __( 'Position', 'hi-wpessential' ) ),
			] );
		} );
	};

	function gradientCss( g ) {
		if ( ! g.from && ! g.to ) {
			return '';
		}
		var a = undefined === g.fromStop ? 0 : g.fromStop;
		var b = undefined === g.toStop ? 100 : g.toStop;
		var stops = ( g.from || g.to ) + ' ' + a + '%, ' + ( g.to || g.from ) + ' ' + b + '%';
		return 'radial' === g.type ? 'radial-gradient(circle at ' + ( g.position || 'center center' ) + ', ' + stops + ')' : 'linear-gradient(' + ( undefined === g.angle ? 180 : g.angle ) + 'deg, ' + stops + ')';
	}

	// Gradient: Elementor's background gradient, with a live preview bar.
	types.gradient = function ( ctx ) {
		var bar = h( 'div', { class: 'wpec-gradient__bar' } );
		var g = group( [
			part( ctx, 'type', 'choose', { choices: { '': [ __( 'Linear', 'hi-wpessential' ), __( 'Linear', 'hi-wpessential' ) ], radial: [ __( 'Radial', 'hi-wpessential' ), __( 'Radial', 'hi-wpessential' ) ] } }, __( 'Type', 'hi-wpessential' ) ),
			part( ctx, 'from', 'color', {}, __( 'First colour', 'hi-wpessential' ) ),
			part( ctx, 'fromStop', 'range', { min: 0, max: 100, unit: '%' }, __( 'Location', 'hi-wpessential' ) ),
			part( ctx, 'to', 'color', {}, __( 'Second colour', 'hi-wpessential' ) ),
			part( ctx, 'toStop', 'range', { min: 0, max: 100, unit: '%' }, __( 'Location', 'hi-wpessential' ) ),
			part( ctx, 'angle', 'range', { min: 0, max: 360, step: 5, unit: 'deg' }, __( 'Angle (linear)', 'hi-wpessential' ) ),
			part( ctx, 'position', 'select', { choices: { '': __( 'Center', 'hi-wpessential' ), 'top left': 'Top left', top: 'Top', 'top right': 'Top right', left: 'Left', right: 'Right', 'bottom left': 'Bottom left', bottom: 'Bottom', 'bottom right': 'Bottom right' } }, __( 'Position (radial)', 'hi-wpessential' ) ),
		] );
		return { el: h( 'div', { class: 'wpec-gradient' }, [ bar, g.el ] ), refresh: function () {
			var css = gradientCss( parse( ctx.get() ) );
			bar.style.background = css || 'repeating-conic-gradient(#ddd 0 25%, #fff 0 50%) 0 0 / 12px 12px';
			bar.textContent = css ? '' : __( 'Theme default', 'hi-wpessential' );
			g.refresh();
		} };
	};

	// Media: pick an image from the Media Library (Elementor's MEDIA).
	types.media = function ( ctx ) {
		var box = h( 'div', { class: 'wpec-media' } );
		var frame = null;
		var open = function () {
			if ( ! window.wp || ! window.wp.media ) {
				return;
			}
			if ( ! frame ) {
				frame = window.wp.media( { title: ctx.label || __( 'Choose image', 'hi-wpessential' ), library: { type: 'image' }, multiple: false } );
				frame.on( 'select', function () {
					var a = frame.state().get( 'selection' ).first().toJSON();
					ctx.set( JSON.stringify( { id: a.id, url: a.url } ) );
				} );
			}
			frame.open();
		};
		return { el: box, refresh: function () {
			var v = parse( ctx.get() );
			box.innerHTML = '';
			if ( v.url ) {
				box.appendChild( h( 'button', { type: 'button', class: 'wpec-media__thumb', onclick: open, 'aria-label': __( 'Replace image', 'hi-wpessential' ) }, [ h( 'img', { src: v.url, alt: '' } ) ] ) );
				box.appendChild( h( 'div', { class: 'wpec-media__actions' }, [ h( 'button', { type: 'button', class: 'wpec-link', text: __( 'Replace', 'hi-wpessential' ), onclick: open } ), h( 'button', { type: 'button', class: 'wpec-link is-danger', text: __( 'Remove', 'hi-wpessential' ), onclick: function () {
					ctx.set( '' );
				} } ) ] ) );
			} else {
				box.appendChild( h( 'button', { type: 'button', class: 'wpec-media__drop', onclick: open, html: svg( '<path d="M12 16V4m0 0-5 5m5-5 5 5M4 20h16"/>', 20 ) + '<span>' + __( 'Upload or choose an image', 'hi-wpessential' ) + '</span>' } ) );
			}
		} };
	};

	// Background: Classic (colour + image with position, size, repeat, attachment) or Gradient.
	types.background = function ( ctx ) {
		var typeCtl = part( ctx, 'type', 'choose', { choices: { '': [ __( 'Classic', 'hi-wpessential' ), __( 'Classic', 'hi-wpessential' ) ], gradient: [ __( 'Gradient', 'hi-wpessential' ), __( 'Gradient', 'hi-wpessential' ) ] } }, __( 'Type', 'hi-wpessential' ) );
		var classic = group( [
			part( ctx, 'color', 'color', {}, __( 'Colour', 'hi-wpessential' ) ),
			part( ctx, 'image', 'media', {}, __( 'Image', 'hi-wpessential' ) ),
			part( ctx, 'position', 'select', { choices: { '': __( 'Default', 'hi-wpessential' ), 'center center': 'Center', 'top center': 'Top', 'bottom center': 'Bottom', 'center left': 'Left', 'center right': 'Right' } }, __( 'Position', 'hi-wpessential' ) ),
			part( ctx, 'size', 'buttons', { choices: { '': __( 'Auto', 'hi-wpessential' ), cover: __( 'Cover', 'hi-wpessential' ), contain: __( 'Contain', 'hi-wpessential' ) } }, __( 'Size', 'hi-wpessential' ) ),
			part( ctx, 'repeat', 'select', { choices: { '': __( 'Default', 'hi-wpessential' ), 'no-repeat': 'No repeat', repeat: 'Repeat', 'repeat-x': 'Repeat X', 'repeat-y': 'Repeat Y' } }, __( 'Repeat', 'hi-wpessential' ) ),
			part( ctx, 'attachment', 'buttons', { choices: { '': __( 'Scroll', 'hi-wpessential' ), fixed: __( 'Fixed', 'hi-wpessential' ) } }, __( 'Attachment', 'hi-wpessential' ) ),
		] );
		var grad = types.gradient( child( ctx, 'gradient', {}, __( 'Gradient', 'hi-wpessential' ) ) );
		var gradBox = h( 'div', null, [ grad.el ] );
		return { el: h( 'div', { class: 'wpec-background' }, [ typeCtl.node, classic.el, gradBox ] ), refresh: function () {
			var isGrad = 'gradient' === parse( ctx.get() ).type;
			typeCtl.ctl.refresh();
			classic.el.hidden = isGrad;
			gradBox.hidden = ! isGrad;
			( isGrad ? grad : classic ).refresh();
		} };
	};

	/* ---------------------------------------------------------------- api */

	var api = {
		h: h,
		types: types,
		parse: parse,
		stringify: stringify,
		currentDevice: 'desktop',
		setDevice: function ( d ) {
			api.currentDevice = d;
			if ( api.onDevice ) {
				api.onDevice( d );
			}
			listeners.forEach( function ( fn ) {
				fn();
			} );
		},
		/**
		 * Build a control.
		 *
		 * @param {string} type One of wpeControls.types.
		 * @param {Object} ctx  { opt, label, get(), set( v ) }.
		 * @return {Object} { el, refresh, devices }
		 */
		create: function ( type, ctx ) {
			ctx.device = ctx.device || function () {
				return api.currentDevice;
			};
			return ctx.opt && ctx.opt.responsive ? responsive( type, ctx ) : build( type, ctx );
		},
	};
	window.wpeControls = api;
}() );
