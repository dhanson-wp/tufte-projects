=== Tufte Projects ===
Contributors: dhanson
Requires at least: 6.7
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A Projects post type for showcasing plugins, themes, and tools, with fields synced from WordPress.org and GitHub.

== Description ==

Tufte Projects adds a Projects post type for showing off the plugins, themes, and tools you've built. Each project has a type, a set of tools, and fields such as version, license, requirements, and download link.

* Fields fill in from WordPress.org and GitHub twice a day, and you can override any of them by hand.
* Block Bindings put those fields into any template or pattern, and empty rows hide themselves.
* Templates for a single project and the projects archive come with the plugin. A block theme can override either one.
* Four patterns cover project cards, features, install steps, and FAQs.
* A sidebar panel in the editor shows synced values and offers a "Refresh now" button.

It was built for the Tufte Blocks theme and works with any block theme.

== Installation ==

1. Upload the plugin to `/wp-content/plugins/`, or install it from the Plugins screen.
2. Activate it. Projects appear in the admin menu.
3. Add a project, enter a WordPress.org slug or a GitHub URL, and save.

To sync from the command line, run `wp tufte-projects sync [<id>]`.

== Changelog ==

= 0.1.1 =
* A sync won't wipe a project's release date and download link anymore when GitHub rate-limits, times out, or errors on the latest release. It'll keep the last good values, and only a real "no releases" answer clears them.

= 0.1.0 =
* First release. The Projects feature moves out of the Tufte Blocks theme into this plugin, with the same post type, taxonomies, fields, and block binding, so existing projects keep working.
