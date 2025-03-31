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
class Admin {
	/**
	 * CONSTRUCTOR
	 */
	private function __construct() {}

	/**
	 * INIT
	 */
	public static function init() {

		\add_action( 'admin_menu', array( __CLASS__, 'add_settings_pages' ) );

		\add_action( 'admin_init', array( __CLASS__, 'register_settings' ), 7 );
		\add_action( 'rest_api_init', array( __CLASS__, 'register_settings' ) );
		\add_action( 'admin_init', array( __CLASS__, 'tools_actions' ), 9 );
		\add_action( 'admin_init', array( __CLASS__, 'notices_actions' ), 9 );
		\add_action( 'admin_init', array( __CLASS__, 'maybe_flush_rewrite_rules' ), 11 );

		\add_action( 'admin_notices', array( __CLASS__, 'check_conflicts' ), 0 );
		\add_action( 'update_option_xmlsf_sitemaps', array( __CLASS__, 'update_sitemaps' ), 10, 2 );

		// ACTION LINK.
		\add_filter( 'plugin_action_links_' . XMLSF_BASENAME, array( __CLASS__, 'add_action_link' ) );
		\add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_meta_links' ), 10, 2 );

		// Shared Admin pages sidebar actions.
		\add_action( 'xmlsf_admin_sidebar', array( __CLASS__, 'admin_sidebar_help' ) );
		\add_action( 'xmlsf_admin_sidebar', array( __CLASS__, 'admin_sidebar_contribute' ), 20 );

		if ( \XMLSF\sitemaps_enabled( 'sitemap' ) ) {
			\add_action( 'admin_init', array( __NAMESPACE__ . '\Sitemap', 'register_settings' ), 7 );
			\add_action( 'rest_api_init', array( __NAMESPACE__ . '\Sitemap', 'register_settings' ) );
			\add_action( 'admin_init', array( __NAMESPACE__ . '\Sitemap', 'tools_actions' ), 9 );
			\add_action( 'admin_notices', array( __NAMESPACE__ . '\Sitemap', 'check_conflicts' ), 0 );

			// META.
			\add_action( 'add_meta_boxes', array( __NAMESPACE__ . '\Sitemap', 'add_meta_box' ) );
			\add_action( 'save_post', array( __NAMESPACE__ . '\Sitemap', 'save_metadata' ) );

			// Placeholders for advanced options.
			\add_action( 'xmlsf_posttype_archive_field_options', array( __NAMESPACE__ . '\Fields', 'advanced_archive_field_options' ) );

			// QUICK EDIT.
			\add_action( 'admin_init', array( __NAMESPACE__ . '\Sitemap', 'add_columns' ) );
			\add_action( 'quick_edit_custom_box', array( __NAMESPACE__ . '\Fields', 'quick_edit_fields' ) );
			\add_action( 'save_post', array( __NAMESPACE__ . '\Sitemap', 'quick_edit_save' ) );
			\add_action( 'admin_head', array( __NAMESPACE__ . '\Sitemap', 'quick_edit_script' ), 99 );
			// BULK EDIT.
			\add_action( 'bulk_edit_custom_box', array( __NAMESPACE__ . '\Fields', 'bulk_edit_fields' ), 0 );
		}

		if ( \XMLSF\sitemaps_enabled( 'news' ) ) {
			\add_action( 'admin_init', array( __NAMESPACE__ . '\Sitemap_News', 'register_settings' ), 7 );
			\add_action( 'rest_api_init', array( __NAMESPACE__ . '\Sitemap_News', 'register_settings' ) );
			\add_action( 'admin_init', array( __NAMESPACE__ . '\Sitemap_News', 'tools_actions' ), 9 );
			\add_action( 'admin_notices', array( __NAMESPACE__ . '\Sitemap_News', 'check_conflicts' ), 0 );

			// META.
			\add_action( 'add_meta_boxes', array( __NAMESPACE__ . '\Sitemap_News', 'add_meta_box' ) );
			\add_action( 'save_post', array( __NAMESPACE__ . '\Sitemap_News', 'save_metadata' ) );
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
	 * Maybe flush rewrite rules
	 *
	 * Uses $wp_rewrite->wp_rewrite_rules() which checks for empty rewrite_rules option.
	 */
	public static function maybe_flush_rewrite_rules() {
		global $wp_rewrite;
		$wp_rewrite->wp_rewrite_rules(); // Recreates rewrite rules only when needed.
	}

	/**
	 * SETTINGS
	 */

	/**
	 * Update actions for Sitemaps
	 *
	 * @param mixed $old   Old option value.
	 * @param mixed $value Saved option value.
	 */
	public static function update_sitemaps( $old, $value ) {
		$old   = (array) $old;
		$value = (array) $value;

		if ( $old !== $value ) {
			// Check static files.
			self::check_static_files();

			// Flush rewrite rules on upcoming init.
			\delete_option( 'rewrite_rules' );
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
		\add_action(
			'load-options-reading.php',
			array( __CLASS__, 'xml_sitemaps_help' )
		);

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
	}

	/**
	 * SITEMAPS
	 */

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
		$found  = self::check_static_files( 'robots.txt', 0 );
		$static = ! empty( $found ) && \in_array( 'robots.txt', $found, true );

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
	 * Check for static sitemap files
	 *
	 * @param mixed $files     Filename or array of filenames.
	 * @param bool  $verbosity Verbosity level: 0|false (no messages), 1|true (warnings only) or 2 (warnings or success).
	 *
	 * @return array Found static files.
	 */
	public static function check_static_files( $files = array(), $verbosity = 1 ) {
		if ( empty( $files ) ) { // TODO a better way of getting file names.
			$sitemaps = (array) \get_option( 'xmlsf_sitemaps', \XMLSF\get_default_settings( 'sitemaps' ) );
			foreach ( $sitemaps as $type => $file ) {
				$files[] = $file;
			}
		}

		$home_path = \trailingslashit( \get_home_path() );
		$found     = array();

		foreach ( (array) $files as $pretty ) {
			if ( ! empty( $pretty ) && \file_exists( $home_path . $pretty ) ) {
				$found[] = $pretty;
				$verbosity && \add_settings_error(
					'static_files_notice',
					'static_files',
					\sprintf( /* translators: %1$s file name, %2$s is XML Sitemap (linked to options-reading.php) */
						\esc_html__( 'A conflicting static file has been found: %1$s. Either delete it or disable the corresponding %2$s.', 'xml-sitemap-feed' ),
						\esc_html( $pretty ),
						'<a href="' . \esc_url( \admin_url( 'options-reading.php' ) ) . '#xmlsf_sitemaps">' . \esc_html__( 'XML Sitemap', 'xml-sitemap-feed' ) . '</a>'
					),
					'warning'
				);
			}
		}

		// Tell me if all is OK.
		$verbosity > 1 && empty( $found ) && \add_settings_error(
			'static_files_notice',
			'static_files',
			__( 'No conflicting static files found.', 'xml-sitemap-feed' ),
			'success'
		);

		return $found;
	}

	/**
	 * Check for conflicting themes and plugins
	 */
	public static function check_conflicts() {
		if ( \wp_doing_ajax() || ! \current_user_can( 'manage_options' ) ) {
			return;
		}

		// If XML Sitemaps Manager is active, remove its init and admin_init hooks.
		if ( \is_plugin_active( 'xml-sitemaps-manager/xml-sitemaps-manager.php' ) && ! \in_array( 'xml_sitemaps_manager', (array) \get_user_meta( \get_current_user_id(), 'xmlsf_dismissed' ), true ) ) {
			\add_action(
				'admin_notices',
				function () {
					include XMLSF_DIR . '/views/admin/notice-xml-sitemaps-manager.php';
				}
			);
		}

		// Catch Box Pro feed redirect.
		if ( \function_exists( 'catchbox_is_feed_url_present' ) && \catchbox_is_feed_url_present( null ) ) {
			\add_action(
				'admin_notices',
				function () {
					include XMLSF_DIR . '/views/admin/notice-catchbox-feed-redirect.php';
				}
			);
		}
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
