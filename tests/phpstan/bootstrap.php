<?php
/**
 * Bootstrap file for declarations needed by PHPStan.
 *
 * Any declarations here should match what their values would be within WordPress core,
 * or what the plugin would dictate them to be when ran on the wp-env local environment.
 */

if ( ! defined( 'WPINC' ) ) { define( 'WPINC', 'wp-includes' ); }
if ( ! defined( 'WP_CONTENT_DIR' ) ) { define( 'WP_CONTENT_DIR', 'wp-content' ); }

if ( ! defined( 'SITE_HEALTH_TOOLS_PLUGIN_DIRECTORY' ) ) { define( 'SITE_HEALTH_TOOLS_PLUGIN_DIRECTORY', '/var/www/html/wp-content/plugins/site-health-tools/' ); }
