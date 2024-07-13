/* global ajaxurl, SiteHealthTools */
jQuery( document ).ready( function( $ ) {
	$( '#site-health-file-integrity' ).on( 'submit', function( e ) {
		const data = {
			action: 'site-health-files-integrity-check',
			_wpnonce: SiteHealthTools.nonce.files_integrity_check,
		};

		e.preventDefault();

		$( '#tools-file-integrity-response-holder' ).html( '<span class="spinner"></span>' );
		$( '#tools-file-integrity-response-holder .spinner' ).addClass( 'is-active' );

		$.post(
			ajaxurl,
			data,
			function( response ) {
				$( '#tools-file-integrity-response-holder .spinner' ).removeClass( 'is-active' );
				$( '#tools-file-integrity-response-holder' ).parent().css( 'height', 'auto' );
				$( '#tools-file-integrity-response-holder' ).html( response.data.message );
			}
		);
	} );

	$( '#tools-file-integrity-response-holder' ).on( 'click', 'a[href="#site-health-diff"]', function( e ) {
		const file = $( this ).data( 'file' );

		e.preventDefault();

		$( '#site-health-diff-modal' ).toggle();
		$( '#site-health-diff-modal #site-health-diff-modal-content .spinner' ).addClass( 'is-active' );

		const data = {
			action: 'site-health-view-file-diff',
			file,
			_wpnonce: SiteHealthTools.nonce.view_file_diff,
		};

		$.post(
			ajaxurl,
			data,
			function( response ) {
				$( '#site-health-diff-modal #site-health-diff-modal-diff' ).html( response.data.message );
				$( '#site-health-diff-modal #site-health-diff-modal-content h3' ).html( file );
				$( '#site-health-diff-modal #site-health-diff-modal-content .spinner' ).removeClass( 'is-active' );
			}
		);
	} );
} );
