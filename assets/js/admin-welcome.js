/**
 * Welcome banner: installs WPEssential from WordPress.org with core's wp.updates (the same
 * flow as Plugins > Add New), then follows the activation link. Without JavaScript the
 * buttons are ordinary links to the core install and activate screens.
 */
( function () {
	'use strict';
	var banner = document.querySelector( '.wpe-welcome' );
	if ( ! banner ) {
		return;
	}
	var __ = window.wp && window.wp.i18n ? window.wp.i18n.__ : function ( s ) {
		return s;
	};
	var cta = banner.querySelector( '.wpe-welcome__cta' );
	var status = banner.querySelector( '.wpe-welcome__status' );

	function say( text ) {
		status.textContent = text;
	}

	if ( cta && 'install' === cta.dataset.action && window.wp && window.wp.updates ) {
		cta.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			if ( cta.classList.contains( 'is-busy' ) ) {
				return;
			}
			cta.classList.add( 'is-busy' );
			cta.setAttribute( 'aria-disabled', 'true' );
			cta.textContent = __( 'Installing…', 'hi-wpessential' );
			say( __( 'Installing WPEssential from WordPress.org…', 'hi-wpessential' ) );
			window.wp.updates.installPlugin( {
				slug: 'wpessential',
				success: function ( response ) {
					cta.textContent = __( 'Activating…', 'hi-wpessential' );
					say( __( 'Installed. Activating…', 'hi-wpessential' ) );
					window.location.href = response.activateUrl || banner.dataset.activate;
				},
				error: function ( response ) {
					// Already installed (e.g. in another tab): just activate it.
					if ( response && 'folder_exists' === response.errorCode ) {
						window.location.href = banner.dataset.activate;
						return;
					}
					cta.classList.remove( 'is-busy' );
					cta.removeAttribute( 'aria-disabled' );
					cta.textContent = __( 'Try again', 'hi-wpessential' );
					say( ( response && response.errorMessage ) || __( 'The install failed. Try again, or install it from Plugins > Add New.', 'hi-wpessential' ) );
				},
			} );
		} );
	}

	// The core X button (is-dismissible) hides it for good.
	banner.addEventListener( 'click', function ( event ) {
		if ( ! event.target.closest( '.notice-dismiss' ) ) {
			return;
		}
		var body = new window.FormData();
		body.append( 'action', 'wpessential_dismiss_welcome' );
		body.append( '_ajax_nonce', window.wpessentialWelcome.nonce );
		window.fetch( window.wpessentialWelcome.ajax, { method: 'POST', credentials: 'same-origin', body: body } );
	} );
}() );
