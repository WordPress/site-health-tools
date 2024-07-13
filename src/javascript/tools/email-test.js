/* global ajaxurl, SiteHealthTools */
jQuery( document ).ready( function( $ ) {
	$( '#site-health-mail-check' ).on( 'submit', function( e ) {
		const email = $( '#site-health-mail-check #email' ).val(),
			emailMessage = $( '#site-health-mail-check #email_message' ).val();

		e.preventDefault();

		$( '#tools-mail-check-response-holder' ).html( '<span class="spinner"></span>' );
		$( '#tools-mail-check-response-holder .spinner' ).addClass( 'is-active' );

		const data = {
			action: 'site-health-mail-check',
			email,
			email_message: emailMessage,
			_wpnonce: SiteHealthTools.nonce.mail_check,
		};

		$.post(
			ajaxurl,
			data,
			function( response ) {
				$( '#tools-mail-check-response-holder .spinner' ).removeClass( 'is-active' );
				$( '#tools-mail-check-response-holder' ).parent().css( 'height', 'auto' );
				$( '#tools-mail-check-response-holder' ).html( response.data.message );
			}
		);
	} );
} );
