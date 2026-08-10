import path from 'path';
import { test as base, expect } from '@playwright/test';

// Used to tell the plugin's own JavaScript/PHP apart from WordPress core's
// when scanning for warnings. Must match the mounted plugin directory name
// (the repository folder name, per `wp-env`'s local source mounting) and the
// `WP_DEBUG_LOG` path configured in `.wp-env.json`.
export const PLUGIN_SLUG = 'site-health-tools';
export const DEBUG_LOG_PATH = '/tmp/wp-debug.log';

// Where `auth.setup.ts` saves the signed-in browser storage state, so that
// `signIn()` only has to run once per test run instead of once per test.
export const AUTH_FILE = path.join( __dirname, '.auth/admin.json' );

const isFromPlugin = ( source: string ) =>
	source.includes( `/plugins/${ PLUGIN_SLUG }/` );

/**
 * A `test` that fails when the page under test emits a console
 * warning/error or an uncaught exception originating from this plugin's own
 * JavaScript. Messages from WordPress core or other plugins are ignored.
 */
export const test = base.extend( {
	page: async ( { page }, use ) => {
		const pluginWarnings: string[] = [];

		page.on( 'console', ( message ) => {
			if ( message.type() !== 'warning' && message.type() !== 'error' ) {
				return;
			}

			const location = message.location();

			if ( isFromPlugin( location.url ) || isFromPlugin( message.text() ) ) {
				pluginWarnings.push(
					`[console.${ message.type() }] ${ message.text() } (${ location.url }:${ location.lineNumber })`
				);
			}
		} );

		page.on( 'pageerror', ( error ) => {
			if ( isFromPlugin( error.stack || error.message ) ) {
				pluginWarnings.push( `[pageerror] ${ error.stack || error.message }` );
			}
		} );

		await use( page );

		expect(
			pluginWarnings,
			`Site Health Tools JavaScript produced warnings/errors:\n${ pluginWarnings.join( '\n' ) }`
		).toEqual( [] );
	},
} );

export { expect };

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
};
