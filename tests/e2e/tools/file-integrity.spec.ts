import { test, expect } from '@playwright/test';
import { signIn } from "../common";

test( 'Validate the existence of a functional File Integrity tab', async ( { page } ) => {
	const sectionSlug = 'file-integrity';

	await signIn( { page } );

	await page.goto( '/wp-admin/site-health.php?tab=tools' );

	const tabContainer = page.locator( '#health-check-accordion-block-' + sectionSlug );

	expect(
		tabContainer,
		'No File Integrity section found.'
	).toBeDefined();

	await expect(
		tabContainer,
		'The File Integrity section is not collapsed by default.'
	).toBeHidden();

	await page.locator( '[aria-controls=health-check-accordion-block-' + sectionSlug + ']' ).click();

	await expect(
		tabContainer,
		'The File Integrity section can not be expanded.'
	).toBeVisible();
} );
