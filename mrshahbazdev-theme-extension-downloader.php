<?php
/**
 * Plugin Name: MrShahbazDev Theme & Extension Downloader
 * Description: Download any installed WordPress theme or plugin as a ZIP file from the admin dashboard.
 * Version: 1.2.2
 * Author: mrshahbazdev
 * Author URI: https://github.com/mrshahbazdev
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mrshahbazdev-theme-extension-downloader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WP_DOWNLOADER_VERSION', '1.2.2' );
define( 'WP_DOWNLOADER_FILE', __FILE__ );
define( 'WP_DOWNLOADER_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_DOWNLOADER_URL', plugin_dir_url( __FILE__ ) );

require_once WP_DOWNLOADER_DIR . 'includes/class-mrshahbazdev-theme-extension-downloader-admin.php';

add_action( 'admin_menu', array( 'WP_Downloader_Admin', 'add_menu' ) );
add_action( 'admin_init', array( 'WP_Downloader_Admin', 'handle_download' ) );
add_action( 'admin_enqueue_scripts', array( 'WP_Downloader_Admin', 'add_assets' ) );
