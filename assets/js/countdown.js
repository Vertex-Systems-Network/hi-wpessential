/* Live countdown for [data-date] blocks (Pulse home). Updates days, hours, minutes and seconds every second. */
( function () {
	var boxes = document.querySelectorAll( '.wpe-px-count[data-date]' );
	if ( ! boxes.length ) {
		return;
	}
	function pad( n ) {
		return ( n < 10 ? '0' : '' ) + n;
	}
	function tick() {
		boxes.forEach( function ( box ) {
			var left = Math.max( 0, Date.parse( box.getAttribute( 'data-date' ) ) - Date.now() ) / 1000;
			var parts = [ Math.floor( left / 86400 ), Math.floor( left / 3600 ) % 24, Math.floor( left / 60 ) % 60, Math.floor( left ) % 60 ];
			box.querySelectorAll( 'b' ).forEach( function ( el, i ) {
				el.textContent = i ? pad( parts[ i ] ) : parts[ i ];
			} );
		} );
	}
	tick();
	setInterval( tick, 1000 );
}() );
