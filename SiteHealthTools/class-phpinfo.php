<?php
/**
 * Provide a means to access extended phpinfo details.
 *
 * @package Site Health Tools
 */

namespace SiteHealthTools;

// Make sure the file is not directly accessible.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

/**
 * Class PhpInfo
 */
class Phpinfo extends Site_Health_Tool {

	public function __construct() {
		$this->label = \__( 'PHP Info', 'site-health-tools' );

		if ( ! function_exists( 'phpinfo' ) ) {
			$this->description = \__( 'The phpinfo() function has been disabled by your host. Please contact the host if you need more information about your setup.', 'site-health-tools' );
		} else {
			$this->description = \__( 'Some scenarios require you to look up more detailed server configurations than what is normally required. The PHP Info page allows you to view all available configuration options for your PHP setup. Please be advised that WordPress does not guarantee that any information shown on that page may not be considered sensitive.', 'site-health-tools' );
		}

		\add_action( 'site_health_tab_content', array( $this, 'add_site_health_tab_content' ) );

		parent::__construct();
	}

	/**
	 * Render the PHP Info tab content.
	 *
	 * @param string $tab The slug of the tab being requested.
	 * @return void
	 */
	public function add_site_health_tab_content( $tab ) {
		// If the host has disabled `phpinfo()`, do not try to load the page..
		if ( ! function_exists( 'phpinfo' ) ) {
			return;
		}

		if ( 'phpinfo' === $tab ) {
			include_once( SITE_HEALTH_TOOLS_PLUGIN_DIRECTORY . '/templates/phpinfo.php' );
		}
	}

	/**
	 * Render the PHP Info tab content.
	 *
	 * @return void
	 */
	public function tab_content() : void {
		// If the host has disabled `phpinfo()`, do not offer a button alternative.
		if ( ! function_exists( 'phpinfo' ) ) {
			return;
		}

		$phpinfo_url = \add_query_arg( array( 'tab' => 'phpinfo' ), \admin_url( 'site-health.php' ) );
		if ( defined( 'HEALTH_CHECK_BACKCOMPAT_LOADED' ) && HEALTH_CHECK_BACKCOMPAT_LOADED ) {
			$phpinfo_url = \add_query_arg(
				array(
					'page' => 'site-health-tools',
					'tab'  => 'phpinfo',
				),
				\admin_url( 'tools.php' )
			);
		}

		?>

		<div class="site-health-view-more">
			<?php
			printf(
				'<a href="%s" class="button button-primary">%s</a>',
				\esc_url( $phpinfo_url ),
				\esc_html__( 'View extended PHP information', 'site-health-tools' )
			);
			?>
		</div>

		<?php
	}
}

new Phpinfo();
