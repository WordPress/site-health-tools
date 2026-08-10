import { execFileSync } from 'child_process';
import { DEBUG_LOG_PATH, PLUGIN_SLUG } from './common';

/**
 * Reads the WordPress debug log accumulated during the e2e run and fails the
 * run if it contains any PHP notices/warnings/errors originating from this
 * plugin's own code. Entries from WordPress core, other plugins, or themes
 * are ignored.
 */
async function globalTeardown() {
	const log = execFileSync(
		'npx',
		[
			'wp-env',
			'run',
			'tests-cli',
			'wp',
			'eval',
			`echo file_exists( '${ DEBUG_LOG_PATH }' ) ? file_get_contents( '${ DEBUG_LOG_PATH }' ) : '';`,
		],
		{ encoding: 'utf-8' }
	);

	const pluginWarnings = log
		.split( '\n' )
		.filter( ( line ) => line.includes( `/plugins/${ PLUGIN_SLUG }/` ) );

	if ( pluginWarnings.length > 0 ) {
		throw new Error(
			'Site Health Tools produced PHP notices/warnings/errors during the e2e run:\n\n' +
				pluginWarnings.join( '\n' )
		);
	}
}

export default globalTeardown;
