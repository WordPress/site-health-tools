<?php
/**
 * Base class for the Tools tab to be extended.
 *
 * @package Site Health Tools
 */

namespace SiteHealthTools;

// Make sure the file is not directly accessible.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

/**
 * Class Health_Check_Tools
 */
abstract class Site_Health_Tool {
	/**
	 * The description for the tab.
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * The label for the tab.
	 *
	 * @var string
	 */
	protected $label;

	public function __construct() {
		\add_filter( 'health_check_tools_tab', array( $this, 'tab_setup' ) );
	}

	/**
	 * @param array<int, array<string,string>> $tabs
	 *
	 * @return array<int, array<string,string>>
	 */
	public function tab_setup( array $tabs ) : array {
		if ( empty( $this->label ) ) {
			return $tabs;
		}

		ob_start();
		?>

		<div>
			<?php if ( $this->has_description() ) : ?>
				<p>
					<?php
						echo $this->get_description(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 -- Description is expected to contain various markup and is defined by the individual classes in the code, and not by user input.
					?>
				</p>
			<?php endif; ?>

			<?php $this->tab_content(); ?>
		</div>

		<?php

		$tab_content = ob_get_clean();

		$tabs[] = array(
			'label'   => $this->label,
			'content' => (string) $tab_content,
		);

		return $tabs;
	}

	public function tab_content() : void {}

	public function has_description() : bool {
		return ! empty( $this->description );
	}

	public function get_description() : string {
		return $this->description;
	}
}
