<?php
/**
 * Site Health tab contents.
 *
 * @package Site Health Tools
 */

// Make sure the file is not directly accessible.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

?>

<div class="health-check-body">
	<h2>
		<?php esc_html_e( 'Tools', 'health-check' ); ?>
	</h2>

	<div id="health-check-tools" role="presentation" class="health-check-accordion">
		<?php
		/**
		 * Filter the features available under the Tools tab.
		 *
		 * You may introduce your own, or modify the behavior of existing tools here,
		 * although we recommend not modifying anything provided by the plugin it self.
		 *
		 * Any interactive elements should be introduced using JavaScript and/or CSS, either
		 * inline, or by enqueueing them via the appropriate actions.
		 *
		 * @param array $args {
		 *     An unassociated array of tabs, listed in the order they are registered.
		 *
		 *     @type array $tab {
		 *         An associated array containing the tab title, and content.
		 *
		 *         @type string $label   A pre-escaped string used to label your tool section.
		 *         @type string $content The content of your tool tab, with any code you may need.
		 *     }
		 * }
		 */
		$tabs = \apply_filters( 'health_check_tools_tab', array() );

		foreach ( $tabs as $count => $tab ) :
			$slug_id = \sanitize_title( $tab['label'] );

			?>

			<h3 class="health-check-accordion-heading">
				<button aria-expanded="false" class="health-check-accordion-trigger" aria-controls="health-check-accordion-block-<?php echo esc_attr( $slug_id ); ?>" type="button">
				<span class="title">
					<?php echo \esc_html( $tab['label'] ); ?>
				</span>
					<span class="icon"></span>
				</button>
			</h3>
			<div id="health-check-accordion-block-<?php echo esc_attr( $slug_id ); ?>" class="health-check-accordion-panel" hidden="hidden">
				<?php
					echo $tab['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is expected to contain various markup and CSS/JavaScript to tackle the individual tool tabs use cases.
				?>
			</div>

		<?php endforeach; ?>
	</div>

	<?php include_once( __DIR__ . '/diff-modal.php' ); ?>
</div>
