=== Site Health Tools ===
Tags: health check, site health
Contributors: Clorith
Requires at least: 5.8
Requires PHP: 7.1
Tested up to: 6.6
Stable tag: 1.0.0
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Introduces additional common tools to the Site Health interface.

== Description ==

The Site Health Tools plugin implements a selection of additional tools to the Site Health section of your site, to help you diagnose common issues.

These are checks that require some form of user interaction, and therefore are not as good of a fit as the automatic nature of the normal Site Health checker.

Providing tools such as:
- Debug log viewer
- File integrity checker
- PHP information
- Mail sending test
- Plugin PHP compatibility checker
- And more...

== Frequently Asked Questions ==

= Is this a replacement for the Health Check plugin? =

Yes, this plugin takes the tools originally made available via the Health Check plugin, and lets you add just them, without any additional features to your own site.

= What external resources, if any, does this plugin use? =

Although some external resources are used, no such connections are made automatically, and then only for the tools you choose, and can only be triggered by a user with access to the Site Health tools.

These resources include:
- [WordPress.org](https://wordpress.org) - Used to fetch the file checksums to verify WordPress core files integrity ([privacy policy](https://wordpress.org/about/privacy/))
- [WPTide.org](https://wptide.org) - Used for the PHP compatibility checker

= Can I contribute to this plugin? =

Yes, the plugin is open source and available on the [WordPress/site-health-tools GitHub repository](https://github.com/wordpress/site-health-tools), and we welcome all types of contributions!

== Changelog ==

= 1.0.0 (<date TBD>)
* Initial release
