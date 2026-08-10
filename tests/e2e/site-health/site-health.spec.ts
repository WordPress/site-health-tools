import { test, expect } from '../common';

test( 'Check that `Tools` is added as a tab to Site Health', async ( { page } ) => {
	await page.goto( '/wp-admin/site-health.php' );

	await expect(
		page.locator( 'nav.health-check-tabs-wrapper' ).getByText( 'Tools' ),
		'The Tools tab was not found in the Site Health page.'
	).toBeVisible();
} );
