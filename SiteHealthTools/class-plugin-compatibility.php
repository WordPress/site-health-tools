<?php

namespace SiteHealthTools;

class Plugin_Compatibility extends Site_Health_Tool {

	public function __construct() {
		$this->label       = \__( 'Plugin compatibility', 'site-health-tools' );
		$this->description = sprintf(
			'%s<br>%s',
			\__( 'Attempt to identify the compatibility of your plugins before upgrading PHP, note that a compatibility check may not always be accurate, and you may want to contact the plugin author to confirm that things will continue working.', 'site-health-tools' ),
			\__( 'The compatibility check will need to send requests to the <a href="https://wptide.org">WPTide</a> project to fetch the test results for each of your plugins.', 'site-health-tools' )
		);

		\add_action( 'rest_api_init', array( $this, 'register_plugin_compat_rest_route' ) );

		parent::__construct();
	}

	public function register_plugin_compat_rest_route() : void {
		\register_rest_route(
			'site-health-tools/v1',
			'plugin-compat',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'check_plugin_version' ),
				'permission_callback' => function() {
					return \current_user_can( 'view_site_health_checks' );
				},
			)
		);
	}

	public function tab_content() : void {
		?>
		<table class="wp-list-table widefat fixed striped" id="site-health-tool-plugin-compat-list">
			<thead>
			<tr>
				<th><?php \esc_html_e( 'Plugin', 'site-health-tools' ); ?></th>
				<th><?php \esc_html_e( 'Version', 'site-health-tools' ); ?></th>
				<th><?php \esc_html_e( 'Minimum PHP', 'site-health-tools' ); ?></th>
				<th><?php \esc_html_e( 'Highest supported PHP', 'site-health-tools' ); ?></th>
			</tr>
			</thead>

			<tbody>
			<?php
			$plugins = \get_plugins();

			foreach ( $plugins as $slug => $plugin ) {
				printf(
					'<tr data-plugin-slug="%s" data-plugin-version="%s" data-plugin-checked="false"><td>%s</td><td>%s</td><td>%s</td><td class="supported-version">%s</td></tr>',
					\esc_attr( $slug ),
					\esc_attr( $plugin['Version'] ),
					\esc_html( $plugin['Name'] ),
					\esc_html( $plugin['Version'] ),
					( isset( $plugin['RequiresPHP'] ) && ! empty( $plugin['RequiresPHP'] ) ? \esc_html( $plugin['RequiresPHP'] ) : '&mdash;' ),
					'<span class="spinner"></span>'
				);
			}
			?>
			</tbody>
		</table>

		<p>
			<button type="button" class="button button-primary" id="site-health-tool-plugin-compat">
				<?php \esc_html_e( 'Check plugins', 'site-health-tools' ); ?>
			</button>
		</p>
		<?php
	}

	/**
	 * Check the compatibility of a plugin.
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	function check_plugin_version( \WP_REST_Request $request ) {
		if ( ! $request->has_param( 'slug' ) || ! $request->has_param( 'version' ) ) {
			return new \WP_Error( 'missing_arg', \__( 'The slug, or version, is missing from the request.', 'site-health-tools' ) );
		}

		$slug    = $request->get_param( 'slug' );
		$version = $request->get_param( 'version' );

		/*
		 * Override for the Health Check plugin, which has back-compat code we are aware
		 * of and can account for early on. It should not become a habit to add exceptions for
		 * plugins in this field, this is rather to avoid confusion and concern in users of this plugin specifically.
		 */
		if ( 'site-health/site-health.php' === $slug ) {
			$response = array(
				'version' => '8.1',
			);
		} else {
			$response = array(
				'version' => $this->get_highest_supported_php( $slug, $version ),
			);
		}

		return new \WP_REST_Response( $response, 200 );
	}

	function get_highest_supported_php( string $slug, string $version ) : string {
		$versions = $this->get_supported_php( $slug, $version );

		if ( empty( $versions ) ) {
			return \__( 'Could not be determined', 'site-health-tools' );
		}

		$highest = '0';

		foreach ( $versions as $version ) {
			if ( version_compare( $version, $highest, '>' ) ) {
				$highest = $version;
			}
		}

		return $highest;
	}

	/**
	 * Get the supported PHP versions for a plugin.
	 *
	 * @param string $slug    The plugin slug.
	 * @param string $version The plugin version.
	 *
	 * @return array<int, string>
	 */
	function get_supported_php( string $slug, string $version ) : array {
		// Clean up the slug, in case it's got more details
		if ( stristr( $slug, '/' ) ) {
			$parts = explode( '/', $slug );
			$slug  = $parts[0];
		}

		$transient_name = sprintf(
			'site-health-tide-%s-%s',
			$slug,
			$version
		);

		$tide_versions = \get_transient( $transient_name );

		if ( false === $tide_versions ) {
			$tide_api_response = \wp_remote_get(
				sprintf(
					'https://wptide.org/api/v1/audit/wporg/plugin/%s/%s?reports=all',
					$slug,
					$version
				)
			);

			$tide_response = \wp_remote_retrieve_body( $tide_api_response );

			$json = json_decode( $tide_response );

			if ( empty( $json ) || ! isset( $json->reports->phpcs_phpcompatibilitywp->report->compatible ) ) {
				$tide_versions = array();
			} else {
				$tide_versions = $json->reports->phpcs_phpcompatibilitywp->report->compatible;
			}

			\set_transient( $transient_name, $tide_versions, 1 * WEEK_IN_SECONDS );
		}

		return $tide_versions;
	}
}

new Plugin_Compatibility();
