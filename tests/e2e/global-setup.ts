import { execFileSync } from 'child_process';
import { DEBUG_LOG_PATH } from './common';

/**
 * Wipes the `tests` wp-env database before the e2e run starts, so every run
 * begins from a fresh install (no pending DB upgrades, unconfirmed admin
 * email, or other leftover state from a previous run to work around), then
 * truncates the debug log so `global-teardown.ts` only sees PHP
 * notices/warnings/errors produced during this run.
 */
async function globalSetup() {
	execFileSync( 'npx', [ 'wp-env', 'clean', 'tests' ], { stdio: 'inherit' } );

	execFileSync(
		'npx',
		[ 'wp-env', 'run', 'tests-cli', 'wp', 'core', 'update-db' ],
		{ stdio: 'inherit' }
	);

	execFileSync(
		'npx',
		[
			'wp-env',
			'run',
			'tests-cli',
			'wp',
			'eval',
			`file_put_contents( '${ DEBUG_LOG_PATH }', '' );`,
		],
		{ stdio: 'inherit' }
	);
}

export default globalSetup;
