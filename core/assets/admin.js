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
		logo: '<path d="M12 2 3 7v10l9 5 9-5V7l-9-5Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="m3 7 9 5 9-5M12 12v10" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
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
			return __( 'Something went wrong.', 'wpessential' );
		}
		if ( e.code === 'fetch_error' || e.name === 'TypeError' ) {
			return __( 'The connection to your site dropped. Check your internet and try again.', 'wpessential' );
		}
		if ( e.code === 'invalid_json' ) {
			return __( 'The server sent an invalid response — usually a PHP error or a timeout. Your progress is saved.', 'wpessential' );
		}
		if ( e.code === 'rest_cookie_invalid_nonce' ) {
			return __( 'Your login session expired. Reload the page and sign in again.', 'wpessential' );
		}
		return e.message || __( 'Something went wrong.', 'wpessential' );
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
		[ 'welcome', __( 'Dashboard', 'wpessential' ), 'home' ],
		[ 'demos', __( 'Demo Library', 'wpessential' ), 'grid' ],
		[ 'plugins', __( 'Plugins', 'wpessential' ), 'plug' ],
		[ 'license', __( 'License', 'wpessential' ), 'key' ],
		[ 'status', __( 'System Status', 'wpessential' ), 'pulse' ],
	];

	function shell() {
		root.dataset.mode = state.mode;
		root.innerHTML = `
			<header class="wpe-top">
				<div class="wpe-brand"><span class="wpe-brand__mark">${ ico( 'logo', 20 ) }</span><span class="wpe-brand__name">WPEssential</span><span class="wpe-brand__ver">v${ esc( cfg.version ) }</span></div>
				<nav class="wpe-tabs" aria-label="${ esc( __( 'Dashboard sections', 'wpessential' ) ) }">
					${ TABS.map( ( [ id, label, icon ] ) => `<button class="wpe-tab" data-tab="${ id }">${ ico( icon, 17 ) }${ esc( label ) }</button>` ).join( '' ) }
				</nav>
				<div class="wpe-top__end">
					<a class="wpe-btn wpe-btn--ghost wpe-btn--sm" href="${ esc( cfg.docs ) }" target="_blank" rel="noopener">${ ico( 'book', 16 ) }<span>${ esc( __( 'Docs', 'wpessential' ) ) }</span></a>
					<button class="wpe-btn wpe-btn--ghost wpe-btn--sm wpe-btn--icon" data-mode-toggle aria-label="${ esc( __( 'Switch dashboard colour mode', 'wpessential' ) ) }">${ ico( state.mode === 'dark' ? 'sun' : 'moon', 16 ) }</button>
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
		return `<div class="wpe-ring ${ cls }"><svg viewBox="0 0 200 200"><defs><linearGradient id="wpe-grad" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#4f46e5"/><stop offset=".6" stop-color="#7c3aed"/><stop offset="1" stop-color="#db2777"/></linearGradient></defs><circle class="wpe-ring__track" cx="100" cy="100" r="${ r }"/><circle class="wpe-ring__bar" cx="100" cy="100" r="${ r }" stroke-dasharray="${ c }" stroke-dashoffset="${ c - ( c * pct ) / 100 }"/></svg><div class="wpe-ring__label"><b>${ pct }<small>%</small></b><span>${ esc( label ) }</span></div></div>`;
	};

	/* ------------------------------------------------------------------ welcome */

	async function viewWelcome() {
		const name = cfg.user.split( ' ' )[ 0 ];
		view( `
			<section class="wpe-hero">
				<div>
					<span class="wpe-hero__chip"><b>${ esc( __( 'NEW', 'wpessential' ) ) }</b>${ esc( sprintf( __( '%s is active', 'wpessential' ), cfg.theme.name ) ) }</span>
					<h1>${ esc( sprintf( __( 'Welcome, %s. Let’s build something beautiful.', 'wpessential' ), name ) ) }</h1>
					<p>${ esc( __( 'Pick a demo, choose Gutenberg or Elementor, and get a finished website with pages, images, menus and a shop — in about a minute.', 'wpessential' ) ) }</p>
					<div class="wpe-hero__actions">
						<button class="wpe-btn wpe-btn--white wpe-btn--lg" data-go="demos">${ ico( 'grid' ) }${ esc( __( 'Browse demos', 'wpessential' ) ) }</button>
						<a class="wpe-btn wpe-btn--glass wpe-btn--lg" href="${ esc( cfg.editor ) }">${ ico( 'brush' ) }${ esc( __( 'Customize site', 'wpessential' ) ) }</a>
					</div>
				</div>
				<div class="wpe-hero__visual">
					<img src="${ esc( cfg.theme.shot ) }" alt="">
					<div class="wpe-float wpe-float--a"><span class="wpe-float__ico">${ ico( 'check', 16, 3 ) }</span><div>${ esc( __( 'Demo imported', 'wpessential' ) ) }<small>${ esc( __( 'in 48 seconds', 'wpessential' ) ) }</small></div></div>
					<div class="wpe-float wpe-float--b"><span class="wpe-float__ico">${ ico( 'layers', 16 ) }</span><div>${ esc( __( 'Two builders', 'wpessential' ) ) }<small>Gutenberg · Elementor</small></div></div>
				</div>
			</section>
			<div class="wpe-grid wpe-stats" id="wpe-stats">${ [ 0, 1, 2, 3 ].map( () => '<div class="wpe-card wpe-stat"><div class="wpe-skel" style="width:44px;height:44px"></div><div style="flex:1"><div class="wpe-skel" style="height:10px;width:60%;margin-bottom:8px"></div><div class="wpe-skel" style="height:14px;width:80%"></div></div></div>' ).join( '' ) }</div>
			<div class="wpe-grid wpe-split">
				<section class="wpe-card"><div class="wpe-card__body">
					<span class="wpe-eyebrow">${ esc( __( 'Get started', 'wpessential' ) ) }</span>
					<h2 style="font-size:20px;margin-top:6px">${ esc( __( 'Three steps to launch', 'wpessential' ) ) }</h2>
					<ol class="wpe-steps" id="wpe-steps"></ol>
				</div></section>
				<section class="wpe-card"><div class="wpe-card__body">
					<span class="wpe-eyebrow">${ esc( __( 'Resources', 'wpessential' ) ) }</span>
					<h2 style="font-size:20px;margin-top:6px">${ esc( __( 'Help when you need it', 'wpessential' ) ) }</h2>
					<div class="wpe-links">
						<a class="wpe-link" href="${ esc( cfg.docs ) }" target="_blank" rel="noopener"><span class="wpe-stat__ico">${ ico( 'book' ) }</span><div><b>${ esc( __( 'Documentation', 'wpessential' ) ) }</b><span>${ esc( __( 'Guides for every feature', 'wpessential' ) ) }</span></div>${ ico( 'ext', 16 ) }</a>
						<a class="wpe-link" href="${ esc( cfg.support ) }" target="_blank" rel="noopener"><span class="wpe-stat__ico wpe-stat__ico--green">${ ico( 'help' ) }</span><div><b>${ esc( __( 'Support', 'wpessential' ) ) }</b><span>${ esc( __( 'Real people, fast answers', 'wpessential' ) ) }</span></div>${ ico( 'ext', 16 ) }</a>
						<a class="wpe-link" href="${ esc( cfg.editor ) }"><span class="wpe-stat__ico wpe-stat__ico--pink">${ ico( 'brush' ) }</span><div><b>${ esc( __( 'Site Editor', 'wpessential' ) ) }</b><span>${ esc( __( 'Colours, fonts, header and footer', 'wpessential' ) ) }</span></div>${ ico( 'arrow', 16 ) }</a>
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
			const licLabel = { active: __( 'Active', 'wpessential' ), expired: __( 'Expired', 'wpessential' ), invalid: __( 'Invalid', 'wpessential' ) }[ lic.status ] || __( 'Not connected', 'wpessential' );
			$( '#wpe-stats' ).innerHTML = [
				[ 'layers', '', __( 'Theme', 'wpessential' ), cfg.theme.name + ' ' + cfg.theme.version ],
				[ 'grid', '--pink', __( 'Imported demo', 'wpessential' ), state.imported ? state.imported.title : __( 'None yet', 'wpessential' ) ],
				[ 'key', lic.status === 'active' ? '--green' : '--amber', __( 'License', 'wpessential' ), licLabel ],
				[ 'pulse', score >= 80 ? '--green' : '--amber', __( 'System health', 'wpessential' ), score + '%' ],
			].map( ( [ i, c, l, v ] ) => `<div class="wpe-card wpe-stat"><span class="wpe-stat__ico ${ c ? 'wpe-stat__ico' + c : '' }">${ ico( i, 20 ) }</span><div><small>${ esc( l ) }</small><strong>${ esc( v ) }</strong></div></div>` ).join( '' );
			const steps = [
				[ !! state.imported, __( 'Import a demo', 'wpessential' ), __( 'Start from a complete, ready-made website.', 'wpessential' ), 'demos', __( 'Open library', 'wpessential' ) ],
				[ lic.status === 'active', __( 'Activate your license', 'wpessential' ), __( 'Unlock Pro demos, updates and support.', 'wpessential' ), 'license', __( 'Activate', 'wpessential' ) ],
				[ false, __( 'Make it yours', 'wpessential' ), __( 'Swap the logo, colours and words in the Site Editor.', 'wpessential' ), '', __( 'Customize', 'wpessential' ) ],
			];
			$( '#wpe-steps' ).innerHTML = steps.map( ( [ done, t, d, tab, cta ], i ) => `<li class="${ done ? 'is-done' : '' }"><span class="wpe-steps__n">${ done ? ico( 'check', 16, 3 ) : i + 1 }</span><div><h4>${ esc( t ) }</h4><p>${ esc( d ) }</p></div>${ tab ? `<button class="wpe-btn wpe-btn--soft wpe-btn--sm" data-go="${ tab }">${ esc( cta ) }</button>` : `<a class="wpe-btn wpe-btn--soft wpe-btn--sm" href="${ esc( cfg.editor ) }">${ esc( cta ) }</a>` }</li>` ).join( '' );
			$$( '#wpe-steps [data-go]' ).forEach( ( b ) => b.addEventListener( 'click', () => go( b.dataset.go ) ) );
		} catch ( e ) {
			toast( 'error', __( 'Could not load the dashboard', 'wpessential' ), errMsg( e ) );
		}
	}

	/* ------------------------------------------------------------------ demos */

	const CATS = [ [ 'all', __( 'All', 'wpessential' ) ], [ 'business', __( 'Business', 'wpessential' ) ], [ 'agency', __( 'Agency', 'wpessential' ) ], [ 'shop', __( 'Shop', 'wpessential' ) ], [ 'blog', __( 'Blog', 'wpessential' ) ], [ 'restaurant', __( 'Restaurant', 'wpessential' ) ], [ 'education', __( 'Education', 'wpessential' ) ] ];

	async function viewDemos() {
		const f = state.filter;
		view( `
			<div class="wpe-head"><div><h1>${ esc( __( 'Demo Library', 'wpessential' ) ) }</h1><p>${ esc( __( 'Every demo is a complete website. Preview it, then import it for Gutenberg or Elementor — plugins, pages, images and menus included.', 'wpessential' ) ) }</p></div>
				<button class="wpe-btn wpe-btn--ghost" data-refresh data-busy="${ esc( __( 'Refreshing', 'wpessential' ) ) }">${ ico( 'refresh', 16 ) }${ esc( __( 'Refresh library', 'wpessential' ) ) }</button></div>
			<div id="wpe-banners"></div>
			<div class="wpe-card wpe-toolbar">
				<label class="wpe-search"><span class="wpe-sr">${ esc( __( 'Search demos', 'wpessential' ) ) }</span>${ ico( 'search', 17 ) }<input type="search" value="${ esc( f.q ) }" placeholder="${ esc( __( 'Search demos…', 'wpessential' ) ) }" data-q></label>
				<div class="wpe-chips" role="group" aria-label="${ esc( __( 'Category', 'wpessential' ) ) }">${ CATS.map( ( [ id, l ] ) => `<button class="wpe-chip" data-cat="${ id }" aria-pressed="${ f.cat === id }">${ esc( l ) }</button>` ).join( '' ) }</div>
				<div class="wpe-seg" role="group" aria-label="${ esc( __( 'Plan', 'wpessential' ) ) }">${ [ [ 'all', __( 'All', 'wpessential' ) ], [ 'free', __( 'Free', 'wpessential' ) ], [ 'pro', __( 'Pro', 'wpessential' ) ] ].map( ( [ id, l ] ) => `<button data-type="${ id }" aria-pressed="${ f.type === id }">${ esc( l ) }</button>` ).join( '' ) }</div>
				<div class="wpe-seg" role="group" aria-label="${ esc( __( 'Builder', 'wpessential' ) ) }">${ [ [ 'all', __( 'Any builder', 'wpessential' ), '' ], [ 'gutenberg', 'Gutenberg', 'gutenberg' ], [ 'elementor', 'Elementor', 'elementor' ] ].map( ( [ id, l, i ] ) => `<button data-builder="${ id }" aria-pressed="${ f.builder === id }">${ i ? ico( i, 14 ) : '' }${ esc( l ) }</button>` ).join( '' ) }</div>
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
				toast( 'success', __( 'Library refreshed', 'wpessential' ) );
			} catch ( err ) {
				toast( 'error', __( 'Refresh failed', 'wpessential' ), errMsg( err ) );
			}
		} ) );

		try {
			await loadDemos();
			if ( state.tab === 'demos' ) {
				rerender();
			}
		} catch ( e ) {
			$( '#wpe-demos' ).outerHTML = `<div class="wpe-card wpe-empty"><span class="wpe-empty__ico">${ ico( 'error', 28 ) }</span><h3>${ esc( __( 'The library could not load', 'wpessential' ) ) }</h3><p>${ esc( errMsg( e ) ) }</p></div>`;
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
			html = `<div class="wpe-banner wpe-banner--${ failed ? 'error' : 'warning' }">${ ico( failed ? 'error' : 'clock', 22 ) }<div><b>${ esc( failed ? sprintf( __( 'The import of %s stopped', 'wpessential' ), state.job.title ) : sprintf( __( 'The import of %s was interrupted', 'wpessential' ), state.job.title ) ) }</b>${ esc( __( 'Your progress is saved. Continue exactly where it left off.', 'wpessential' ) ) }</div><button class="wpe-btn wpe-btn--primary wpe-btn--sm" data-resume>${ ico( 'refresh', 15 ) }${ esc( __( 'Resume import', 'wpessential' ) ) }</button></div>` + html;
		}
		if ( state.imported ) {
			html += `<div class="wpe-banner wpe-banner--success">${ ico( 'ok', 22 ) }<div><b>${ esc( sprintf( __( '%s is installed on this site', 'wpessential' ), state.imported.title ) ) }</b>${ esc( __( 'You can remove everything it added and restore your previous settings at any time.', 'wpessential' ) ) }</div><button class="wpe-btn wpe-btn--danger wpe-btn--sm" data-remove data-busy="${ esc( __( 'Removing', 'wpessential' ) ) }">${ ico( 'trash', 15 ) }${ esc( __( 'Remove demo', 'wpessential' ) ) }</button></div>`;
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
		if ( ! window.confirm( sprintf( __( 'Remove %s? Its pages, posts, products, images and menus will be deleted and your previous settings restored. Content you created yourself is not touched.', 'wpessential' ), state.imported.title ) ) ) {
			return;
		}
		await busy( btn, async () => {
			try {
				const res = await api( { path: '/wpessential/v1/import/remove', method: 'POST' } );
				toast( 'success', __( 'Demo removed', 'wpessential' ), res.message );
				await loadDemos();
				renderDemos();
			} catch ( e ) {
				toast( 'error', __( 'Removal failed', 'wpessential' ), errMsg( e ) );
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
			grid.innerHTML = `<div class="wpe-card wpe-empty" style="grid-column:1/-1"><span class="wpe-empty__ico">${ ico( 'search', 28 ) }</span><h3>${ esc( __( 'No demos match these filters', 'wpessential' ) ) }</h3><p>${ esc( __( 'Try another category or clear the search.', 'wpessential' ) ) }</p><p style="margin-top:16px"><button class="wpe-btn wpe-btn--soft" data-clear>${ esc( __( 'Clear filters', 'wpessential' ) ) }</button></p></div>`;
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
					${ d.imported ? `<span class="wpe-badge wpe-badge--done">${ ico( 'check', 12, 3 ) }${ esc( __( 'IMPORTED', 'wpessential' ) ) }</span>` : '' }
					<img src="${ esc( d.thumbnail ) }" alt="${ esc( d.title ) }" loading="lazy">
					<div class="wpe-demo__overlay"><button class="wpe-btn wpe-btn--white" data-preview="${ esc( d.id ) }">${ ico( 'eye', 17 ) }${ esc( __( 'Live preview', 'wpessential' ) ) }</button></div>
				</div>
				<div class="wpe-demo__body">
					<div class="wpe-demo__title"><h3>${ esc( d.title ) }</h3></div>
					${ d.tagline ? `<p>${ esc( d.tagline ) }</p>` : '' }
					<div class="wpe-demo__meta">${ builders.map( ( b ) => `<span class="wpe-pill wpe-builder-ico wpe-builder-ico--${ b }">${ ico( b, 13 ) }${ b === 'elementor' ? 'Elementor' : 'Gutenberg' }</span>` ).join( '' ) }${ ( d.highlights || [] ).slice( 0, 2 ).map( ( h ) => `<span class="wpe-pill">${ esc( h ) }</span>` ).join( '' ) }</div>
				</div>
				<div class="wpe-demo__foot">
					<button class="wpe-btn wpe-btn--ghost" data-preview="${ esc( d.id ) }">${ ico( 'eye', 16 ) }${ esc( __( 'Preview', 'wpessential' ) ) }</button>
					${ d.locked ? `<button class="wpe-btn wpe-btn--grad" data-unlock>${ ico( 'lock', 16 ) }${ esc( __( 'Unlock', 'wpessential' ) ) }</button>` : `<button class="wpe-btn wpe-btn--primary" data-import="${ esc( d.id ) }">${ ico( 'download', 16 ) }${ esc( d.imported ? __( 'Re-import', 'wpessential' ) : __( 'Import', 'wpessential' ) ) }</button>` }
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
		const url = demo.preview_url || '';
		const devices = [ [ 'desktop', 'monitor', __( 'Desktop', 'wpessential' ) ], [ 'tablet', 'tablet', __( 'Tablet', 'wpessential' ) ], [ 'mobile', 'phone', __( 'Mobile', 'wpessential' ) ] ];
		const body = ( d ) => url
			? `<iframe src="${ esc( url ) }" title="${ esc( demo.title ) }" loading="lazy"></iframe>`
			: `<img src="${ esc( shots[ d ] || shots.desktop || demo.thumbnail ) }" alt="${ esc( sprintf( __( '%1$s on %2$s', 'wpessential' ), demo.title, d ) ) }">`;
		const { el, close } = modal( 'wpe-preview', `
			<div class="wpe-preview__bar">
				<div><h3>${ esc( demo.title ) }</h3><span class="wpe-muted" style="font-size:12px">${ esc( ( demo.pages || [] ).join( ' · ' ) ) }</span></div>
				<div class="wpe-seg" role="group" aria-label="${ esc( __( 'Device', 'wpessential' ) ) }">${ devices.map( ( [ id, i, l ], n ) => `<button data-device="${ id }" aria-pressed="${ n === 0 }">${ ico( i, 15 ) }<span>${ esc( l ) }</span></button>` ).join( '' ) }</div>
				${ demo.locked ? `<button class="wpe-btn wpe-btn--grad" data-unlock>${ ico( 'lock', 16 ) }${ esc( __( 'Unlock with Pro', 'wpessential' ) ) }</button>` : `<button class="wpe-btn wpe-btn--primary" data-import>${ ico( 'download', 16 ) }${ esc( __( 'Import this demo', 'wpessential' ) ) }</button>` }
				<button class="wpe-btn wpe-btn--ghost wpe-btn--icon wpe-modal__close" data-close aria-label="${ esc( __( 'Close preview', 'wpessential' ) ) }">${ ico( 'x' ) }</button>
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
		const w = {
			step: resume ? 3 : 0,
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
		const STEPS = [ __( 'Builder', 'wpessential' ), __( 'Plugins', 'wpessential' ), __( 'Content', 'wpessential' ), __( 'Import', 'wpessential' ), __( 'Done', 'wpessential' ) ];

		const { el, close } = modal( 'wpe-wizard', `
			<aside class="wpe-rail">
				<div class="wpe-rail__demo"><img src="${ esc( demo.thumbnail ) }" alt=""><div><b>${ esc( demo.title ) }</b><small>${ esc( demo.type === 'pro' ? 'Pro demo' : 'Free demo' ) }</small></div></div>
				<ol>${ STEPS.map( ( s, i ) => `<li data-rail="${ i }"><span class="wpe-rail__n">${ i + 1 }</span><span class="wpe-rail__label">${ esc( s ) }</span></li>` ).join( '' ) }</ol>
				<div class="wpe-rail__foot">${ ico( 'shield', 16 ) }${ esc( __( 'Safe import — every change can be undone.', 'wpessential' ) ) }</div>
			</aside>
			<section class="wpe-pane"><div class="wpe-pane__body" data-body></div><footer class="wpe-pane__foot" data-foot></footer></section>
			<button class="wpe-btn wpe-btn--ghost wpe-btn--icon wpe-btn--sm wpe-modal__close" data-close aria-label="${ esc( __( 'Close', 'wpessential' ) ) }">${ ico( 'x', 16 ) }</button>`, {
			guard: () => {
				if ( w.running ) {
					// eslint-disable-next-line no-alert
					return window.confirm( __( 'The import keeps its progress if you close this window. You can resume it from the Demo Library. Close anyway?', 'wpessential' ) );
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
			foot.innerHTML = `${ back ? `<button class="wpe-btn wpe-btn--ghost" data-back>${ ico( 'back', 16 ) }${ esc( __( 'Back', 'wpessential' ) ) }</button>` : '' }<span class="wpe-grow"></span>${ extra }${ next ? `<button class="wpe-btn wpe-btn--primary wpe-btn--lg" data-next>${ esc( nextLabel ) }${ ico( nextIcon, 17 ) }</button>` : '' }`;
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
			[ stepBuilder, stepPlugins, stepContent, stepImport, stepDone ][ step ]();
		}

		function stepBuilder() {
			const opt = ( id, title, text, logoCls ) => {
				const has = builders.includes( id );
				return `<label class="wpe-option"><input type="radio" name="wpe-builder" value="${ id }" ${ w.builder === id ? 'checked' : '' } ${ has ? '' : 'disabled' }><span class="wpe-option__check">${ ico( 'check', 14, 3 ) }</span><span class="wpe-option__logo ${ logoCls }">${ ico( id, 28 ) }</span><h4>${ esc( title ) }</h4><p>${ esc( has ? text : __( 'This demo has no version for this builder yet.', 'wpessential' ) ) }</p></label>`;
			};
			paint( `<h2>${ esc( __( 'Choose your page builder', 'wpessential' ) ) }</h2><p class="wpe-pane__lead">${ esc( __( 'Both versions look identical. Pick the editor you or your client will use every day.', 'wpessential' ) ) }</p>
				<div class="wpe-options">${ opt( 'gutenberg', 'Gutenberg', __( 'The native WordPress block editor. Fastest pages, no extra plugin.', 'wpessential' ), '' ) }${ opt( 'elementor', 'Elementor', cfg.elementor ? __( 'Visual drag-and-drop editing. Elementor is already installed.', 'wpessential' ) : __( 'Visual drag-and-drop editing. Elementor will be installed for you.', 'wpessential' ), 'wpe-option__logo--el' ) }</div>` );
			$$( 'input[name="wpe-builder"]', body ).forEach( ( r ) => r.addEventListener( 'change', () => {
				w.builder = r.value;
				w.plugins = null;
			} ) );
			footer( false, () => show( 1 ), __( 'Continue', 'wpessential' ) );
		}

		async function stepPlugins() {
			paint( `<h2>${ esc( __( 'Plugins this demo uses', 'wpessential' ) ) }</h2><p class="wpe-pane__lead">${ esc( __( 'We install, activate or switch off plugins so the demo works exactly like the preview.', 'wpessential' ) ) }</p><div class="wpe-list" data-list>${ [ 0, 1 ].map( () => '<div class="wpe-row"><div class="wpe-skel" style="width:40px;height:40px"></div><div class="wpe-row__main"><div class="wpe-skel" style="height:12px;width:40%"></div></div></div>' ).join( '' ) }</div>` );
			footer( true, () => show( 2 ), __( 'Continue', 'wpessential' ) );
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
				list.innerHTML = `<div class="wpe-callout wpe-callout--info">${ ico( 'info' ) }<div>${ esc( __( 'No extra plugins needed — this demo runs on the theme alone.', 'wpessential' ) ) }</div></div>`;
				return;
			}
			const pill = ( p ) => {
				if ( p.state === 'conflicts' ) {
					return p.status === 'active' ? `<span class="wpe-pill wpe-pill--warning"><i></i>${ esc( __( 'Will be deactivated', 'wpessential' ) ) }</span>` : `<span class="wpe-pill"><i></i>${ esc( __( 'Not needed', 'wpessential' ) ) }</span>`;
				}
				return {
					active: `<span class="wpe-pill wpe-pill--success"><i></i>${ esc( __( 'Active', 'wpessential' ) ) }</span>`,
					inactive: `<span class="wpe-pill wpe-pill--info"><i></i>${ esc( __( 'Will be activated', 'wpessential' ) ) }</span>`,
					missing: `<span class="wpe-pill wpe-pill--primary"><i></i>${ esc( __( 'Will be installed', 'wpessential' ) ) }</span>`,
				}[ p.status ];
			};
			list.innerHTML = w.plugins.map( ( p ) => `<div class="wpe-row"><span class="wpe-row__ico">${ esc( p.name.charAt( 0 ) ) }</span><div class="wpe-row__main"><b>${ esc( p.name ) }</b><span>${ esc( { required: __( 'Required', 'wpessential' ), recommended: __( 'Recommended', 'wpessential' ), conflicts: __( 'Conflicts with this demo', 'wpessential' ) }[ p.state ] || '' ) }${ p.version ? ' · v' + esc( p.version ) : '' }</span></div>${ pill( p ) }${ p.state === 'recommended' && p.pending ? `<label class="wpe-switch" title="${ esc( __( 'Include', 'wpessential' ) ) }"><input type="checkbox" data-skip="${ esc( p.slug ) }" ${ w.skip.includes( p.slug ) ? '' : 'checked' } aria-label="${ esc( sprintf( __( 'Include %s', 'wpessential' ), p.name ) ) }"><span></span></label>` : '' }</div>` ).join( '' );
			$$( '[data-skip]', list ).forEach( ( c ) => c.addEventListener( 'change', () => {
				w.skip = c.checked ? w.skip.filter( ( s ) => s !== c.dataset.skip ) : [ ...w.skip, c.dataset.skip ];
			} ) );
		}

		function stepContent() {
			const shop = ( w.plugins || [] ).some( ( p ) => p.slug === 'woocommerce' && ! w.skip.includes( 'woocommerce' ) ) || cfg.woo;
			const rows = [
				[ 'content', 'file', __( 'Pages, posts and menus', 'wpessential' ), sprintf( __( '%s and their navigation.', 'wpessential' ), ( demo.pages || [] ).slice( 0, 5 ).join( ', ' ) ) ],
				[ 'media', 'image', __( 'Images', 'wpessential' ), __( 'Copied to your Media Library, licensed for your site.', 'wpessential' ) ],
				[ 'products', 'bag', __( 'Shop products', 'wpessential' ), shop ? __( 'Products, categories, prices and shipping.', 'wpessential' ) : __( 'Needs WooCommerce — enable it on the previous step.', 'wpessential' ), ! shop ],
				[ 'settings', 'cog', __( 'Site settings', 'wpessential' ), __( 'Home page, blog page and store options.', 'wpessential' ) ],
			];
			paint( `<h2>${ esc( __( 'What should we import?', 'wpessential' ) ) }</h2><p class="wpe-pane__lead">${ esc( __( 'Everything is on by default for a site that matches the preview.', 'wpessential' ) ) }</p>
				<div class="wpe-list">${ rows.map( ( [ k, i, t, d, dis ] ) => `<label class="wpe-row" style="cursor:pointer"><span class="wpe-row__ico">${ ico( i, 19 ) }</span><div class="wpe-row__main"><b>${ esc( t ) }</b><span>${ esc( d ) }</span></div><span class="wpe-switch"><input type="checkbox" data-opt="${ k }" ${ w.opts[ k ] && ! dis ? 'checked' : '' } ${ dis ? 'disabled' : '' }><span></span></span></label>` ).join( '' ) }
				${ state.imported ? `<label class="wpe-row" style="cursor:pointer;border-color:var(--c-warning)"><span class="wpe-row__ico" style="color:var(--c-warning)">${ ico( 'trash', 19 ) }</span><div class="wpe-row__main"><b>${ esc( sprintf( __( 'Remove %s first', 'wpessential' ), state.imported.title ) ) }</b><span>${ esc( __( 'Recommended: start from a clean site instead of adding duplicates.', 'wpessential' ) ) }</span></div><span class="wpe-switch"><input type="checkbox" data-opt="reset" ${ w.opts.reset ? 'checked' : '' }><span></span></span></label>` : '' }</div>
				<div class="wpe-callout wpe-callout--info">${ ico( 'shield' ) }<div>${ esc( __( 'Your existing posts and pages are never changed. Imported items are tagged, so they can be removed in one click later.', 'wpessential' ) ) }</div></div>` );
			$$( '[data-opt]', body ).forEach( ( c ) => c.addEventListener( 'change', () => {
				w.opts[ c.dataset.opt ] = c.checked;
			} ) );
			if ( ! shop ) {
				w.opts.products = false;
			}
			footer( true, () => show( 3 ), __( 'Start import', 'wpessential' ), 'rocket' );
		}

		/* ---------- import ---------- */

		function stepImport() {
			paint( `<div class="wpe-run">${ ring( 0, __( 'Preparing', 'wpessential' ), 'is-running' ) }<div><h2 data-title>${ esc( __( 'Importing your demo…', 'wpessential' ) ) }</h2><p class="wpe-pane__lead" data-sub>${ esc( __( 'Keep this window open. It usually takes about a minute.', 'wpessential' ) ) }</p><ul class="wpe-tasks" data-tasks></ul></div></div>
				<div class="wpe-console"><div class="wpe-console__bar" data-filters></div><div class="wpe-console__lines" data-lines role="log" aria-live="polite"></div></div>
				<div data-error></div>` );
			foot.innerHTML = `<span class="wpe-muted" data-elapsed>${ ico( 'clock', 15 ) }</span><span class="wpe-grow"></span><button class="wpe-btn wpe-btn--ghost" data-cancel>${ esc( __( 'Cancel import', 'wpessential' ) ) }</button>`;
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
			bar.innerHTML = [ [ 'all', __( 'All', 'wpessential' ) ], [ 'info', __( 'Info', 'wpessential' ) ], [ 'success', __( 'Success', 'wpessential' ) ], [ 'warning', __( 'Warnings', 'wpessential' ) ], [ 'error', __( 'Errors', 'wpessential' ) ] ].map( ( [ id, l ] ) => `<button class="wpe-filter wpe-filter--${ id }" data-f="${ id }" aria-pressed="${ w.filter === id }">${ esc( l ) }<em>${ c[ id ] }</em></button>` ).join( '' ) + `<span class="wpe-grow"></span><button class="wpe-filter" data-copy>${ ico( 'copy', 13 ) }${ esc( __( 'Copy log', 'wpessential' ) ) }</button>`;
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
			const done = () => toast( 'success', __( 'Log copied', 'wpessential' ), __( 'Paste it into your support ticket.', 'wpessential' ) );
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
				r.outerHTML = ring( job.progress, failed ? __( 'Stopped', 'wpessential' ) : current ? __( 'Step', 'wpessential' ) + ' ' + ( job.index + 1 ) + '/' + job.steps.length : __( 'Complete', 'wpessential' ), job.status === 'running' ? 'is-running' : failed ? 'is-failed' : '' );
			}
			const tasks = $( '[data-tasks]', body );
			if ( tasks ) {
				tasks.innerHTML = job.steps.map( ( s ) => `<li class="is-${ s.status }"><span class="wpe-tasks__ico">${ s.status === 'done' ? ico( 'check', 12, 3.5 ) : s.status === 'failed' ? ico( 'x', 12, 3.5 ) : s.status === 'skipped' ? ico( 'arrow', 11, 3 ) : '' }</span>${ esc( s.label ) }${ s.total > 1 ? `<span class="wpe-tasks__count">${ s.done }/${ s.total }</span>` : '' }</li>` ).join( '' );
			}
			const el2 = $( '[data-elapsed]', foot );
			if ( el2 ) {
				el2.innerHTML = ico( 'clock', 15 ) + ' ' + esc( sprintf( __( '%ss elapsed', 'wpessential' ), job.elapsed ) );
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
					localLog( 'info', __( 'Resumed from the saved position.', 'wpessential' ) );
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
						fail( errMsg( e ), __( 'Reload the page, then resume the import from the Demo Library.', 'wpessential' ), true );
						return;
					}
					retries++;
					if ( retries > 4 ) {
						w.running = false;
						fail( errMsg( e ), __( 'Your progress is saved. Press Resume when the server is back.', 'wpessential' ), true );
						return;
					}
					const wait = 1500 * Math.pow( 2, retries - 1 );
					localLog( 'warning', sprintf( __( 'The server did not answer (%1$s). Retrying in %2$d seconds…', 'wpessential' ), errMsg( e ), wait / 1000 ) );
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
				setTimeout( () => show( 4 ), 700 );
			} else if ( job.status === 'failed' ) {
				const last = [ ...w.logs ].reverse().find( ( l ) => l.level === 'error' );
				fail( last ? last.msg : __( 'The import stopped.', 'wpessential' ), last ? last.hint : '', true );
			} else if ( job.status === 'cancelled' ) {
				fail( __( 'Import cancelled.', 'wpessential' ), __( 'Resume to finish it, or remove what was imported so far.', 'wpessential' ), false, true );
			}
		}

		function fail( message, hint, canRetry, cancelled = false ) {
			const box = $( '[data-error]', body );
			if ( box ) {
				box.innerHTML = `<div class="wpe-callout wpe-callout--${ cancelled ? 'warning' : 'error' }">${ ico( cancelled ? 'warn' : 'error' ) }<div><b>${ esc( message ) }</b>${ hint ? `<br>${ esc( hint ) }` : '' }</div></div>`;
			}
			const h = $( '[data-title]', body );
			if ( h ) {
				h.textContent = cancelled ? __( 'Import paused', 'wpessential' ) : __( 'The import needs your attention', 'wpessential' );
			}
			foot.innerHTML = `<button class="wpe-btn wpe-btn--danger" data-rollback data-busy="${ esc( __( 'Removing', 'wpessential' ) ) }">${ ico( 'trash', 16 ) }${ esc( __( 'Remove imported content', 'wpessential' ) ) }</button><span class="wpe-grow"></span><button class="wpe-btn wpe-btn--ghost" data-copy2>${ ico( 'copy', 16 ) }${ esc( __( 'Copy log', 'wpessential' ) ) }</button>${ canRetry || cancelled ? `<button class="wpe-btn wpe-btn--primary" data-retry>${ ico( 'refresh', 16 ) }${ esc( __( 'Resume', 'wpessential' ) ) }</button>` : `<button class="wpe-btn wpe-btn--primary" data-again>${ ico( 'back', 16 ) }${ esc( __( 'Back to options', 'wpessential' ) ) }</button>` }`;
			$( '[data-copy2]', foot ).addEventListener( 'click', copyLog );
			const rb = $( '[data-rollback]', foot );
			rb.addEventListener( 'click', () => busy( rb, async () => {
				try {
					if ( w.job && w.job.status === 'running' ) {
						await api( { path: '/wpessential/v1/import/cancel', method: 'POST' } );
					}
					const res = await api( { path: '/wpessential/v1/import/remove', method: 'POST' } );
					toast( 'success', __( 'Everything was rolled back', 'wpessential' ), res.message );
					state.imported = null;
					close( true );
				} catch ( e ) {
					toast( e.code === 'wpe_nothing' ? 'info' : 'error', e.code === 'wpe_nothing' ? __( 'Nothing to remove', 'wpessential' ) : __( 'Rollback failed', 'wpessential' ), errMsg( e ) );
					if ( e.code === 'wpe_nothing' ) {
						close( true );
					}
				}
			} ) );
			const rt = $( '[data-retry]', foot );
			if ( rt ) {
				rt.addEventListener( 'click', () => {
					$( '[data-error]', body ).innerHTML = '';
					foot.innerHTML = `<span class="wpe-muted" data-elapsed></span><span class="wpe-grow"></span><button class="wpe-btn wpe-btn--ghost" data-cancel>${ esc( __( 'Cancel import', 'wpessential' ) ) }</button>`;
					$( '[data-cancel]', foot ).addEventListener( 'click', cancel );
					const h2 = $( '[data-title]', body );
					if ( h2 ) {
						h2.textContent = __( 'Importing your demo…', 'wpessential' );
					}
					resumeRun();
				} );
			}
			const ag = $( '[data-again]', foot );
			if ( ag ) {
				ag.addEventListener( 'click', () => show( 2 ) );
			}
		}

		async function resumeRun() {
			w.running = true;
			localLog( 'info', __( 'Resuming…', 'wpessential' ) );
			try {
				const job = await api( { path: '/wpessential/v1/import/run', method: 'POST', data: { since: w.since, retry: true } } );
				update( job );
				after( job );
			} catch ( e ) {
				w.running = false;
				fail( errMsg( e ), __( 'Your progress is saved. Try again in a moment.', 'wpessential' ), true );
			}
		}

		async function cancel() {
			// eslint-disable-next-line no-alert
			if ( ! window.confirm( __( 'Cancel the import? What was imported so far stays until you remove it.', 'wpessential' ) ) ) {
				return;
			}
			w.cancelled = true;
			try {
				const job = await api( { path: '/wpessential/v1/import/cancel', method: 'POST' } );
				w.cancelled = false;
				update( job );
				after( job );
			} catch ( e ) {
				toast( 'error', __( 'Could not cancel', 'wpessential' ), errMsg( e ) );
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
				<h2>${ esc( __( 'Your website is ready!', 'wpessential' ) ) }</h2>
				<p>${ esc( issues ? sprintf( __( '%1$s was imported. %2$d item(s) need a look — see the notes below.', 'wpessential' ), demo.title, issues ) : sprintf( __( '%s was imported without a single issue. Time to make it yours.', 'wpessential' ), demo.title ) ) }</p>
				<div class="wpe-done__stats"><div><b>${ mins }s</b><span>${ esc( __( 'Import time', 'wpessential' ) ) }</span></div><div><b>${ w.job ? w.job.steps.filter( ( s ) => s.status === 'done' ).length : 0 }</b><span>${ esc( __( 'Steps completed', 'wpessential' ) ) }</span></div><div><b>${ issues }</b><span>${ esc( __( 'Warnings', 'wpessential' ) ) }</span></div></div>
				<div class="wpe-done__actions"><a class="wpe-btn wpe-btn--grad wpe-btn--lg" href="${ esc( cfg.site ) }" target="_blank" rel="noopener">${ ico( 'eye' ) }${ esc( __( 'View your site', 'wpessential' ) ) }</a><a class="wpe-btn wpe-btn--ghost wpe-btn--lg" href="${ esc( cfg.editor ) }">${ ico( 'brush' ) }${ esc( __( 'Customize', 'wpessential' ) ) }</a></div>
				${ issues ? `<div class="wpe-console" style="text-align:left"><div class="wpe-console__lines">${ notes.map( lineHtml ).join( '' ) }</div></div>` : '' }
			</div>` );
			foot.innerHTML = `<span class="wpe-grow"></span><button class="wpe-btn wpe-btn--ghost" data-finish>${ esc( __( 'Back to dashboard', 'wpessential' ) ) }</button>`;
			$( '[data-finish]', foot ).addEventListener( 'click', () => close( true ) );
			if ( ! issues ) {
				confetti( el.querySelector( '.wpe-modal__panel' ) );
			}
			toast( 'success', __( 'Import complete', 'wpessential' ), demo.title );
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
		const colors = [ '#4f46e5', '#7c3aed', '#db2777', '#f59e0b', '#10b981', '#38bdf8' ];
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
		view( `<div class="wpe-head"><div><h1>${ esc( __( 'Plugins', 'wpessential' ) ) }</h1><p>${ esc( __( 'Plugins used by WPEssential demos. Install, switch on or off and delete them here.', 'wpessential' ) ) }</p></div></div><div class="wpe-card"><div class="wpe-card__body"><div class="wpe-list" id="wpe-plugins">${ [ 0, 1, 2 ].map( () => '<div class="wpe-row"><div class="wpe-skel" style="width:40px;height:40px"></div><div class="wpe-row__main"><div class="wpe-skel" style="height:12px;width:30%"></div></div></div>' ).join( '' ) }</div></div></div>` );
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
			box.innerHTML = `<div class="wpe-empty"><span class="wpe-empty__ico">${ ico( 'plug', 28 ) }</span><h3>${ esc( __( 'No plugins needed', 'wpessential' ) ) }</h3><p>${ esc( __( 'The demos in your library run on the theme alone.', 'wpessential' ) ) }</p></div>`;
			return;
		}
		const btn = ( p, a, label, cls, icon ) => `<button class="wpe-btn wpe-btn--${ cls } wpe-btn--sm" data-act="${ a }" data-slug="${ esc( p.slug ) }" data-busy="${ esc( label ) }">${ ico( icon, 15 ) }${ esc( label ) }</button>`;
		box.innerHTML = state.plugins.map( ( p ) => `<div class="wpe-row"><span class="wpe-row__ico">${ esc( p.name.charAt( 0 ) ) }</span><div class="wpe-row__main"><b>${ esc( p.name ) }</b><span>${ esc( p.state === 'required' ? __( 'Required by some demos', 'wpessential' ) : __( 'Recommended', 'wpessential' ) ) }${ p.version ? ' · v' + esc( p.version ) : '' }</span></div>
			${ { active: `<span class="wpe-pill wpe-pill--success"><i></i>${ esc( __( 'Active', 'wpessential' ) ) }</span>`, inactive: `<span class="wpe-pill wpe-pill--warning"><i></i>${ esc( __( 'Inactive', 'wpessential' ) ) }</span>`, missing: `<span class="wpe-pill"><i></i>${ esc( __( 'Not installed', 'wpessential' ) ) }</span>` }[ p.status ] }
			${ p.status === 'missing' ? btn( p, 'install', __( 'Install & activate', 'wpessential' ), 'primary', 'download' ) : '' }
			${ p.status === 'inactive' ? btn( p, 'activate', __( 'Activate', 'wpessential' ), 'primary', 'check' ) + btn( p, 'delete', __( 'Delete', 'wpessential' ), 'danger', 'trash' ) : '' }
			${ p.status === 'active' ? btn( p, 'deactivate', __( 'Deactivate', 'wpessential' ), 'ghost', 'x' ) : '' }</div>` ).join( '' );
		$$( '[data-act]', box ).forEach( ( b ) => b.addEventListener( 'click', () => busy( b, async () => {
			if ( b.dataset.act === 'delete' && ! window.confirm( __( 'Delete this plugin and its files?', 'wpessential' ) ) ) { // eslint-disable-line no-alert
				return;
			}
			try {
				const res = await api( { path: '/wpessential/v1/plugins/action', method: 'POST', data: { slug: b.dataset.slug, action: b.dataset.act } } );
				state.plugins = state.plugins.map( ( p ) => ( p.slug === res.slug ? { ...p, ...res } : p ) );
				toast( 'success', res.name, { active: __( 'is active.', 'wpessential' ), inactive: __( 'is inactive.', 'wpessential' ), missing: __( 'was deleted.', 'wpessential' ) }[ res.status ] );
				setTimeout( renderPlugins, 0 );
			} catch ( e ) {
				toast( 'error', __( 'Plugin action failed', 'wpessential' ), errMsg( e ) );
			}
		} ) ) );
	}

	/* ------------------------------------------------------------------ license */

	async function viewLicense() {
		view( `<div class="wpe-head"><div><h1>${ esc( __( 'License', 'wpessential' ) ) }</h1><p>${ esc( __( 'Activate your purchase on this domain to unlock Pro demos, automatic updates and priority support.', 'wpessential' ) ) }</p></div></div><div class="wpe-license" id="wpe-license"><div class="wpe-skel" style="height:240px;border-radius:24px"></div><div class="wpe-skel" style="height:240px;border-radius:20px"></div></div>` );
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
		const label = { active: __( 'Active', 'wpessential' ), expired: __( 'Expired', 'wpessential' ), invalid: __( 'Invalid', 'wpessential' ) }[ l.status ] || __( 'Not connected', 'wpessential' );
		box.innerHTML = `
			<div class="wpe-lic-card" data-status="${ esc( l.status ) }">
				<div class="wpe-lic-card__top"><span class="wpe-brand"><span class="wpe-brand__mark">${ ico( 'logo', 20 ) }</span>WPEssential Pro</span><span class="wpe-pill ${ active ? 'wpe-pill--success' : l.key ? 'wpe-pill--warning' : '' }" style="${ active || l.key ? '' : 'background:rgb(255 255 255 / 14%);color:#fff;border-color:transparent' }"><i></i>${ esc( label ) }</span></div>
				<div class="wpe-lic-card__key">${ esc( l.key || '•••• •••• •••• ••••' ) }</div>
				<div class="wpe-lic-card__meta"><div>${ esc( __( 'Domain', 'wpessential' ) ) }<b>${ esc( l.domain ) }</b></div><div>${ esc( __( 'Expires', 'wpessential' ) ) }<b>${ esc( l.expires ? ( l.expires === 'lifetime' ? __( 'Lifetime', 'wpessential' ) : l.expires ) : '—' ) }</b></div><div>${ esc( __( 'Provider', 'wpessential' ) ) }<b>${ esc( l.provider ) }</b></div></div>
			</div>
			<div class="wpe-card"><div class="wpe-card__body">
				${ active ? `<h2 style="font-size:20px">${ esc( __( 'You’re all set', 'wpessential' ) ) }</h2><p class="wpe-muted" style="margin-top:6px">${ esc( sprintf( __( 'This license is active on %s.', 'wpessential' ), l.domain ) ) }${ l.plan ? ' ' + esc( sprintf( __( 'Plan: %s.', 'wpessential' ), l.plan ) ) : '' }</p>
					<ul class="wpe-perks">${ [ __( 'All Pro demos unlocked', 'wpessential' ), __( 'One-click theme updates', 'wpessential' ), __( 'Priority support', 'wpessential' ) ].map( ( t ) => `<li>${ ico( 'ok', 18 ) }${ esc( t ) }</li>` ).join( '' ) }</ul>
					<div style="display:flex;gap:10px;margin-top:24px;flex-wrap:wrap"><button class="wpe-btn wpe-btn--danger" data-deact data-busy="${ esc( __( 'Deactivating', 'wpessential' ) ) }">${ esc( __( 'Deactivate on this domain', 'wpessential' ) ) }</button></div>`
		: `<h2 style="font-size:20px">${ esc( l.status === 'expired' ? __( 'Renew to keep Pro features', 'wpessential' ) : __( 'Activate your license', 'wpessential' ) ) }</h2><p class="wpe-muted" style="margin-top:6px">${ esc( __( 'Find the key in your purchase email or your wpessential.org account.', 'wpessential' ) ) }</p>
					<form class="wpe-lic-form" data-form><label class="wpe-field"><span class="wpe-sr">${ esc( __( 'License key', 'wpessential' ) ) }</span><input type="password" autocomplete="off" spellcheck="false" placeholder="${ esc( __( 'Paste your license key', 'wpessential' ) ) }" data-key required><button type="button" class="wpe-btn wpe-btn--ghost wpe-btn--sm wpe-btn--icon" data-reveal aria-label="${ esc( __( 'Show key', 'wpessential' ) ) }">${ ico( 'eye', 15 ) }</button></label><button class="wpe-btn wpe-btn--primary wpe-btn--lg" type="submit" data-busy="${ esc( __( 'Checking', 'wpessential' ) ) }">${ ico( 'key', 17 ) }${ esc( __( 'Activate', 'wpessential' ) ) }</button></form>
					${ error ? `<div class="wpe-callout wpe-callout--error" role="alert">${ ico( 'error' ) }<div>${ esc( error ) }</div></div>` : l.message ? `<div class="wpe-callout wpe-callout--warning">${ ico( 'warn' ) }<div>${ esc( l.message ) }</div></div>` : '' }
					<ul class="wpe-perks">${ [ __( 'Unlock every Pro demo in the library', 'wpessential' ), __( 'Automatic updates from your dashboard', 'wpessential' ), __( 'Priority support from the WPEssential Team', 'wpessential' ) ].map( ( t ) => `<li>${ ico( 'ok', 18 ) }${ esc( t ) }</li>` ).join( '' ) }</ul>
					<p style="margin-top:20px"><a href="https://wpessential.org/pricing/" target="_blank" rel="noopener">${ esc( __( 'Don’t have a license? See plans', 'wpessential' ) ) } →</a></p>` }
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
						toast( 'success', __( 'License activated', 'wpessential' ), __( 'Pro demos are unlocked.', 'wpessential' ) );
						state.demos = null;
						setTimeout( () => renderLicense(), 0 );
					} catch ( err ) {
						setTimeout( () => renderLicense( errMsg( err ) ), 0 );
					}
				} );
			} );
		}
		const de = $( '[data-deact]', box );
		if ( de ) {
			de.addEventListener( 'click', () => {
				// eslint-disable-next-line no-alert
				if ( ! window.confirm( __( 'Deactivate the license on this domain? You can activate it on another site afterwards.', 'wpessential' ) ) ) {
					return;
				}
				busy( de, async () => {
					try {
						state.license = await api( { path: '/wpessential/v1/license/deactivate', method: 'POST' } );
						toast( 'info', __( 'License deactivated', 'wpessential' ) );
						state.demos = null;
						setTimeout( () => renderLicense(), 0 );
					} catch ( err ) {
						toast( 'error', __( 'Deactivation failed', 'wpessential' ), errMsg( err ) );
					}
				} );
			} );
		}
	}

	/* ------------------------------------------------------------------ status */

	async function viewStatus() {
		view( `<div class="wpe-head"><div><h1>${ esc( __( 'System Status', 'wpessential' ) ) }</h1><p>${ esc( __( 'What your server offers and what to improve before importing.', 'wpessential' ) ) }</p></div><button class="wpe-btn wpe-btn--ghost" data-copy-report>${ ico( 'copy', 16 ) }${ esc( __( 'Copy report', 'wpessential' ) ) }</button></div><div class="wpe-status" id="wpe-status"><div class="wpe-skel" style="height:300px;border-radius:20px"></div><div class="wpe-skel" style="height:420px;border-radius:20px"></div></div>` );
		try {
			const st = await loadStatus();
			const score = healthScore( st.checks );
			const fails = st.checks.filter( ( c ) => c.state !== 'ok' ).length;
			$( '#wpe-status' ).innerHTML = `
				<div class="wpe-card wpe-health">${ ring( score, __( 'Health', 'wpessential' ) ) }<h3>${ esc( score >= 90 ? __( 'Excellent', 'wpessential' ) : score >= 70 ? __( 'Good', 'wpessential' ) : __( 'Needs work', 'wpessential' ) ) }</h3><p class="wpe-muted" style="margin-top:6px">${ esc( fails ? sprintf( __( '%d item(s) could be better. Imports still run; fixes make them faster.', 'wpessential' ), fails ) : __( 'Your server is ready for any demo.', 'wpessential' ) ) }</p></div>
				<div class="wpe-grid">
					<div class="wpe-card wpe-checks">${ st.checks.map( ( c ) => `<div class="wpe-row"><span class="wpe-state wpe-state--${ c.state }">${ ico( c.state === 'ok' ? 'check' : c.state === 'warn' ? 'warn' : 'x', 15, 2.5 ) }</span><div class="wpe-row__main"><b>${ esc( c.label ) }</b>${ c.hint ? `<span>${ esc( c.hint ) }</span>` : '' }</div><span class="wpe-pill ${ c.state === 'ok' ? '' : c.state === 'warn' ? 'wpe-pill--warning' : 'wpe-pill--danger' }">${ esc( c.value ) }</span></div>` ).join( '' ) }</div>
					<div class="wpe-card"><div class="wpe-card__body" style="padding-bottom:12px"><h3 style="font-size:16px">${ esc( __( 'Environment', 'wpessential' ) ) }</h3></div><table class="wpe-env">${ st.environment.map( ( [ k, v ] ) => `<tr><td>${ esc( k ) }</td><td>${ esc( v ) }</td></tr>` ).join( '' ) }</table></div>
				</div>`;
			$( '[data-copy-report]' ).addEventListener( 'click', () => {
				const text = st.checks.map( ( c ) => `${ c.label }: ${ c.value } [${ c.state }]` ).concat( st.environment.map( ( [ k, v ] ) => `${ k }: ${ v }` ) ).join( '\n' );
				navigator.clipboard.writeText( text ).then( () => toast( 'success', __( 'Report copied', 'wpessential' ) ), () => toast( 'error', __( 'Copy failed', 'wpessential' ) ) );
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
