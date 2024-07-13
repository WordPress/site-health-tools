/* global SiteHealthTools */
jQuery( document ).ready( function( $ ) {
	$( '#site-health-tool-plugin-compat' ).on( 'click', function() {
		$( 'tr', '#site-health-tool-plugin-compat-list' ).data( 'plugin-checked', false );
		$( '.spinner', '#site-health-tool-plugin-compat-list' ).addClass( 'is-active' );

		$( this ).attr( 'disabled', true );

		HealthCheckToolsPluginCompatTest();
	} );

	function HealthCheckToolsPluginCompatTest() {
		const $plugins = $( '[data-plugin-checked="false"]', '#site-health-tool-plugin-compat-list' );

		if ( $plugins.length <= 0 ) {
			return;
		}

		const $nextPlugin = $( $plugins[ 0 ] );

		$nextPlugin.attr( 'data-plugin-checked', 'true' );

		const data = {
			slug: $nextPlugin.data( 'plugin-slug' ),
			version: $nextPlugin.data( 'plugin-version' ),
			_wpnonce: SiteHealthTools.nonce.rest_api,
		};

		$.post(
			SiteHealthTools.rest_api.tools.plugin_compat,
			data,
			function( response ) {
				$( '.spinner', $nextPlugin ).removeClass( 'is-active' );
				$( '.supported-version', $nextPlugin ).append( response.version );

				HealthCheckToolsPluginCompatTest();
			}
		);
	}
} );
