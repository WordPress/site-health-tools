import { test, expect } from '@playwright/test';
import { signIn } from "../common";

test( 'Validate the existence of a functional Robots.txt Viewer tab', async ( { page } ) => {
	const sectionSlug = 'robots-txt-viewer';

	await signIn( { page } );

	await page.goto( '/wp-admin/site-health.php?tab=tools' );

	const tabContainer = page.locator( '#health-check-accordion-block-' + sectionSlug );

	expect(
		tabContainer,
		'No Robots.txt Viewer section found.'
	).toBeDefined();

	await expect(
		tabContainer,
		'The Robots.txt Viewer section is not collapsed by default.'
	).toBeHidden();

	await page.locator( '[aria-controls=health-check-accordion-block-' + sectionSlug + ']' ).click();

	await expect(
		tabContainer,
		'The Robots.txt Viewer section can not be expanded.'
	).toBeVisible();
} );
