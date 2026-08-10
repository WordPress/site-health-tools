import { test, expect } from '../common';

test( 'Validate the existence of a functional Transient Summary tab', async ( { page } ) => {
	const sectionSlug = 'transient-summary';

	await page.goto( '/wp-admin/site-health.php?tab=tools' );

	const tabContainer = page.locator( '#health-check-accordion-block-' + sectionSlug );

	expect(
		tabContainer,
		'No Transient Summary section found.'
	).toBeDefined();

	await expect(
		tabContainer,
		'The Transient Summary section is not collapsed by default.'
	).toBeHidden();

	await page.locator( '[aria-controls=health-check-accordion-block-' + sectionSlug + ']' ).click();

	await expect(
		tabContainer,
		'The Transient Summary section can not be expanded.'
	).toBeVisible();
} );
