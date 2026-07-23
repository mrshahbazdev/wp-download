<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Downloader_Admin {

	public static function add_menu() {
		add_management_page(
			__( 'WP Downloader', 'wp-downloader' ),
			__( 'WP Downloader', 'wp-downloader' ),
			'manage_options',
			'wp-downloader',
			array( __CLASS__, 'render_page' )
		);
	}

	public static function handle_download() {
		if ( ! isset( $_GET['page'], $_GET['wpd_action'], $_GET['wpd_type'], $_GET['wpd_slug'] ) ) {
			return;
		}

		if ( 'wp-downloader' !== $_GET['page'] ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to download this item.', 'wp-downloader' ) );
		}

		check_admin_referer( 'wpd_download' );

		$type = sanitize_text_field( wp_unslash( $_GET['wpd_type'] ) );
		$slug = sanitize_text_field( wp_unslash( $_GET['wpd_slug'] ) );

		if ( 'plugin' === $type ) {
			$source = WP_PLUGIN_DIR . '/' . $slug;
			$name   = $slug;
		} elseif ( 'theme' === $type ) {
			$theme  = wp_get_theme( $slug );
			$source = $theme->get_stylesheet_directory();
			$name   = $theme->get_stylesheet();
		} else {
			wp_die( esc_html__( 'Invalid download type.', 'wp-downloader' ) );
		}

		if ( ! is_dir( $source ) ) {
			wp_die( esc_html__( 'Item not found.', 'wp-downloader' ) );
		}

		$real_source = realpath( $source );
		$base_dir    = 'plugin' === $type ? realpath( WP_PLUGIN_DIR ) : realpath( get_theme_root() );

		if ( false === $real_source || false === $base_dir || 0 !== strpos( $real_source, $base_dir ) ) {
			wp_die( esc_html__( 'Invalid path.', 'wp-downloader' ) );
		}

		if ( ! class_exists( 'ZipArchive' ) ) {
			wp_die( esc_html__( 'ZipArchive extension is not available on this server.', 'wp-downloader' ) );
		}

		$zip_name = $name . '.zip';
		$tmp      = wp_tempnam( 'wpd_' );
		$zip      = new ZipArchive();

		if ( true !== $zip->open( $tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
			wp_die( esc_html__( 'Unable to create ZIP file.', 'wp-downloader' ) );
		}

		self::add_directory_to_zip( $zip, $real_source, basename( $real_source ) );
		$zip->close();

		header( 'Content-Type: application/zip' );
		header( 'Content-Disposition: attachment; filename="' . $zip_name . '"' );
		header( 'Content-Length: ' . filesize( $tmp ) );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		readfile( $tmp );
		unlink( $tmp );
		exit;
	}

	private static function add_directory_to_zip( $zip, $dir, $local_root ) {
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file ) {
			$real_path   = $file->getRealPath();
			$relative    = $local_root . '/' . substr( $real_path, strlen( $dir ) + 1 );
			$relative    = str_replace( '\\', '/', $relative );

			if ( $file->isDir() ) {
				$zip->addEmptyDir( $relative );
			} else {
				$zip->addFile( $real_path, $relative );
			}
		}
	}

	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wp-downloader' ) );
		}

		$themes   = wp_get_themes();
		$plugins  = get_plugins();
		$base_url = admin_url( 'tools.php?page=wp-downloader' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<h2><?php esc_html_e( 'Themes', 'wp-downloader' ); ?></h2>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Name', 'wp-downloader' ); ?></th>
						<th><?php esc_html_e( 'Version', 'wp-downloader' ); ?></th>
						<th><?php esc_html_e( 'Slug', 'wp-downloader' ); ?></th>
						<th><?php esc_html_e( 'Action', 'wp-downloader' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $themes as $slug => $theme ) : ?>
						<tr>
							<td><?php echo esc_html( $theme->get( 'Name' ) ); ?></td>
							<td><?php echo esc_html( $theme->get( 'Version' ) ); ?></td>
							<td><code><?php echo esc_html( $slug ); ?></code></td>
							<td>
								<a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'wpd_action' => 'download', 'wpd_type' => 'theme', 'wpd_slug' => $slug ), $base_url ), 'wpd_download' ) ); ?>">
									<?php esc_html_e( 'Download ZIP', 'wp-downloader' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<h2><?php esc_html_e( 'Plugins', 'wp-downloader' ); ?></h2>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Name', 'wp-downloader' ); ?></th>
						<th><?php esc_html_e( 'Version', 'wp-downloader' ); ?></th>
						<th><?php esc_html_e( 'Slug / Path', 'wp-downloader' ); ?></th>
						<th><?php esc_html_e( 'Action', 'wp-downloader' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $plugins as $plugin_file => $plugin ) : ?>
						<?php
						$parts = explode( '/', $plugin_file );
						$slug  = $parts[0];
						?>
						<tr>
							<td><?php echo esc_html( $plugin['Name'] ); ?></td>
							<td><?php echo esc_html( $plugin['Version'] ); ?></td>
							<td><code><?php echo esc_html( $slug ); ?></code></td>
							<td>
								<a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'wpd_action' => 'download', 'wpd_type' => 'plugin', 'wpd_slug' => $slug ), $base_url ), 'wpd_download' ) ); ?>">
									<?php esc_html_e( 'Download ZIP', 'wp-downloader' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
