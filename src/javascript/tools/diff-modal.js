jQuery( document ).ready( function( $ ) {
	$( '#site-health-diff-modal' ).on( 'click', 'a[href="#site-health-diff-modal-close"]', function( e ) {
		e.preventDefault();
		$( '#site-health-diff-modal' ).toggle();
		$( '#site-health-diff-modal #site-health-diff-modal-diff' ).html( '' );
		$( '#site-health-diff-modal #site-health-diff-modal-content h3' ).html( '' );
	} );

	$( document ).on( 'keyup', function( e ) {
		if ( 27 === e.which ) {
			$( '#site-health-diff-modal' ).css( 'display', 'none' );
			$( '#site-health-diff-modal #site-health-diff-modal-diff' ).html( '' );
			$( '#site-health-diff-modal #site-health-diff-modal-content h3' ).html( '' );
		}
	} );
} );
