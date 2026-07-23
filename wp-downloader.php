<?php
/**
 * Plugin Name: WP Downloader
 * Description: Download any installed WordPress theme or plugin as a ZIP file from the admin dashboard.
 * Version: 1.0.0
 * Author: Devin
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-downloader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WP_DOWNLOADER_VERSION', '1.0.0' );
define( 'WP_DOWNLOADER_FILE', __FILE__ );
define( 'WP_DOWNLOADER_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_DOWNLOADER_URL', plugin_dir_url( __FILE__ ) );

require_once WP_DOWNLOADER_DIR . 'includes/class-wp-downloader-admin.php';

add_action( 'admin_menu', array( 'WP_Downloader_Admin', 'add_menu' ) );
add_action( 'admin_init', array( 'WP_Downloader_Admin', 'handle_download' ) );
