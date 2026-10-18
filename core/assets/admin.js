/* global wpessentialCore */
( function () {
	'use strict';

	const cfg = window.wpessentialCore;
	const api = window.wp.apiFetch;
	const { __, sprintf } = window.wp.i18n;
	const root = document.getElementById( 'wpe-app' );
	if ( ! root || ! cfg ) {
		return;
	}

	/* ------------------------------------------------------------------ helpers */

	const esc = ( s ) => String( s ?? '' ).replace( /[&<>"']/g, ( c ) => ( { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ] ) );
	const $ = ( sel, el = root ) => el.querySelector( sel );
	const $$ = ( sel, el = root ) => Array.from( el.querySelectorAll( sel ) );
	const sleep = ( ms ) => new Promise( ( r ) => setTimeout( r, ms ) );
	const store = {
		get( k, d ) {
			try {
				return window.localStorage.getItem( 'wpe-core-' + k ) ?? d;
			} catch ( e ) {
				return d;
			}
		},
		set( k, v ) {
			try {
				window.localStorage.setItem( 'wpe-core-' + k, v );
			} catch ( e ) {}
		},
	};

	const P = {
		logo: '<defs><linearGradient id="wpe-w" x1="2" y1="12" x2="21" y2="12" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#5ac8d6"/><stop offset=".5" stop-color="#6188c6"/><stop offset="1" stop-color="#625ba8"/></linearGradient></defs><path d="M3 4.5 7 19l4-10.5L15 19l4-14.5" fill="none" stroke="url(#wpe-w)" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"/><circle cx="21.6" cy="19.2" r="2" fill="#5ac8d6" stroke="none"/>',
		home: '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z"/>',
		grid: '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
		plug: '<path d="M9 2v6M15 2v6M6 8h12v4a6 6 0 0 1-12 0V8ZM12 18v4"/>',
		key: '<circle cx="7.5" cy="15.5" r="4.5"/><path d="m10.7 12.3 9.3-9.3M17 6l3 3M14 9l2 2"/>',
		pulse: '<path d="M3 12h4l3-8 4 16 3-8h4"/>',
		search: '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
		eye: '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
		download: '<path d="M12 3v12m0 0-5-5m5 5 5-5M4 21h16"/>',
		lock: '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
		check: '<path d="m5 12 5 5L20 7"/>',
		x: '<path d="M6 6l12 12M18 6 6 18"/>',
		info: '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
		warn: '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01"/>',
		error: '<circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/>',
		ok: '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
		arrow: '<path d="M5 12h14m-6-6 6 6-6 6"/>',
		back: '<path d="M19 12H5m6 6-6-6 6-6"/>',
		monitor: '<rect x="2" y="4" width="20" height="13" rx="2"/><path d="M8 21h8M12 17v4"/>',
		tablet: '<rect x="5" y="2" width="14" height="20" rx="2"/><path d="M11 18h2"/>',
		phone: '<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>',
		sun: '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
		moon: '<path d="M21 13A9 9 0 1 1 11 3a7 7 0 0 0 10 10Z"/>',
		book: '<path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v17H6.5A2.5 2.5 0 0 0 4 21.5v-17Z"/><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>',
		help: '<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.6v.6M12 17h.01"/>',
		brush: '<path d="M9.1 13.4 18 4.5a2.1 2.1 0 0 1 3 3l-8.9 8.9M9 14c-2 0-4 1.5-4 4 0 1-1 2-2 2 1.5 1.5 4 2 6 1 2-1 3-3 3-4.5"/>',
		rocket: '<path d="M4.5 16.5c-1.5 1.3-2 5-2 5s3.7-.5 5-2c.7-.8.7-2.1-.1-2.9a2.2 2.2 0 0 0-2.9-.1ZM12 15l-3-3a22 22 0 0 1 2-3.9A12.9 12.9 0 0 1 22 2c0 2.7-.8 7.5-6 11a22.4 22.4 0 0 1-4 2Z"/><path d="M9 12H4s.6-3 2-4c1.6-1.1 5 0 5 0M12 15v5s3-.6 4-2c1.1-1.6 0-5 0-5"/>',
		trash: '<path d="M3 6h18M8 6V4h8v2M6 6l1 15h10l1-15"/>',
		refresh: '<path d="M21 12a9 9 0 1 1-2.6-6.4L21 8M21 3v5h-5"/>',
		copy: '<rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"/>',
		shield: '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>',
		layers: '<path d="m12 2 10 5-10 5L2 7l10-5Z"/><path d="m2 17 10 5 10-5M2 12l10 5 10-5"/>',
		image: '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-5-5L5 21"/>',
		bag: '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4H6ZM3 6h18M16 10a4 4 0 0 1-8 0"/>',
		cog: '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z"/>',
		file: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Z"/><path d="M14 2v6h6M8 13h8M8 17h5"/>',
		gutenberg: '<path d="M4 4h16v16H4z" fill="none"/><path d="M8 7h5a4 4 0 0 1 0 8H8V7Zm0 4h7" />',
		elementor: '<rect x="3" y="3" width="18" height="18" rx="9"/><path d="M9 8v8M13 8h3M13 12h3M13 16h3"/>',
		spark: '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M5.6 5.6l2.8 2.8M15.6 15.6l2.8 2.8M5.6 18.4l2.8-2.8M15.6 8.4l2.8-2.8"/>',
		clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		ext: '<path d="M14 3h7v7M21 3l-9 9M19 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h5"/>',
	};
	const ico = ( name, size = 18, sw = 2 ) => `<svg width="${ size }" height="${ size }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="${ sw }" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${ P[ name ] || '' }</svg>`;
	const spin = '<span class="wpe-spin" aria-hidden="true"></span>';
	const levelIco = { info: 'info', success: 'ok', warning: 'warn', error: 'error' };

	const errMsg = ( e ) => {
		if ( ! e ) {
			return __( 'Something went wrong.', 'hi-wpessential' );
		}
		if ( e.code === 'fetch_error' || e.name === 'TypeError' ) {
			return __( 'The connection to your site dropped. Check your internet and try again.', 'hi-wpessential' );
		}
		if ( e.code === 'invalid_json' ) {
			return __( 'The server sent an invalid response — usually a PHP error or a timeout. Your progress is saved.', 'hi-wpessential' );
		}
		if ( e.code === 'rest_cookie_invalid_nonce' ) {
			return __( 'Your login session expired. Reload the page and sign in again.', 'hi-wpessential' );
		}
		return e.message || __( 'Something went wrong.', 'hi-wpessential' );
	};

	async function busy( btn, fn ) {
		const html = btn.innerHTML;
		btn.classList.add( 'is-busy' );
		btn.disabled = true;
		btn.innerHTML = spin + '<span>' + esc( btn.dataset.busy || btn.textContent.trim() ) + '</span>';
		try {
			return await fn();
		} finally {
			btn.classList.remove( 'is-busy' );
			btn.disabled = false;
			btn.innerHTML = html;
		}
	}

	/* ------------------------------------------------------------------ toasts */

	function toast( level, title, text = '' ) {
		let box = $( '.wpe-toasts' );
		if ( ! box ) {
			box = document.createElement( 'div' );
			box.className = 'wpe-toasts';
			box.setAttribute( 'role', 'status' );
			root.appendChild( box );
		}
		const el = document.createElement( 'div' );
		el.className = 'wpe-toast wpe-toast--' + level;
		el.innerHTML = ico( levelIco[ level ] || 'info', 20 ) + `<div><b>${ esc( title ) }</b>${ text ? esc( text ) : '' }</div>`;
		box.appendChild( el );
		setTimeout( () => {
			el.classList.add( 'is-leaving' );
			setTimeout( () => el.remove(), 300 );
		}, level === 'error' ? 8000 : 4500 );
	}

	/* ------------------------------------------------------------------ state */

	const state = {
		tab: 'welcome',
		demos: null,
		notices: [],
		imported: null,
		job: null,
		license: null,
		status: null,
		plugins: null,
		filter: { q: '', cat: 'all', type: 'all', builder: 'all' },
		mode: store.get( 'mode', window.matchMedia( '(prefers-color-scheme: dark)' ).matches ? 'dark' : 'light' ),
	};

	const TABS = [
		[ 'welcome', __( 'Dashboard', 'hi-wpessential' ), 'home' ],
		[ 'demos', __( 'Demo Library', 'hi-wpessential' ), 'grid' ],
		[ 'plugins', __( 'Plugins', 'hi-wpessential' ), 'plug' ],
		[ 'license', __( 'License', 'hi-wpessential' ), 'key' ],
		[ 'status', __( 'System Status', 'hi-wpessential' ), 'pulse' ],
	];

	function shell() {
		root.dataset.mode = state.mode;
		root.innerHTML = `
			<header class="wpe-top">
				<div class="wpe-brand"><span class="wpe-brand__mark">${ ico( 'logo', 20 ) }</span><span class="wpe-brand__name">${ esc( cfg.brand ) }</span><span class="wpe-brand__ver">v${ esc( cfg.version ) }</span></div>
				<nav class="wpe-tabs" aria-label="${ esc( __( 'Dashboard sections', 'hi-wpessential' ) ) }">
					${ TABS.map( ( [ id, label, icon ] ) => `<button class="wpe-tab" data-tab="${ id }">${ ico( icon, 17 ) }${ esc( label ) }</button>` ).join( '' ) }
				</nav>
				<div class="wpe-top__end">
					<a class="wpe-btn wpe-btn--ghost wpe-btn--sm" href="${ esc( cfg.docs ) }" target="_blank" rel="noopener">${ ico( 'book', 16 ) }<span>${ esc( __( 'Docs', 'hi-wpessential' ) ) }</span></a>
					<button class="wpe-btn wpe-btn--ghost wpe-btn--sm wpe-btn--icon" data-mode-toggle aria-label="${ esc( __( 'Switch dashboard colour mode', 'hi-wpessential' ) ) }">${ ico( state.mode === 'dark' ? 'sun' : 'moon', 16 ) }</button>
				</div>
			</header>
			<main class="wpe-main" id="wpe-view" tabindex="-1"></main>`;

		$$( '.wpe-tab' ).forEach( ( b ) => b.addEventListener( 'click', () => go( b.dataset.tab ) ) );
		$( '[data-mode-toggle]' ).addEventListener( 'click', ( e ) => {
			state.mode = state.mode === 'dark' ? 'light' : 'dark';
			store.set( 'mode', state.mode );
			root.dataset.mode = state.mode;
			e.currentTarget.innerHTML = ico( state.mode === 'dark' ? 'sun' : 'moon', 16 );
		} );
	}

	function go( tab, push = true ) {
		if ( ! TABS.some( ( t ) => t[ 0 ] === tab ) ) {
			tab = 'welcome';
		}
		state.tab = tab;
		if ( push ) {
			window.history.replaceState( null, '', '#' + tab );
		}
		$$( '.wpe-tab' ).forEach( ( b ) => b.setAttribute( 'aria-current', b.dataset.tab === tab ? 'page' : 'false' ) );
		// Keep the WP sidebar sub-menu in sync.
		document.querySelectorAll( '#toplevel_page_wpessential .wp-submenu li' ).forEach( ( li ) => {
			const a = li.querySelector( 'a' );
			if ( a ) {
				const h = a.getAttribute( 'href' ) || '';
				li.classList.toggle( 'current', tab === 'welcome' ? h.endsWith( 'page=wpessential' ) : h.endsWith( '#' + tab ) );
			}
		} );
		( { welcome: viewWelcome, demos: viewDemos, plugins: viewPlugins, license: viewLicense, status: viewStatus } )[ tab ]();
	}

	const view = ( html ) => {
		const v = $( '#wpe-view' );
		v.innerHTML = `<div class="wpe-view">${ html }</div>`;
		return v;
	};

	/* ------------------------------------------------------------------ data */

	async function loadDemos( refresh = false ) {
		const res = await api( { path: '/wpessential/v1/demos' + ( refresh ? '?refresh=1' : '' ) } );
		state.demos = res.demos;
		state.notices = res.notices || [];
		state.imported = res.imported;
		state.job = res.job;
		return res;
	}
	async function loadLicense() {
		state.license = await api( { path: '/wpessential/v1/license' } );
		return state.license;
	}
	async function loadStatus() {
		state.status = await api( { path: '/wpessential/v1/status' } );
		return state.status;
	}

	const healthScore = ( checks ) => {
		const pts = checks.reduce( ( s, c ) => s + ( c.state === 'ok' ? 1 : c.state === 'warn' ? 0.5 : 0 ), 0 );
		return Math.round( ( pts / checks.length ) * 100 );
	};

	const ring = ( pct, label, cls = '' ) => {
		const r = 84;
		const c = 2 * Math.PI * r;
		return `<div class="wpe-ring ${ cls }"><svg viewBox="0 0 200 200"><defs><linearGradient id="wpe-grad" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#625ba8"/><stop offset=".6" stop-color="#6188c6"/><stop offset="1" stop-color="#5ac8d6"/></linearGradient></defs><circle class="wpe-ring__track" cx="100" cy="100" r="${ r }"/><circle class="wpe-ring__bar" cx="100" cy="100" r="${ r }" stroke-dasharray="${ c }" stroke-dashoffset="${ c - ( c * pct ) / 100 }"/></svg><div class="wpe-ring__label"><b>${ pct }<small>%</small></b><span>${ esc( label ) }</span></div></div>`;
	};

	/* ------------------------------------------------------------------ welcome */

	async function viewWelcome() {
		const name = cfg.user.split( ' ' )[ 0 ];
		view( `
			<section class="wpe-hero">
				<div>
					<span class="wpe-hero__chip"><b>${ esc( __( 'NEW', 'hi-wpessential' ) ) }</b>${ esc( sprintf( __( '%s is active', 'hi-wpessential' ), cfg.theme.name ) ) }</span>
					<h1>${ esc( sprintf( __( 'Welcome, %s. Let’s build something beautiful.', 'hi-wpessential' ), name ) ) }</h1>
					<p>${ esc( __( 'Pick a demo, choose Gutenberg or Elementor, and get a finished website with pages, images, menus and a shop — in about a minute.', 'hi-wpessential' ) ) }</p>
					<div class="wpe-hero__actions">
						<button class="wpe-btn wpe-btn--white wpe-btn--lg" data-go="demos">${ ico( 'grid' ) }${ esc( __( 'Browse demos', 'hi-wpessential' ) ) }</button>
						<a class="wpe-btn wpe-btn--glass wpe-btn--lg" href="${ esc( cfg.editor ) }">${ ico( 'brush' ) }${ esc( __( 'Customize site', 'hi-wpessential' ) ) }</a>
					</div>
				</div>
				<div class="wpe-hero__visual">
					<img src="${ esc( cfg.theme.shot ) }" alt="">
					<div class="wpe-float wpe-float--a"><span class="wpe-float__ico">${ ico( 'check', 16, 3 ) }</span><div>${ esc( __( 'Demo imported', 'hi-wpessential' ) ) }<small>${ esc( __( 'in 48 seconds', 'hi-wpessential' ) ) }</small></div></div>
					<div class="wpe-float wpe-float--b"><span class="wpe-float__ico">${ ico( 'layers', 16 ) }</span><div>${ esc( __( 'Two builders', 'hi-wpessential' ) ) }<small>Gutenberg · Elementor</small></div></div>
				</div>
			</section>
			<div class="wpe-grid wpe-stats" id="wpe-stats">${ [ 0, 1, 2, 3 ].map( () => '<div class="wpe-card wpe-stat"><div class="wpe-skel" style="width:44px;height:44px"></div><div style="flex:1"><div class="wpe-skel" style="height:10px;width:60%;margin-bottom:8px"></div><div class="wpe-skel" style="height:14px;width:80%"></div></div></div>' ).join( '' ) }</div>
			<div class="wpe-grid wpe-split">
				<section class="wpe-card"><div class="wpe-card__body">
					<span class="wpe-eyebrow">${ esc( __( 'Get started', 'hi-wpessential' ) ) }</span>
					<h2 style="font-size:20px;margin-top:6px">${ esc( __( 'Three steps to launch', 'hi-wpessential' ) ) }</h2>
					<ol class="wpe-steps" id="wpe-steps"></ol>
				</div></section>
				<section class="wpe-card"><div class="wpe-card__body">
					<span class="wpe-eyebrow">${ esc( __( 'Resources', 'hi-wpessential' ) ) }</span>
					<h2 style="font-size:20px;margin-top:6px">${ esc( __( 'Help when you need it', 'hi-wpessential' ) ) }</h2>
					<div class="wpe-links">
						<a class="wpe-link" href="${ esc( cfg.docs ) }" target="_blank" rel="noopener"><span class="wpe-stat__ico">${ ico( 'book' ) }</span><div><b>${ esc( __( 'Documentation', 'hi-wpessential' ) ) }</b><span>${ esc( __( 'Guides for every feature', 'hi-wpessential' ) ) }</span></div>${ ico( 'ext', 16 ) }</a>
						<a class="wpe-link" href="${ esc( cfg.support ) }" target="_blank" rel="noopener"><span class="wpe-stat__ico wpe-stat__ico--green">${ ico( 'help' ) }</span><div><b>${ esc( __( 'Support', 'hi-wpessential' ) ) }</b><span>${ esc( __( 'Real people, fast answers', 'hi-wpessential' ) ) }</span></div>${ ico( 'ext', 16 ) }</a>
						<a class="wpe-link" href="${ esc( cfg.editor ) }"><span class="wpe-stat__ico wpe-stat__ico--pink">${ ico( 'brush' ) }</span><div><b>${ esc( __( 'Site Editor', 'hi-wpessential' ) ) }</b><span>${ esc( __( 'Colours, fonts, header and footer', 'hi-wpessential' ) ) }</span></div>${ ico( 'arrow', 16 ) }</a>
					</div>
				</div></section>
			</div>` );
		$$( '[data-go]' ).forEach( ( b ) => b.addEventListener( 'click', () => go( b.dataset.go ) ) );

		try {
			const [ , lic, st ] = await Promise.all( [ state.demos ? null : loadDemos(), loadLicense(), loadStatus() ] );
			if ( state.tab !== 'welcome' ) {
				return;
			}
			const score = healthScore( st.checks );
			const licLabel = { active: __( 'Active', 'hi-wpessential' ), expired: __( 'Expired', 'hi-wpessential' ), invalid: __( 'Invalid', 'hi-wpessential' ) }[ lic.status ] || __( 'Not connected', 'hi-wpessential' );
			$( '#wpe-stats' ).innerHTML = [
				[ 'layers', '', __( 'Theme', 'hi-wpessential' ), cfg.theme.name + ' ' + cfg.theme.version ],
				[ 'grid', '--pink', __( 'Imported demo', 'hi-wpessential' ), state.imported ? state.imported.title : __( 'None yet', 'hi-wpessential' ) ],
				[ 'key', lic.status === 'active' ? '--green' : '--amber', __( 'License', 'hi-wpessential' ), licLabel ],
				[ 'pulse', score >= 80 ? '--green' : '--amber', __( 'System health', 'hi-wpessential' ), score + '%' ],
			].map( ( [ i, c, l, v ] ) => `<div class="wpe-card wpe-stat"><span class="wpe-stat__ico ${ c ? 'wpe-stat__ico' + c : '' }">${ ico( i, 20 ) }</span><div><small>${ esc( l ) }</small><strong>${ esc( v ) }</strong></div></div>` ).join( '' );
			const steps = [
				[ !! state.imported, __( 'Import a demo', 'hi-wpessential' ), __( 'Start from a complete, ready-made website.', 'hi-wpessential' ), 'demos', __( 'Open library', 'hi-wpessential' ) ],
				[ lic.status === 'active', __( 'Activate your license', 'hi-wpessential' ), __( 'Unlock Pro demos, updates and support.', 'hi-wpessential' ), 'license', __( 'Activate', 'hi-wpessential' ) ],
				[ false, __( 'Make it yours', 'hi-wpessential' ), __( 'Swap the logo, colours and words in the Site Editor.', 'hi-wpessential' ), '', __( 'Customize', 'hi-wpessential' ) ],
			];
			$( '#wpe-steps' ).innerHTML = steps.map( ( [ done, t, d, tab, cta ], i ) => `<li class="${ done ? 'is-done' : '' }"><span class="wpe-steps__n">${ done ? ico( 'check', 16, 3 ) : i + 1 }</span><div><h4>${ esc( t ) }</h4><p>${ esc( d ) }</p></div>${ tab ? `<button class="wpe-btn wpe-btn--soft wpe-btn--sm" data-go="${ tab }">${ esc( cta ) }</button>` : `<a class="wpe-btn wpe-btn--soft wpe-btn--sm" href="${ esc( cfg.editor ) }">${ esc( cta ) }</a>` }</li>` ).join( '' );
			$$( '#wpe-steps [data-go]' ).forEach( ( b ) => b.addEventListener( 'click', () => go( b.dataset.go ) ) );
		} catch ( e ) {
			toast( 'error', __( 'Could not load the dashboard', 'hi-wpessential' ), errMsg( e ) );
		}
	}

	/* ------------------------------------------------------------------ demos */

	const CATS = [ [ 'all', __( 'All', 'hi-wpessential' ) ], [ 'business', __( 'Business', 'hi-wpessential' ) ], [ 'agency', __( 'Agency', 'hi-wpessential' ) ], [ 'shop', __( 'Shop', 'hi-wpessential' ) ], [ 'blog', __( 'Blog', 'hi-wpessential' ) ], [ 'restaurant', __( 'Restaurant', 'hi-wpessential' ) ], [ 'education', __( 'Education', 'hi-wpessential' ) ] ];

	async function viewDemos() {
		const f = state.filter;
		view( `
			<div class="wpe-head"><div><h1>${ esc( __( 'Demo Library', 'hi-wpessential' ) ) }</h1><p>${ esc( __( 'Every demo is a complete website. Preview it, then import it for Gutenberg or Elementor — plugins, pages, images and menus included.', 'hi-wpessential' ) ) }</p></div>
				<button class="wpe-btn wpe-btn--ghost" data-refresh data-busy="${ esc( __( 'Refreshing', 'hi-wpessential' ) ) }">${ ico( 'refresh', 16 ) }${ esc( __( 'Refresh library', 'hi-wpessential' ) ) }</button></div>
			<div id="wpe-banners"></div>
			<div class="wpe-card wpe-toolbar">
				<label class="wpe-search"><span class="wpe-sr">${ esc( __( 'Search demos', 'hi-wpessential' ) ) }</span>${ ico( 'search', 17 ) }<input type="search" value="${ esc( f.q ) }" placeholder="${ esc( __( 'Search demos…', 'hi-wpessential' ) ) }" data-q></label>
				<div class="wpe-chips" role="group" aria-label="${ esc( __( 'Category', 'hi-wpessential' ) ) }">${ CATS.map( ( [ id, l ] ) => `<button class="wpe-chip" data-cat="${ id }" aria-pressed="${ f.cat === id }">${ esc( l ) }</button>` ).join( '' ) }</div>
				<div class="wpe-seg" role="group" aria-label="${ esc( __( 'Plan', 'hi-wpessential' ) ) }">${ [ [ 'all', __( 'All', 'hi-wpessential' ) ], [ 'free', __( 'Free', 'hi-wpessential' ) ], [ 'pro', __( 'Pro', 'hi-wpessential' ) ] ].map( ( [ id, l ] ) => `<button data-type="${ id }" aria-pressed="${ f.type === id }">${ esc( l ) }</button>` ).join( '' ) }</div>
				<div class="wpe-seg" role="group" aria-label="${ esc( __( 'Builder', 'hi-wpessential' ) ) }">${ [ [ 'all', __( 'Any builder', 'hi-wpessential' ), '' ], [ 'gutenberg', 'Gutenberg', 'gutenberg' ], [ 'elementor', 'Elementor', 'elementor' ] ].map( ( [ id, l, i ] ) => `<button data-builder="${ id }" aria-pressed="${ f.builder === id }">${ i ? ico( i, 14 ) : '' }${ esc( l ) }</button>` ).join( '' ) }</div>
			</div>
			<div class="wpe-grid wpe-demos" id="wpe-demos">${ [ 0, 1, 2 ].map( () => '<div class="wpe-card wpe-demo"><div class="wpe-skel" style="aspect-ratio:16/11;border-radius:0"></div><div class="wpe-demo__body"><div class="wpe-skel" style="height:16px;width:50%"></div><div class="wpe-skel" style="height:12px;width:90%"></div></div></div>' ).join( '' ) }</div>` );

		const rerender = () => renderDemos();
		$( '[data-q]' ).addEventListener( 'input', ( e ) => {
			f.q = e.target.value;
			rerender();
		} );
		$$( '[data-cat]' ).forEach( ( b ) => b.addEventListener( 'click', () => {
			f.cat = b.dataset.cat;
			$$( '[data-cat]' ).forEach( ( x ) => x.setAttribute( 'aria-pressed', x === b ) );
			rerender();
		} ) );
		[ 'type', 'builder' ].forEach( ( k ) => $$( `[data-${ k }]` ).forEach( ( b ) => b.addEventListener( 'click', () => {
			f[ k ] = b.dataset[ k ];
			$$( `[data-${ k }]` ).forEach( ( x ) => x.setAttribute( 'aria-pressed', x === b ) );
			rerender();
		} ) ) );
		$( '[data-refresh]' ).addEventListener( 'click', ( e ) => busy( e.currentTarget, async () => {
			try {
				await loadDemos( true );
				rerender();
				toast( 'success', __( 'Library refreshed', 'hi-wpessential' ) );
			} catch ( err ) {
				toast( 'error', __( 'Refresh failed', 'hi-wpessential' ), errMsg( err ) );
			}
		} ) );

		loadAccount().catch( () => {
			state.account = { signed_in: false };
		} );
		try {
			await loadDemos();
			if ( state.tab === 'demos' ) {
				rerender();
			}
		} catch ( e ) {
			$( '#wpe-demos' ).outerHTML = `<div class="wpe-card wpe-empty"><span class="wpe-empty__ico">${ ico( 'error', 28 ) }</span><h3>${ esc( __( 'The library could not load', 'hi-wpessential' ) ) }</h3><p>${ esc( errMsg( e ) ) }</p></div>`;
		}
	}

	function banners() {
		const box = $( '#wpe-banners' );
		if ( ! box ) {
			return;
		}
		let html = state.notices.map( ( n ) => `<div class="wpe-banner wpe-banner--${ esc( n.level ) }">${ ico( levelIco[ n.level ] || 'info', 22 ) }<div>${ esc( n.message ) }</div></div>` ).join( '' );
		if ( state.job ) {
			const failed = state.job.status === 'failed';
			html = `<div class="wpe-banner wpe-banner--${ failed ? 'error' : 'warning' }">${ ico( failed ? 'error' : 'clock', 22 ) }<div><b>${ esc( failed ? sprintf( __( 'The import of %s stopped', 'hi-wpessential' ), state.job.title ) : sprintf( __( 'The import of %s was interrupted', 'hi-wpessential' ), state.job.title ) ) }</b>${ esc( __( 'Your progress is saved. Continue exactly where it left off.', 'hi-wpessential' ) ) }</div><button class="wpe-btn wpe-btn--primary wpe-btn--sm" data-resume>${ ico( 'refresh', 15 ) }${ esc( __( 'Resume import', 'hi-wpessential' ) ) }</button></div>` + html;
		}
		if ( state.imported ) {
			html += `<div class="wpe-banner wpe-banner--success">${ ico( 'ok', 22 ) }<div><b>${ esc( sprintf( __( '%s is installed on this site', 'hi-wpessential' ), state.imported.title ) ) }</b>${ esc( __( 'You can remove everything it added and restore your previous settings at any time.', 'hi-wpessential' ) ) }</div><button class="wpe-btn wpe-btn--danger wpe-btn--sm" data-remove data-busy="${ esc( __( 'Removing', 'hi-wpessential' ) ) }">${ ico( 'trash', 15 ) }${ esc( __( 'Remove demo', 'hi-wpessential' ) ) }</button></div>`;
		}
		box.innerHTML = html;
		const resume = $( '[data-resume]', box );
		if ( resume ) {
			resume.addEventListener( 'click', () => {
				const demo = state.demos.find( ( d ) => d.id === state.job.demo );
				if ( demo ) {
					wizard( demo, { resume: true } );
				}
			} );
		}
		const remove = $( '[data-remove]', box );
		if ( remove ) {
			remove.addEventListener( 'click', () => removeDemo( remove ) );
		}
	}

	async function removeDemo( btn ) {
		// eslint-disable-next-line no-alert
		if ( ! window.confirm( sprintf( __( 'Remove %s? Its pages, posts, products, images and menus will be deleted and your previous settings restored. Content you created yourself is not touched.', 'hi-wpessential' ), state.imported.title ) ) ) {
			return;
		}
		await busy( btn, async () => {
			try {
				const res = await api( { path: '/wpessential/v1/import/remove', method: 'POST' } );
				toast( 'success', __( 'Demo removed', 'hi-wpessential' ), res.message );
				await loadDemos();
				renderDemos();
			} catch ( e ) {
				toast( 'error', __( 'Removal failed', 'hi-wpessential' ), errMsg( e ) );
			}
		} );
	}

	function renderDemos() {
		banners();
		const f = state.filter;
		const q = f.q.trim().toLowerCase();
		const list = ( state.demos || [] ).filter( ( d ) =>
			( f.cat === 'all' || ( d.niche || [] ).includes( f.cat ) ) &&
			( f.type === 'all' || d.type === f.type ) &&
			( f.builder === 'all' || ( d.builders && d.builders[ f.builder ] ) ) &&
			( ! q || ( d.title + ' ' + ( d.tagline || '' ) + ' ' + ( d.niche || [] ).join( ' ' ) ).toLowerCase().includes( q ) )
		);
		const grid = $( '#wpe-demos' );
		if ( ! grid ) {
			return;
		}
		if ( ! list.length ) {
			grid.innerHTML = `<div class="wpe-card wpe-empty" style="grid-column:1/-1"><span class="wpe-empty__ico">${ ico( 'search', 28 ) }</span><h3>${ esc( __( 'No demos match these filters', 'hi-wpessential' ) ) }</h3><p>${ esc( __( 'Try another category or clear the search.', 'hi-wpessential' ) ) }</p><p style="margin-top:16px"><button class="wpe-btn wpe-btn--soft" data-clear>${ esc( __( 'Clear filters', 'hi-wpessential' ) ) }</button></p></div>`;
			$( '[data-clear]', grid ).addEventListener( 'click', () => {
				state.filter = { q: '', cat: 'all', type: 'all', builder: 'all' };
				viewDemos();
			} );
			return;
		}
		grid.innerHTML = list.map( ( d, i ) => {
			const builders = Object.keys( d.builders || {} );
			return `<article class="wpe-card wpe-demo" style="animation-delay:${ i * 60 }ms">
				<div class="wpe-demo__media">
					<span class="wpe-badge wpe-badge--${ d.type === 'pro' ? 'pro' : 'free' }">${ d.type === 'pro' ? ico( 'spark', 12, 3 ) + 'PRO' : 'FREE' }</span>
					${ d.imported ? `<span class="wpe-badge wpe-badge--done">${ ico( 'check', 12, 3 ) }${ esc( __( 'IMPORTED', 'hi-wpessential' ) ) }</span>` : '' }
					<img src="${ esc( d.thumbnail ) }" alt="${ esc( d.title ) }" loading="lazy">
					<div class="wpe-demo__overlay"><button class="wpe-btn wpe-btn--white" data-preview="${ esc( d.id ) }">${ ico( 'eye', 17 ) }${ esc( __( 'Live preview', 'hi-wpessential' ) ) }</button></div>
				</div>
				<div class="wpe-demo__body">
					<div class="wpe-demo__title"><h3>${ esc( d.title ) }</h3></div>
					${ d.tagline ? `<p>${ esc( d.tagline ) }</p>` : '' }
					<div class="wpe-demo__meta">${ builders.map( ( b ) => `<span class="wpe-pill wpe-builder-ico wpe-builder-ico--${ b }">${ ico( b, 13 ) }${ b === 'elementor' ? 'Elementor' : 'Gutenberg' }</span>` ).join( '' ) }${ ( d.highlights || [] ).slice( 0, 2 ).map( ( h ) => `<span class="wpe-pill">${ esc( h ) }</span>` ).join( '' ) }</div>
				</div>
				<div class="wpe-demo__foot">
					<button class="wpe-btn wpe-btn--ghost" data-preview="${ esc( d.id ) }">${ ico( 'eye', 16 ) }${ esc( __( 'Preview', 'hi-wpessential' ) ) }</button>
					${ d.locked ? `<button class="wpe-btn wpe-btn--grad" data-unlock>${ ico( 'lock', 16 ) }${ esc( __( 'Unlock', 'hi-wpessential' ) ) }</button>` : `<button class="wpe-btn wpe-btn--primary" data-import="${ esc( d.id ) }">${ ico( 'download', 16 ) }${ esc( d.imported ? __( 'Re-import', 'hi-wpessential' ) : __( 'Import', 'hi-wpessential' ) ) }</button>` }
				</div>
			</article>`;
		} ).join( '' );
		const byId = ( id ) => state.demos.find( ( d ) => d.id === id );
		$$( '[data-preview]', grid ).forEach( ( b ) => b.addEventListener( 'click', () => preview( byId( b.dataset.preview ) ) ) );
		$$( '[data-import]', grid ).forEach( ( b ) => b.addEventListener( 'click', () => wizard( byId( b.dataset.import ) ) ) );
		$$( '[data-unlock]', grid ).forEach( ( b ) => b.addEventListener( 'click', () => go( 'license' ) ) );
	}

	/* ------------------------------------------------------------------ modal */

	function modal( cls, html, { onClose, guard } = {} ) {
		const prev = document.activeElement;
		const m = document.createElement( 'div' );
		m.className = 'wpe-modal ' + cls;
		m.setAttribute( 'role', 'dialog' );
		m.setAttribute( 'aria-modal', 'true' );
		m.innerHTML = `<div class="wpe-modal__scrim"></div><div class="wpe-modal__panel">${ html }</div>`;
		root.appendChild( m );
		document.documentElement.style.overflow = 'hidden';
		const close = ( force ) => {
			if ( ! force && guard && ! guard() ) {
				return;
			}
			document.removeEventListener( 'keydown', key );
			document.documentElement.style.overflow = '';
			m.remove();
			if ( prev && prev.focus ) {
				prev.focus();
			}
			if ( onClose ) {
				onClose();
			}
		};
		const key = ( e ) => {
			if ( e.key === 'Escape' ) {
				close();
			}
			if ( e.key === 'Tab' ) {
				const f = $$( 'button:not([disabled]), a[href], input:not([disabled]), [tabindex]:not([tabindex="-1"])', m ).filter( ( el ) => el.offsetParent !== null );
				if ( f.length ) {
					const first = f[ 0 ];
					const last = f[ f.length - 1 ];
					if ( e.shiftKey && document.activeElement === first ) {
						e.preventDefault();
						last.focus();
					} else if ( ! e.shiftKey && document.activeElement === last ) {
						e.preventDefault();
						first.focus();
					}
				}
			}
		};
		document.addEventListener( 'keydown', key );
		$( '.wpe-modal__scrim', m ).addEventListener( 'click', () => close() );
		setTimeout( () => {
			const first = $( '[autofocus], .wpe-modal__panel button', m );
			if ( first ) {
				first.focus();
			}
		}, 50 );
		return { el: m, close };
	}

	/* ------------------------------------------------------------------ preview */

	function preview( demo ) {
		const shots = demo.preview || {};
		// Live demos run in WordPress Playground from the same package the import uses.
		const live = demo.playground || {};
		const liveBuilders = Object.keys( live );
		let url = demo.preview_url || ( liveBuilders.length ? live[ liveBuilders[ 0 ] ] : '' );
		const devices = [ [ 'desktop', 'monitor', __( 'Desktop', 'hi-wpessential' ) ], [ 'tablet', 'tablet', __( 'Tablet', 'hi-wpessential' ) ], [ 'mobile', 'phone', __( 'Mobile', 'hi-wpessential' ) ] ];
		const body = ( d ) => url
			? `<iframe src="${ esc( url ) }" title="${ esc( demo.title ) }" loading="lazy" allow="clipboard-write"></iframe>`
			: `<img src="${ esc( shots[ d ] || shots.desktop || demo.thumbnail ) }" alt="${ esc( sprintf( __( '%1$s on %2$s', 'hi-wpessential' ), demo.title, d ) ) }">`;
		const { el, close } = modal( 'wpe-preview', `
			<div class="wpe-preview__bar">
				<div><h3>${ esc( demo.title ) }</h3><span class="wpe-muted" style="font-size:12px">${ esc( ( demo.pages || [] ).join( ' · ' ) ) }</span></div>
				${ liveBuilders.length > 1 ? `<div class="wpe-seg" role="group" aria-label="${ esc( __( 'Builder', 'hi-wpessential' ) ) }">${ liveBuilders.map( ( b, n ) => `<button data-live="${ esc( b ) }" aria-pressed="${ n === 0 }"><span>${ esc( 'elementor' === b ? 'Elementor' : 'Gutenberg' ) }</span></button>` ).join( '' ) }</div>` : '' }
				<div class="wpe-seg" role="group" aria-label="${ esc( __( 'Device', 'hi-wpessential' ) ) }">${ devices.map( ( [ id, i, l ], n ) => `<button data-device="${ id }" aria-pressed="${ n === 0 }">${ ico( i, 15 ) }<span>${ esc( l ) }</span></button>` ).join( '' ) }</div>
				${ demo.locked ? `<button class="wpe-btn wpe-btn--grad" data-unlock>${ ico( 'lock', 16 ) }${ esc( __( 'Unlock with Pro', 'hi-wpessential' ) ) }</button>` : `<button class="wpe-btn wpe-btn--primary" data-import>${ ico( 'download', 16 ) }${ esc( __( 'Import this demo', 'hi-wpessential' ) ) }</button>` }
				<button class="wpe-btn wpe-btn--ghost wpe-btn--icon wpe-modal__close" data-close aria-label="${ esc( __( 'Close preview', 'hi-wpessential' ) ) }">${ ico( 'x' ) }</button>
			</div>
			<div class="wpe-preview__stage"><div class="wpe-frame" data-device="desktop"><div class="wpe-frame__chrome"><i></i><i></i><i></i><span>${ esc( ( url || cfg.site ).replace( /^https?:\/\//, '' ) ) }</span></div><div data-shot>${ body( 'desktop' ) }</div></div></div>` );
		$$( '[data-device]', el ).forEach( ( b ) => b.addEventListener( 'click', () => {
			$$( '[data-device]', el ).forEach( ( x ) => x.setAttribute( 'aria-pressed', x === b ) );
			const frame = $( '.wpe-frame', el );
			frame.dataset.device = b.dataset.device;
			if ( ! url ) {
				$( '[data-shot]', el ).innerHTML = body( b.dataset.device );
			}
			$( '.wpe-preview__stage', el ).scrollTop = 0;
		} ) );
		$$( '[data-live]', el ).forEach( ( b ) => b.addEventListener( 'click', () => {
			$$( '[data-live]', el ).forEach( ( x ) => x.setAttribute( 'aria-pressed', x === b ) );
			url = live[ b.dataset.live ];
			$( '[data-shot]', el ).innerHTML = body( 'desktop' );
		} ) );
		$( '[data-close]', el ).addEventListener( 'click', () => close() );
		const imp = $( '[data-import]', el );
		if ( imp ) {
			imp.addEventListener( 'click', () => {
				close( true );
				wizard( demo );
			} );
		}
		const un = $( '[data-unlock]', el );
		if ( un ) {
			un.addEventListener( 'click', () => {
				close( true );
				go( 'license' );
			} );
		}
	}

	/* ------------------------------------------------------------------ wizard */

	function wizard( demo, { resume = false } = {} ) {
		const builders = Object.keys( demo.builders || {} );
		const needAccount = ! resume && ( demo.locked || ! ( state.account && state.account.signed_in ) );
		const flow = needAccount ? [ stepAccount, stepBuilder, stepPlugins, stepContent, stepImport, stepDone ] : [ stepBuilder, stepPlugins, stepContent, stepImport, stepDone ];
		const at = ( fn ) => flow.indexOf( fn );
		const w = {
			step: resume ? at( stepImport ) : 0,
			builder: resume && state.job ? state.job.builder : builders[ 0 ],
			plugins: null,
			skip: [],
			opts: { content: true, media: true, products: true, settings: true, plugins: true, reset: false },
			job: null,
			since: 0,
			logs: [],
			filter: 'all',
			running: false,
			cancelled: false,
		};
		const STEPS = ( needAccount ? [ __( 'Account', 'hi-wpessential' ) ] : [] ).concat( [ __( 'Builder', 'hi-wpessential' ), __( 'Plugins', 'hi-wpessential' ), __( 'Content', 'hi-wpessential' ), __( 'Import', 'hi-wpessential' ), __( 'Done', 'hi-wpessential' ) ] );

		const { el, close } = modal( 'wpe-wizard', `
			<aside class="wpe-rail">
				<div class="wpe-rail__demo"><img src="${ esc( demo.thumbnail ) }" alt=""><div><b>${ esc( demo.title ) }</b><small>${ esc( demo.type === 'pro' ? 'Pro demo' : 'Free demo' ) }</small></div></div>
				<ol>${ STEPS.map( ( s, i ) => `<li data-rail="${ i }"><span class="wpe-rail__n">${ i + 1 }</span><span class="wpe-rail__label">${ esc( s ) }</span></li>` ).join( '' ) }</ol>
				<div class="wpe-rail__foot">${ ico( 'shield', 16 ) }${ esc( __( 'Safe import — every change can be undone.', 'hi-wpessential' ) ) }</div>
			</aside>
			<section class="wpe-pane"><div class="wpe-pane__body" data-body></div><footer class="wpe-pane__foot" data-foot></footer></section>
			<button class="wpe-btn wpe-btn--ghost wpe-btn--icon wpe-btn--sm wpe-modal__close" data-close aria-label="${ esc( __( 'Close', 'hi-wpessential' ) ) }">${ ico( 'x', 16 ) }</button>`, {
			guard: () => {
				if ( w.running ) {
					// eslint-disable-next-line no-alert
					return window.confirm( __( 'The import keeps its progress if you close this window. You can resume it from the Demo Library. Close anyway?', 'hi-wpessential' ) );
				}
				return true;
			},
			onClose: () => {
				w.cancelled = true;
				window.removeEventListener( 'beforeunload', unload );
				if ( state.tab === 'demos' ) {
					loadDemos().then( renderDemos ).catch( () => {} );
				}
			},
		} );
		const unload = ( e ) => {
			if ( w.running ) {
				e.preventDefault();
				e.returnValue = '';
			}
		};
		window.addEventListener( 'beforeunload', unload );
		$( '[data-close]', el ).addEventListener( 'click', () => close() );
		const body = $( '[data-body]', el );
		const foot = $( '[data-foot]', el );

		const rail = () => $$( '[data-rail]', el ).forEach( ( li ) => {
			const i = Number( li.dataset.rail );
			li.className = i < w.step ? 'is-done' : i === w.step ? 'is-current' : '';
			$( '.wpe-rail__n', li ).innerHTML = i < w.step ? ico( 'check', 14, 3 ) : String( i + 1 );
		} );
		const footer = ( back, next, nextLabel, nextIcon = 'arrow', extra = '' ) => {
			foot.innerHTML = `${ back ? `<button class="wpe-btn wpe-btn--ghost" data-back>${ ico( 'back', 16 ) }${ esc( __( 'Back', 'hi-wpessential' ) ) }</button>` : '' }<span class="wpe-grow"></span>${ extra }${ next ? `<button class="wpe-btn wpe-btn--primary wpe-btn--lg" data-next>${ esc( nextLabel ) }${ ico( nextIcon, 17 ) }</button>` : '' }`;
			if ( back ) {
				$( '[data-back]', foot ).addEventListener( 'click', () => show( w.step - 1 ) );
			}
			if ( next ) {
				$( '[data-next]', foot ).addEventListener( 'click', next );
			}
		};
		const paint = ( html ) => {
			body.innerHTML = `<div class="wpe-view">${ html }</div>`;
			body.scrollTop = 0;
		};

		function show( step ) {
			w.step = step;
			rail();
			flow[ step ]();
		}


		function stepAccount() {
			const locked = () => demo.locked && ! ( state.license && state.license.status === 'active' && state.license.pro );
			paint( `<h2>${ esc( locked() ? __( 'Unlock this Pro demo', 'hi-wpessential' ) : __( 'Connect your WPEssential account', 'hi-wpessential' ) ) }</h2><p class="wpe-pane__lead">${ esc( locked() ? __( 'Sign in and use a Pro license on this site — or pick a plan. Checkout opens in a new tab.', 'hi-wpessential' ) : __( 'Sign in or create a free account to keep your demos, licenses and updates in one place.', 'hi-wpessential' ) ) }</p><div data-acct></div>` );
			const next = () => {
				if ( locked() ) {
					toast( 'warning', __( 'A Pro license is needed', 'hi-wpessential' ), __( 'Use a Pro license on this site, or choose a plan.', 'hi-wpessential' ) );
					return;
				}
				show( at( stepBuilder ) );
			};
			footer( false, next, __( 'Continue', 'hi-wpessential' ) );
			renderAccount( $( '[data-acct]', body ), {
				skip: demo.locked ? null : () => show( at( stepBuilder ) ),
				onLicensed: async () => {
					try {
						await loadLicense();
						if ( demo.locked && state.license.status === 'active' && state.license.pro ) {
							demo.locked = false;
							toast( 'success', __( 'Demo unlocked', 'hi-wpessential' ), demo.title );
						}
					} catch ( e ) {}
				},
			} );
		}

		function stepBuilder() {
			const opt = ( id, title, text, logoCls ) => {
				const has = builders.includes( id );
				return `<label class="wpe-option"><input type="radio" name="wpe-builder" value="${ id }" ${ w.builder === id ? 'checked' : '' } ${ has ? '' : 'disabled' }><span class="wpe-option__check">${ ico( 'check', 14, 3 ) }</span><span class="wpe-option__logo ${ logoCls }">${ ico( id, 28 ) }</span><h4>${ esc( title ) }</h4><p>${ esc( has ? text : __( 'This demo has no version for this builder yet.', 'hi-wpessential' ) ) }</p></label>`;
			};
			paint( `<h2>${ esc( __( 'Choose your page builder', 'hi-wpessential' ) ) }</h2><p class="wpe-pane__lead">${ esc( __( 'Both versions look identical. Pick the editor you or your client will use every day.', 'hi-wpessential' ) ) }</p>
				<div class="wpe-options">${ opt( 'gutenberg', 'Gutenberg', __( 'The native WordPress block editor. Fastest pages, no extra plugin.', 'hi-wpessential' ), '' ) }${ opt( 'elementor', 'Elementor', cfg.elementor ? __( 'Visual drag-and-drop editing. Elementor is already installed.', 'hi-wpessential' ) : __( 'Visual drag-and-drop editing. Elementor will be installed for you.', 'hi-wpessential' ), 'wpe-option__logo--el' ) }</div>` );
			$$( 'input[name="wpe-builder"]', body ).forEach( ( r ) => r.addEventListener( 'change', () => {
				w.builder = r.value;
				w.plugins = null;
			} ) );
			footer( w.step > 0, () => show( at( stepPlugins ) ), __( 'Continue', 'hi-wpessential' ) );
		}

		async function stepPlugins() {
			paint( `<h2>${ esc( __( 'Plugins this demo uses', 'hi-wpessential' ) ) }</h2><p class="wpe-pane__lead">${ esc( __( 'We install, activate or switch off plugins so the demo works exactly like the preview.', 'hi-wpessential' ) ) }</p><div class="wpe-list" data-list>${ [ 0, 1 ].map( () => '<div class="wpe-row"><div class="wpe-skel" style="width:40px;height:40px"></div><div class="wpe-row__main"><div class="wpe-skel" style="height:12px;width:40%"></div></div></div>' ).join( '' ) }</div>` );
			footer( true, () => show( at( stepContent ) ), __( 'Continue', 'hi-wpessential' ) );
			try {
				if ( ! w.plugins ) {
					w.plugins = await api( { path: `/wpessential/v1/demo-plugins?demo=${ encodeURIComponent( demo.id ) }&builder=${ w.builder }` } );
				}
			} catch ( e ) {
				$( '[data-list]', body ).innerHTML = `<div class="wpe-callout wpe-callout--error">${ ico( 'error' ) }<div>${ esc( errMsg( e ) ) }</div></div>`;
				return;
			}
			const list = $( '[data-list]', body );
			if ( ! list ) {
				return;
			}
			if ( ! w.plugins.length ) {
				list.innerHTML = `<div class="wpe-callout wpe-callout--info">${ ico( 'info' ) }<div>${ esc( __( 'No extra plugins needed — this demo runs on the theme alone.', 'hi-wpessential' ) ) }</div></div>`;
				return;
			}
			const pill = ( p ) => {
				if ( p.state === 'conflicts' ) {
					return p.status === 'active' ? `<span class="wpe-pill wpe-pill--warning"><i></i>${ esc( __( 'Will be deactivated', 'hi-wpessential' ) ) }</span>` : `<span class="wpe-pill"><i></i>${ esc( __( 'Not needed', 'hi-wpessential' ) ) }</span>`;
				}
				return {
					active: `<span class="wpe-pill wpe-pill--success"><i></i>${ esc( __( 'Active', 'hi-wpessential' ) ) }</span>`,
					inactive: `<span class="wpe-pill wpe-pill--info"><i></i>${ esc( __( 'Will be activated', 'hi-wpessential' ) ) }</span>`,
					missing: `<span class="wpe-pill wpe-pill--primary"><i></i>${ esc( __( 'Will be installed', 'hi-wpessential' ) ) }</span>`,
				}[ p.status ];
			};
			list.innerHTML = w.plugins.map( ( p ) => `<div class="wpe-row"><span class="wpe-row__ico">${ esc( p.name.charAt( 0 ) ) }</span><div class="wpe-row__main"><b>${ esc( p.name ) }</b><span>${ esc( { required: __( 'Required', 'hi-wpessential' ), recommended: __( 'Recommended', 'hi-wpessential' ), conflicts: __( 'Conflicts with this demo', 'hi-wpessential' ) }[ p.state ] || '' ) }${ p.version ? ' · v' + esc( p.version ) : '' }</span></div>${ pill( p ) }${ p.state === 'recommended' && p.pending ? `<label class="wpe-switch" title="${ esc( __( 'Include', 'hi-wpessential' ) ) }"><input type="checkbox" data-skip="${ esc( p.slug ) }" ${ w.skip.includes( p.slug ) ? '' : 'checked' } aria-label="${ esc( sprintf( __( 'Include %s', 'hi-wpessential' ), p.name ) ) }"><span></span></label>` : '' }</div>` ).join( '' );
			$$( '[data-skip]', list ).forEach( ( c ) => c.addEventListener( 'change', () => {
				w.skip = c.checked ? w.skip.filter( ( s ) => s !== c.dataset.skip ) : [ ...w.skip, c.dataset.skip ];
			} ) );
		}

		function stepContent() {
			const shop = ( w.plugins || [] ).some( ( p ) => p.slug === 'woocommerce' && ! w.skip.includes( 'woocommerce' ) ) || cfg.woo;
			const rows = [
				[ 'content', 'file', __( 'Pages, posts and menus', 'hi-wpessential' ), sprintf( __( '%s and their navigation.', 'hi-wpessential' ), ( demo.pages || [] ).slice( 0, 5 ).join( ', ' ) ) ],
				[ 'media', 'image', __( 'Images', 'hi-wpessential' ), __( 'Copied to your Media Library, licensed for your site.', 'hi-wpessential' ) ],
				[ 'products', 'bag', __( 'Shop products', 'hi-wpessential' ), shop ? __( 'Products, categories, prices and shipping.', 'hi-wpessential' ) : __( 'Needs WooCommerce — enable it on the previous step.', 'hi-wpessential' ), ! shop ],
				[ 'settings', 'cog', __( 'Site settings', 'hi-wpessential' ), __( 'Home page, blog page and store options.', 'hi-wpessential' ) ],
			];
			paint( `<h2>${ esc( __( 'What should we import?', 'hi-wpessential' ) ) }</h2><p class="wpe-pane__lead">${ esc( __( 'Everything is on by default for a site that matches the preview.', 'hi-wpessential' ) ) }</p>
				<div class="wpe-list">${ rows.map( ( [ k, i, t, d, dis ] ) => `<label class="wpe-row" style="cursor:pointer"><span class="wpe-row__ico">${ ico( i, 19 ) }</span><div class="wpe-row__main"><b>${ esc( t ) }</b><span>${ esc( d ) }</span></div><span class="wpe-switch"><input type="checkbox" data-opt="${ k }" ${ w.opts[ k ] && ! dis ? 'checked' : '' } ${ dis ? 'disabled' : '' }><span></span></span></label>` ).join( '' ) }
				${ state.imported ? `<label class="wpe-row" style="cursor:pointer;border-color:var(--c-warning)"><span class="wpe-row__ico" style="color:var(--c-warning)">${ ico( 'trash', 19 ) }</span><div class="wpe-row__main"><b>${ esc( sprintf( __( 'Remove %s first', 'hi-wpessential' ), state.imported.title ) ) }</b><span>${ esc( __( 'Recommended: start from a clean site instead of adding duplicates.', 'hi-wpessential' ) ) }</span></div><span class="wpe-switch"><input type="checkbox" data-opt="reset" ${ w.opts.reset ? 'checked' : '' }><span></span></span></label>` : '' }</div>
				<div class="wpe-callout wpe-callout--info">${ ico( 'shield' ) }<div>${ esc( __( 'Your existing posts and pages are never changed. Imported items are tagged, so they can be removed in one click later.', 'hi-wpessential' ) ) }</div></div>` );
			$$( '[data-opt]', body ).forEach( ( c ) => c.addEventListener( 'change', () => {
				w.opts[ c.dataset.opt ] = c.checked;
			} ) );
			if ( ! shop ) {
				w.opts.products = false;
			}
			footer( true, () => show( at( stepImport ) ), __( 'Start import', 'hi-wpessential' ), 'rocket' );
		}

		/* ---------- import ---------- */

		function stepImport() {
			paint( `<div class="wpe-run">${ ring( 0, __( 'Preparing', 'hi-wpessential' ), 'is-running' ) }<div><h2 data-title>${ esc( __( 'Importing your demo…', 'hi-wpessential' ) ) }</h2><p class="wpe-pane__lead" data-sub>${ esc( __( 'Keep this window open. It usually takes about a minute.', 'hi-wpessential' ) ) }</p><ul class="wpe-tasks" data-tasks></ul></div></div>
				<div class="wpe-console"><div class="wpe-console__bar" data-filters></div><div class="wpe-console__lines" data-lines role="log" aria-live="polite"></div></div>
				<div data-error></div>` );
			foot.innerHTML = `<span class="wpe-muted" data-elapsed>${ ico( 'clock', 15 ) }</span><span class="wpe-grow"></span><button class="wpe-btn wpe-btn--ghost" data-cancel>${ esc( __( 'Cancel import', 'hi-wpessential' ) ) }</button>`;
			$( '[data-cancel]', foot ).addEventListener( 'click', cancel );
			filters();
			if ( resume ) {
				attach();
			} else {
				begin();
			}
		}

		function filters() {
			const c = { all: w.logs.length, info: 0, success: 0, warning: 0, error: 0 };
			w.logs.forEach( ( l ) => c[ l.level ]++ );
			const bar = $( '[data-filters]', body );
			if ( ! bar ) {
				return;
			}
			bar.innerHTML = [ [ 'all', __( 'All', 'hi-wpessential' ) ], [ 'info', __( 'Info', 'hi-wpessential' ) ], [ 'success', __( 'Success', 'hi-wpessential' ) ], [ 'warning', __( 'Warnings', 'hi-wpessential' ) ], [ 'error', __( 'Errors', 'hi-wpessential' ) ] ].map( ( [ id, l ] ) => `<button class="wpe-filter wpe-filter--${ id }" data-f="${ id }" aria-pressed="${ w.filter === id }">${ esc( l ) }<em>${ c[ id ] }</em></button>` ).join( '' ) + `<span class="wpe-grow"></span><button class="wpe-filter" data-copy>${ ico( 'copy', 13 ) }${ esc( __( 'Copy log', 'hi-wpessential' ) ) }</button>`;
			$$( '[data-f]', bar ).forEach( ( b ) => b.addEventListener( 'click', () => {
				w.filter = b.dataset.f;
				filters();
				lines( true );
			} ) );
			$( '[data-copy]', bar ).addEventListener( 'click', copyLog );
		}

		const lineHtml = ( l ) => `<div class="wpe-line wpe-line--${ esc( l.level ) }"><time>${ new Date( l.time * 1000 ).toLocaleTimeString( [], { hour: '2-digit', minute: '2-digit', second: '2-digit', hourCycle: 'h23' } ) }</time><span class="wpe-line__lv">${ ico( levelIco[ l.level ], 14 ) }</span><span class="wpe-line__msg">${ esc( l.msg ) }${ l.hint ? `<span class="wpe-line__hint">${ esc( l.hint ) }</span>` : '' }</span></div>`;

		function lines( full, fresh = [] ) {
			const box = $( '[data-lines]', body );
			if ( ! box ) {
				return;
			}
			const show = ( l ) => w.filter === 'all' || l.level === w.filter;
			if ( full ) {
				box.innerHTML = w.logs.filter( show ).map( lineHtml ).join( '' );
			} else {
				box.insertAdjacentHTML( 'beforeend', fresh.filter( show ).map( lineHtml ).join( '' ) );
			}
			box.scrollTop = box.scrollHeight;
		}

		function localLog( level, msg, hint = '' ) {
			const l = { seq: 0, time: Date.now() / 1000, level, msg, hint };
			w.logs.push( l );
			lines( false, [ l ] );
			filters();
		}

		function copyLog() {
			const text = `${ demo.title } · ${ w.builder }\n` + w.logs.map( ( l ) => `[${ new Date( l.time * 1000 ).toISOString() }] ${ l.level.toUpperCase() } ${ l.msg }${ l.hint ? ' — ' + l.hint : '' }` ).join( '\n' );
			const done = () => toast( 'success', __( 'Log copied', 'hi-wpessential' ), __( 'Paste it into your support ticket.', 'hi-wpessential' ) );
			if ( navigator.clipboard ) {
				navigator.clipboard.writeText( text ).then( done, () => {} );
			} else {
				const t = document.createElement( 'textarea' );
				t.value = text;
				document.body.appendChild( t );
				t.select();
				document.execCommand( 'copy' );
				t.remove();
				done();
			}
		}

		function update( job ) {
			w.job = job;
			if ( job.log && job.log.length ) {
				w.logs.push( ...job.log );
				w.since = job.seq;
				lines( false, job.log );
				filters();
			}
			const failed = job.status === 'failed';
			const r = $( '.wpe-ring', body );
			if ( r ) {
				const current = job.steps[ job.index ];
				r.outerHTML = ring( job.progress, failed ? __( 'Stopped', 'hi-wpessential' ) : current ? __( 'Step', 'hi-wpessential' ) + ' ' + ( job.index + 1 ) + '/' + job.steps.length : __( 'Complete', 'hi-wpessential' ), job.status === 'running' ? 'is-running' : failed ? 'is-failed' : '' );
			}
			const tasks = $( '[data-tasks]', body );
			if ( tasks ) {
				tasks.innerHTML = job.steps.map( ( s ) => `<li class="is-${ s.status }"><span class="wpe-tasks__ico">${ s.status === 'done' ? ico( 'check', 12, 3.5 ) : s.status === 'failed' ? ico( 'x', 12, 3.5 ) : s.status === 'skipped' ? ico( 'arrow', 11, 3 ) : '' }</span>${ esc( s.label ) }${ s.total > 1 ? `<span class="wpe-tasks__count">${ s.done }/${ s.total }</span>` : '' }</li>` ).join( '' );
			}
			const el2 = $( '[data-elapsed]', foot );
			if ( el2 ) {
				el2.innerHTML = ico( 'clock', 15 ) + ' ' + esc( sprintf( __( '%ss elapsed', 'hi-wpessential' ), job.elapsed ) );
				el2.style.display = 'inline-flex';
				el2.style.gap = '6px';
				el2.style.alignItems = 'center';
			}
		}

		async function begin() {
			w.running = true;
			try {
				const job = await api( { path: '/wpessential/v1/import/start', method: 'POST', data: { demo: demo.id, builder: w.builder, options: { ...w.opts, plugins: true, skip: w.skip } } } );
				update( job );
				loop();
			} catch ( e ) {
				w.running = false;
				fail( errMsg( e ), '', false );
			}
		}

		async function attach() {
			w.running = true;
			try {
				const job = await api( { path: '/wpessential/v1/import/run', method: 'POST', data: { since: 0, retry: true } } );
				w.logs = [];
				update( job );
				if ( job.status === 'running' ) {
					localLog( 'info', __( 'Resumed from the saved position.', 'hi-wpessential' ) );
				}
				after( job );
			} catch ( e ) {
				w.running = false;
				fail( errMsg( e ), '', true );
			}
		}

		async function loop() {
			let retries = 0;
			while ( ! w.cancelled ) {
				let job;
				try {
					job = await api( { path: '/wpessential/v1/import/run', method: 'POST', data: { since: w.since } } );
					retries = 0;
				} catch ( e ) {
					if ( w.cancelled ) {
						return;
					}
					if ( e.code === 'rest_cookie_invalid_nonce' || e.code === 'rest_forbidden' ) {
						w.running = false;
						fail( errMsg( e ), __( 'Reload the page, then resume the import from the Demo Library.', 'hi-wpessential' ), true );
						return;
					}
					retries++;
					if ( retries > 4 ) {
						w.running = false;
						fail( errMsg( e ), __( 'Your progress is saved. Press Resume when the server is back.', 'hi-wpessential' ), true );
						return;
					}
					const wait = 1500 * Math.pow( 2, retries - 1 );
					localLog( 'warning', sprintf( __( 'The server did not answer (%1$s). Retrying in %2$d seconds…', 'hi-wpessential' ), errMsg( e ), wait / 1000 ) );
					await sleep( wait );
					continue;
				}
				update( job );
				if ( job.status !== 'running' ) {
					after( job );
					return;
				}
				await sleep( 250 );
			}
		}

		function after( job ) {
			if ( job.status === 'running' ) {
				loop();
				return;
			}
			w.running = false;
			if ( job.status === 'done' ) {
				setTimeout( () => show( at( stepDone ) ), 700 );
			} else if ( job.status === 'failed' ) {
				const last = [ ...w.logs ].reverse().find( ( l ) => l.level === 'error' );
				fail( last ? last.msg : __( 'The import stopped.', 'hi-wpessential' ), last ? last.hint : '', true );
			} else if ( job.status === 'cancelled' ) {
				fail( __( 'Import cancelled.', 'hi-wpessential' ), __( 'Resume to finish it, or remove what was imported so far.', 'hi-wpessential' ), false, true );
			}
		}

		function fail( message, hint, canRetry, cancelled = false ) {
			const box = $( '[data-error]', body );
			if ( box ) {
				box.innerHTML = `<div class="wpe-callout wpe-callout--${ cancelled ? 'warning' : 'error' }">${ ico( cancelled ? 'warn' : 'error' ) }<div><b>${ esc( message ) }</b>${ hint ? `<br>${ esc( hint ) }` : '' }</div></div>`;
			}
			const h = $( '[data-title]', body );
			if ( h ) {
				h.textContent = cancelled ? __( 'Import paused', 'hi-wpessential' ) : __( 'The import needs your attention', 'hi-wpessential' );
			}
			foot.innerHTML = `<button class="wpe-btn wpe-btn--danger" data-rollback data-busy="${ esc( __( 'Removing', 'hi-wpessential' ) ) }">${ ico( 'trash', 16 ) }${ esc( __( 'Remove imported content', 'hi-wpessential' ) ) }</button><span class="wpe-grow"></span><button class="wpe-btn wpe-btn--ghost" data-copy2>${ ico( 'copy', 16 ) }${ esc( __( 'Copy log', 'hi-wpessential' ) ) }</button>${ canRetry || cancelled ? `<button class="wpe-btn wpe-btn--primary" data-retry>${ ico( 'refresh', 16 ) }${ esc( __( 'Resume', 'hi-wpessential' ) ) }</button>` : `<button class="wpe-btn wpe-btn--primary" data-again>${ ico( 'back', 16 ) }${ esc( __( 'Back to options', 'hi-wpessential' ) ) }</button>` }`;
			$( '[data-copy2]', foot ).addEventListener( 'click', copyLog );
			const rb = $( '[data-rollback]', foot );
			rb.addEventListener( 'click', () => busy( rb, async () => {
				try {
					if ( w.job && w.job.status === 'running' ) {
						await api( { path: '/wpessential/v1/import/cancel', method: 'POST' } );
					}
					const res = await api( { path: '/wpessential/v1/import/remove', method: 'POST' } );
					toast( 'success', __( 'Everything was rolled back', 'hi-wpessential' ), res.message );
					state.imported = null;
					close( true );
				} catch ( e ) {
					toast( e.code === 'wpe_nothing' ? 'info' : 'error', e.code === 'wpe_nothing' ? __( 'Nothing to remove', 'hi-wpessential' ) : __( 'Rollback failed', 'hi-wpessential' ), errMsg( e ) );
					if ( e.code === 'wpe_nothing' ) {
						close( true );
					}
				}
			} ) );
			const rt = $( '[data-retry]', foot );
			if ( rt ) {
				rt.addEventListener( 'click', () => {
					$( '[data-error]', body ).innerHTML = '';
					foot.innerHTML = `<span class="wpe-muted" data-elapsed></span><span class="wpe-grow"></span><button class="wpe-btn wpe-btn--ghost" data-cancel>${ esc( __( 'Cancel import', 'hi-wpessential' ) ) }</button>`;
					$( '[data-cancel]', foot ).addEventListener( 'click', cancel );
					const h2 = $( '[data-title]', body );
					if ( h2 ) {
						h2.textContent = __( 'Importing your demo…', 'hi-wpessential' );
					}
					resumeRun();
				} );
			}
			const ag = $( '[data-again]', foot );
			if ( ag ) {
				ag.addEventListener( 'click', () => show( at( stepContent ) ) );
			}
		}

		async function resumeRun() {
			w.running = true;
			localLog( 'info', __( 'Resuming…', 'hi-wpessential' ) );
			try {
				const job = await api( { path: '/wpessential/v1/import/run', method: 'POST', data: { since: w.since, retry: true } } );
				update( job );
				after( job );
			} catch ( e ) {
				w.running = false;
				fail( errMsg( e ), __( 'Your progress is saved. Try again in a moment.', 'hi-wpessential' ), true );
			}
		}

		async function cancel() {
			// eslint-disable-next-line no-alert
			if ( ! window.confirm( __( 'Cancel the import? What was imported so far stays until you remove it.', 'hi-wpessential' ) ) ) {
				return;
			}
			w.cancelled = true;
			try {
				const job = await api( { path: '/wpessential/v1/import/cancel', method: 'POST' } );
				w.cancelled = false;
				update( job );
				after( job );
			} catch ( e ) {
				toast( 'error', __( 'Could not cancel', 'hi-wpessential' ), errMsg( e ) );
			}
		}

		/* ---------- done ---------- */

		function stepDone() {
			// Server-environment notes from the preflight are advice, not import problems.
			const notes = w.logs.filter( ( l ) => ( l.level === 'warning' || l.level === 'error' ) && l.step !== 'preflight' );
			const issues = notes.length;
			const mins = w.job ? w.job.elapsed : 0;
			paint( `<div class="wpe-done">
				<span class="wpe-done__badge ${ issues ? 'wpe-done__badge--warn' : '' }"><svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg></span>
				<h2>${ esc( __( 'Your website is ready!', 'hi-wpessential' ) ) }</h2>
				<p>${ esc( issues ? sprintf( __( '%1$s was imported. %2$d item(s) need a look — see the notes below.', 'hi-wpessential' ), demo.title, issues ) : sprintf( __( '%s was imported without a single issue. Time to make it yours.', 'hi-wpessential' ), demo.title ) ) }</p>
				<div class="wpe-done__stats"><div><b>${ mins }s</b><span>${ esc( __( 'Import time', 'hi-wpessential' ) ) }</span></div><div><b>${ w.job ? w.job.steps.filter( ( s ) => s.status === 'done' ).length : 0 }</b><span>${ esc( __( 'Steps completed', 'hi-wpessential' ) ) }</span></div><div><b>${ issues }</b><span>${ esc( __( 'Warnings', 'hi-wpessential' ) ) }</span></div></div>
				<div class="wpe-done__actions"><a class="wpe-btn wpe-btn--grad wpe-btn--lg" href="${ esc( cfg.site ) }" target="_blank" rel="noopener">${ ico( 'eye' ) }${ esc( __( 'View your site', 'hi-wpessential' ) ) }</a><a class="wpe-btn wpe-btn--ghost wpe-btn--lg" href="${ esc( cfg.editor ) }">${ ico( 'brush' ) }${ esc( __( 'Customize', 'hi-wpessential' ) ) }</a></div>
				${ issues ? `<div class="wpe-console" style="text-align:left"><div class="wpe-console__lines">${ notes.map( lineHtml ).join( '' ) }</div></div>` : '' }
			</div>` );
			foot.innerHTML = `<span class="wpe-grow"></span><button class="wpe-btn wpe-btn--ghost" data-finish>${ esc( __( 'Back to dashboard', 'hi-wpessential' ) ) }</button>`;
			$( '[data-finish]', foot ).addEventListener( 'click', () => close( true ) );
			if ( ! issues ) {
				confetti( el.querySelector( '.wpe-modal__panel' ) );
			}
			toast( 'success', __( 'Import complete', 'hi-wpessential' ), demo.title );
		}

		show( w.step );
	}

	function confetti( host ) {
		if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			return;
		}
		const c = document.createElement( 'canvas' );
		c.className = 'wpe-confetti';
		host.appendChild( c );
		const ctx = c.getContext( '2d' );
		const W = ( c.width = host.clientWidth );
		const H = ( c.height = host.clientHeight );
		const colors = [ '#625ba8', '#6188c6', '#5ac8d6', '#f5b547', '#3dd68c', '#ffffff' ];
		const parts = Array.from( { length: 140 }, () => ( { x: W / 2 + ( Math.random() - 0.5 ) * 200, y: H * 0.35, vx: ( Math.random() - 0.5 ) * 14, vy: -Math.random() * 14 - 4, s: Math.random() * 6 + 4, r: Math.random() * 6, vr: ( Math.random() - 0.5 ) * 0.3, c: colors[ Math.floor( Math.random() * colors.length ) ] } ) );
		let t = 0;
		const tick = () => {
			ctx.clearRect( 0, 0, W, H );
			parts.forEach( ( p ) => {
				p.vy += 0.35;
				p.vx *= 0.99;
				p.x += p.vx;
				p.y += p.vy;
				p.r += p.vr;
				ctx.save();
				ctx.translate( p.x, p.y );
				ctx.rotate( p.r );
				ctx.fillStyle = p.c;
				ctx.globalAlpha = Math.max( 0, 1 - t / 160 );
				ctx.fillRect( -p.s / 2, -p.s / 4, p.s, p.s / 2 );
				ctx.restore();
			} );
			if ( ++t < 160 ) {
				window.requestAnimationFrame( tick );
			} else {
				c.remove();
			}
		};
		tick();
	}

	/* ------------------------------------------------------------------ plugins */

	async function viewPlugins() {
		view( `<div class="wpe-head"><div><h1>${ esc( __( 'Plugins', 'hi-wpessential' ) ) }</h1><p>${ esc( __( 'Plugins used by WPEssential demos. Install, switch on or off and delete them here.', 'hi-wpessential' ) ) }</p></div></div><div class="wpe-card"><div class="wpe-card__body"><div class="wpe-list" id="wpe-plugins">${ [ 0, 1, 2 ].map( () => '<div class="wpe-row"><div class="wpe-skel" style="width:40px;height:40px"></div><div class="wpe-row__main"><div class="wpe-skel" style="height:12px;width:30%"></div></div></div>' ).join( '' ) }</div></div></div>` );
		try {
			state.plugins = await api( { path: '/wpessential/v1/plugins' } );
			renderPlugins();
		} catch ( e ) {
			$( '#wpe-plugins' ).innerHTML = `<div class="wpe-callout wpe-callout--error">${ ico( 'error' ) }<div>${ esc( errMsg( e ) ) }</div></div>`;
		}
	}

	function renderPlugins() {
		const box = $( '#wpe-plugins' );
		if ( ! box ) {
			return;
		}
		if ( ! state.plugins.length ) {
			box.innerHTML = `<div class="wpe-empty"><span class="wpe-empty__ico">${ ico( 'plug', 28 ) }</span><h3>${ esc( __( 'No plugins needed', 'hi-wpessential' ) ) }</h3><p>${ esc( __( 'The demos in your library run on the theme alone.', 'hi-wpessential' ) ) }</p></div>`;
			return;
		}
		const btn = ( p, a, label, cls, icon ) => `<button class="wpe-btn wpe-btn--${ cls } wpe-btn--sm" data-act="${ a }" data-slug="${ esc( p.slug ) }" data-busy="${ esc( label ) }">${ ico( icon, 15 ) }${ esc( label ) }</button>`;
		box.innerHTML = state.plugins.map( ( p ) => `<div class="wpe-row"><span class="wpe-row__ico">${ esc( p.name.charAt( 0 ) ) }</span><div class="wpe-row__main"><b>${ esc( p.name ) }</b><span>${ esc( p.state === 'required' ? __( 'Required by some demos', 'hi-wpessential' ) : __( 'Recommended', 'hi-wpessential' ) ) }${ p.version ? ' · v' + esc( p.version ) : '' }</span></div>
			${ { active: `<span class="wpe-pill wpe-pill--success"><i></i>${ esc( __( 'Active', 'hi-wpessential' ) ) }</span>`, inactive: `<span class="wpe-pill wpe-pill--warning"><i></i>${ esc( __( 'Inactive', 'hi-wpessential' ) ) }</span>`, missing: `<span class="wpe-pill"><i></i>${ esc( __( 'Not installed', 'hi-wpessential' ) ) }</span>` }[ p.status ] }
			${ p.status === 'missing' ? btn( p, 'install', __( 'Install & activate', 'hi-wpessential' ), 'primary', 'download' ) : '' }
			${ p.status === 'inactive' ? btn( p, 'activate', __( 'Activate', 'hi-wpessential' ), 'primary', 'check' ) + btn( p, 'delete', __( 'Delete', 'hi-wpessential' ), 'danger', 'trash' ) : '' }
			${ p.status === 'active' ? btn( p, 'deactivate', __( 'Deactivate', 'hi-wpessential' ), 'ghost', 'x' ) : '' }</div>` ).join( '' );
		$$( '[data-act]', box ).forEach( ( b ) => b.addEventListener( 'click', () => busy( b, async () => {
			if ( b.dataset.act === 'delete' && ! window.confirm( __( 'Delete this plugin and its files?', 'hi-wpessential' ) ) ) { // eslint-disable-line no-alert
				return;
			}
			try {
				const res = await api( { path: '/wpessential/v1/plugins/action', method: 'POST', data: { slug: b.dataset.slug, action: b.dataset.act } } );
				state.plugins = state.plugins.map( ( p ) => ( p.slug === res.slug ? { ...p, ...res } : p ) );
				toast( 'success', res.name, { active: __( 'is active.', 'hi-wpessential' ), inactive: __( 'is inactive.', 'hi-wpessential' ), missing: __( 'was deleted.', 'hi-wpessential' ) }[ res.status ] );
				setTimeout( renderPlugins, 0 );
			} catch ( e ) {
				toast( 'error', __( 'Plugin action failed', 'hi-wpessential' ), errMsg( e ) );
			}
		} ) ) );
	}

	/* ------------------------------------------------------------------ license */

	async function viewLicense() {
		view( `<div class="wpe-head"><div><h1>${ esc( __( 'License', 'hi-wpessential' ) ) }</h1><p>${ esc( __( 'Activate your purchase on this domain to unlock Pro demos, automatic updates and priority support.', 'hi-wpessential' ) ) }</p></div></div><div class="wpe-license" id="wpe-license"><div class="wpe-skel" style="height:240px;border-radius:24px"></div><div class="wpe-skel" style="height:240px;border-radius:20px"></div></div><div id="wpe-account" style="margin-top:24px"></div>` );
		renderAccount( $( '#wpe-account' ), { onLicensed: () => loadLicense().then( () => renderLicense() ) } );
		try {
			await loadLicense();
			renderLicense();
		} catch ( e ) {
			$( '#wpe-license' ).innerHTML = `<div class="wpe-callout wpe-callout--error">${ ico( 'error' ) }<div>${ esc( errMsg( e ) ) }</div></div>`;
		}
	}

	function renderLicense( error = '' ) {
		const l = state.license;
		const box = $( '#wpe-license' );
		if ( ! box ) {
			return;
		}
		const active = l.status === 'active';
		const label = { active: __( 'Active', 'hi-wpessential' ), expired: __( 'Expired', 'hi-wpessential' ), invalid: __( 'Invalid', 'hi-wpessential' ) }[ l.status ] || __( 'Not connected', 'hi-wpessential' );
		box.innerHTML = `
			<div class="wpe-lic-card" data-status="${ esc( l.status ) }">
				<div class="wpe-lic-card__top"><span class="wpe-brand"><span class="wpe-brand__mark">${ ico( 'logo', 20 ) }</span>${ esc( cfg.brand ) } ${ esc( l.plan || 'Pro' ) }</span><span class="wpe-pill ${ active ? 'wpe-pill--success' : l.key ? 'wpe-pill--warning' : '' }" style="${ active || l.key ? '' : 'background:rgb(255 255 255 / 14%);color:#fff;border-color:transparent' }"><i></i>${ esc( label ) }</span></div>
				<div class="wpe-lic-card__key">${ esc( l.key || '•••• •••• •••• ••••' ) }</div>
				<div class="wpe-lic-card__meta"><div>${ esc( __( 'Domain', 'hi-wpessential' ) ) }<b>${ esc( l.domain ) }</b></div><div>${ esc( __( 'Expires', 'hi-wpessential' ) ) }<b>${ esc( l.expires ? ( l.expires === 'lifetime' ? __( 'Lifetime', 'hi-wpessential' ) : l.expires ) : '—' ) }</b></div><div>${ esc( __( 'Provider', 'hi-wpessential' ) ) }<b>${ esc( l.provider ) }</b></div></div>
			</div>
			<div class="wpe-card"><div class="wpe-card__body">
				${ active ? `<h2 style="font-size:20px">${ esc( __( 'You’re all set', 'hi-wpessential' ) ) }</h2><p class="wpe-muted" style="margin-top:6px">${ esc( sprintf( __( 'This license is active on %s.', 'hi-wpessential' ), l.domain ) ) }${ l.plan ? ' ' + esc( sprintf( __( 'Plan: %s.', 'hi-wpessential' ), l.plan ) ) : '' }</p>
					<ul class="wpe-perks">${ [ __( 'All Pro demos unlocked', 'hi-wpessential' ), __( 'One-click theme updates', 'hi-wpessential' ), __( 'Priority support', 'hi-wpessential' ) ].map( ( t ) => `<li>${ ico( 'ok', 18 ) }${ esc( t ) }</li>` ).join( '' ) }</ul>
					<div style="display:flex;gap:10px;margin-top:24px;flex-wrap:wrap"><button class="wpe-btn wpe-btn--danger" data-deact data-busy="${ esc( __( 'Deactivating', 'hi-wpessential' ) ) }">${ esc( __( 'Deactivate on this domain', 'hi-wpessential' ) ) }</button></div>`
		: `<h2 style="font-size:20px">${ esc( l.status === 'expired' ? __( 'Renew to keep Pro features', 'hi-wpessential' ) : __( 'Activate your license', 'hi-wpessential' ) ) }</h2><p class="wpe-muted" style="margin-top:6px">${ esc( __( 'Find the key in your purchase email or your wpessential.org account.', 'hi-wpessential' ) ) }</p>
					<form class="wpe-lic-form" data-form><label class="wpe-field"><span class="wpe-sr">${ esc( __( 'License key', 'hi-wpessential' ) ) }</span><input type="password" autocomplete="off" spellcheck="false" placeholder="${ esc( __( 'Paste your license key', 'hi-wpessential' ) ) }" data-key required><button type="button" class="wpe-btn wpe-btn--ghost wpe-btn--sm wpe-btn--icon" data-reveal aria-label="${ esc( __( 'Show key', 'hi-wpessential' ) ) }">${ ico( 'eye', 15 ) }</button></label><button class="wpe-btn wpe-btn--primary wpe-btn--lg" type="submit" data-busy="${ esc( __( 'Checking', 'hi-wpessential' ) ) }">${ ico( 'key', 17 ) }${ esc( __( 'Activate', 'hi-wpessential' ) ) }</button></form>
					${ error ? `<div class="wpe-callout wpe-callout--error" role="alert">${ ico( 'error' ) }<div>${ esc( error ) }</div></div>` : l.message ? `<div class="wpe-callout wpe-callout--warning">${ ico( 'warn' ) }<div>${ esc( l.message ) }</div></div>` : '' }
					<ul class="wpe-perks">${ [ __( 'Unlock every Pro demo in the library', 'hi-wpessential' ), __( 'Automatic updates from your dashboard', 'hi-wpessential' ), __( 'Priority support from the WPEssential Team', 'hi-wpessential' ) ].map( ( t ) => `<li>${ ico( 'ok', 18 ) }${ esc( t ) }</li>` ).join( '' ) }</ul>
					<p style="margin-top:20px"><a href="#wpe-account" data-jump>${ esc( __( 'Don’t have a license? Sign in or pick a plan below', 'hi-wpessential' ) ) } ↓</a></p>` }
			</div></div>`;
		const form = $( '[data-form]', box );
		if ( form ) {
			$( '[data-reveal]', form ).addEventListener( 'click', ( e ) => {
				const i = $( '[data-key]', form );
				i.type = i.type === 'password' ? 'text' : 'password';
				e.currentTarget.innerHTML = ico( i.type === 'password' ? 'eye' : 'lock', 15 );
			} );
			form.addEventListener( 'submit', ( e ) => {
				e.preventDefault();
				const btn = $( '[type="submit"]', form );
				busy( btn, async () => {
					try {
						state.license = await api( { path: '/wpessential/v1/license/activate', method: 'POST', data: { key: $( '[data-key]', form ).value } } );
						toast( 'success', __( 'License activated', 'hi-wpessential' ), __( 'Pro demos are unlocked.', 'hi-wpessential' ) );
						state.demos = null;
						setTimeout( () => renderLicense(), 0 );
					} catch ( err ) {
						setTimeout( () => renderLicense( errMsg( err ) ), 0 );
					}
				} );
			} );
		}
		const jump = $( '[data-jump]', box );
		if ( jump ) {
			jump.addEventListener( 'click', ( e ) => {
				e.preventDefault();
				$( '#wpe-account' ).scrollIntoView( { behavior: 'smooth' } );
			} );
		}
		const de = $( '[data-deact]', box );
		if ( de ) {
			de.addEventListener( 'click', () => {
				// eslint-disable-next-line no-alert
				if ( ! window.confirm( __( 'Deactivate the license on this domain? You can activate it on another site afterwards.', 'hi-wpessential' ) ) ) {
					return;
				}
				busy( de, async () => {
					try {
						state.license = await api( { path: '/wpessential/v1/license/deactivate', method: 'POST' } );
						toast( 'info', __( 'License deactivated', 'hi-wpessential' ) );
						state.demos = null;
						setTimeout( () => renderLicense(), 0 );
					} catch ( err ) {
						toast( 'error', __( 'Deactivation failed', 'hi-wpessential' ), errMsg( err ) );
					}
				} );
			} );
		}
	}


	/* ------------------------------------------------------------------ account */

	async function loadAccount() {
		state.account = await api( { path: '/wpessential/v1/account' } );
		return state.account;
	}

	const period = ( p ) => ( p.months > 0 ? sprintf( __( '/ %d mo', 'hi-wpessential' ), p.months ) : __( 'one-time', 'hi-wpessential' ) );
	const money = ( p ) => new Intl.NumberFormat( undefined, { style: 'currency', currency: p.currency || 'USD', maximumFractionDigits: p.price % 1 ? 2 : 0 } ).format( p.price );

	async function renderAccount( box, opts = {} ) {
		if ( ! box ) {
			return;
		}
		box.innerHTML = '<div class="wpe-card"><div class="wpe-card__body"><div class="wpe-skel" style="height:18px;width:30%;margin-bottom:12px"></div><div class="wpe-skel" style="height:120px"></div></div></div>';
		try {
			await loadAccount();
		} catch ( e ) {
			state.account = { signed_in: false };
			if ( e.code !== 'wpe_signed_out' ) {
				box.innerHTML = `<div class="wpe-callout wpe-callout--warning">${ ico( 'warn' ) }<div><b>${ esc( __( 'Your WPEssential account could not be reached', 'hi-wpessential' ) ) }</b><br>${ esc( errMsg( e ) ) }</div></div>`;
				return;
			}
		}
		paintAccount( box, opts );
	}

	function paintAccount( box, opts, error = '' ) {
		const a = state.account || { signed_in: false };
		if ( ! a.signed_in ) {
			const mode = box.dataset.mode || 'login';
			box.innerHTML = `<div class="wpe-card wpe-auth">
				<div class="wpe-auth__aside"><span class="wpe-brand__mark">${ ico( 'logo', 22 ) }</span><h3>${ esc( mode === 'login' ? __( 'Sign in to your account', 'hi-wpessential' ) : __( 'Create your free account', 'hi-wpessential' ) ) }</h3><p>${ esc( __( 'Your licenses, Pro demos and subscription in one place. New accounts include the Free plan for this website.', 'hi-wpessential' ) ) }</p>
					<ul class="wpe-perks">${ [ __( 'Activate licenses without copying keys', 'hi-wpessential' ), __( 'Upgrade, renew or switch plans in one click', 'hi-wpessential' ), __( 'Manage websites and auto-renewal', 'hi-wpessential' ) ].map( ( t ) => `<li>${ ico( 'ok', 18 ) }${ esc( t ) }</li>` ).join( '' ) }</ul></div>
				<form class="wpe-auth__form" data-auth novalidate>
					<div class="wpe-seg" role="tablist"><button type="button" data-mode="login" aria-pressed="${ mode === 'login' }">${ esc( __( 'Sign in', 'hi-wpessential' ) ) }</button><button type="button" data-mode="register" aria-pressed="${ mode === 'register' }">${ esc( __( 'Create account', 'hi-wpessential' ) ) }</button></div>
					${ mode === 'register' ? `<label class="wpe-label">${ esc( __( 'Full name', 'hi-wpessential' ) ) }<input type="text" name="name" autocomplete="name" required></label>` : '' }
					<label class="wpe-label">${ esc( __( 'Email', 'hi-wpessential' ) ) }<input type="text" name="${ mode === 'login' ? 'login' : 'email' }" autocomplete="email" inputmode="email" required></label>
					<label class="wpe-label">${ esc( __( 'Password', 'hi-wpessential' ) ) }<input type="password" name="password" autocomplete="${ mode === 'login' ? 'current-password' : 'new-password' }" minlength="8" required></label>
					${ error ? `<div class="wpe-callout wpe-callout--error" role="alert">${ ico( 'error' ) }<div>${ esc( error ) }</div></div>` : '' }
					<button class="wpe-btn wpe-btn--primary wpe-btn--lg" type="submit" data-busy="${ esc( mode === 'login' ? __( 'Signing in', 'hi-wpessential' ) : __( 'Creating account', 'hi-wpessential' ) ) }">${ esc( mode === 'login' ? __( 'Sign in', 'hi-wpessential' ) : __( 'Create free account', 'hi-wpessential' ) ) }${ ico( 'arrow', 17 ) }</button>
					${ mode === 'login' ? `<a class="wpe-auth__link" href="https://wpessential.org/my-account/lost-password/" target="_blank" rel="noopener">${ esc( __( 'Forgot password?', 'hi-wpessential' ) ) }</a>` : `<p class="wpe-auth__link wpe-muted">${ esc( __( 'By creating an account you agree to the Hi WPEssential terms.', 'hi-wpessential' ) ) }</p>` }
					${ opts.skip ? `<button type="button" class="wpe-btn wpe-btn--ghost" data-skip-auth>${ esc( __( 'Continue without an account', 'hi-wpessential' ) ) }</button>` : '' }
				</form></div>`;
			$$( '[data-mode]', box ).forEach( ( b ) => b.addEventListener( 'click', () => {
				box.dataset.mode = b.dataset.mode;
				paintAccount( box, opts );
			} ) );
			const skip = $( '[data-skip-auth]', box );
			if ( skip ) {
				skip.addEventListener( 'click', opts.skip );
			}
			const form = $( '[data-auth]', box );
			form.addEventListener( 'submit', ( e ) => {
				e.preventDefault();
				const data = Object.fromEntries( new FormData( form ) );
				const empty = Object.entries( data ).find( ( [ , v ] ) => ! String( v ).trim() );
				if ( empty ) {
					paintAccount( box, opts, __( 'Fill in every field.', 'hi-wpessential' ) );
					return;
				}
				busy( $( '[type="submit"]', form ), async () => {
					try {
						state.account = await api( { path: '/wpessential/v1/account/' + ( mode === 'login' ? 'login' : 'register' ), method: 'POST', data } );
						toast( 'success', mode === 'login' ? __( 'Signed in', 'hi-wpessential' ) : __( 'Account created', 'hi-wpessential' ), state.account.user.email );
						state.demos = null;
						setTimeout( () => {
							paintAccount( box, opts );
							if ( opts.onLicensed ) {
								opts.onLicensed();
							}
						}, 0 );
					} catch ( err ) {
						setTimeout( () => paintAccount( box, opts, errMsg( err ) ), 0 );
					}
				} );
			} );
			return;
		}

		const statusPill = ( l ) => ( { active: 'success', expired: 'warning', cancelled: 'warning', revoked: 'danger' }[ l.status ] || '' );
		const rank = ( k ) => [ 'free', 'monthly', 'quarterly', 'yearly', 'lifetime', 'enterprise', 'white_label' ].indexOf( k );
		const best = ( a.licenses || [] ).filter( ( l ) => l.status === 'active' ).sort( ( x, y ) => rank( y.plan_key ) - rank( x.plan_key ) )[ 0 ];
		box.innerHTML = `<div class="wpe-card"><div class="wpe-card__body">
			<div class="wpe-acct-head"><span class="wpe-avatar">${ esc( ( a.user.name || a.user.email ).charAt( 0 ).toUpperCase() ) }<img src="${ esc( a.user.avatar ) }" alt="" width="44" height="44" onerror="this.remove()"></span><div><b>${ esc( a.user.name || a.user.email ) }</b><span class="wpe-muted">${ esc( a.user.email ) }</span></div><span class="wpe-grow"></span>
				<button class="wpe-btn wpe-btn--ghost wpe-btn--sm" data-acct-refresh>${ ico( 'refresh', 15 ) }${ esc( __( 'Refresh', 'hi-wpessential' ) ) }</button>
				${ a.account ? `<a class="wpe-btn wpe-btn--ghost wpe-btn--sm" href="${ esc( a.account ) }" target="_blank" rel="noopener">${ ico( 'ext', 15 ) }${ esc( __( 'Manage account', 'hi-wpessential' ) ) }</a>` : '' }
				<button class="wpe-btn wpe-btn--ghost wpe-btn--sm" data-logout>${ esc( __( 'Sign out', 'hi-wpessential' ) ) }</button></div>
			${ error ? `<div class="wpe-callout wpe-callout--error" role="alert">${ ico( 'error' ) }<div>${ esc( error ) }</div></div>` : '' }
			<h3 class="wpe-acct-title">${ esc( __( 'Your licenses', 'hi-wpessential' ) ) }</h3>
			<div class="wpe-list">${ ( a.licenses || [] ).map( ( l ) => `<div class="wpe-row wpe-lic-row ${ l.here ? 'is-here' : '' }">
				<span class="wpe-row__ico">${ ico( 'key', 18 ) }</span>
				<div class="wpe-row__main"><b>${ esc( l.plan ) } <span class="wpe-pill wpe-pill--${ statusPill( l ) }"><i></i>${ esc( l.status ) }</span></b><span>${ esc( l.mask ) } · ${ esc( sprintf( __( '%1$d of %2$s websites', 'hi-wpessential' ), l.sites_used, l.sites_limit ? l.sites_limit : '∞' ) ) } · ${ esc( l.expires === 'lifetime' ? __( 'Never expires', 'hi-wpessential' ) : sprintf( l.auto_renew ? __( 'Renews %s', 'hi-wpessential' ) : __( 'Ends %s', 'hi-wpessential' ), l.expires ) ) }</span></div>
				${ l.expires !== 'lifetime' && l.plan_key !== 'free' && l.status === 'active' ? `<label class="wpe-switch" title="${ esc( __( 'Auto-renew', 'hi-wpessential' ) ) }"><input type="checkbox" data-renew="${ l.id }" ${ l.auto_renew ? 'checked' : '' } aria-label="${ esc( __( 'Auto-renew', 'hi-wpessential' ) ) }"><span></span></label>` : '' }
				${ l.status !== 'active' && l.plan_key !== 'free' ? `<button class="wpe-btn wpe-btn--primary wpe-btn--sm" data-buy="${ esc( l.plan_key ) }" data-license="${ l.id }" data-mode="renew">${ esc( __( 'Renew', 'hi-wpessential' ) ) }</button>` : l.here ? `<span class="wpe-pill wpe-pill--success">${ ico( 'check', 12, 3 ) }${ esc( __( 'This site', 'hi-wpessential' ) ) }</span>` : `<button class="wpe-btn wpe-btn--soft wpe-btn--sm" data-use="${ l.id }" data-busy="${ esc( __( 'Activating', 'hi-wpessential' ) ) }">${ esc( __( 'Use on this site', 'hi-wpessential' ) ) }</button>` }
			</div>` ).join( '' ) || `<p class="wpe-muted">${ esc( __( 'No licenses yet — choose a plan below.', 'hi-wpessential' ) ) }</p>` }</div>
			<h3 class="wpe-acct-title">${ esc( best && best.plan_key !== 'free' ? __( 'Upgrade your plan', 'hi-wpessential' ) : __( 'Choose a plan', 'hi-wpessential' ) ) }</h3>
			<div class="wpe-plans">${ ( a.plans || [] ).map( ( p ) => {
				const owned = best && best.plan_key === p.key;
				const lower = best && rank( p.key ) < rank( best.plan_key ) && best.plan_key !== 'free';
				const upgrade = best && best.plan_key !== 'free' && ! owned && ! lower;
				return `<article class="wpe-plan ${ p.key === 'yearly' ? 'is-featured' : '' } ${ owned ? 'is-owned' : '' }">${ p.key === 'yearly' ? `<span class="wpe-plan__flag">${ esc( __( 'Most popular', 'hi-wpessential' ) ) }</span>` : '' }<h4>${ esc( p.name ) }</h4><p class="wpe-plan__price">${ esc( money( p ) ) }<small>${ esc( period( p ) ) }</small></p><ul>${ p.features.map( ( f ) => `<li>${ ico( 'check', 14, 3 ) }${ esc( f ) }</li>` ).join( '' ) }</ul>
					${ owned ? `<span class="wpe-pill wpe-pill--success" style="justify-content:center">${ esc( __( 'Current plan', 'hi-wpessential' ) ) }</span>` : `<button class="wpe-btn ${ p.key === 'yearly' ? 'wpe-btn--grad' : 'wpe-btn--ghost' }" data-buy="${ esc( p.key ) }" ${ upgrade ? `data-license="${ best.id }" data-mode="upgrade"` : '' } ${ lower ? 'disabled' : '' }>${ esc( upgrade ? __( 'Upgrade', 'hi-wpessential' ) : __( 'Choose', 'hi-wpessential' ) ) }</button>` }</article>`;
			} ).join( '' ) }</div>
			<p class="wpe-muted" style="margin-top:14px;font-size:12px">${ esc( __( 'Checkout opens on wpessential.org already signed in. Upgrades credit the unused part of your current plan. Staging and local sites do not count toward your website limit.', 'hi-wpessential' ) ) }</p>
		</div></div>`;

		const refresh = () => renderAccount( box, opts );
		$( '[data-acct-refresh]', box ).addEventListener( 'click', refresh );
		$( '[data-logout]', box ).addEventListener( 'click', async () => {
			await api( { path: '/wpessential/v1/account/logout', method: 'POST' } ).catch( () => {} );
			state.account = { signed_in: false };
			paintAccount( box, opts );
		} );
		$$( '[data-use]', box ).forEach( ( b ) => b.addEventListener( 'click', () => busy( b, async () => {
			try {
				const res = await api( { path: '/wpessential/v1/account/use', method: 'POST', data: { license_id: b.dataset.use } } );
				state.license = res.license;
				state.account = res.account;
				state.demos = null;
				toast( 'success', __( 'License active on this site', 'hi-wpessential' ) );
				setTimeout( () => {
					paintAccount( box, opts );
					if ( opts.onLicensed ) {
						opts.onLicensed();
					}
				}, 0 );
			} catch ( e ) {
				setTimeout( () => paintAccount( box, opts, errMsg( e ) ), 0 );
			}
		} ) ) );
		$$( '[data-renew]', box ).forEach( ( c ) => c.addEventListener( 'change', async () => {
			try {
				state.account = await api( { path: '/wpessential/v1/account/action', method: 'POST', data: { license_id: c.dataset.renew, action: c.checked ? 'auto_renew_on' : 'auto_renew_off' } } );
				toast( 'success', c.checked ? __( 'Auto-renew is on', 'hi-wpessential' ) : __( 'Auto-renew is off', 'hi-wpessential' ), c.checked ? '' : __( 'Your license works until its end date.', 'hi-wpessential' ) );
			} catch ( e ) {
				c.checked = ! c.checked;
				toast( 'error', __( 'Could not change auto-renew', 'hi-wpessential' ), errMsg( e ) );
			}
		} ) );
		$$( '[data-buy]', box ).forEach( ( b ) => b.addEventListener( 'click', () => busy( b, async () => {
			const win = window.open( 'about:blank', '_blank' );
			try {
				const res = await api( { path: '/wpessential/v1/account/checkout', method: 'POST', data: { plan: b.dataset.buy, license_id: b.dataset.license || 0, mode: b.dataset.mode || 'buy' } } );
				if ( win ) {
					win.location = res.url;
				} else {
					window.location = res.url;
				}
				toast( 'info', __( 'Checkout opened in a new tab', 'hi-wpessential' ), __( 'When payment is done, press Refresh here.', 'hi-wpessential' ) );
			} catch ( e ) {
				if ( win ) {
					win.close();
				}
				toast( 'error', __( 'Checkout could not start', 'hi-wpessential' ), errMsg( e ) );
			}
		} ) ) );
	}

	/* ------------------------------------------------------------------ status */

	async function viewStatus() {
		view( `<div class="wpe-head"><div><h1>${ esc( __( 'System Status', 'hi-wpessential' ) ) }</h1><p>${ esc( __( 'What your server offers and what to improve before importing.', 'hi-wpessential' ) ) }</p></div><button class="wpe-btn wpe-btn--ghost" data-copy-report>${ ico( 'copy', 16 ) }${ esc( __( 'Copy report', 'hi-wpessential' ) ) }</button></div><div class="wpe-status" id="wpe-status"><div class="wpe-skel" style="height:300px;border-radius:20px"></div><div class="wpe-skel" style="height:420px;border-radius:20px"></div></div>` );
		try {
			const st = await loadStatus();
			const score = healthScore( st.checks );
			const fails = st.checks.filter( ( c ) => c.state !== 'ok' ).length;
			$( '#wpe-status' ).innerHTML = `
				<div class="wpe-card wpe-health">${ ring( score, __( 'Health', 'hi-wpessential' ) ) }<h3>${ esc( score >= 90 ? __( 'Excellent', 'hi-wpessential' ) : score >= 70 ? __( 'Good', 'hi-wpessential' ) : __( 'Needs work', 'hi-wpessential' ) ) }</h3><p class="wpe-muted" style="margin-top:6px">${ esc( fails ? sprintf( __( '%d item(s) could be better. Imports still run; fixes make them faster.', 'hi-wpessential' ), fails ) : __( 'Your server is ready for any demo.', 'hi-wpessential' ) ) }</p></div>
				<div class="wpe-grid">
					<div class="wpe-card wpe-checks">${ st.checks.map( ( c ) => `<div class="wpe-row"><span class="wpe-state wpe-state--${ c.state }">${ ico( c.state === 'ok' ? 'check' : c.state === 'warn' ? 'warn' : 'x', 15, 2.5 ) }</span><div class="wpe-row__main"><b>${ esc( c.label ) }</b>${ c.hint ? `<span>${ esc( c.hint ) }</span>` : '' }</div><span class="wpe-pill ${ c.state === 'ok' ? '' : c.state === 'warn' ? 'wpe-pill--warning' : 'wpe-pill--danger' }">${ esc( c.value ) }</span></div>` ).join( '' ) }</div>
					<div class="wpe-card"><div class="wpe-card__body" style="padding-bottom:12px"><h3 style="font-size:16px">${ esc( __( 'Environment', 'hi-wpessential' ) ) }</h3></div><table class="wpe-env">${ st.environment.map( ( [ k, v ] ) => `<tr><td>${ esc( k ) }</td><td>${ esc( v ) }</td></tr>` ).join( '' ) }</table></div>
				</div>`;
			$( '[data-copy-report]' ).addEventListener( 'click', () => {
				const text = st.checks.map( ( c ) => `${ c.label }: ${ c.value } [${ c.state }]` ).concat( st.environment.map( ( [ k, v ] ) => `${ k }: ${ v }` ) ).join( '\n' );
				navigator.clipboard.writeText( text ).then( () => toast( 'success', __( 'Report copied', 'hi-wpessential' ) ), () => toast( 'error', __( 'Copy failed', 'hi-wpessential' ) ) );
			} );
		} catch ( e ) {
			$( '#wpe-status' ).innerHTML = `<div class="wpe-callout wpe-callout--error">${ ico( 'error' ) }<div>${ esc( errMsg( e ) ) }</div></div>`;
		}
	}

	/* ------------------------------------------------------------------ boot */

	shell();
	go( ( window.location.hash || '#welcome' ).slice( 1 ), false );
	window.addEventListener( 'hashchange', () => go( window.location.hash.slice( 1 ), false ) );
}() );
