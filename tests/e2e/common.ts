import { expect } from '@playwright/test';

export const signIn = async ( { page } ) => {
	await page.goto( '/wp-login.php' );

	// Check if the final URL is within `wp-admin`, determining that no sign-in is required.
	if ( ! page.url().includes( 'wp-admin/' ) ) {
		await page.fill( 'input[name="log"]', 'admin' );
		await page.fill( 'input[name="pwd"]', 'password' );

		// Click the login button.
		await page.click( '#wp-submit' );

		// Wait for the page to load after submitting the form.
		await page.waitForURL( '/wp-admin/' );
	}

	expect(
		page.url().includes( 'wp-admin/' ),
		'Sign-in failed. Check the credentials in the test file.'
	).toBeTruthy();
}
