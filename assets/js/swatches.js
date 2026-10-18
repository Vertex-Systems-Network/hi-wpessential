/* Variation swatches: shows each variation <select> on product pages as buttons that drive the original select. */
( function () {
	document.querySelectorAll( 'form.variations_form select[name^="attribute_"]' ).forEach( function ( select ) {
		var row = document.createElement( 'div' );
		row.className = 'wpe-swatches';
		row.setAttribute( 'role', 'radiogroup' );
		Array.prototype.forEach.call( select.options, function ( opt ) {
			if ( ! opt.value ) {
				return;
			}
			var b = document.createElement( 'button' );
			b.type = 'button';
			b.className = 'wpe-swatch';
			b.textContent = opt.textContent;
			b.setAttribute( 'role', 'radio' );
			b.setAttribute( 'aria-checked', 'false' );
			b.addEventListener( 'click', function () {
				select.value = select.value === opt.value ? '' : opt.value;
				select.dispatchEvent( new Event( 'change', { bubbles: true } ) );
				if ( window.jQuery ) {
					window.jQuery( select ).trigger( 'change' );
				}
			} );
			row.appendChild( b );
		} );
		select.classList.add( 'wpe-swatches__select' );
		select.insertAdjacentElement( 'afterend', row );
		function sync() {
			row.querySelectorAll( '.wpe-swatch' ).forEach( function ( b, i ) {
				var opt = Array.prototype.filter.call( select.options, function ( o ) { return o.value; } )[ i ];
				b.setAttribute( 'aria-checked', String( !! opt && opt.value === select.value ) );
				b.disabled = !! opt && opt.disabled;
			} );
		}
		select.addEventListener( 'change', sync );
		if ( window.jQuery ) {
			window.jQuery( select ).on( 'change', sync );
			window.jQuery( select.form ).on( 'woocommerce_update_variation_values', sync );
		}
		sync();
	} );
}() );
