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
	 * Plugin compatibility hooks and filters.
	 * Hooked on admin_init.
	 */
	public static function compat() {
		// Catch Box Pro compatibility.
		if ( \function_exists( 'catchbox_is_feed_url_present' ) ) {
			\add_action( 'admin_notices', array( __NAMESPACE__ . '\Compat\Catch_Box_Pro', 'admin_notices' ) );
		}
	}

	/**
	 * Add options page
	 */
	public static function add_settings_pages() {
		if ( \XMLSF\sitemaps_enabled( 'sitemap' ) ) {
			// This page will be under "Settings".
			$screen_id = \add_options_page(
				__( 'XML Sitemap', 'xml-sitemap-feed' ),
				__( 'XML Sitemap', 'xml-sitemap-feed' ),
				'manage_options',
				'xmlsf',
				array( __NAMESPACE__ . '\Sitemap', 'settings_page' )
			);

			// Settings hooks.
			\add_action( 'xmlsf_add_settings', array( __NAMESPACE__ . '\Sitemap', 'add_settings' ) );

			// Help tabs.
			\add_action( 'load-' . $screen_id, array( __NAMESPACE__ . '\Sitemap', 'help_tabs' ) );
		}

		if ( \XMLSF\sitemaps_enabled( 'news' ) ) {
			// This page will be under "Settings".
			$screen_id = \add_options_page(
				__( 'Google News Sitemap', 'xml-sitemap-feed' ),
				__( 'Google News', 'xml-sitemap-feed' ),
				'manage_options',
				'xmlsf_news',
				array( __NAMESPACE__ . '\Sitemap_News', 'settings_page' )
			);

			// Settings hooks.
			\add_action( 'xmlsf_news_add_settings', array( __NAMESPACE__ . '\Sitemap_News', 'add_settings' ) );

			// Help tab.
			\add_action( 'load-' . $screen_id, array( __NAMESPACE__ . '\Sitemap_News', 'help_tab' ) );
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
	 * Tools actions
	 */
	public static function tools_actions() {
		if ( ! isset( $_POST['_xmlsf_help_nonce'] ) || ! \wp_verify_nonce( \sanitize_key( $_POST['_xmlsf_help_nonce'] ), XMLSF_BASENAME . '-help' ) ) {
			return;
		}

		// TODO clear global settings.
		if ( isset( $_POST['xmlsf-clear-settings-general'] ) ) {
			self::clear_settings();
		}

		if ( isset( $_POST['xmlsf-flush-rewrite-rules'] ) ) {
			// Flush rewrite rules.
			\flush_rewrite_rules( false );
			\add_settings_error(
				'flush_admin_notice',
				'flush_admin_notice',
				__( 'WordPress rewrite rules have been flushed.', 'xml-sitemap-feed' ),
				'success'
			);
		}
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
}
