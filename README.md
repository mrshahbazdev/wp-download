# Shahbaz Theme & Plugin Downloader – Download WordPress Themes & Plugins as ZIP

Shahbaz Theme & Plugin Downloader is a lightweight, secure WordPress admin tool that lets you download any installed theme or plugin as a ZIP file directly from the dashboard. It is perfect for backups, migrations, offline development, or sharing a custom build with clients.

[![WordPress](https://img.shields.io/badge/WordPress-6.5%2B-blue)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-8892BF)](https://php.net/)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-green)](https://www.gnu.org/licenses/gpl-2.0.html)

## Why Shahbaz Theme & Plugin Downloader?

Whether you need to back up an installed theme, clone a plugin to another site, or archive your work before an update, Shahbaz Theme & Plugin Downloader makes it fast. No FTP, no cPanel, and no command line required.

## Key Features

- **One-click ZIP downloads** for any installed WordPress theme or plugin.
- **Modern card-based admin UI** with tabs, search, and active/inactive status badges.
- **Bulk download** – select multiple themes or plugins and download them as one ZIP archive.
- **Single-file plugin support** – even simple plugins like `hello.php` are handled correctly.
- **Secure path validation** – only themes inside `wp-content/themes` and plugins inside `wp-content/plugins` can be downloaded.
- **Nonce & capability checks** – restricted to users with the `manage_options` capability.
- **No external dependencies** – uses the native PHP `ZipArchive` extension.

## Installation

1. Download the latest `shahbaz-theme-plugin-downloader.zip` release.
2. Go to **Plugins → Add New → Upload Plugin** in your WordPress admin.
3. Choose the ZIP file and click **Install Now**.
4. Click **Activate**.

Alternatively, extract the `shahbaz-theme-plugin-downloader` folder into `/wp-content/plugins/` and activate it from the Plugins screen.

## Usage

1. From the WordPress admin menu, go to **Tools → Shahbaz Theme & Plugin Downloader**.
2. Use the **Themes** and **Plugins** tabs to switch between item types.
3. Use the search box to filter by name, slug, author, or description.
4. Click **Download ZIP** next to any item to save it individually.
5. Use the checkboxes to select multiple items, then click **Download Selected as ZIP** for a combined archive.

## Requirements

- WordPress 6.5 or higher
- PHP 7.4 or higher
- PHP `ZipArchive` extension enabled

## Frequently Asked Questions

### Who can use Shahbaz Theme & Plugin Downloader?
Only administrators (users with the `manage_options` capability) can access the Shahbaz Theme & Plugin Downloader page.

### Does it work with active themes and plugins?
Yes. Active and inactive themes and plugins can be downloaded.

### Can I download multiple items at once?
Yes. Select the items you want and use the bulk download button. The archive is organized into `themes/` and `plugins/` folders.

### Is the downloaded ZIP installable?
Yes. The ZIP structure is compatible with the standard WordPress theme and plugin uploaders.

### Does it support multisite?
It uses `manage_options` capability checks, which should work on multisite when accessed by a network administrator.

## Changelog

### 1.2.0
- Redesigned admin UI with tabs, search, cards, and active/inactive badges.
- Added bulk download support.
- Added single-file plugin support.
- Improved path validation and security.

### 1.0.0
- Initial release with individual theme and plugin ZIP downloads.

## License

Shahbaz Theme & Plugin Downloader is free software released under the [GNU General Public License v2.0 or later](https://www.gnu.org/licenses/gpl-2.0.html).

## Support & Contributions

For bug reports, feature requests, or contributions, please open an issue or pull request on GitHub:
https://github.com/mrshahbazdev/wp-download
