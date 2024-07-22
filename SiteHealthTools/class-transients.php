<?php
/**
 * Provide an overview of, and means to flush, transient data.
 *
 * @package Site Health Tools
 */

namespace SiteHealthTools;

// Make sure the file is not directly accessible.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

class Transients extends Site_Health_Tool {

	public function __construct() {
		$this->label       = \__( 'Transient summary', 'site-health-tools' );
		$this->description = \__( 'Transients are temporary pieces of data, that is often requested, or gathered from third party sources, and stored in your website database to improve site performance. These pieces of data may over time become large and take up a lot of space, and can then be safely deleted, as their content is not critical to the functionality of your site.', 'site-health-tools' );

		\add_action( 'wp_ajax_site-health-clear-transients', array( $this, 'clear_transients' ) );

		parent::__construct();
	}

	public function clear_transients() : void {
		global $wpdb;

		\check_ajax_referer( 'site-health-clear-transients' );

		if ( ! \current_user_can( 'manage_options' ) ) {
			\wp_send_json_error( \__( 'Sorry, you are not allowed to manage options on this site.', 'site-health-tools' ) );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct, and uncached query is used to most efficiently handle the SQL cleanup process.
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_%' OR option_name LIKE '\_site\_transient\_%'" );

		\wp_cache_flush();

		\wp_send_json_success(
			array(
				'message' => sprintf(
					'<p><strong>%s</strong></p>',
					\__( 'Transients cleared.', 'site-health-tools' )
				),
			)
		);
	}

	public function tab_content() : void {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct, and uncached query is used to get the most accurate values possible.
		$transients = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE ( option_name LIKE '\_transient\_%' OR option_name LIKE '\_site\_transient\_%' ) AND option_name NOT LIKE '%_transient_timeout_%'" );

		if ( ! $transients ) {
			echo '<p>' . \esc_html__( 'No transients found.', 'site-health-tools' ) . '</p>';
			return;
		}

		echo sprintf(
			'<p>%s</p>',
			sprintf(
				// translators: %1$s: Number of transients, %2$s: Size of transients.
				\esc_html__( 'Your site currently contains a total of %1$s transients, taking up an estimated %2$s of space in your database.', 'site-health-tools' ),
				sprintf(
					'<strong>%d</strong>',
					count( $transients )
				),
				sprintf(
					'<strong>%s</strong>',
					\esc_html( (string) \size_format( strlen( serialize( $transients ) ) ) )
				)
			)
		);

		echo '<button type="button" id="site-health-tools-clear-transients" class="button button-primary">' . \esc_html__( 'Delete all transients', 'site-health-tools' ) . '</button>';
	}
}

new Transients();
