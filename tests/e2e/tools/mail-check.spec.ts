import { test, expect } from '@playwright/test';
import { signIn } from "../common";

test( 'Validate the existence of a functional Mail Check tab', async ( { page } ) => {
	const sectionSlug = 'mail-check';

	await signIn( { page } );

	await page.goto( '/wp-admin/site-health.php?tab=tools' );

	const tabContainer = page.locator( '#health-check-accordion-block-' + sectionSlug );

	expect(
		tabContainer,
		'No Mail Check section found.'
	).toBeDefined();

	await expect(
		tabContainer,
		'The Mail Check section is not collapsed by default.'
	).toBeHidden();

	await page.locator( '[aria-controls=health-check-accordion-block-' + sectionSlug + ']' ).click();

	await expect(
		tabContainer,
		'The Mail Check section can not be expanded.'
	).toBeVisible();
} );
