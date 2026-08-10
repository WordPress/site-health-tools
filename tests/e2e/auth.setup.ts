import { test as setup } from '@playwright/test';
import { signIn, AUTH_FILE } from './common';

// Runs once per test run (see the `setup` project's dependents in
// `../e2e.config.ts`) and saves the signed-in session so individual tests
// don't each have to sign in themselves. A test that specifically needs to
// exercise the sign-in/sign-out flow can still call `signIn()` (or clear
// cookies first) on its own.
setup( 'authenticate as admin', async ( { page } ) => {
	await signIn( { page } );

	await page.context().storageState( { path: AUTH_FILE } );
} );
