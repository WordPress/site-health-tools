import { test, expect } from '@playwright/test';
import { signIn } from "../common";

test( 'Validate the existence of a functional PHP Info tab', async ( { page } ) => {
	const sectionSlug = 'php-info';

	await signIn( { page } );

	await page.goto( '/wp-admin/site-health.php?tab=tools' );

	const tabContainer = page.locator( '#health-check-accordion-block-' + sectionSlug );

	expect(
		tabContainer,
		'No PHP Info section found.'
	).toBeDefined();

	await expect(
		tabContainer,
		'The PHP Info section is not collapsed by default.'
	).toBeHidden();

	await page.locator( '[aria-controls=health-check-accordion-block-' + sectionSlug + ']' ).click();

	await expect(
		tabContainer,
		'The PHP Info section can not be expanded.'
	).toBeVisible();
} );
