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

//		$debug_log = @file_get_contents( $logfile, offset: -1024 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- `file_get_contents` used to retrieve contents of local file.
		$debug_log = $this->tail( $logfile, 99 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- `file_get_contents` used to retrieve contents of local file.

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

	/**
	 * Slightly modified version of http://www.geekality.net/2011/05/28/php-tail-tackling-large-files/
	 * Modified and modernized, refactored for OOP from the version at https://gist.github.com/lorenzos/1711e81a9162320fde20
	 * @author Torleif Berger, Lorenzo Stanco, Knut Sparhell (2024)
	 * @link http://stackoverflow.com/a/15025877/995958
	 * @license http://creativecommons.org/licenses/by/3.0/
	 */
	private function tail( /*string */$filepath, /*int */$lines = 1, /*bool */$adaptive = true )/*: bool|string*/ {

		// Open file
		$f = @fopen( $filepath, 'rb' );
		if ( $f === false ) return false;

		// Sets buffer size, according to the number of lines to retrieve.
		// This gives a performance boost when reading a few lines from the file.
		if ( ! $adaptive ) $buffer = 4096;
		else $buffer = $lines < 2 ? 64 : ( $lines < 10 ? 512 : 4096 );

		// Jump to last character
		fseek( $f, -1, \SEEK_END ) ;

		// Read it and adjust line number if necessary
		// (Otherwise the result would be wrong if file doesn't end with a blank line)
		if ( fread( $f, 1 ) != \PHP_EOL ) $lines -= 1;

		// Start reading
		$output = '';
		$chunk  = '';

		// While we would like more
		while ( ftell( $f ) > 0 && $lines >= 0 ) {

			// Figure out how far back we should jump
			$seek = min( ftell( $f ), $buffer );

			// Do the jump (backwards, relative to where we are)
			fseek( $f, -$seek, \SEEK_CUR );

			// Read a chunk and prepend it to our output
			$output = ( $chunk = fread( $f, $seek ) ) . $output;

			// Jump back to where we started reading
			fseek( $f, -mb_strlen( $chunk, '8bit' ), \SEEK_CUR );

			// Decrease our line counter
			$lines -= substr_count( $chunk, \PHP_EOL );

		}

		// While we have too many lines
		// (Because of buffer size we might have read too many)
		while ( $lines++ < 0 ) {

			// Find first newline and remove all text before that
			$output = substr( $output, strpos( $output, \PHP_EOL ) + 1 );

		}

		// Close file and return
		fclose( $f );
		return trim( $output );

	}
}

new Debug_Log_Viewer();
