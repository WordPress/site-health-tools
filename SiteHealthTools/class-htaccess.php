<?php
/**
 * Checks if a .htaccess file exists and is used.
 *
 * @package Site Health Tools
 */

namespace SiteHealthTools;

// Make sure the file is not directly accessible.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

/**
 * Class Mail Check
 */
class Htaccess extends Site_Health_Tool {

	public function __construct() {
		$this->label       = \__( '.htaccess Viewer', 'site-health-tools' );
		$this->description = \__( 'The <code>.htaccess</code> file tells your server (if supported) how to handle links and file requests. This file usually requires direct server access to view, but if your system supports these files, you can verify its content here.', 'site-health-tools' );

		parent::__construct();
	}

	public function tab_content() : void {
		global $wp_rewrite;

		if ( $wp_rewrite->using_mod_rewrite_permalinks() ) {
			if ( file_exists( ABSPATH . '.htaccess' ) ) {
				printf(
					'<pre>%s</pre>',
					\esc_html( (string) file_get_contents( ABSPATH . '.htaccess' ) )
				);
			} else {
				printf(
					'<p>%s</p>',
					sprintf(
						// translators: %s: `.htaccess` file reference in code tags.
						\esc_html__( 'Your site is using %s rules to handle permalinks, but no .htaccess file was found. This means that your .htaccess file is not being used to handle requests.', 'site-health-tools' ),
						'<code>.htaccess</code>'
					)
				);
			}
		} else {
			printf(
				'<p>%s</p>',
				sprintf(
					// translators: %s: `.htaccess` file reference in code tags.
					\esc_html__( 'Your site is not using %s to handle permalinks. This means that your .htaccess file is not being used to handle requests, and they are most likely handled directly by your web-server software.', 'site-health-tools' ),
					'<code>.htaccess</code>'
				)
			);
		}
		?>
		<?php
	}

}

new Htaccess();
