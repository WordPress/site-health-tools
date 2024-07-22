<?php
/**
 * Plugins primary file, in charge of including all necessary files for the plugin to work.
 *
 * @package Site Health Tools
 *
 * @wordpress-plugin
 * Plugin Name: Site Health Tools
 * Plugin URI: https://wordpress.org/plugins/site-health-tools/
 * Description: A plugin that adds tools to the Site Health page in WordPress.
 * Version: 1.0.0
 * Author: Clorith
 * Requires PHP: 7.1
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: site-health-tools
 */

namespace SiteHealthTools;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You may not directly access this file.' );
}

define( 'SITE_HEALTH_TOOLS_PLUGIN_DIRECTORY', __DIR__ );

$tool_files = array(
	'class-site-health-tool.php',
	'class-debug-log-viewer.php',
	'class-files-integrity.php',
	'class-htaccess.php',
	'class-mail-check.php',
	'class-phpinfo.php',
	'class-plugin-compatibility.php',
	'class-robotstxt.php',
	'class-transients.php',
);

foreach ( $tool_files as $tool_file ) {
	if ( ! file_exists( __DIR__ . '/SiteHealthTools/' . $tool_file ) ) {
		continue;
	}

	require_once __DIR__ . '/SiteHealthTools/' . $tool_file;
}

/**
 * Adds the Tools tab to the Site Health page.
 *
 * @param array<string, string> $tabs The tabs on the Site Health page.
 *
 * @return array<string, string>
 */
function add_tools_tab( array $tabs ) : array {
	return array_merge(
		$tabs,
		array(
			'tools' => \esc_html__( 'Tools', 'health-check' ),
		)
	);
}

/**
 * Adds the content for the Tools tab on the Site Health page.
 *
 * @param string $tab The current tab being viewed.
 *
 * @return void
 */
function add_tools_tab_content( string $tab ) : void {
	if ( 'tools' !== $tab ) {
		return;
	}

	include_once( __DIR__ . '/templates/tools.php' );
}

/**
 * Enqueues the scripts and styles for the plugin.
 *
 * @return void
 */
function enqueue_scripts() : void {
	$screen = \get_current_screen();

	if ( 'tools_page_site-health' !== $screen->id && 'site-health' !== $screen->id ) {
		return;
	}

	if ( ! isset( $_GET['tab'] ) || 'tools' !== $_GET['tab'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not needed, as we are checking which tab to present to the user, and not processing data.
		return;
	}

	$assets = include __DIR__ . '/build/site-health-tools.asset.php';

	// Set up dependencies this way to also include jQuery which is not automatically picked up by the dependency scanner.
	$dependencies = array_merge(
		$assets['dependencies'],
		array( 'jquery' )
	);

	\wp_enqueue_style( 'site-health-tools', \plugins_url( 'build/site-health-tools.css', __FILE__ ), array(), $assets['version'] );
	\wp_enqueue_script( 'site-health-tools', \plugins_url( 'build/site-health-tools.js', __FILE__ ), $dependencies, $assets['version'], true );
	\wp_localize_script(
		'site-health-tools',
		'SiteHealthTools',
		array(
			'rest_api' => array(
				'tools' => array(
					'plugin_compat' => \rest_url( 'site-health-tools/v1/plugin-compat' ),
				),
			),
			'nonce'    => array(
				'rest_api'              => \wp_create_nonce( 'wp_rest' ),
				'files_integrity_check' => \wp_create_nonce( 'site-health-files-integrity-check' ),
				'view_file_diff'        => \wp_create_nonce( 'site-health-view-file-diff' ),
				'mail_check'            => \wp_create_nonce( 'site-health-mail-check' ),
				'clear_transients'      => \wp_create_nonce( 'site-health-clear-transients' ),
			),
		)
	);
}

\add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts' );
\add_filter( 'site_health_navigation_tabs', __NAMESPACE__ . '\add_tools_tab' );
\add_action( 'site_health_tab_content', __NAMESPACE__ . '\add_tools_tab_content' );
