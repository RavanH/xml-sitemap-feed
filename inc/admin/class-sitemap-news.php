<?php
/**
 * XMLSF Admin Sitemap News CLASS
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Admin;

/**
 * XMLSF Admin Sitemap News CLASS
 */
class Sitemap_News {
	/**
	 * Initialize hooks and filters.
	 */
	public static function init() {
		\add_action( 'admin_notices', array( '\XMLSF\Admin\Sitemap_News', 'check_advanced' ), 0 );

		// META.
		\add_action( 'add_meta_boxes', array( '\XMLSF\Admin\Sitemap_News', 'add_meta_box' ) );
		\add_action( 'save_post', array( '\XMLSF\Admin\Sitemap_News', 'save_metadata' ) );
	}

	/**
	 * Plugin compatibility hooks and filters.
	 * Hooked on admin_init.
	 */
	public static function compat() {
		// Yoast SEO compatibility.
		if ( \is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			\add_action( 'admin_notices', array( '\XMLSF\Compat\WP_SEO', 'news_admin_notice' ) );
		}

		// Squirrly SEO compatibility.
		if ( \is_plugin_active( 'squirrly-seo/squirrly.php' ) ) {
			\add_action( 'admin_notices', array( '\XMLSF\Compat\Squirrly_SEO', 'news_admin_notices' ) );
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
	 * Tools actions
	 */
	public static function tools_actions() {
		// Skip if doing ajax or no valid nonce.
		if ( \wp_doing_ajax() || ! isset( $_POST['_xmlsf_help_nonce'] ) || ! \wp_verify_nonce( sanitize_key( $_POST['_xmlsf_help_nonce'] ), XMLSF_BASENAME . '-help' ) ) {
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
	 * Compare versions to known compatibility.
	 */
	public static function compatible_with_advanced() {
		// Return true if plugin is not active.
		if ( ! \is_plugin_active( 'xml-sitemap-feed-advanced-news/xml-sitemap-advanced-news.php' ) ) {
			return true;
		}

		// Check version.
		\defined( 'XMLSF_NEWS_ADV_VERSION' ) || \define( 'XMLSF_NEWS_ADV_VERSION', '0' );

		return \version_compare( XMLSF_NEWS_ADV_MIN_VERSION, XMLSF_NEWS_ADV_VERSION, '<=' );
	}

	/**
	 * Check for conflicting themes and plugins
	 */
	public static function check_advanced() {
		if ( ! \current_user_can( 'manage_options' ) ) {
			return;
		}

		// Google News Advanced incompatibility notice.
		if ( ! self::compatible_with_advanced() && ! \in_array( 'xmlsf_advanced_news', (array) get_user_meta( get_current_user_id(), 'xmlsf_dismissed' ), true ) ) {
			include XMLSF_DIR . '/views/admin/notice-xmlsf-advanced-news.php';
		}
	}

	/**
	 * Add a News Sitemap meta box to the side column
	 */
	public static function add_meta_box() {
		$news_tags       = \get_option( 'xmlsf_news_tags' );
		$news_post_types = ! empty( $news_tags['post_type'] ) && \is_array( $news_tags['post_type'] ) ? $news_tags['post_type'] : array( 'post' );

		// Only include metabox on post types that are included.
		foreach ( $news_post_types as $post_type ) {
			\add_meta_box(
				'xmlsf_news_section',
				__( 'Google News', 'xml-sitemap-feed' ),
				array( __CLASS__, 'meta_box' ),
				$post_type,
				'side'
			);
		}
	}

	/**
	 * Add a News Sitemap meta box to the side column
	 *
	 * @param obj $post The post object.
	 */
	public static function meta_box( $post ) {
		// Use nonce for verification.
		\wp_nonce_field( XMLSF_BASENAME, '_xmlsf_news_nonce' );

		// Use get_post_meta to retrieve an existing value from the database and use the value for the form.
		$exclude  = 'private' === $post->post_status || \get_post_meta( $post->ID, '_xmlsf_news_exclude', true );
		$disabled = 'private' === $post->post_status;

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-meta-box-news.php';
	}

	/**
	 * When the post is saved, save our meta data
	 *
	 * @param int $post_id The post ID.
	 */
	public static function save_metadata( $post_id ) {
		// Verify nonce and user privileges.
		if (
			! isset( $_POST['_xmlsf_news_nonce'] ) ||
			! \wp_verify_nonce( \wp_unslash( \sanitize_key( $_POST['_xmlsf_news_nonce'] ) ), XMLSF_BASENAME ) ||
			! \current_user_can( 'edit_post', $post_id )
		) {
			return;
		}

		// _xmlsf_news_exclude
		if ( empty( $_POST['xmlsf_news_exclude'] ) ) {
			\delete_post_meta( $post_id, '_xmlsf_news_exclude' );
		} else {
			\update_post_meta( $post_id, '_xmlsf_news_exclude', '1' );
		}
	}

	/**
	 * Add options page
	 */
	public static function add_options_page() {
		// This page will be under "Settings".
		$screen_id = \add_options_page(
			__( 'Google News Sitemap', 'xml-sitemap-feed' ),
			__( 'Google News', 'xml-sitemap-feed' ),
			'manage_options',
			'xmlsf_news',
			array( __CLASS__, 'settings_page' )
		);

		// Settings hooks.
		\add_action( 'xmlsf_news_add_settings', array( __CLASS__, 'add_settings' ) );

		// Tools actions.
		\add_action( 'load-' . $screen_id, array( __CLASS__, 'tools_actions' ) );

		// Help tab.
		\add_action( 'load-' . $screen_id, array( __CLASS__, 'help_tab' ) );
	}

	/**
	 * Options page callback
	 */
	public static function settings_page() {
		global $wp_rewrite;

		$active_tab = isset( $_GET['tab'] ) ? \sanitize_key( $_GET['tab'] ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		\do_action( 'xmlsf_news_add_settings', $active_tab );

		// prepare sitemap link url.
		$sitemap_url = \xmlsf()->sitemap_news->get_sitemap_url();

		// Sidebar actions.
		\add_action(
			'xmlsf_admin_sidebar',
			function () {
				include XMLSF_DIR . '/views/admin/sidebar-news-links.php';
			},
			9
		);

		include XMLSF_DIR . '/views/admin/page-sitemap-news.php';
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
					array( __CLASS__, 'name_field' ),
					'xmlsf_news_general',
					'news_sitemap_general_section'
				);
				\add_settings_field(
					'xmlsf_news_post_type',
					__( 'Post types', 'xml-sitemap-feed' ),
					array( __CLASS__, 'post_type_field' ),
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
							array( __CLASS__, 'categories_field' ),
							'xmlsf_news_general',
							'news_sitemap_general_section'
						);
						break;
					}
				}

				// Source labels - deprecated.
				\add_settings_field(
					'xmlsf_news_labels',
					__( 'Content labels', 'xml-sitemap-feed' ),
					function () {
						include XMLSF_DIR . '/views/admin/field-news-labels.php';
					},
					'xmlsf_news_general',
					'news_sitemap_general_section'
				);
		}
	}

	/**
	 * Register settings
	 */
	public static function register_settings() {
		\register_setting(
			'xmlsf_news_general',
			'xmlsf_news_tags',
			array( __CLASS__, 'sanitize_news_tags' )
		);

		// Dummy register setting to prevent admin error on Save Settings from Advanced tab.
		\register_setting(
			'xmlsf_news_advanced',
			''
		);
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
		}

		// Hook for additional/advanced help tab.
		\do_action( 'xmlsf_news_help_tabs', $screen, $active_tab );

		\ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-news-sidebar.php';
		$content = \ob_get_clean();

		$screen->set_help_sidebar( $content );
	}

	/**
	 * News source name field
	 */
	public static function name_field() {
		$options = (array) \get_option( 'xmlsf_news_tags', array() );
		$name    = ! empty( $options['name'] ) ? $options['name'] : '';

		if ( XMLSF_GOOGLE_NEWS_NAME ) {
			$name = XMLSF_GOOGLE_NEWS_NAME;
		}

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-news-name.php';
	}

	/**
	 * Post type field
	 */
	public static function post_type_field() {
		global $wp_taxonomies;

		$post_types = \apply_filters(
			'xmlsf_news_post_types',
			\get_post_types(
				array(
					'public'       => true,
					'hierarchical' => false,
				)
				/*,'objects'*/
			)
		);

		// Make sure post types are allowed and publicly viewable.
		$post_types = \array_diff( $post_types, \xmlsf()->disabled_post_types() );
		$post_types = \array_filter( $post_types, 'is_post_type_viewable' );

		if ( ! \is_array( $post_types ) || empty( $post_types ) ) {
			// This should never happen.
			echo '<p class="description warning">' . \esc_html__( 'There appear to be no post types available.', 'xml-sitemap-feed' ) . '</p>';
			return;
		}

		$options        = (array) \get_option( 'xmlsf_news_tags', array() );
		$news_post_type = isset( $options['post_type'] ) && ! empty( $options['post_type'] ) ? (array) $options['post_type'] : array( 'post' );
		$type           = \apply_filters( 'xmlsf_news_post_type_field_type', 1 === \count( $news_post_type ) ? 'radio' : 'checkbox' );
		$allowed        = ( ! empty( $options['categories'] ) && isset( $wp_taxonomies['category'] ) ) ? $wp_taxonomies['category']->object_type : $post_types;
		$do_warning     = ( ! empty( $options['categories'] ) && \count( $post_types ) > 1 ) ? true : false;

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-news-post-type.php';
	}

	/**
	 * Categories field
	 */
	public static function categories_field() {
		$options             = (array) \get_option( 'xmlsf_news_tags', array() );
		$selected_categories = isset( $options['categories'] ) && \is_array( $options['categories'] ) ? $options['categories'] : array();

		if ( \function_exists( '\pll_languages_list' ) ) {
			\add_filter(
				'get_terms_args',
				function ( $args ) {
					$args['lang'] = '';
					return $args;
				}
			);
		}

		global $sitepress;
		if ( $sitepress ) {
			\remove_filter( 'get_terms_args', array( $sitepress, 'get_terms_args_filter' ) );
			\remove_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1 );
			\remove_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ) );
		}

		$cat_list = \str_replace(
			'name="post_category[]"',
			'name="xmlsf_news_tags[categories][]"',
			\wp_terms_checklist(
				null,
				array(
					'taxonomy'      => 'category',
					'selected_cats' => $selected_categories,
					'echo'          => false,
				)
			)
		);

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-news-categories.php';
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
