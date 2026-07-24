<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Downloader_Admin {

	public static function add_menu() {
		add_management_page(
			__( 'Shahbaz Theme & Extension Downloader', 'shahbaz-theme-extension-downloader' ),
			__( 'Shahbaz Theme & Extension Downloader', 'shahbaz-theme-extension-downloader' ),
			'manage_options',
			'shahbaz-theme-extension-downloader',
			array( __CLASS__, 'render_page' )
		);
	}

	public static function add_assets( $hook ) {
		if ( 'tools_page_shahbaz-theme-extension-downloader' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'shahbaz-theme-extension-downloader-admin',
			WP_DOWNLOADER_URL . 'assets/css/admin.css',
			array(),
			WP_DOWNLOADER_VERSION
		);

		wp_enqueue_script(
			'shahbaz-theme-extension-downloader-admin',
			WP_DOWNLOADER_URL . 'assets/js/admin.js',
			array(),
			WP_DOWNLOADER_VERSION,
			true
		);
	}

	public static function handle_download() {
		if ( ! isset( $_REQUEST['page'], $_REQUEST['wpd_action'] ) || 'shahbaz-theme-extension-downloader' !== $_REQUEST['page'] ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to download this item.', 'shahbaz-theme-extension-downloader' ) );
		}

		check_admin_referer( 'wpd_download' );

		if ( ! class_exists( 'ZipArchive' ) ) {
			wp_die( esc_html__( 'ZipArchive extension is not available on this server.', 'shahbaz-theme-extension-downloader' ) );
		}

		$action = sanitize_text_field( wp_unslash( $_REQUEST['wpd_action'] ) );

		if ( 'download' === $action ) {
			if ( ! isset( $_REQUEST['wpd_type'], $_REQUEST['wpd_slug'] ) ) {
				wp_die( esc_html__( 'Missing download parameters.', 'shahbaz-theme-extension-downloader' ) );
			}

			$type = sanitize_text_field( wp_unslash( $_REQUEST['wpd_type'] ) );
			$slug = sanitize_text_field( wp_unslash( $_REQUEST['wpd_slug'] ) );

			$source = self::resolve_source( $type, $slug );
			if ( is_wp_error( $source ) ) {
				wp_die( esc_html( $source->get_error_message() ) );
			}

			$name = self::get_item_name( $type, $slug );
			self::serve_zip( array( array( 'source' => $source, 'root' => $slug, 'type' => $type ) ), $name );
		}

		if ( 'bulk_download' === $action ) {
			if ( ! isset( $_REQUEST['wpd_items'] ) || '' === $_REQUEST['wpd_items'] ) {
				wp_die( esc_html__( 'No items selected.', 'shahbaz-theme-extension-downloader' ) );
			}

			$items_raw = sanitize_text_field( wp_unslash( $_REQUEST['wpd_items'] ) );
			$items     = array_filter( array_map( 'trim', explode( ',', $items_raw ) ) );

			$entries = array();
			foreach ( $items as $item ) {
				$parts = explode( ':', $item, 2 );
				if ( count( $parts ) !== 2 ) {
					continue;
				}

				$type = $parts[0];
				$slug = $parts[1];

				$source = self::resolve_source( $type, $slug );
				if ( is_wp_error( $source ) ) {
					continue;
				}

				$entries[] = array(
					'source' => $source,
					'root'   => ( 'plugin' === $type ? 'plugins/' : 'themes/' ) . $slug,
					'type'   => $type,
				);
			}

			if ( empty( $entries ) ) {
				wp_die( esc_html__( 'No valid items were found to download.', 'shahbaz-theme-extension-downloader' ) );
			}

			$zip_name = 'shahbaz-theme-extension-downloader-bulk-' . current_time( 'Ymd-His' );
			self::serve_zip( $entries, $zip_name );
		}
	}

	private static function resolve_source( $type, $slug ) {
		if ( 'plugin' === $type ) {
			$base_dir = realpath( WP_PLUGIN_DIR );
			$path     = realpath( WP_PLUGIN_DIR . '/' . $slug );

			if ( false === $path || false === $base_dir ) {
				return new WP_Error( 'invalid_path', __( 'Invalid plugin path.', 'shahbaz-theme-extension-downloader' ) );
			}

			$base_dir = str_replace( '\\', '/', $base_dir );
			$path     = str_replace( '\\', '/', $path );

			if ( 0 !== strpos( $path, $base_dir ) ) {
				return new WP_Error( 'invalid_path', __( 'Invalid plugin path.', 'shahbaz-theme-extension-downloader' ) );
			}

			if ( is_dir( $path ) || is_file( $path ) ) {
				return $path;
			}
		}

		if ( 'theme' === $type ) {
			$theme = wp_get_theme( $slug );
			if ( ! $theme->exists() ) {
				return new WP_Error( 'not_found', __( 'Theme not found.', 'shahbaz-theme-extension-downloader' ) );
			}

			$path     = realpath( $theme->get_stylesheet_directory() );
			$base_dir = realpath( get_theme_root() );

			if ( false === $path || false === $base_dir ) {
				return new WP_Error( 'invalid_path', __( 'Invalid theme path.', 'shahbaz-theme-extension-downloader' ) );
			}

			$base_dir = str_replace( '\\', '/', $base_dir );
			$path     = str_replace( '\\', '/', $path );

			if ( 0 !== strpos( $path, $base_dir ) ) {
				return new WP_Error( 'invalid_path', __( 'Invalid theme path.', 'shahbaz-theme-extension-downloader' ) );
			}

			return $path;
		}

		return new WP_Error( 'invalid_type', __( 'Invalid item type.', 'shahbaz-theme-extension-downloader' ) );
	}

	private static function get_item_name( $type, $slug ) {
		if ( 'plugin' === $type ) {
			$all_plugins = get_plugins();
			foreach ( $all_plugins as $file => $plugin ) {
				$parts = explode( '/', $file );
				if ( $parts[0] === $slug ) {
					return sanitize_file_name( $plugin['Name'] );
				}
			}
		}

		if ( 'theme' === $type ) {
			$theme = wp_get_theme( $slug );
			if ( $theme->exists() ) {
				return sanitize_file_name( $theme->get( 'Name' ) );
			}
		}

		return $slug;
	}

	private static function serve_zip( $entries, $zip_base_name ) {
		$tmp = wp_tempnam( 'wpd_' );
		$zip = new ZipArchive();

		if ( true !== $zip->open( $tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
			wp_die( esc_html__( 'Unable to create ZIP file.', 'shahbaz-theme-extension-downloader' ) );
		}

		foreach ( $entries as $entry ) {
			if ( is_file( $entry['source'] ) ) {
				$zip->addFile( $entry['source'], $entry['root'] );
			} elseif ( is_dir( $entry['source'] ) ) {
				self::add_directory_to_zip( $zip, $entry['source'], $entry['root'] );
			}
		}

		$zip->close();

		$zip_name = $zip_base_name . '.zip';
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
			$real_path = $file->getRealPath();
			$relative  = $local_root . '/' . substr( $real_path, strlen( $dir ) + 1 );
			$relative  = str_replace( '\\', '/', $relative );

			if ( $file->isDir() ) {
				$zip->addEmptyDir( $relative );
			} else {
				$zip->addFile( $real_path, $relative );
			}
		}
	}

	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'shahbaz-theme-extension-downloader' ) );
		}

		$themes  = wp_get_themes();
		$plugins = get_plugins();
		$active_theme = get_stylesheet();

		$active_plugins = array();
		foreach ( wp_get_active_and_valid_plugins() as $plugin_path ) {
			$parts = explode( '/', plugin_basename( $plugin_path ) );
			$active_plugins[ $parts[0] ] = true;
		}

		$base_url = admin_url( 'tools.php?page=shahbaz-theme-extension-downloader' );
		?>
		<div class="wrap wpd-wrap">
			<div class="wpd-header">
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<div class="wpd-toolbar">
					<input type="search" id="wpd-search" placeholder="<?php esc_attr_e( 'Search themes & plugins...', 'shahbaz-theme-extension-downloader' ); ?>" />
				</div>
			</div>

			<form id="wpd-bulk-form" method="post" action="<?php echo esc_url( $base_url ); ?>&wpd_action=bulk_download">
				<?php wp_nonce_field( 'wpd_download' ); ?>
				<input type="hidden" name="wpd_items" id="wpd-items" value="" />
				<div class="wpd-bulk-bar">
					<span><?php esc_html_e( 'Selected:', 'shahbaz-theme-extension-downloader' ); ?> <strong id="wpd-checked-count">0</strong></span>
					<?php submit_button( __( 'Download Selected as ZIP', 'shahbaz-theme-extension-downloader' ), 'primary', 'wpd_bulk_submit', false ); ?>
				</div>
			</form>

			<div class="wpd-tabs">
				<button type="button" class="wpd-tab active" data-target="wpd-themes">
					<?php esc_html_e( 'Themes', 'shahbaz-theme-extension-downloader' ); ?> (<?php echo esc_html( number_format_i18n( count( $themes ) ) ); ?>)
				</button>
				<button type="button" class="wpd-tab" data-target="wpd-plugins">
					<?php esc_html_e( 'Plugins', 'shahbaz-theme-extension-downloader' ); ?> (<?php echo esc_html( number_format_i18n( count( $plugins ) ) ); ?>)
				</button>
			</div>

			<div id="wpd-themes" class="wpd-panel">
				<?php self::render_grid( $themes, 'theme', $active_theme, $base_url ); ?>
			</div>

			<div id="wpd-plugins" class="wpd-panel hidden">
				<?php self::render_grid( $plugins, 'plugin', $active_plugins, $base_url ); ?>
			</div>
		</div>
		<?php
	}

	private static function render_grid( $items, $type, $active_data, $base_url ) {
		?>
		<div class="wpd-toolbar" style="margin-bottom: 16px;">
			<label>
				<input type="checkbox" class="wpd-select-all" />
				<?php esc_html_e( 'Select all visible', 'shahbaz-theme-extension-downloader' ); ?>
			</label>
		</div>
		<div class="wpd-grid">
			<?php if ( empty( $items ) ) : ?>
				<div class="wpd-empty"><?php esc_html_e( 'No items found.', 'shahbaz-theme-extension-downloader' ); ?></div>
			<?php else : ?>
				<?php foreach ( $items as $key => $item ) : ?>
					<?php
					if ( 'theme' === $type ) {
						$slug    = $key;
						$name    = $item->get( 'Name' );
						$version = $item->get( 'Version' );
						$author  = $item->get( 'Author' );
						$desc    = $item->get( 'Description' );
						$active  = ( $slug === $active_data );
					} else {
						$parts   = explode( '/', $key );
						$slug    = $parts[0];
						$name    = $item['Name'];
						$version = $item['Version'];
						$author  = $item['Author'];
						$desc    = $item['Description'];
						$active  = isset( $active_data[ $slug ] );
					}

					$search_text = implode( ' ', array( $name, $version, $author, $desc, $slug ) );
					$download_url = wp_nonce_url( add_query_arg( array( 'wpd_action' => 'download', 'wpd_type' => $type, 'wpd_slug' => $slug ), $base_url ), 'wpd_download' );
					?>
					<div class="wpd-card" data-search="<?php echo esc_attr( $search_text ); ?>">
						<div class="wpd-card__header">
							<h3 class="wpd-card__title"><?php echo esc_html( $name ); ?></h3>
							<input type="checkbox" class="wpd-card__checkbox" value="<?php echo esc_attr( $type . ':' . $slug ); ?>" />
						</div>
						<div class="wpd-card__meta">
							<?php echo esc_html( $slug ); ?> &bull; <?php esc_html_e( 'Version', 'shahbaz-theme-extension-downloader' ); ?> <?php echo esc_html( $version ); ?>
						</div>
						<div class="wpd-card__desc">
							<?php echo esc_html( wp_trim_words( $desc, 25 ) ); ?>
						</div>
						<div class="wpd-card__footer">
							<span class="wpd-badge <?php echo $active ? 'wpd-badge--active' : 'wpd-badge--inactive'; ?>">
								<?php echo $active ? esc_html__( 'Active', 'shahbaz-theme-extension-downloader' ) : esc_html__( 'Inactive', 'shahbaz-theme-extension-downloader' ); ?>
							</span>
							<a class="button" href="<?php echo esc_url( $download_url ); ?>">
								<?php esc_html_e( 'Download ZIP', 'shahbaz-theme-extension-downloader' ); ?>
							</a>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<div class="wpd-empty" style="display: none;">
			<?php esc_html_e( 'No items match your search.', 'shahbaz-theme-extension-downloader' ); ?>
		</div>
		<?php
	}
}
