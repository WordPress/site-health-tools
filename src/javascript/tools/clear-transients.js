/* global ajaxurl, SiteHealthTools */
jQuery( document ).ready( function( $ ) {
	$( 'body' ).on( 'click', '#site-health-tools-clear-transients', function( e ) {
		e.preventDefault();

		const data = {
			action: 'site-health-clear-transients',
			_wpnonce: SiteHealthTools.nonce.clear_transients,
		};

		$.post(
			ajaxurl,
			data,
			function( response ) {
				$( '#tools-clear-transients-response-holder' ).html( response.data.message );
			}
		);
	} );
} );
