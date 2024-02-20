<?php
/**
 * XMLSF Admin Sitemap News CLASS
 *
 * @package XML Sitemap & Google News
 */

/**
 * XMLSF Admin Sitemap News CLASS
 */
class XMLSF_Admin_Sitemap_News {
	/**
	 * Holds the values to be used in the fields callbacks.
	 *
	 * @var array $options Options array.
	 */
	private $options;

	/**
	 * Minimal compatible pro version
	 *
	 * @var float
	 */
	public static $compat_pro_min = '1.3.5';

	/**
	 * Start up.
	 */
	public function __construct() {
		// META.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_metadata' ) );

		// SETTINGS.
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		// Settings hooks.
		add_action( 'xmlsf_news_add_settings', array( $this, 'add_settings' ) );

		// ACTIONS & CHECKS.
		add_action( 'admin_init', array( $this, 'tools_actions' ), 9 );
		add_action( 'admin_init', array( $this, 'check_conflicts' ), 11 );
		add_action( 'admin_init', array( $this, 'check_news_advanced' ), 11 );
	}

	/**
	 * Clear settings
	 */
	public function clear_settings() {
		// Update to defaults.
		update_option( 'xmlsf_news_tags', xmlsf()->default_news_tags );
	}

	/**
	 * Tools actions
	 */
	public function tools_actions() {
		if ( ! isset( $_POST['_xmlsf_help_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_xmlsf_help_nonce'] ), XMLSF_BASENAME . '-help' ) ) {
			return;
		}

		if ( isset( $_POST['xmlsf-check-conflicts-news'] ) ) {
			// Reset ignored warnings.
			delete_user_meta( get_current_user_id(), 'xmlsf_dismissed' );

			xmlsf_admin()->check_static_files( 'sitemap-news.xml' );
		}

		if ( isset( $_POST['xmlsf-clear-settings-news'] ) ) {
			$this->clear_settings();
			add_settings_error(
				'notice_clear_settings',
				'notice_clear_settings',
				__( 'Settings reset to the plugin defaults.', 'xml-sitemap-feed' ),
				'updated'
			);
		}
	}

	/**
	 * CHECKS
	 */

	/**
	 * Google News Advanced incompatibility notice
	 */
	public function check_news_advanced() {
		// Skip if no advanced plugin or dismissed.
		if (
			wp_doing_ajax() ||
			! is_plugin_active( 'xml-sitemap-feed-advanced-news/xml-sitemap-advanced-news.php' ) ||
			in_array( 'xmlsf_advanced_news', (array) get_user_meta( get_current_user_id(), 'xmlsf_dismissed' ) )
		) {
			return;
		}

		if ( ! $this->compatible_with_advanced() ) {
			add_action(
				'admin_notices',
				function () {
					include XMLSF_DIR . '/views/admin/notice-xmlsf-advanced-news.php';
				}
			);
		}
	}

	/**
	 * Compare versions to known compatibility.
	 */
	public function compatible_with_advanced() {
		// Check version.
		defined( 'XMLSF_NEWS_ADV_VERSION' ) || define( 'XMLSF_NEWS_ADV_VERSION', '0.1' );

		return version_compare( self::$compat_pro_min, XMLSF_NEWS_ADV_VERSION, '<=' );
	}

	/**
	 * Check for conflicting themes and plugins
	 */
	public function check_conflicts() {
		// Catch Box Pro feed redirect.
		if ( function_exists( 'catchbox_is_feed_url_present' ) && catchbox_is_feed_url_present( null ) ) {
			add_action(
				'admin_notices',
				function () {
					include XMLSF_DIR . '/views/admin/notice-catchbox-feed-redirect.php';
				}
			);
		}

		// Ad Inserter XML setting incompatibility warning.
		if ( is_plugin_active( 'ad-inserter/ad-inserter.php' ) ) {
			$adsettings = get_option( 'ad_inserter' );
			if ( is_array( $adsettings ) && ! empty( $adsettings ) ) {
				foreach ( $adsettings as $ad => $settings ) {
					// Check rss feed setting.
					if ( ! empty( $settings['code'] ) && empty( $settings['disable_insertion'] ) && ! empty( $settings['enable_feed'] ) ) {
						add_action(
							'admin_notices',
							function () {
								include XMLSF_DIR . '/views/admin/notice-ad-insterter-feed.php';
							}
						);
						break;
					}
				}
			}
		}
	}

	/**
	 * META BOXES
	 */

	/**
	 * Add a News Sitemap meta box to the side column
	 */
	public function add_meta_box() {
		$news_tags       = get_option( 'xmlsf_news_tags' );
		$news_post_types = ! empty( $news_tags['post_type'] ) && is_array( $news_tags['post_type'] ) ? $news_tags['post_type'] : array( 'post' );

		// Only include metabox on post types that are included.
		foreach ( $news_post_types as $post_type ) {
			add_meta_box(
				'xmlsf_news_section',
				__( 'Google News', 'xml-sitemap-feed' ),
				array( $this, 'meta_box' ),
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
	public function meta_box( $post ) {
		// Use nonce for verification.
		wp_nonce_field( XMLSF_BASENAME, '_xmlsf_news_nonce' );

		// Use get_post_meta to retrieve an existing value from the database and use the value for the form.
		$exclude = 'private' == $post->post_status || get_post_meta( $post->ID, '_xmlsf_news_exclude', true );
		$disabled = 'private' == $post->post_status;

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/meta-box-news.php';
	}

	/**
	 * When the post is saved, save our meta data
	 *
	 * @param int $post_id The post ID.
	 */
	public function save_metadata( $post_id ) {
		// Verify nonce and user privileges.
		if (
			! isset( $_POST['_xmlsf_news_nonce'] ) ||
			! wp_verify_nonce( wp_unslash( sanitize_key( $_POST['_xmlsf_news_nonce'] ) ), XMLSF_BASENAME ) ||
			! current_user_can( 'edit_post', $post_id )
		) {
			return;
		}

		// _xmlsf_news_exclude
		if ( empty( $_POST['xmlsf_news_exclude'] ) ) {
			delete_post_meta( $post_id, '_xmlsf_news_exclude' );
		} else {
			update_post_meta( $post_id, '_xmlsf_news_exclude', '1' );
		}
	}

	/**
	 * SETTINGS
	 */

	/**
	 * Add options page
	 */
	public function add_settings_page() {
		// This page will be under "Settings".
		$screen_id = add_options_page(
			__( 'Google News Sitemap', 'xml-sitemap-feed' ),
			__( 'Google News', 'xml-sitemap-feed' ),
			'manage_options',
			'xmlsf_news',
			array( $this, 'settings_page' )
		);

		// Help tab.
		add_action( 'load-' . $screen_id, array( $this, 'help_tab' ) );
	}

	/**
	 * Options page callback
	 */
	public function settings_page() {
		global $wp_rewrite;
		$this->options = (array) get_option( 'xmlsf_news_tags', array() );

		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';

		do_action( 'xmlsf_news_add_settings', $active_tab );

		// prepare sitemap link url.
		$sitemap_url = xmlsf_sitemap_url( 'news' );

		// Sidebar actions.
		add_action(
			'xmlsf_admin_sidebar',
			function () {
				include XMLSF_DIR . '/views/admin/sidebar-news-links.php';
			},
			9
		);

		include XMLSF_DIR . '/views/admin/page-sitemap-news.php';
	}

	/**
	 * Add advanced settings
	 *
	 * @param string $active_tab The active tab slug.
	 */
	public function add_settings( $active_tab = '' ) {
		if ( 'advanced' === $active_tab ) {
			// ADVANCED SECTION.
			add_settings_section(
				'news_sitemap_advanced_section',
				/* '<a name="xmlnf"></a>'.__( 'Google News Sitemap', 'xml-sitemap-feed' ) */
				'',
				'',
				'xmlsf_news_advanced'
			);

			// Hierarchical post types.
			add_settings_field(
				'xmlsf_news_hierarchical',
				__( 'Hierarchical post types', 'xml-sitemap-feed' ),
				function () {
					include XMLSF_DIR . '/views/admin/field-news-hierarchical.php';
				},
				'xmlsf_news_advanced',
				'news_sitemap_advanced_section'
			);

			// Keywords.
			add_settings_field(
				'xmlsf_news_keywords',
				__( 'Keywords', 'xml-sitemap-feed' ),
				function () {
					include XMLSF_DIR . '/views/admin/field-news-keywords.php';
				},
				'xmlsf_news_advanced',
				'news_sitemap_advanced_section'
			);

			// Stock tickers.
			add_settings_field(
				'xmlsf_news_stock_tickers',
				__( 'Stock tickers', 'xml-sitemap-feed' ),
				function () {
					include XMLSF_DIR . '/views/admin/field-news-stocktickers.php';
				},
				'xmlsf_news_advanced',
				'news_sitemap_advanced_section'
			);
		} else {
			// GENERAL SECTION.
			add_settings_section(
				'news_sitemap_general_section',
				/* '<a name="xmlnf"></a>'.__( 'Google News Sitemap', 'xml-sitemap-feed' ) */
				'',
				'',
				'xmlsf_news_general'
			);

			// SETTINGS.
			add_settings_field(
				'xmlsf_news_name',
				'<label for="xmlsf_news_name">' . __( 'Publication name', 'xml-sitemap-feed' ) . '</label>',
				array( $this, 'name_field' ),
				'xmlsf_news_general',
				'news_sitemap_general_section'
			);
			add_settings_field(
				'xmlsf_news_post_type',
				__( 'Post types', 'xml-sitemap-feed' ),
				array( $this, 'post_type_field' ),
				'xmlsf_news_general',
				'news_sitemap_general_section'
			);

			global $wp_taxonomies;
			$news_post_type = isset( $this->options['post_type'] ) && ! empty( $this->options['post_type'] ) ? (array) $this->options['post_type'] : array( 'post' );
			$post_types     = ( isset( $wp_taxonomies['category'] ) ) ? $wp_taxonomies['category']->object_type : array();

			foreach ( $news_post_type as $post_type ) {
				if ( in_array( $post_type, $post_types ) ) {
					add_settings_field( 'xmlsf_news_categories', translate( 'Categories' ), array( $this, 'categories_field' ), 'xmlsf_news_general', 'news_sitemap_general_section' );
					break;
				}
			}

			// Source labels - deprecated.
			add_settings_field(
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
	public function register_settings() {
		register_setting(
			'xmlsf_news_general',
			'xmlsf_news_tags',
			array( 'XMLSF_Admin_Sitemap_News_Sanitize', 'news_tags_settings' )
		);
	}

	/**
	 * Advanced section intro
	 *
	 * @param string $active_tab Active tab.
	 */
	public function section_advanced_intro( $active_tab = '' ) {
		if ( 'advanced' === $active_tab ) {
			include XMLSF_DIR . '/views/admin/section-advanced-intro.php';
		}
	}

	/**
	 * Help tab
	 */
	public function help_tab() {
		$screen     = get_current_screen();
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-news.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = ob_get_clean();

		$screen->add_help_tab(
			array(
				'id'      => 'sitemap-news-settings',
				'title'   => __( 'Google News Sitemap', 'xml-sitemap-feed' ),
				'content' => $content,
			)
		);

		switch ( $active_tab ) {
			case 'general':
				// Publication name.
				ob_start();
				include XMLSF_DIR . '/views/admin/help-tab-news-name.php';
				include XMLSF_DIR . '/views/admin/help-tab-support.php';
				$content = ob_get_clean();
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-news-name',
						'title'   => __( 'Publication name', 'xml-sitemap-feed' ),
						'content' => $content,
					)
				);
				// Categories.
				ob_start();
				include XMLSF_DIR . '/views/admin/help-tab-news-categories.php';
				include XMLSF_DIR . '/views/admin/help-tab-support.php';
				$content = ob_get_clean();
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-news-categories',
						'title'   => translate( 'Categories' ),
						'content' => $content,
					)
				);
				// Source labels.
				ob_start();
				include XMLSF_DIR . '/views/admin/help-tab-news-labels.php';
				include XMLSF_DIR . '/views/admin/help-tab-support.php';
				$content = ob_get_clean();
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-news-labels',
						'title'   => __( 'Content labels', 'xml-sitemap-feed' ),
						'content' => $content,
					)
				);
				break;

			case 'advanced':
				// Keywords.
				ob_start();
				include XMLSF_DIR . '/views/admin/help-tab-news-keywords.php';
				include XMLSF_DIR . '/views/admin/help-tab-support.php';
				$content = ob_get_clean();
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-news-keywords',
						'title'   => __( 'Keywords', 'xml-sitemap-feed' ),
						'content' => $content,
					)
				);
				// Stokc tickers.
				ob_start();
				include XMLSF_DIR . '/views/admin/help-tab-news-stocktickers.php';
				include XMLSF_DIR . '/views/admin/help-tab-support.php';
				$content = ob_get_clean();
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-news-stocktickers',
						'title'   => __( 'Stock tickers', 'xml-sitemap-feed' ),
						'content' => $content,
					)
				);
		}

		// Hook for additional/advanced help tab.
		do_action( 'xmlsf_news_help_tabs', $screen, $active_tab );

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-news-sidebar.php';
		$content = ob_get_clean();

		$screen->set_help_sidebar( $content );
	}

	/**
	 * News source name field
	 */
	public function name_field() {
		$name = ! empty( $this->options['name'] ) ? $this->options['name'] : '';

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-news-name.php';
	}

	/**
	 * Post type field
	 */
	public function post_type_field() {
		global $wp_taxonomies;

		$post_types = apply_filters(
			'xmlsf_news_post_types',
			get_post_types(
				array(
					'public'       => true,
					'hierarchical' => false,
				)
				/*,'objects'*/
			)
		);

		// Make sure post types are allowed and publicly viewable.
		$post_types = array_diff( $post_types, xmlsf()->disabled_post_types() );
		$post_types = array_filter( $post_types, 'is_post_type_viewable' );

		if ( ! is_array( $post_types ) || empty( $post_types ) ) {
			// This should never happen.
			echo '<p class="description warning">' . esc_html__( 'There appear to be no post types available.', 'xml-sitemap-feed' ) . '</p>';
			return;
		}

		$news_post_type = isset( $this->options['post_type'] ) && ! empty( $this->options['post_type'] ) ? (array) $this->options['post_type'] : array( 'post' );
		$type           = apply_filters( 'xmlsf_news_post_type_field_type', 1 === count( $news_post_type ) ? 'radio' : 'checkbox' );
		$allowed        = ( ! empty( $this->options['categories'] ) && isset( $wp_taxonomies['category'] ) ) ? $wp_taxonomies['category']->object_type : $post_types;
		$do_warning     = ( ! empty( $this->options['categories'] ) && count( $post_types ) > 1 ) ? true : false;

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-news-post-type.php';
	}

	/**
	 * Categories field
	 */
	public function categories_field() {
		$selected_categories = isset( $this->options['categories'] ) && is_array( $this->options['categories'] ) ? $this->options['categories'] : array();

		if ( function_exists( 'pll_languages_list' ) ) {
			add_filter(
				'get_terms_args',
				function ( $args ) {
					$args['lang'] = implode( ', ', pll_languages_list() );
					return $args;
				}
			);
		}

		global $sitepress;
		if ( $sitepress ) {
			remove_filter( 'get_terms_args', array( $sitepress, 'get_terms_args_filter' ) );
			remove_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1 );
			remove_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ) );
		}

		$cat_list = str_replace(
			'name="post_category[]"',
			'name="xmlsf_news_tags[categories][]"',
			wp_terms_checklist(
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
}

new XMLSF_Admin_Sitemap_News();
