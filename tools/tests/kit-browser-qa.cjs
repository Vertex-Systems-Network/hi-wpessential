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
	// Shared advanced controls are active in both editors and respect print media.
	const glass = page.locator( '.wpe-chart.wpek-glass' ).first();
	assert.ok( await glass.count() === 1, label + ': shared glass class missing' );
	const glassBlur = await glass.evaluate( node => getComputedStyle( node ).backdropFilter );
	assert.ok( glassBlur.includes( 'blur(14px)' ), label + ': glass backdrop blur not applied: ' + glassBlur );
	const iconLink = page.locator( '.wpe-icon-single.wpek-focus-ring a' ).first();
	assert.ok( await iconLink.count() === 1, label + ': shared focus ring class missing' );
	await iconLink.focus();
	const ringWidth = await iconLink.evaluate( node => getComputedStyle( node ).outlineWidth );
	assert.equal( ringWidth, '3px', label + ': custom focus ring width not applied' );
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
