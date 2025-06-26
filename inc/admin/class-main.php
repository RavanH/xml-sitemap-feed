<?php
/**
 * XMLSF Admin CLASS
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Admin;

/**
 * XMLSF Admin CLASS
 */
class Main {
	/**
	 * Initialize the admin class.
	 */
	public static function init() {
		self::notices_actions();

		\add_action( 'update_option_xmlsf_sitemaps', array( __CLASS__, 'update_sitemaps' ) );

		// ACTION LINK.
		\add_filter( 'plugin_action_links_' . XMLSF_BASENAME, array( __CLASS__, 'add_action_link' ) );
		\add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_meta_links' ), 10, 2 );

		// Shared Admin pages sidebar actions.
		\add_action( 'xmlsf_admin_sidebar', array( __CLASS__, 'admin_sidebar_help' ) );
		\add_action( 'xmlsf_admin_sidebar', array( __CLASS__, 'admin_sidebar_contribute' ), 20 );

		if ( \XMLSF\sitemaps_enabled( 'sitemap' ) ) {
			namespace\Sitemap::init();
		}

		if ( \XMLSF\sitemaps_enabled( 'news' ) ) {
			namespace\Sitemap_News::init();
		}
	}

	/**
	 * Plugin compatibility hooks and filters.
	 */
	public static function compat() {
		// Catch Box Pro compatibility.
		if ( \function_exists( 'catchbox_is_feed_url_present' ) ) {
			\add_action( 'admin_notices', array( '\XMLSF\Compat\Catch_Box_Pro', 'admin_notices' ) );
		}

		if ( \XMLSF\sitemaps_enabled( 'sitemap' ) ) {
			namespace\Sitemap::compat();
		}

		if ( \XMLSF\sitemaps_enabled( 'news' ) ) {
			namespace\Sitemap_News::compat();
		}
	}

	/**
	 * Add options pages
	 */
	public static function add_options_pages() {
		if ( \XMLSF\sitemaps_enabled( 'sitemap' ) ) {
			namespace\Sitemap::add_options_page();
		}

		if ( \XMLSF\sitemaps_enabled( 'news' ) ) {
			namespace\Sitemap_News::add_options_page();
		}
	}

	/**
	 * SETTINGS
	 */

	/**
	 * Update actions for Sitemaps
	 */
	public static function update_sitemaps() {
		if ( ! xmlsf()->using_permalinks() ) {
			return;
		}

		// Set transients for flushing.
		set_transient( 'xmlsf_sitemaps_updated', true );
	}

	/**
	 * Maybe sitemaps option was updated.
	 *
	 * Checks $_GET['settings-updated'] and transient 'xmlsf_sitemaps_updated'. Hooked into settings page load actions.
	 */
	public static function maybe_sitemaps_updated() {
		if ( ! empty( $_GET['settings-updated'] ) && \delete_transient( 'xmlsf_sitemaps_updated' ) ) {
			// Flush rewrite rules.
			\flush_rewrite_rules( false );

			// Check static files.
			$sitemaps = (array) \get_option( 'xmlsf_sitemaps' );

			if ( ! empty( $sitemaps['sitemap'] ) ) {
				$slug = \is_object( \xmlsf()->sitemap ) ? \xmlsf()->sitemap->slug() : 'sitemap';

				if ( \file_exists( \trailingslashit( \get_home_path() ) . $slug . '.xml' ) ) {
					\add_settings_error(
						'static_files_notice',
						'static_file_' . $slug,
						\sprintf( /* translators: %1$s file name, %2$s is XML Sitemap (linked to options-reading.php) */
							\esc_html__( 'A conflicting static file has been found: %1$s. Either delete it or disable the corresponding %2$s.', 'xml-sitemap-feed' ),
							\esc_html( $slug . '.xml' ),
							'<a href="' . \esc_url( \admin_url( 'options-reading.php' ) ) . '#xmlsf_sitemaps">' . \esc_html__( 'XML Sitemap', 'xml-sitemap-feed' ) . '</a>'
						),
						'warning'
					);
				}
			}

			if ( ! empty( $sitemaps['sitemap-news'] ) ) {
				$slug = \is_object( \xmlsf()->sitemap_news ) ? \xmlsf()->sitemap_news->slug() : 'sitemap-news';

				if ( \file_exists( \trailingslashit( \get_home_path() ) . $slug . '.xml' ) ) {
					\add_settings_error(
						'static_files_notice',
						'static_file_' . $slug,
						\sprintf( /* translators: %1$s file name, %2$s is XML Sitemap (linked to options-reading.php) */
							\esc_html__( 'A conflicting static file has been found: %1$s. Either delete it or disable the corresponding %2$s.', 'xml-sitemap-feed' ),
							\esc_html( $slug . '.xml' ),
							'<a href="' . \esc_url( \admin_url( 'options-reading.php' ) ) . '#xmlsf_sitemaps">' . \esc_html__( 'XML Sitemap', 'xml-sitemap-feed' ) . '</a>'
						),
						'warning'
					);
				}
			}
		}
	}

	/**
	 * Register settings and add settings fields
	 */
	public static function register_settings() {
		// Sitemaps.
		\register_setting(
			'reading',
			\get_option( 'blog_public' ) ? 'xmlsf_sitemaps' : ''
		);
		\add_settings_field(
			'xmlsf_sitemaps',
			__( 'Enable XML sitemaps', 'xml-sitemap-feed' ),
			array( __CLASS__, 'sitemaps_settings_field' ),
			'reading'
		);

		// Help tab.
		\add_action( 'load-options-reading.php', array( __CLASS__, 'xml_sitemaps_help' ) );

		// Robots rules.
		\register_setting(
			'reading',
			'xmlsf_robots',
			'sanitize_textarea_field'
		);
		\add_settings_field(
			'xmlsf_robots',
			__( 'Additional robots.txt rules', 'xml-sitemap-feed' ),
			array( __CLASS__, 'robots_settings_field' ),
			'reading'
		);

		// Maybe flush rewrite rules.
		\add_action( 'load-options-reading.php', array( __CLASS__, 'maybe_sitemaps_updated' ) );

		if ( \XMLSF\sitemaps_enabled( 'sitemap' ) ) {
			namespace\Sitemap::register_settings();
		}

		if ( \XMLSF\sitemaps_enabled( 'news' ) ) {
			namespace\Sitemap_News::register_settings();
		}
	}

	/**
	 * Sitemaps help tabs
	 */
	public static function xml_sitemaps_help() {
		\ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-sitemaps.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = \ob_get_clean();

		\get_current_screen()->add_help_tab(
			array(
				'id'       => 'sitemap-settings',
				'title'    => __( 'Enable XML sitemaps', 'xml-sitemap-feed' ),
				'content'  => $content,
				'priority' => 11,
			)
		);

		\ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-robots.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = \ob_get_clean();

		\get_current_screen()->add_help_tab(
			array(
				'id'       => 'robots',
				'title'    => __( 'Additional robots.txt rules', 'xml-sitemap-feed' ),
				'content'  => $content,
				'priority' => 11,
			)
		);
	}

	/**
	 * Sitemap settings fields
	 */
	public static function sitemaps_settings_field() {
		if ( 1 === (int) \get_option( 'blog_public' ) ) {
			$sitemaps = (array) \get_option( 'xmlsf_sitemaps', \XMLSF\get_default_settings( 'sitemaps' ) );
			// The actual fields for data entry.
			include XMLSF_DIR . '/views/admin/field-sitemaps.php';
		} else {
			\esc_html_e( 'XML Sitemaps are not available because of your site&#8217;s visibility settings (above).', 'xml-sitemap-feed' );
		}
	}

	/**
	 * ROBOTS
	 */
	public static function robots_settings_field() {
		global $wp_rewrite;

		$rules  = (array) \get_option( 'rewrite_rules' );
		$static = \file_exists( \trailingslashit( \get_home_path() ) . 'robots.txt' );

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-robots.php';
	}

	/**
	 * Clear settings
	 */
	public static function clear_settings() {
		// TODO reset Settings > Reading options here...

		\add_settings_error(
			'notice_clear_settings',
			'notice_clear_settings',
			__( 'Settings reset to the plugin defaults.', 'xml-sitemap-feed' ),
			'updated'
		);
	}

	/**
	 * Admin sidbar help section
	 */
	public static function admin_sidebar_help() {
		include XMLSF_DIR . '/views/admin/sidebar-help.php';
	}

	/**
	 * Admin sidbar contribute section
	 */
	public static function admin_sidebar_contribute() {
		include XMLSF_DIR . '/views/admin/sidebar-contribute.php';
	}

	/**
	 * Admin notices actions
	 */
	public static function notices_actions() {
		if ( ! isset( $_POST['_xmlsf_notice_nonce'] ) || ! \wp_verify_nonce( sanitize_key( $_POST['_xmlsf_notice_nonce'] ), XMLSF_BASENAME . '-notice' ) ) {
			return;
		}

		if ( isset( $_POST['xmlsf-dismiss'] ) ) {
			// Store user notice dismissal.
			$dismissed = \sanitize_key( $_POST['xmlsf-dismiss'] );
			\add_user_meta(
				\get_current_user_id(),
				'xmlsf_dismissed',
				$dismissed,
				false
			);
		}
	}

	/**
	 * Add action link
	 *
	 * @param array $links Array of links.
	 */
	public static function add_action_link( $links ) {
		$settings_link = '<a href="' . \admin_url( 'options-reading.php' ) . '#xmlsf_sitemaps">' . \translate( 'Settings' ) . '</a>';
		\array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Add plugin meta links
	 *
	 * @param array  $links Array of links.
	 * @param string $file Plugin file name.
	 */
	public static function plugin_meta_links( $links, $file ) {
		if ( XMLSF_BASENAME === $file ) {
			$links[] = '<a target="_blank" href="https://wordpress.org/support/plugin/xml-sitemap-feed/">' . __( 'Support', 'xml-sitemap-feed' ) . '</a>';
			$links[] = '<a target="_blank" href="https://wordpress.org/support/plugin/xml-sitemap-feed/reviews/?filter=5#new-post">' . __( 'Rate ★★★★★', 'xml-sitemap-feed' ) . '</a>';
		}
		return $links;
	}

	/**
	 * Plugin activation
	 *
	 * @since 5.4
	 * @return void
	 */
	public static function activate() {
		// Load sitemap.
		\xmlsf()->get_server( 'sitemap' );

		// Add core rules if needed.
		if ( \function_exists( 'wp_sitemaps_get_server' ) && 'core' === \xmlsf()->sitemap->server_type ) {
			$sitemaps = \wp_sitemaps_get_server();
			$sitemaps->register_rewrites();
		}

		// Register new plugin rules.
		\xmlsf()->register_rewrites();

		// Then flush.
		\flush_rewrite_rules( false );
	}

	/**
	 * Plugin de-activation
	 *
	 * @since 5.0
	 * @return void
	 */
	public static function deactivate() {
		// Clear all cache metadata.
		// Clear all meta caches...
		\delete_metadata( 'post', 0, '_xmlsf_image_attached', '', true );
		\delete_metadata( 'post', 0, '_xmlsf_image_featured', '', true );
		\delete_metadata( 'post', 0, '_xmlsf_comment_date_gmt', '', true );
		\delete_metadata( 'term', 0, 'term_modified', '', true );
		\delete_metadata( 'user', 0, 'user_modified', '', true );
		\delete_transient( 'xmlsf_images_meta_primed' );
		\delete_transient( 'xmlsf_comments_meta_primed' );

		// Remove old rules.
		\xmlsf()->unregister_rewrites();

		// Re-add core rules.
		if ( \function_exists( 'wp_sitemaps_get_server' ) ) {
			$sitemaps = \wp_sitemaps_get_server();
			$sitemaps->register_rewrites();
		}

		// Then flush.
		\flush_rewrite_rules( false );
	}
}
