<?php

namespace SiteHealthTools;

class Debug_Log_Viewer extends Site_Health_Tool {

	public function __construct() {
		$this->label       = \__( 'Debug logs', 'site-health-tools' );
		$this->description = \__( 'When configured, and enabled, this section will show you any errors or warnings that have been caused by code on your site.', 'site-health-tools' );

		parent::__construct();
	}

	private function read_debug_log() : string {
		if ( ! defined( 'WP_DEBUG_LOG' ) || false === \WP_DEBUG_LOG ) {
			return '';
		}

		$logfile = \WP_DEBUG_LOG;

		/*
		 * `WP_DEBUG_LOG` can be a boolean value, or a path to a file.
		 * In the case of a boolean value of `true`, the default file location path will be used.
		 */
		if ( is_bool( $logfile ) ) {
			$logfile = \WP_CONTENT_DIR . '/debug.log';
		}

		if ( ! file_exists( $logfile ) ) {
			return '';
		}

		$debug_log = @file_get_contents( $logfile ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- `file_get_contents` used to retrieve contents of local file.

		if ( false === $debug_log ) {
			return sprintf(
				// translators: %s: The path to the debug log file.
				\__( 'The debug log file found at `%s`, could not be read.', 'site-health-tools' ),
				$logfile
			);
		}

		return $debug_log;
	}

	public function tab_content() : void {
		if ( ! defined( 'WP_DEBUG_LOG' ) || false === \WP_DEBUG_LOG ) {
			printf(
				'<p>%s</p>',
				sprintf(
					// translators: %s: The `WP_DEBUG_LOG` constant wrapped in code tags.
					\esc_html__( 'Because the %s constant is not set to allow logging of errors and warnings, there are no more details here.', 'site-health-tools' ),
					'<code>WP_DEBUG_LOG</code>'
				)
			);
		}

		printf(
			'<p>%s</p>',
			sprintf(
				// translators: 1: Reference to the `WP_DEBUG_LOG` constant wrapped in code tags. 2: The URL to the Debugging in WordPress article.
				\esc_html__( 'You can read more about the %1$s constant, as well as how to enable and configure it, in the %2$s article.', 'site-health-tools' ),
				'<code>WP_DEBUG_LOG</code>',
				sprintf(
					'<a href="%s">%s</a>',
					// translators: The localized URL to the Debugging in WordPress article, if available.
					\esc_html__( 'https://wordpress.org/documentation/article/debugging-in-wordpress/#wp_debug_log', 'site-health-tools' ),
					\esc_html__( 'Debugging in WordPress', 'site-health-tools' )
				)
			)
		);

		if ( defined( 'WP_DEBUG_LOG' ) && false !== \WP_DEBUG_LOG ) {
			printf(
				'<label class="screen-reader-text" for="site-health-debug-log-viewer">%s</label>',
				\esc_html__( 'Debug log contents', 'site-health-tools' )
			);
			printf(
				'<textarea style="width:100%%;" id="site-health-debug-log-viewer" rows="20" readonly>%s</textarea>',
				\esc_textarea( $this->read_debug_log() )
			);
		}
	}
}

new Debug_Log_Viewer();
