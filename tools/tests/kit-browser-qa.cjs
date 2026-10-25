/**
 * Browser regression for the seven Kit widgets across Gutenberg and Elementor.
 * Screenshots are retained for human review; failures stop the CI job.
 */
'use strict';

const assert = require( 'node:assert/strict' );
const fs = require( 'node:fs' );
const path = require( 'node:path' );
const { chromium } = require( 'playwright' );

const base = process.env.QA_BASE_URL || 'http://127.0.0.1:8090';
const dir = process.env.QA_ARTIFACT_DIR || '/tmp/kit-qa';
const fixture = JSON.parse( fs.readFileSync( process.env.QA_FIXTURE_JSON, 'utf8' ) );
const targets = {
	'price-list': '.wpe-price-list',
	'menu': '.wpe-menu',
	'forms': '.wpe-forms',
	'icon': '.wpe-icon-single',
	'chart': '.wpe-chart',
	'flip': '.wpe-flip',
	'site-archive-widgets': '.wpe-site-archive-widgets'
};
fs.mkdirSync( dir, { recursive: true } );

// WCAG 2.x relative luminance check for theme-aware form labels.
function rgb( value ) {
	const hex = value.trim().match( /^#([a-f0-9]{6})$/i );
	if ( hex ) {
		return [ 0, 2, 4 ].map( i => parseInt( hex[1].slice( i, i + 2 ), 16 ) );
	}
	const triplet = value.match( /rgba?\((\d+)[, ]+(\d+)[, ]+(\d+)/ );
	assert.ok( triplet, 'Unknown CSS color: ' + value );
	return [ Number( triplet[1] ), Number( triplet[2] ), Number( triplet[3] ) ];
}
function luminance( value ) {
	const a = rgb( value ).map( part => {
		const n = part / 255;
		return n <= 0.04045 ? n / 12.92 : ( ( n + 0.055 ) / 1.055 ) ** 2.4;
	} );
	return a[0] * 0.2126 + a[1] * 0.7152 + a[2] * 0.0722;
}
function contrast( foreground, background ) {
	const a = luminance( foreground );
	const b = luminance( background );
	return ( Math.max( a, b ) + 0.05 ) / ( Math.min( a, b ) + 0.05 );
}


async function verifyPage( page, id, label ) {
	const result = await page.goto( base + '/?page_id=' + id, { waitUntil: 'domcontentloaded', timeout: 60000 } );
	assert.equal( result.status(), 200, label + ': HTTP 200' );
	for ( const [ name, selector ] of Object.entries( targets ) ) {
		assert.ok( await page.locator( selector ).count(), label + ': missing ' + name );
	}
	assert.ok( await page.locator( '.wpe-forms .wpforms-form' ).count(), label + ': real WPForms form not found' );
	assert.ok( await page.locator( '.wpe-forms input[type="email"]' ).count(), label + ': WPForms email field missing' );
	assert.equal( await page.locator( '.wpe-chart.is-donut svg circle' ).count(), 2, label + ': incorrect chart output' );

	// The default mouse position may already hover this widget. Measure the
	// rest state with the pointer explicitly outside before entering.
	await page.mouse.move( 0, page.viewportSize().height - 2 );
	assert.equal( await page.locator( '.wpe-price-list.wpek-elevation' ).first().evaluate( node => node.matches( ':hover' ) ), false, label + ': baseline must not already hover' );
	// Theme elevation, allowlisted clip shape and embedded no-network noise.
	const visualLayer = await page.evaluate( () => {
		const chart = document.querySelector( '.wpe-chart.wpek-clip-slant.wpek-grain' );
		const price = document.querySelector( '.wpe-price-list.wpek-elevation.wpek-elev-rest-sm.wpek-elev-hover-lg' );
		const icon = document.querySelector( '.wpe-icon-single.wpek-elev-focus-md' );
		if ( ! chart || ! price || ! icon ) {
			return { error: 'Missing Kit shape/grain/elevation controls' };
		}
		const grain = getComputedStyle( chart, '::before' );
		return {
			clip: getComputedStyle( chart ).clipPath,
			grainImage: grain.backgroundImage,
			grainOpacity: parseFloat( grain.opacity ),
			grainPointer: grain.pointerEvents,
			grainPosition: grain.position,
			shadowRest: getComputedStyle( price ).boxShadow,
			iconFocusTier: getComputedStyle( icon ).getPropertyValue( '--wpek-elev-focus' ).trim()
		};
	} );
	assert.ok( visualLayer.clip.includes( 'polygon(' ), label + ': slant clip-path missing: ' + JSON.stringify( visualLayer ) );
	assert.ok( visualLayer.grainImage.includes( 'data:image/svg+xml' ), label + ': SVG noise not embedded' );
	assert.ok( Math.abs( visualLayer.grainOpacity - 0.09 ) < 0.001, label + ': noise intensity failed' );
	assert.equal( visualLayer.grainPointer, 'none', label + ': texture intercepts pointer input' );
	assert.equal( visualLayer.grainPosition, 'absolute', label + ': grain overlay positioned wrong' );
	assert.notEqual( visualLayer.shadowRest, 'none', label + ': theme shadow absent at rest' );
	assert.ok( visualLayer.iconFocusTier.length > 0, label + ': keyboard focus shadow token absent' );
	await page.emulateMedia( { reducedMotion: 'reduce' } );
	await page.locator( '.wpe-price-list.wpek-elevation' ).first().hover();
	const hoverShadow = await page.locator( '.wpe-price-list.wpek-elevation' ).first().evaluate( el => getComputedStyle( el ).boxShadow );
	const elevationDebug = await page.locator( '.wpe-price-list.wpek-elevation' ).first().evaluate( el => ({
		hovering: el.matches( ':hover' ),
		computed: getComputedStyle( el ).boxShadow,
		rest: getComputedStyle( el ).getPropertyValue( '--wpek-elev-rest' ),
		hover: getComputedStyle( el ).getPropertyValue( '--wpek-elev-hover' ),
		classes: el.className
	}) );
	assert.equal( elevationDebug.hovering, true, label + ': actual pointer did not hover element' );
	assert.notEqual( hoverShadow, visualLayer.shadowRest, label + ': hover elevation did not change' );
	await page.mouse.move( 0, 0 );
	console.log( 'PASS: ' + label + ': clip shape, SVG grain and theme state elevation' );
	// The browser context defaults to reduced motion. Turn it off for normal-animation checks.
	// The QA browser defaults to reduced motion; verify animation only in normal mode.
	await page.emulateMedia( { reducedMotion: 'no-preference' } );
	// Opt-in shared visual effects: rounded gradient border, animated angle,
	// allowlisted blend mode and fade-edge mask work in both editor frontends.
	const gradientBox = page.locator( '.wpe-price-list.wpek-gradient-border.wpek-gradient-border-animated' ).first();
	const maskedChart = page.locator( '.wpe-chart.wpek-mask-bottom' ).first();
	const blendedFlip = page.locator( '.wpe-flip.wpek-blend-multiply' ).first();
	assert.equal( await gradientBox.count(), 1, label + ': gradient border class missing' );
	assert.equal( await maskedChart.count(), 1, label + ': gradient mask class missing' );
	assert.equal( await blendedFlip.count(), 1, label + ': blend mode class missing' );
	const fx = await page.evaluate( () => {
		const price = document.querySelector( '.wpe-price-list.wpek-gradient-border' );
		const chart = document.querySelector( '.wpe-chart.wpek-mask-bottom' );
		const flip = document.querySelector( '.wpe-flip.wpek-blend-multiply' );
		const border = getComputedStyle( price, '::after' );
		return {
			angle: getComputedStyle( price ).getPropertyValue( '--wpek-gradient-angle' ).trim(),
			width: getComputedStyle( price ).getPropertyValue( '--wpek-gradient-width' ).trim(),
			borderBackground: border.backgroundImage,
			borderMask: border.webkitMaskImage || border.maskImage,
			borderPointerEvents: border.pointerEvents,
			borderAnimation: border.animationName,
			fade: getComputedStyle( chart ).maskImage,
			blend: getComputedStyle( flip ).mixBlendMode
		};
	} );
	assert.equal( fx.angle, '45deg', label + ': configured border angle missing' );
	assert.equal( fx.width, '3px', label + ': responsive gradient border width missing' );
	assert.ok( fx.borderBackground.includes( 'conic-gradient' ), label + ': rounded gradient overlay absent' );
	assert.ok( fx.borderMask.includes( 'linear-gradient' ), label + ': border cut-out mask absent' );
	assert.equal( fx.borderPointerEvents, 'none', label + ': gradient overlay blocks pointer interactions' );
	assert.equal( fx.borderAnimation, 'wpek-gradient-spin', label + ': animated border animation missing' );
	assert.ok( fx.fade.includes( 'linear-gradient' ), label + ': fade-edge mask did not compute' );
	assert.equal( fx.blend, 'multiply', label + ': CSS blend mode did not compute' );
	await page.emulateMedia( { reducedMotion: 'reduce' } );
	const reducedGradient = await gradientBox.evaluate( node => getComputedStyle( node, '::after' ).animationName );
	assert.equal( reducedGradient, 'none', label + ': border animation ignores reduced motion' );
	await page.emulateMedia( { reducedMotion: 'no-preference' } );
	console.log( 'PASS: ' + label + ': gradient border, mask, blend and reduced-motion guard' );

	await page.emulateMedia( { reducedMotion: 'no-preference' } );
	// Per-layer settings must create different travel vectors in both editors.
	await page.waitForFunction( () => {
		const root = document.querySelector( '.wpe-price-list.wpek-parallax' );
		return root && root.children[ 0 ]?.classList.contains( 'wpek-parallax-layer' );
	}, null, { timeout: 15000 } );
	const layerInfo = await page.locator( '.wpe-price-list.wpek-parallax' ).first().evaluate( node => {
		const styles = getComputedStyle( node );
		return {
			travel1: styles.getPropertyValue( '--wpek-parallax-layer-1' ).trim(),
			travel2: styles.getPropertyValue( '--wpek-parallax-layer-2' ).trim(),
			active1: node.children[ 0 ].classList.contains( 'wpek-parallax-layer' ),
			active2: node.children[ 1 ].classList.contains( 'wpek-parallax-layer' ),
			offset1: node.children[ 0 ].style.getPropertyValue( '--wpek-parallax-y' ),
			offset2: node.children[ 1 ].style.getPropertyValue( '--wpek-parallax-y' )
		};
	} );
	assert.equal( layerInfo.travel1, '80px', label + ': first parallax travel' );
	assert.equal( layerInfo.travel2, '-45px', label + ': second parallax travel' );
	assert.ok( layerInfo.active1 && layerInfo.active2, label + ': two independent layers not activated' );
	assert.ok( layerInfo.offset1.endsWith( 'px' ) && layerInfo.offset2.endsWith( 'px' ), label + ': runtime offsets missing' );
	console.log( 'PASS: ' + label + ': two responsive parallax layers activated' );
	// Shared motion is progressively enhanced and bound after scripts load.
	const priceMotion = page.locator( '.wpe-price-list.wpek-motion-trigger-click.wpek-stagger' ).first();
	const chartMotion = page.locator( '.wpe-chart.wpek-motion-trigger-load' ).first();
	const flipMotion = page.locator( '.wpe-site-archive-widgets.wpek-motion-trigger-hover' ).first();
	const formMotion = page.locator( '.wpe-forms.wpek-motion-trigger-scroll' ).first();
	await priceMotion.locator( ':scope' ).waitFor( { state: 'attached', timeout: 20000 } );
	assert.ok( await chartMotion.count(), label + ': load trigger missing' );
	assert.ok( await flipMotion.count(), label + ': hover trigger missing' );
	assert.ok( await formMotion.count(), label + ': scroll trigger missing' );
	await page.waitForFunction( () => {
		const node = document.querySelector( '.wpe-price-list.wpek-motion-trigger-click' );
		return Boolean( node && node.classList.contains( 'wpek-motion-ready' ) );
	}, null, { timeout: 20000 } );
	assert.equal( await priceMotion.evaluate( node => node.classList.contains( 'is-motion-active' ) ), false, label + ': click animation started without click' );
	await priceMotion.evaluate( node => node.click() );
	assert.equal( await priceMotion.evaluate( node => node.classList.contains( 'is-motion-active' ) ), true, label + ': click trigger did not activate' );
	await page.waitForFunction( () => document.querySelector( '.wpe-chart.wpek-motion-trigger-load' )?.classList.contains( 'is-motion-active' ), null, { timeout: 10000 } );
	await flipMotion.dispatchEvent( 'pointerenter' );
	assert.equal( await flipMotion.evaluate( node => node.classList.contains( 'is-motion-active' ) ), true, label + ': hover did not activate' );
	await flipMotion.dispatchEvent( 'pointerleave' );
	assert.equal( await flipMotion.evaluate( node => node.classList.contains( 'is-motion-active' ) ), false, label + ': pointer leave did not reset' );
	const delay = await priceMotion.locator( ':scope > :nth-child(2)' ).evaluate( node => getComputedStyle( node ).animationDelay );
	assert.equal( delay, '0.125s', label + ': stagger 125ms direct-child delay missing' );
	await page.emulateMedia( { reducedMotion: 'reduce' } );
	await page.waitForFunction( () => {
		const root = document.querySelector( '.wpe-price-list.wpek-parallax' );
		return root && ! root.children[ 0 ].classList.contains( 'wpek-parallax-layer' );
	}, null, { timeout: 15000 } );
	const reduced = await priceMotion.evaluate( node => ( {
		name: getComputedStyle( node ).animationName,
		transform: getComputedStyle( node ).transform
	} ) );
	assert.equal( reduced.name, 'wpek-reduced-fade', label + ': reduced-motion fade alternative unavailable' );
	await page.emulateMedia( { reducedMotion: 'reduce' } );
	console.log( 'PASS: ' + label + ': click/hover/load, stagger and reduced motion' );

	// Verify the actual published Gutenberg pattern renders in both menu variants.
	assert.ok( await page.getByText( 'Kit QA Saved Mega Content' ).count() >= 2, label + ': saved mega panel missing' );
	await page.setViewportSize( { width: 1440, height: 900 } );
	const desktopMenu = page.locator( '.wpe-menu__desktop .wpe-menu__has-template' ).first();
	const desktopToggle = desktopMenu.locator( ':scope > .wpe-menu__submenu-button' );
	assert.ok( await desktopToggle.count() === 1, label + ': desktop mega disclosure button missing' );
	await desktopToggle.click();
	assert.equal( await desktopToggle.getAttribute( 'aria-expanded' ), 'true', label + ': desktop aria-expanded not updated' );
	assert.ok( await desktopMenu.locator( ':scope > .wpe-menu__mega-content' ).isVisible(), label + ': desktop template not visible after click' );
	await page.keyboard.press( 'Escape' );
	assert.equal( await desktopToggle.getAttribute( 'aria-expanded' ), 'false', label + ': Escape did not close mega menu' );
	await page.setViewportSize( { width: 390, height: 844 } );
	const mobileMenu = page.locator( '.wpe-menu__mobile' ).first();
	await mobileMenu.locator( ':scope > summary' ).click();
	const mobileToggle = mobileMenu.locator( '.wpe-menu__has-template > .wpe-menu__submenu-button' ).first();
	assert.ok( await mobileToggle.count() === 1, label + ': mobile submenu toggle missing' );
	await mobileToggle.click();
	assert.equal( await mobileToggle.getAttribute( 'aria-expanded' ), 'true', label + ': mobile aria-expanded not updated' );
	assert.ok( await mobileMenu.locator( '.wpe-menu__mega-content' ).first().isVisible(), label + ': mobile mega panel not visible' );
	await mobileToggle.click();
	assert.equal( await mobileToggle.getAttribute( 'aria-expanded' ), 'false', label + ': mobile submenu did not close' );
	await mobileMenu.locator( ':scope > summary' ).click();
	await page.setViewportSize( { width: 1440, height: 900 } );
	// Shared container query visibility reacts to parent size, not viewport width.
	const containerStatus = await page.evaluate( async () => {
		const form = document.querySelector( '.wpe-forms.wpek-query-container' );
		const icon = document.querySelector( '.wpe-icon-single' );
		if ( ! form || ! icon ) {
			return { error: 'missing container source or icon', narrow: null, wide: null };
		}
		const parent = icon.parentNode;
		const next = icon.nextSibling;
		const previousWidth = form.style.width;
		const nextFrame = () => new Promise( resolve => requestAnimationFrame( () => requestAnimationFrame( resolve ) ) );
		try {
			form.appendChild( icon );
			form.style.width = '280px';
			await nextFrame();
			const narrow = getComputedStyle( icon ).display;
			form.style.width = '560px';
			await nextFrame();
			const wide = getComputedStyle( icon ).display;
			return { source: getComputedStyle( form ).containerType, narrow, wide };
		} finally {
			if ( next ) {
				parent.insertBefore( icon, next );
			} else {
				parent.appendChild( icon );
			}
			form.style.width = previousWidth;
		}
	} );
	assert.equal( containerStatus.source, 'inline-size', label + ': source is not a query container' );
	assert.equal( containerStatus.narrow, 'none', label + ': 280px container should hide child' );
	assert.notEqual( containerStatus.wide, 'none', label + ': 560px container should show child' );

	// Theme tokens resolve against the real theme.json palette.
	const themeTokens = await page.evaluate( () => {
		const price = getComputedStyle( document.querySelector( '.wpe-price-list' ) );
		return { paddingTop: price.paddingTop, radius: price.borderTopLeftRadius };
	} );
	assert.equal( themeTokens.paddingTop, '8px', label + ': theme small spacing token' );
	assert.equal( themeTokens.radius, '10px', label + ': theme medium radius token' );
	console.log( 'PASS: ' + label + ': parent-width query and theme spacing/radius tokens' );
	// CSS logical spacing must flip physical edges with document direction.
	// Direction is a layout-level property. Change it and give Chromium a frame
	// to resolve logical-property mappings instead of toggling twice in one tick.
	const directionOriginal = await page.evaluate( () => ( {
		price: document.querySelector( '.wpe-price-list' ).getAttribute( 'dir' ),
		icon: document.querySelector( '.wpe-icon-single' ).getAttribute( 'dir' )
	} ) );
	const directional = {};
	try {
		for ( const dir of [ 'ltr', 'rtl' ] ) {
			await page.evaluate( value => {
				document.querySelector( '.wpe-price-list' ).setAttribute( 'dir', value );
				document.querySelector( '.wpe-icon-single' ).setAttribute( 'dir', value );
			}, dir );
			await page.waitForTimeout( 50 );
			directional[ dir ] = await page.evaluate( () => {
				const price = getComputedStyle( document.querySelector( '.wpe-price-list' ) );
				const icon = getComputedStyle( document.querySelector( '.wpe-icon-single' ) );
				return {
					marginLeft: price.marginLeft,
					marginRight: price.marginRight,
					paddingLeft: icon.paddingLeft,
					paddingRight: icon.paddingRight,
					marginStart: price.marginInlineStart,
					paddingEnd: icon.paddingInlineEnd,
					priceDirection: price.direction,
					iconDirection: icon.direction
				};
			} );
		}
	} finally {
		await page.evaluate( saved => {
			for ( const [ selector, attr ] of [ [ '.wpe-price-list', saved.price ], [ '.wpe-icon-single', saved.icon ] ] ) {
				const element = document.querySelector( selector );
				if ( attr === null ) {
					element.removeAttribute( 'dir' );
				} else {
					element.setAttribute( 'dir', attr );
				}
			}
		}, directionOriginal );
	}
	console.log( 'RTL COMPUTED:', label, JSON.stringify( directional ) );
	assert.equal( directional.ltr.marginStart, '14px', label + ': LTR logical margin value' );
	assert.equal( directional.rtl.marginStart, '14px', label + ': RTL logical margin value' );
	assert.equal( directional.ltr.marginLeft, '14px', label + ': LTR inline-start margin is left' );
	assert.equal( directional.rtl.marginRight, '14px', label + ': RTL inline-start margin is right' );
	assert.equal( directional.ltr.paddingEnd, '9px', label + ': LTR logical padding value' );
	assert.equal( directional.rtl.paddingEnd, '9px', label + ': RTL logical padding value' );
	assert.equal( directional.ltr.paddingRight, '9px', label + ': LTR inline-end padding is right' );
	assert.equal( directional.rtl.paddingLeft, '9px', label + ': RTL inline-end padding is left' );
	console.log( 'PASS: ' + label + ': LTR and RTL logical spacing edges mirror correctly' );
	// Shared advanced controls are active in both editors and respect print media.
	const glass = page.locator( '.wpe-chart.wpek-glass' ).first();
	assert.ok( await glass.count() === 1, label + ': shared glass class missing' );
	const glassBlur = await glass.evaluate( node => getComputedStyle( node ).backdropFilter );
	assert.ok( glassBlur.includes( 'blur(14px)' ), label + ': glass backdrop blur not applied: ' + glassBlur );
	const iconLink = page.locator( '.wpe-icon-single.wpek-focus-ring a' ).first();
	assert.ok( await iconLink.count() === 1, label + ': shared focus ring class missing' );
	await page.keyboard.press( 'Tab' );
	await iconLink.focus();
	// Focus styling is intentionally keyboard-only: mouse/programmatic focus alone
	// should not trigger :focus-visible. Verify the actual browser-computed result.
	const ring = await iconLink.evaluate( node => ( {
		focusVisible: node.matches( ':focus-visible' ),
		width: getComputedStyle( node ).outlineWidth,
		style: getComputedStyle( node ).outlineStyle,
		offset: getComputedStyle( node ).outlineOffset
	} ) );
	assert.ok( ring.focusVisible, label + ': keyboard focus-visible state was not activated' );
	assert.equal( ring.width, '3px', label + ': custom focus ring width not applied' );
	assert.equal( ring.style, 'solid', label + ': custom focus ring style not applied' );
	assert.equal( ring.offset, '2px', label + ': custom focus offset not applied' );
	console.log( 'PASS: ' + label + ': keyboard focus ring 3px solid, 2px offset' );
	await page.emulateMedia( { media: 'print' } );
	const printVisibility = await glass.evaluate( node => getComputedStyle( node ).display );
	assert.equal( printVisibility, 'none', label + ': hide-on-print not applied' );
	await page.emulateMedia( { media: 'screen' } );
	const flip = page.locator( '.wpe-flip__details' ).first();
	await flip.locator( 'summary' ).click();
	assert.equal( await flip.evaluate( node => node.open ), true, label + ': native flip disclosure failed' );
	console.log( 'PASS: ' + label + ': seven Kit elements and real WPForms visible' );
	for ( const vp of [
		{ width: 1440, height: 900, type: 'desktop' },
		{ width: 390, height: 844, type: 'mobile' }
	] ) {
		await page.setViewportSize( { width: vp.width, height: vp.height } );
		await page.evaluate( () => new Promise( resolve => requestAnimationFrame( () => requestAnimationFrame( resolve ) ) ) );

		const noisePct = await page.locator( '.wpe-chart.wpek-grain' ).first().evaluate( el => parseFloat( getComputedStyle( el, '::before' ).opacity ) );
		const grainDebug = await page.locator( '.wpe-chart.wpek-grain' ).first().evaluate( el => ({
			rootValue: getComputedStyle( el ).getPropertyValue( '--wpek-grain-opacity' ).trim(),
			pseudoValue: getComputedStyle( el, '::before' ).getPropertyValue( '--wpek-grain-opacity' ).trim(),
			pseudoOpacity: getComputedStyle( el, '::before' ).opacity,
			width: window.innerWidth,
			media560: matchMedia( '(max-width: 767px)' ).matches
		}) );
		console.log( 'GRAIN BREAKPOINT DEBUG:', label, vp.width, JSON.stringify( grainDebug ) );
		assert.ok( Math.abs( noisePct - ( vp.width < 768 ? 0.04 : 0.09 ) ) < 0.002, label + ': responsive grain intensity incorrect' );
		const layer2 = await page.locator( '.wpe-price-list.wpek-parallax' ).first().evaluate( node => {
			return getComputedStyle( node ).getPropertyValue( '--wpek-parallax-layer-2' ).trim();
		} );
		assert.equal( layer2, vp.width < 768 ? '0px' : '-45px', label + ': per-layer responsive off switch' );

		const stickyPosition = await page.locator( '.wpe-site-archive-widgets.wpek-sticky' ).first()
			.evaluate( node => ( { position: getComputedStyle( node ).position, top: getComputedStyle( node ).top } ) );
		assert.equal( stickyPosition.position, vp.width < 768 ? 'static' : 'sticky', label + ': responsive sticky setting' );
		if ( vp.width >= 768 ) {
			assert.equal( stickyPosition.top, '24px', label + ': sticky top offset' );
		}

		await page.evaluate( () => new Promise( resolve => requestAnimationFrame( () => requestAnimationFrame( resolve ) ) ) );
		const fluidSpacing = await page.evaluate( () => {
			const style = getComputedStyle( document.querySelector( '.wpe-chart' ) );
			return {
				padding: parseFloat( style.paddingTop ),
				gap: parseFloat( style.gap ),
				cssWidth: window.innerWidth
			};
		} );
		const expectedPadding = vp.width === 1440 ? 32 : 8 + ( 32 - 8 ) * ( ( vp.width - 360 ) / 1080 );
		assert.equal( fluidSpacing.cssWidth, vp.width, label + ': CSS viewport width mismatch' );
		const expectedGap = vp.width === 1440 ? 16 : 4 + ( 16 - 4 ) * ( ( vp.width - 360 ) / 1080 );
		assert.ok( Math.abs( fluidSpacing.padding - expectedPadding ) < 0.2, label + ': fluid padding differs: ' + JSON.stringify( fluidSpacing ) );
		assert.ok( Math.abs( fluidSpacing.gap - expectedGap ) < 0.2, label + ': fluid gap differs: ' + JSON.stringify( fluidSpacing ) );

		const archive = page.locator( '.wpe-site-archive-widgets.wpek-device-desktop' ).first();
		assert.ok( await archive.count(), label + ': CSS device rule missing' );
		const displayed = await archive.evaluate( node => getComputedStyle( node ).display );
		if ( vp.width < 768 ) {
			assert.equal( displayed, 'none', label + ': desktop-only element visible on mobile' );
		} else {
			assert.notEqual( displayed, 'none', label + ': desktop-only element absent on desktop' );
		}
		for ( const scheme of [ 'light', 'dark' ] ) {
			await page.evaluate( mode => document.documentElement.setAttribute( 'data-wpe-scheme', mode ), scheme );
			const palette = await page.evaluate( () => {
				const target = document.querySelector( '.wpe-forms .wpforms-field-label' );
				if ( ! target ) {
					return { foreground: '', background: '' };
				}
				// Compare with the actually painted ancestor, not the palette variable.
				// Theme transitions may leave a stale computed preset during mobile viewport changes.
				let background = '';
				for ( let element = target; element; element = element.parentElement ) {
					const value = getComputedStyle( element ).backgroundColor;
					if ( value && value !== 'transparent' && ! /rgba\(0, 0, 0, 0\)/.test( value ) ) {
						background = value;
						break;
					}
				}
				return {
					foreground: getComputedStyle( target ).color,
					background: background || ( document.documentElement.getAttribute( 'data-wpe-scheme' ) === 'dark' ? 'rgb(13, 18, 34)' : 'rgb(255, 255, 255)' )
				};
			} );
			assert.ok( palette.foreground && palette.background, label + ': missing WPForms colors' );
			const contrastRatio = contrast( palette.foreground, palette.background );
			console.log( 'CONTRAST:', label, vp.type, scheme, palette, contrastRatio.toFixed( 2 ) + ':1' );
			assert.ok( contrastRatio >= 4.5, label + ' ' + vp.type + ' ' + scheme + ': WPForms label WCAG AA contrast failure (' + contrastRatio.toFixed( 2 ) + ':1)' );
			await page.screenshot( { path: path.join( dir, label + '-' + vp.type + '-' + scheme + '.png' ), fullPage: true } );
			const overflow = await page.evaluate( () => Math.max( 0, document.documentElement.scrollWidth - document.documentElement.clientWidth ) );
			assert.ok( overflow < 125, label + ': severe horizontal overflow: ' + overflow );
			console.log( 'PASS: ' + label + ' ' + vp.type + ' ' + scheme + ' (overflow: ' + overflow + 'px)' );
		}
	}
	// A deterministic long page lets us verify that the named marker ends sticky
	// before its parent ends. This uses a clone of the actual rendered Kit element.
	await page.setViewportSize( { width: 1440, height: 900 } );
	const stopped = await page.evaluate( async () => {
		const original = document.querySelector( '.wpe-site-archive-widgets.wpek-sticky-stop' );
		if ( ! original ) {
			return { error: 'Sticky root with named stop is missing' };
		}
		const previous = window.scrollY;
		const stage = document.createElement( 'div' );
		stage.style.cssText = 'position:relative;height:1200px;min-height:1200px';
		const sticky = original.cloneNode( true );
		sticky.removeAttribute( 'id' );
		sticky.style.setProperty( '--wpek-sticky-offset', '24px' );
		const marker = document.createElement( 'div' );
		marker.id = 'kit-motion-stop';
		marker.style.cssText = 'position:absolute;top:430px;height:1px';
		const tail = document.createElement( 'div' );
		tail.style.height = '950px';
		stage.append( sticky, marker );
		document.body.append( stage, tail );
		const frames = () => new Promise( resolve => requestAnimationFrame( () => requestAnimationFrame( resolve ) ) );
		try {
			await frames();
			window.scrollTo( 0, window.scrollY + stage.getBoundingClientRect().top + 470 );
			await frames();
			await frames();
			const computed = getComputedStyle( sticky );
			return {
				push: parseFloat( computed.getPropertyValue( '--wpek-sticky-stop-push' ) ),
				position: computed.position,
				targetTop: marker.getBoundingClientRect().top,
				stickyBottom: sticky.getBoundingClientRect().bottom
			};
		} finally {
			stage.remove();
			tail.remove();
			window.scrollTo( 0, previous );
		}
	} );
	assert.equal( stopped.position, 'sticky', label + ': sticky disabled in desktop test' );
	assert.ok( Number.isFinite( stopped.push ) && stopped.push < -20, label + ': stop marker did not halt sticky: ' + JSON.stringify( stopped ) );
	console.log( 'PASS: ' + label + ': named sticky stop applies actual negative offset' );
}
async function verifyGutenberg( page ) {
	await page.setViewportSize( { width: 1440, height: 900 } );
	const response = await page.goto( base + '/wp-admin/post.php?post=' + fixture.gutenberg + '&action=edit', { waitUntil: 'domcontentloaded', timeout: 60000 } );
	assert.equal( response.status(), 200 );
	await page.waitForFunction( () => window.wp && wp.blocks && wp.data && wp.data.select( 'core/block-editor' ), null, { timeout: 60000 } );
	await page.waitForFunction( () => wp.data.select( 'core/block-editor' ).getBlocks().length >= 7, null, { timeout: 60000 } );
	const state = await page.evaluate( () => ({
		registered: [ 'price-list', 'menu', 'forms', 'icon', 'chart', 'flip', 'site-archive-widgets' ].map( name => Boolean( wp.blocks.getBlockType( 'wpessential/' + name ) ) ),
		blocks: wp.data.select( 'core/block-editor' ).getBlocks().map( block => block.name )
	}) );
	assert.ok( state.registered.every( Boolean ), 'Gutenberg does not register all blocks' );
	for ( const name of fixture.elements ) {
		assert.ok( state.blocks.includes( 'wpessential/' + name ), 'Gutenberg missing block ' + name );
	}
	const title = await page.evaluate( () => {
		const editor = wp.data.select( 'core/block-editor' );
		const chart = editor.getBlocks().find( item => item.name === 'wpessential/chart' );
		wp.data.dispatch( 'core/block-editor' ).selectBlock( chart.clientId );
		wp.data.dispatch( 'core/block-editor' ).updateBlockAttributes( chart.clientId, { title: 'QA Chart Edited' } );
		return wp.data.select( 'core/block-editor' ).getBlockAttributes( chart.clientId ).title;
	} );
	assert.equal( title, 'QA Chart Edited' );
	// Dismiss the first-run editor welcome guide so the actual inspector is visible.
	const welcome = page.locator( '.components-modal__frame' );
	if ( await welcome.count() > 0 ) {
		const close = welcome.locator( '.components-modal__header button' ).first();
		if ( await close.count() > 0 ) {
			await close.click();
		} else {
			await page.keyboard.press( 'Escape' );
		}
	}
	await page.getByText( 'Chart heading', { exact: true } ).first().waitFor( { state: 'visible', timeout: 20000 } );
	await page.screenshot( { path: path.join( dir, 'gutenberg-editor.png' ), fullPage: true } );
	console.log( 'PASS: Gutenberg real editor, sidebar control and block-attribute update' );
}
async function verifyElementor( page ) {
	const response = await page.goto( base + '/wp-admin/post.php?post=' + fixture.elementor + '&action=elementor', { waitUntil: 'domcontentloaded', timeout: 90000 } );
	assert.equal( response.status(), 200 );
	await page.waitForSelector( '#elementor-preview-iframe', { timeout: 90000 } );
	const preview = page.frameLocator( '#elementor-preview-iframe' );
	await preview.locator( '.wpe-price-list' ).first().waitFor( { state: 'attached', timeout: 90000 } );
	for ( const [ name, selector ] of Object.entries( targets ) ) {
		assert.ok( await preview.locator( selector ).count(), 'Elementor preview missing ' + name );
	}
	// Do not count an Elementor iframe as a working editor when the outer UI is still loading.
	try {
		await page.locator( '#elementor-panel' ).waitFor( { state: 'visible', timeout: 90000 } );
		const loading = page.locator( '#elementor-loading' );
		if ( await loading.count() > 0 ) {
			await loading.waitFor( { state: 'hidden', timeout: 30000 } );
		}
		await page.waitForTimeout( 1500 );
	} catch ( error ) {
		const diagnostic = await page.evaluate( () => ({
			title: document.title,
			panel: Boolean( document.querySelector( '#elementor-panel' ) ),
			panelVisible: Boolean( document.querySelector( '#elementor-panel' ) && document.querySelector( '#elementor-panel' ).getClientRects().length ),
			loading: Boolean( document.querySelector( '#elementor-loading' ) && document.querySelector( '#elementor-loading' ).getClientRects().length ),
			elementor: Boolean( window.elementor ),
			body: document.body.innerText.slice( 0, 2500 ),
			iframes: Array.from( document.querySelectorAll( 'iframe' ) ).map( e => e.id )
		}) );
		fs.writeFileSync( path.join( dir, 'elementor-editor-diagnostics.json' ), JSON.stringify( diagnostic, null, 2 ) );
		await page.screenshot( { path: path.join( dir, 'elementor-editor-not-ready.png' ), fullPage: true } );
		throw new Error( 'Elementor outer editor not interactive: ' + JSON.stringify( diagnostic ) + '; original: ' + error.message );
	}
	await page.screenshot( { path: path.join( dir, 'elementor-editor.png' ), fullPage: true } );
	console.log( 'PASS: Elementor outer editor panel is interactive, with all seven widget previews' );
}
(async () => {
	const browser = await chromium.launch( { headless: true, args: [ '--no-sandbox' ] } );
	const context = await browser.newContext( { viewport: { width: 1440, height: 900 }, reducedMotion: 'reduce' } );
	const page = await context.newPage();
	try {
		await page.goto( base + '/wp-login.php' );
		await page.locator( '#user_login' ).fill( 'testadmin' );
		await page.locator( '#user_pass' ).fill( 'testpassword' );
		await Promise.all( [
			page.waitForURL( /wp-admin/, { timeout: 30000 } ),
			page.locator( '#wp-submit' ).click()
		] );
		console.log( 'PASS: logged into WordPress' );
		await verifyPage( page, fixture.gutenberg, 'gutenberg-front' );
		await verifyPage( page, fixture.elementor, 'elementor-front' );
		await verifyGutenberg( page );
		await verifyElementor( page );
		fs.writeFileSync( path.join( dir, 'report.json' ), JSON.stringify( { success: true, fixture }, null, 2 ) );
	} catch ( error ) {
		fs.writeFileSync( path.join( dir, 'report.json' ), JSON.stringify( { success: false, error: String( error.stack || error ) }, null, 2 ) );
		await page.screenshot( { path: path.join( dir, 'failure.png' ), fullPage: true } ).catch( () => {} );
		throw error;
	} finally {
		await browser.close();
	}
})().catch( error => {
	console.error( 'FAIL: Kit browser QA:', error.stack || error );
	process.exitCode = 1;
} );
