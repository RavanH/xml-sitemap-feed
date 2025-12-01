<?php
/**
 * XMLSF Admin Sitemap News Settings
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Admin;

/**
 * XMLSF Admin Sitemap News Settings CLASS
 *
 * @since 5.6
 */
class Sitemap_News_Settings {
	/**
	 * Prepare admin page load.
	 */
	public static function load() {
		// Run GSC actions.
		self::gsc_actions();

		// Run tools actions.
		self::tools_actions();

		// Prepare help tabs.
		self::help_tab();

		// Add settings.
		\add_action( 'xmlsf_news_add_settings', array( __CLASS__, 'add_settings' ) );
	}

	/**
	 * Run admin actions.
	 */
	public static function gsc_actions() {
		// Skip if doing ajax or no valid nonce.
		if ( \wp_doing_ajax() || ! isset( $_POST['_xmlsf_gsc_nonce'] ) || ! \wp_verify_nonce( \sanitize_key( $_POST['_xmlsf_gsc_nonce'] ), XMLSF_BASENAME . '-gsc' ) ) {
			return;
		}

		// Handle disconnection if requested. Runs before anything else.
		if ( isset( $_POST['xmlsf_gsc_disconnect'] ) ) {
			// Clear the refresh token and any related options.
			GSC_Connect::disconnect();

			\add_settings_error(
				'xmlsf_gsc_connect',
				'gsc_disconnected',
				__( 'Disconnected from Google Search Console successfully.', 'xml-sitemap-feed' ),
				'success'
			);
		}

		// Handle manual submit.
		if ( isset( $_POST['xmlsf_gsc_manual_submit'] ) ) {
			// Skip submission if within the grace period for Google News sitemap.
			if ( \get_transient( 'sitemap_notifier_submission_news' ) ) {
				$timeframe = (int) \apply_filters( 'xmlsf_gsc_manual_submit_news_timeframe', MINUTE_IN_SECONDS );
				$message   = \sprintf( /* translators: %1$s: Google News Sitemap, %2$d: number of seconds */ esc_html__( 'Your %1$s submission was skipped: Already sent within the last %2$d seconds.', 'xml-sitemap-feed' ), esc_html__( 'Google News Sitemap', 'xml-sitemap-feed' ), $timeframe );

				\do_action( 'sitemap_notifier_manual_submission_news', $message, 'warning' );

				\add_settings_error(
					'xmlsf_gsc_connect',
					'gsc_manual_submit_news',
					$message,
					'warning'
				);
			} else {
				$sitemap = xmlsf()->sitemap_news->get_sitemap_url();
				$result  = \XMLSF\GSC_Connect::submit( $sitemap );
				if ( \is_wp_error( $result ) ) {
					$message = \sprintf( /* translators: %1$s: Google News Sitemap, %2$s: Error message */ esc_html__( 'Your %1$s submission failed: %2$s', 'xml-sitemap-feed' ), esc_html__( 'Google News Sitemap', 'xml-sitemap-feed' ), $result->get_error_message() );

					\do_action( 'sitemap_notifier_manual_submission_news', $message, 'error' );

					\add_settings_error(
						'xmlsf_gsc_connect',
						'gsc_manual_submit_news',
						$message,
						'error'
					);
				} else {
					$message = \sprintf( /* translators: %s: Google News Sitemap */ esc_html__( 'Your %s was submitted successfully.', 'xml-sitemap-feed' ), esc_html__( 'Google News Sitemap', 'xml-sitemap-feed' ) );

					\do_action( 'sitemap_notifier_manual_submission_news', $message, 'success' );

					\add_settings_error(
						'xmlsf_gsc_connect',
						'gsc_manual_submit_news',
						$message,
						'success'
					);

					$timeframe = \apply_filters( 'xmlsf_gsc_manual_submit_news_timeframe', 60 );
					\set_transient( 'sitemap_notifier_submission_news', true, $timeframe );
				}
			}
		}
	}

	/**
	 * Tools actions
	 */
	public static function tools_actions() {
		// Skip if doing ajax or no valid nonce.
		if ( \wp_doing_ajax() || ! isset( $_POST['_xmlsf_help_nonce'] ) || ! \wp_verify_nonce( \sanitize_key( $_POST['_xmlsf_help_nonce'] ), XMLSF_BASENAME . '-help' ) ) {
			return;
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

		if ( isset( $_POST['xmlsf-check-conflicts'] ) ) {
			// Reset ignored warnings.
			\delete_user_meta( \get_current_user_id(), 'xmlsf_dismissed' );

			// Check static file.
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
			} else {
				\add_settings_error(
					'static_files_notice',
					'static_files',
					\esc_html__( 'No conflicting static files found.', 'xml-sitemap-feed' ),
					'success'
				);
			}
		}

		if ( isset( $_POST['xmlsf-clear-settings'] ) ) {
			self::clear_settings();
			\add_settings_error(
				'notice_clear_settings',
				'notice_clear_settings',
				__( 'Settings reset to the plugin defaults.', 'xml-sitemap-feed' ),
				'updated'
			);
		}
	}

	/**
	 * Clear settings
	 */
	public static function clear_settings() {
		// Update to defaults.
		\update_option( 'xmlsf_news_tags', \XMLSF\get_default_settings( 'news_tags' ) );

		\do_action( 'xmlsf_clear_news_settings' );
	}

	/**
	 * Options page callback
	 */
	public static function settings_page() {
		$active_tab = isset( $_GET['tab'] ) ? \sanitize_key( $_GET['tab'] ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		\do_action( 'xmlsf_news_add_settings', $active_tab );

		// Sidebar actions.
		\add_action( 'xmlsf_admin_sidebar', array( __CLASS__, 'admin_sidebar_gsc_connect' ), 5 );
		\add_action(
			'xmlsf_admin_sidebar',
			function () {
				include XMLSF_DIR . '/views/admin/sidebar-news-tools.php';
			},
			9
		);
		\add_action(
			'xmlsf_admin_sidebar',
			function () {
				include XMLSF_DIR . '/views/admin/sidebar-news-links.php';
			},
			9
		);
		// Advanced plugin plug.
		if ( ! \is_plugin_active( 'xml-sitemap-feed-advanced-news/xml-sitemap-advanced-news.php' ) || ( defined( 'XMLSF_NEWS_ADV_VERSION' ) && version_compare( XMLSF_NEWS_ADV_VERSION, '1.4', '<' ) ) ) {
			\add_action( 'xmlsf_admin_sidebar', array( __CLASS__, 'admin_sidebar_adv_plug' ), 6 );
			\add_action( 'xmlsf_admin_sidebar', array( __CLASS__, 'admin_sidebar_priority_support' ), 11 );
		}

		include XMLSF_DIR . '/views/admin/page-sitemap-news.php';
	}

	/**
	 * Admin sidebar GSC section
	 */
	public static function admin_sidebar_gsc_connect() {
		$sitemap_desc      = __( 'Google News Sitemap', 'xml-sitemap-feed' );
		$settings_page_url = add_query_arg( 'ref', 'xmlsf_news', GSC_Connect::get_settings_url() );

		include XMLSF_DIR . '/views/admin/sidebar-gsc-connect.php';
	}

	/**
	 * Admin sidebar Priority Support section
	 */
	public static function admin_sidebar_priority_support() {
		$adv_plugin_name = __( 'Google News Advanced', 'xml-sitemap-feed' );
		$adv_plugin_url  = 'https://premium.status301.com/downloads/google-news-advanced/';

		include XMLSF_DIR . '/views/admin/sidebar-priority-support.php';
	}

	/**
	 * Admin sidebar Priority Support section
	 */
	public static function admin_sidebar_adv_plug() {
		$adv_plugin_name = __( 'Google News Advanced', 'xml-sitemap-feed' );
		$adv_plugin_url  = 'https://premium.status301.com/downloads/google-news-advanced/';
		$sitemap_name    = __( 'Google News Sitemap', 'xml-sitemap-feed' );

		include XMLSF_DIR . '/views/admin/sidebar-advanced-plug.php';
	}

	/**
	 * Add settings sections and fields.
	 *
	 * @param string $active_tab The active tab slug.
	 */
	public static function add_settings( $active_tab = '' ) {
		switch ( $active_tab ) {
			case 'advanced':
				// ADVANCED SECTION.
				\add_settings_section(
					'news_sitemap_advanced_section',
					'',
					'',
					'xmlsf_news_advanced'
				);
				// Hierarchical post types.
				\add_settings_field(
					'xmlsf_news_hierarchical',
					__( 'Hierarchical post types', 'xml-sitemap-feed' ),
					function () {
						include XMLSF_DIR . '/views/admin/field-news-hierarchical.php';
					},
					'xmlsf_news_advanced',
					'news_sitemap_advanced_section'
				);
				// Keywords.
				\add_settings_field(
					'xmlsf_news_keywords',
					__( 'Keywords', 'xml-sitemap-feed' ),
					function () {
						include XMLSF_DIR . '/views/admin/field-news-keywords.php';
					},
					'xmlsf_news_advanced',
					'news_sitemap_advanced_section'
				);
				// Stock tickers.
				\add_settings_field(
					'xmlsf_news_stock_tickers',
					__( 'Stock tickers', 'xml-sitemap-feed' ),
					function () {
						include XMLSF_DIR . '/views/admin/field-news-stocktickers.php';
					},
					'xmlsf_news_advanced',
					'news_sitemap_advanced_section'
				);
				// Sitemap notifier.
				\add_settings_field(
					'xmlsf_news_sitemap_notifier',
					__( 'Sitemap notifier', 'xml-sitemap-feed' ),
					function () {
						include XMLSF_DIR . '/views/admin/field-news-notifier.php';
					},
					'xmlsf_news_advanced',
					'news_sitemap_advanced_section'
				);
				\add_action( 'xmlsf_news_settings_before', array( __CLASS__, 'section_advanced_intro' ) );
				break;

			case 'general':
			default:
				// GENERAL SECTION.
				\add_settings_section(
					'news_sitemap_general_section',
					'',
					'',
					'xmlsf_news_general'
				);

				// SETTINGS.
				\add_settings_field(
					'xmlsf_news_name',
					'<label for="xmlsf_news_name">' . \__( 'Publication name', 'xml-sitemap-feed' ) . '</label>',
					function () {
						include XMLSF_DIR . '/views/admin/field-news-name.php';
					},
					'xmlsf_news_general',
					'news_sitemap_general_section'
				);
				\add_settings_field(
					'xmlsf_news_post_type',
					__( 'Post types', 'xml-sitemap-feed' ),
					function () {
						include XMLSF_DIR . '/views/admin/field-news-post-type.php';
					},
					'xmlsf_news_general',
					'news_sitemap_general_section'
				);

				global $wp_taxonomies;

				$options        = (array) \get_option( 'xmlsf_news_tags', array() );
				$news_post_type = isset( $options['post_type'] ) && ! empty( $options['post_type'] ) ? (array) $options['post_type'] : array( 'post' );
				$post_types     = ( isset( $wp_taxonomies['category'] ) ) ? $wp_taxonomies['category']->object_type : array();

				foreach ( $news_post_type as $post_type ) {
					if ( \in_array( $post_type, $post_types, true ) ) {
						\add_settings_field(
							'xmlsf_news_categories',
							\translate( 'Categories' ), // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
							function () {
								include XMLSF_DIR . '/views/admin/field-news-categories.php';
							},
							'xmlsf_news_general',
							'news_sitemap_general_section'
						);
						break;
					}
				}

				// GSC Sitemap data.
				\add_settings_section(
					'news_sitemap_gsc_data_section',
					__( 'Google Search Console Report', 'xml-sitemap-feed' ),
					function () {
						include XMLSF_DIR . '/views/admin/section-gsc-data-news.php';
					},
					'xmlsf_news_general'
				);
		}
	}

	/**
	 * Advanced section intro
	 *
	 * @param string $active_tab Active tab.
	 */
	public static function section_advanced_intro( $active_tab = '' ) {
		if ( 'advanced' === $active_tab && ! is_plugin_active( 'xml-sitemap-feed-advanced-news/xml-sitemap-advanced-news.php' ) ) {
			include XMLSF_DIR . '/views/admin/section-advanced-intro.php';
		}
	}

	/**
	 * Help tab
	 */
	public static function help_tab() {
		$screen     = \get_current_screen();
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		\ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-news.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = \ob_get_clean();

		$screen->add_help_tab(
			array(
				'id'      => 'sitemap-news-settings',
				'title'   => \__( 'Google News Sitemap', 'xml-sitemap-feed' ),
				'content' => $content,
			)
		);

		switch ( $active_tab ) {
			case 'general':
				// Publication name.
				\ob_start();
				include XMLSF_DIR . '/views/admin/help-tab-news-name.php';
				include XMLSF_DIR . '/views/admin/help-tab-support.php';
				$content = \ob_get_clean();
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-news-name',
						'title'   => \__( 'Publication name', 'xml-sitemap-feed' ),
						'content' => $content,
					)
				);
				// Post types.
				\ob_start();
				include XMLSF_DIR . '/views/admin/help-tab-news-post-types.php';
				include XMLSF_DIR . '/views/admin/help-tab-support.php';
				$content = \ob_get_clean();
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-news-post-types',
						'title'   => \__( 'Post types', 'xml-sitemap-feed' ),
						'content' => $content,
					)
				);
				// Categories.
				\ob_start();
				include XMLSF_DIR . '/views/admin/help-tab-news-categories.php';
				include XMLSF_DIR . '/views/admin/help-tab-support.php';
				$content = \ob_get_clean();
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-news-categories',
						'title'   => \translate( 'Categories' ), // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
						'content' => $content,
					)
				);
				break;

			case 'advanced':
				// Hierarchical post types.
				\ob_start();
				include XMLSF_DIR . '/views/admin/help-tab-news-hierarchical.php';
				include XMLSF_DIR . '/views/admin/help-tab-support.php';
				$content = \ob_get_clean();
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-news-post-types',
						'title'   => \__( 'Hierarchical post types', 'xml-sitemap-feed' ),
						'content' => $content,
					)
				);
				// Keywords.
				\ob_start();
				include XMLSF_DIR . '/views/admin/help-tab-news-keywords.php';
				include XMLSF_DIR . '/views/admin/help-tab-support.php';
				$content = \ob_get_clean();
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-news-keywords',
						'title'   => \__( 'Keywords', 'xml-sitemap-feed' ),
						'content' => $content,
					)
				);
				// Stock tickers.
				\ob_start();
				include XMLSF_DIR . '/views/admin/help-tab-news-stocktickers.php';
				include XMLSF_DIR . '/views/admin/help-tab-support.php';
				$content = \ob_get_clean();
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-news-stocktickers',
						'title'   => \__( 'Stock tickers', 'xml-sitemap-feed' ),
						'content' => $content,
					)
				);
				// Sitemap notifier.
				\ob_start();
				include XMLSF_DIR . '/views/admin/help-tab-news-notifier.php';
				include XMLSF_DIR . '/views/admin/help-tab-support.php';
				$content = \ob_get_clean();
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-news-notifier',
						'title'   => \__( 'Sitemap notifier', 'xml-sitemap-feed' ),
						'content' => $content,
					)
				);
		}

		// Hook for additional/advanced help tab.
		\do_action( 'xmlsf_news_help_tabs', $screen, $active_tab );

		\ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-news-sidebar.php';
		$content = \ob_get_clean();

		$screen->set_help_sidebar( $content );
	}

	/**
	 * Sanitize news tag settings
	 *
	 * @param array $save Settings array.
	 *
	 * @return array
	 */
	public static function sanitize_news_tags( $save ) {
		$sanitized = \is_array( $save ) ? $save : array();

		// At least one, default post type.
		if ( empty( $sanitized['post_type'] ) || ! \is_array( $sanitized['post_type'] ) ) {
			$sanitized['post_type'] = array( 'post' );
			// Add settings error.
			\add_settings_error(
				'xmlsf_news_tags',
				'xmlsf_news_post_type_error',
				__( 'At least one post type must be selected. Defaulting to "Posts".', 'xml-sitemap-feed' ),
				'error'
			);
		}

		// If there are categories selected, then test.
		// If we have post types selected that do not use the post category taxonomy.
		if ( ! empty( $sanitized['categories'] ) ) {
			global $wp_taxonomies;
			$post_types = ( isset( $wp_taxonomies['category'] ) ) ? $wp_taxonomies['category']->object_type : array();

			$disabled = false;
			foreach ( $sanitized['post_type'] as $post_type ) {
				if ( ! \in_array( $post_type, $post_types, true ) ) {
					$disabled = true;
					break;
				}
			}
			// Suppress category selection.
			if ( $disabled ) {
				unset( $sanitized['categories'] );
			}
		}

		return $sanitized;
	}
}
