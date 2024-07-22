<?php

/**
 * Check all core files against the checksums provided by WordPress API.
 *
 * @package Site Health Tools
 */

namespace SiteHealthTools;

// Make sure the file is not directly accessible.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

/**
 * Class Files_Integrity
 */
class Files_Integrity extends Site_Health_Tool {

	public function __construct() {
		$this->label       = \__( 'File integrity', 'site-health-tools' );
		$this->description = \__( 'The File Integrity checks all the core files with the <code>checksums</code> provided by the WordPress API to see if they are intact. If there are changes you will be able to make a Diff between the files hosted on WordPress.org and your installation to see what has been changed.', 'site-health-tools' );

		\add_action( 'wp_ajax_site-health-files-integrity-check', array( $this, 'run_files_integrity_check' ) );
		\add_action( 'wp_ajax_site-health-view-file-diff', array( $this, 'view_file_diff' ) );

		parent::__construct();
	}

	/**
	 * Gathers checksums from WordPress API and cross checks the core files in the current installation.
	 *
	 * @return void
	 */
	function run_files_integrity_check() {
		\check_ajax_referer( 'site-health-files-integrity-check' );

		$checksums = $this->call_checksum_api();

		$files = $this->parse_checksum_results( $checksums );

		$this->create_the_response( $files );
	}

	/**
	 * Calls the WordPress API on the checksums endpoint
	 *
	 * @uses get_bloginfo()
	 * @uses get_locale()
	 * @uses ABSPATH
	 * @uses wp_remote_get()
	 * @uses get_bloginfo()
	 * @uses strpos()
	 * @uses unset()
	 *
	 * @return array<string, string>
	 */
	function call_checksum_api() : array {
		// Setup variables.
		$wpversion = \get_bloginfo( 'version' );
		$wplocale  = \get_locale();

		// Setup API Call.
		$checksums = \get_core_checksums( $wpversion, $wplocale );

		if ( false === $checksums ) {
			return array();
		}

		\set_transient( 'site-health-checksums', $checksums, 2 * HOUR_IN_SECONDS );

		// Remove the wp-content/ files from checking
		foreach ( $checksums as $file => $checksum ) {
			if ( false !== strpos( $file, 'wp-content/' ) ) {
				unset( $checksums[ $file ] );
			}
		}

		return $checksums;
	}

	/**
	 * Parses the results from the WordPress API call
	 *
	 * @uses file_exists()
	 * @uses md5_file()
	 * @uses ABSPATH
	 *
	 * @param array<string, string> $checksums
	 *
	 * @return array<int, array<int, string>>
	 */
	function parse_checksum_results( array $checksums ) : array {
		// Check if the checksums are valid
		if ( empty( $checksums ) ) {
			return array();
		}

		$filepath = ABSPATH;
		$files    = array();

		// Parse the results to validate checksums.
		foreach ( $checksums as $file => $checksum ) {
			// Check the files.
			if ( file_exists( $filepath . $file ) && md5_file( $filepath . $file ) !== $checksum ) {
				$reason = \esc_html__( 'Content changed', 'site-health-tools' ) . ' <a href="#site-health-diff" data-file="' . $file . '">' . \esc_html__( '(View Diff)', 'site-health-tools' ) . '</a>';
				array_push( $files, array( $file, $reason ) );
			} elseif ( ! file_exists( $filepath . $file ) ) {
				$reason = \esc_html__( 'File not found', 'site-health-tools' );
				array_push( $files, array( $file, $reason ) );
			}
		}

		// Iterate over the core directories to see if any unexpected files exist, but only if the directory iterator is available.
		if ( class_exists( 'RecursiveDirectoryIterator' ) ) {
			$directories = array(
				\untrailingslashit( ABSPATH ),            // Root directory.
				\untrailingslashit( ABSPATH . 'wp-admin' ),    // Admin directory.
				\untrailingslashit( ABSPATH . WPINC ), // Includes directory.
			);

			// Files that will not exist in the checksum iterator, but are expected and should not cause a warning.
			$excluded_files = array(
				'.htaccess',
				'wp-config.php',
			);

			foreach ( $directories as $directory ) {
				// For the root path, do not recursively iterate, to avoid false positives from the `wp-content` directory.
				if ( \untrailingslashit( ABSPATH ) === $directory ) {
					$iterator = new \DirectoryIterator( $directory );
					foreach ( $iterator as $file ) {
						if ( $file->isFile() ) {
							$path = (string) str_replace( ABSPATH, '', $file->getPathname() );

							if ( ! isset( $checksums[ $path ] ) && ! in_array( $path, $excluded_files, true ) ) {
								$reason = \esc_html__( 'This is an unknown file', 'site-health-tools' );
								array_push( $files, array( $path, $reason ) );
							}
						}
					}
				} else {
					$iterator = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $directory ) );
					foreach ( $iterator as $file ) {
						if ( $file->isFile() ) {
							$path = (string) str_replace( ABSPATH, '', $file->getPathname() );

							if ( ! isset( $checksums[ $path ] ) && ! in_array( $path, $excluded_files, true ) ) {
								$reason = \esc_html__( 'This is an unknown file', 'site-health-tools' );
								array_push( $files, array( $path, $reason ) );
							}
						}
					}
				}
			}
		}

		return $files;
	}

	/**
	 * Generates the response
	 *
	 * @uses wp_send_json_success()
	 * @uses wp_die()
	 * @uses ABSPATH
	 *
	 * @param array<int, array<int, string>> $files
	 *
	 * @return void
	 */
	function create_the_response( array $files ) : void {
		$filepath = ABSPATH;
		$output   = '';

		if ( empty( $files ) ) {
			$output .= '<div class="notice notice-success inline"><p>';
			$output .= \esc_html__( 'All files passed the check. Everything seems to be ok!', 'site-health-tools' );
			$output .= '</p></div>';
		} else {
			$output .= '<div class="notice notice-error inline"><p>';
			$output .= \esc_html__( 'It appears as if some files may have been modified.', 'site-health-tools' );
			$output .= '<br>' . \esc_html__( 'One possible reason for this may be that your installation contains translated versions. An easy way to clear this is to reinstall WordPress. Don\'t worry. This will only affect WordPress\' own files, not your themes, plugins or uploaded media.', 'site-health-tools' );
			$output .= '</p></div><table class="widefat striped file-integrity-table"><thead><tr><th>';
			$output .= \esc_html__( 'Status', 'site-health-tools' );
			$output .= '</th><th>';
			$output .= \esc_html__( 'File', 'site-health-tools' );
			$output .= '</th><th>';
			$output .= \esc_html__( 'Reason', 'site-health-tools' );
			$output .= '</th></tr></thead><tfoot><tr><td>';
			$output .= \esc_html__( 'Status', 'site-health-tools' );
			$output .= '</td><td>';
			$output .= \esc_html__( 'File', 'site-health-tools' );
			$output .= '</td><td>';
			$output .= \esc_html__( 'Reason', 'site-health-tools' );
			$output .= '</td></tr></tfoot><tbody>';
			foreach ( $files as $tampered ) {
				$output .= '<tr>';
				$output .= '<td>⚠<span class="screen-reader-text">' . \esc_html__( 'Error', 'site-health-tools' ) . '</span></td>';
				$output .= '<td>' . $filepath . $tampered[0] . '</td>';
				$output .= '<td>' . $tampered[1] . '</td>';
				$output .= '</tr>';
			}
			$output .= '</tbody>';
			$output .= '</table>';
		}

		$response = array(
			'message' => $output,
		);

		\wp_send_json_success( $response );
	}

	/**
	 * Generates Diff view
	 *
	 * @uses get_bloginfo()
	 * @uses wp_remote_get()
	 * @uses wp_remote_retrieve_body()
	 * @uses wp_send_json_success()
	 * @uses wp_die()
	 * @uses ABSPATH
	 * @uses FILE_USE_INCLUDE_PATH
	 * @uses wp_text_diff()
	 *
	 *
	 * @return void
	 */
	function view_file_diff() {
		\check_ajax_referer( 'site-health-view-file-diff' );

		if ( ! \current_user_can( 'view_site_health_checks' ) ) {
			\wp_send_json_error();
		}

		if ( 0 !== \validate_file( $_POST['file'] ) ) {
			\wp_send_json_error( array( 'message' => \esc_html__( 'You do not have access to this file.', 'site-health-tools' ) ) );
		}

		$filepath  = \ABSPATH;
		$file_safe = $_POST['file'];
		$wpversion = \get_bloginfo( 'version' );

		$allowed_files = \get_transient( 'site-health-checksums' );
		if ( false === $allowed_files ) {
			$allowed_files = $this->call_checksum_api();
		}

		if ( ! isset( $allowed_files[ $file_safe ] ) ) {
			\wp_send_json_error( array( 'message' => \esc_html__( 'You do not have access to this file.', 'site-health-tools' ) ) );
		}

		$local_file_body  = file_get_contents( $filepath . $file_safe, true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- `file_get_contents` used to retrieve contents of local file.
		$remote_file      = \wp_remote_get( 'https://core.svn.wordpress.org/tags/' . $wpversion . '/' . $file_safe );
		$remote_file_body = \wp_remote_retrieve_body( $remote_file );
		$diff_args        = array(
			'show_split_view' => true,
		);

		$output   = '<table class="diff"><thead><tr class="diff-sub-title"><th>';
		$output  .= \esc_html__( 'Original', 'site-health-tools' );
		$output  .= '</th><th>';
		$output  .= \esc_html__( 'Modified', 'site-health-tools' );
		$output  .= '</th></tr></table>';
		$output  .= \wp_text_diff( (string) $remote_file_body, (string) $local_file_body, $diff_args );
		$response = array(
			'message' => $output,
		);

		\wp_send_json_success( $response );
	}

	/**
	 * Add the Files integrity checker to the tools tab.
	 *
	 * @return void
	 */
	public function tab_content() : void {
		?>
		<form action="#" id="site-health-file-integrity" method="POST">
			<p>
				<input type="submit" class="button button-primary" value="<?php \esc_html_e( 'Check the Files Integrity', 'site-health-tools' ); ?>">
			</p>
		</form>

		<div id="tools-file-integrity-response-holder">
			<span class="spinner"></span>
		</div>

		<?php
	}
}

new Files_Integrity();
