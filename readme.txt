=== WP Downloader ===
Contributors: mrshahbazdev
Donate link: https://github.com/mrshahbazdev/wp-download
Tags: download theme zip, download plugin zip, backup themes, backup plugins, wordpress downloader
Requires at least: 6.5
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Download any installed WordPress theme or plugin as a ZIP file from your admin dashboard.

== Description ==

WP Downloader is a fast, secure admin tool for WordPress that lets you download any installed theme or plugin as a ZIP archive without FTP, cPanel, or the command line.

Whether you are backing up a site, cloning a plugin to another project, or archiving a custom theme before an update, WP Downloader makes the process simple.

= Features =
* One-click ZIP download for any installed theme or plugin.
* Modern, responsive admin UI with tabs and search.
* Bulk download – select multiple items and download them in one ZIP.
* Active/inactive status badges so you know what is currently in use.
* Single-file plugin support.
* Secure path validation and nonce/capability checks.
* No external dependencies.

== Installation ==

1. Upload the `wp-downloader` folder to `/wp-content/plugins/` or install the ZIP from **Plugins → Add New → Upload Plugin**.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Tools → WP Downloader** to start downloading.

== Frequently Asked Questions ==

= Who can use WP Downloader? =
Only users with the `manage_options` capability (administrators by default) can access WP Downloader.

= Can I download active themes and plugins? =
Yes. Both active and inactive themes and plugins can be downloaded.

= Can I download multiple items at once? =
Yes. Use the checkboxes to select items and click **Download Selected as ZIP**.

= Is the downloaded ZIP installable? =
Yes. The archive structure is compatible with the WordPress theme and plugin uploaders.

== Changelog ==

= 1.2.0 =
* Redesigned admin UI with tabs, search, and card layout.
* Added bulk download support.
* Added single-file plugin support.
* Improved security and path validation.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.2.0 =
Major UI/UX improvements and bulk download support. Recommended for all users.
