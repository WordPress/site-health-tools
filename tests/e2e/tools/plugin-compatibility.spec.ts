import { test, expect } from '../common';

test( 'Validate the existence of a functional Plugin Compatibility tab', async ( { page } ) => {
	const sectionSlug = 'plugin-compatibility';

	await page.goto( '/wp-admin/site-health.php?tab=tools' );

	const tabContainer = page.locator( '#health-check-accordion-block-' + sectionSlug );

	expect(
		tabContainer,
		'No Plugin Compatibility section found.'
	).toBeDefined();

	await expect(
		tabContainer,
		'The Plugin Compatibility section is not collapsed by default.'
	).toBeHidden();

	await page.locator( '[aria-controls=health-check-accordion-block-' + sectionSlug + ']' ).click();

	await expect(
		tabContainer,
		'The Plugin Compatibility section can not be expanded.'
	).toBeVisible();
} );
