const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		"site-health-tools": [
			path.resolve( process.cwd(), 'src/javascript', 'site-health-tools.js' ),
			path.resolve( process.cwd(), 'src/styles', 'site-health-tools.scss' )
		],
	}
}
