<?php
/**
 * XMLSF Admin CLASS
 *
 * @package XML Sitemap & Google News
 */

/**
 * XMLSF Admin CLASS
 */
class XMLSF_Admin {
	/**
	 * Sitemaps settings
	 *
	 * @var array
	 */
	private $settings = array();

	/**
	 * Potentially conflicting files
	 *
	 * @var array
	 */
	protected $conflicting_files = array();

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {

		$this->settings = (array) get_option( 'xmlsf_sitemaps', array() );

		if ( isset( $this->settings['sitemap'] ) ) {
			require XMLSF_DIR . '/inc/class-xmlsf-admin-sitemap-sanitize.php';
			require XMLSF_DIR . '/inc/class-xmlsf-admin-sitemap.php';
		}

		if ( isset( $this->settings['sitemap-news'] ) ) {
			require XMLSF_DIR . '/inc/class-xmlsf-admin-sitemap-news-sanitize.php';
			require XMLSF_DIR . '/inc/class-xmlsf-admin-sitemap-news.php';
		}

		// ACTION LINK.
		add_filter( 'plugin_action_links_' . XMLSF_BASENAME, array( $this, 'add_action_link' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_meta_links' ), 10, 2 );

		// REGISTER SETTINGS.
		add_action( 'admin_init', array( $this, 'register_settings' ), 0 );

		// ACTIONS & CHECKS.
		add_action( 'admin_init', array( $this, 'tools_actions' ), 9 );
		add_action( 'admin_init', array( $this, 'notices_actions' ), 9 );
		add_action( 'admin_init', array( $this, 'check_conflicts' ), 11 );

		// Shared Admin pages sidebar actions.
		add_action( 'xmlsf_admin_sidebar', array( $this, 'admin_sidebar_help' ) );
		add_action( 'xmlsf_admin_sidebar', array( $this, 'admin_sidebar_contribute' ), 20 );

		require XMLSF_DIR . '/inc/class-xmlsf-admin-sanitize.php';
	}

	/**
	 * SETTINGS
	 */

	/**
	 * Register settings and add settings fields
	 */
	public function register_settings() {
		global $wp_rewrite;

		// Sitemaps.
		register_setting(
			'reading',
			'xmlsf_sitemaps',
			array( 'XMLSF_Admin_Sanitize', 'sitemaps_settings' )
		);
		add_settings_field(
			'xmlsf_sitemaps',
			__( 'Enable XML sitemaps', 'xml-sitemap-feed' ),
			array( $this, 'sitemaps_settings_field' ),
			'reading'
		);

		// Custom domains, only when any sitemap is active.
		if ( isset( $this->settings['sitemap'] ) || isset( $this->settings['sitemap-news'] ) ) {
			register_setting(
				'reading',
				'xmlsf_domains',
				array( 'XMLSF_Admin_Sanitize', 'domains_settings' )
			);
			add_settings_field(
				'xmlsf_domains',
				__( 'Allowed domains', 'xml-sitemap-feed' ),
				array( $this, 'domains_settings_field' ),
				'reading'
			);
		}

		// Help tab.
		add_action(
			'load-options-reading.php',
			array( $this, 'xml_sitemaps_help' )
		);

		// Robots rules, only when permalinks are set.
		$rules = get_option( 'rewrite_rules' );
		if ( $wp_rewrite->using_permalinks() && isset( $rules['robots\.txt$'] ) ) {
			register_setting(
				'reading',
				'xmlsf_robots',
				'sanitize_textarea_field'
			);
			add_settings_field(
				'xmlsf_robots',
				__( 'Additional robots.txt rules', 'xml-sitemap-feed' ),
				array( $this, 'robots_settings_field' ),
				'reading'
			);
		}
	}

	/**
	 * SITEMAPS
	 */

	/**
	 * Sitemaps help tabs
	 */
	public function xml_sitemaps_help() {
		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-sitemaps.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = ob_get_clean();

		get_current_screen()->add_help_tab(
			array(
				'id'       => 'sitemap-settings',
				'title'    => __( 'Enable XML sitemaps', 'xml-sitemap-feed' ),
				'content'  => $content,
				'priority' => 11,
			)
		);

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-allowed-domains.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = ob_get_clean();

		get_current_screen()->add_help_tab(
			array(
				'id'       => 'allowed-domains',
				'title'    => __( 'Allowed domains', 'xml-sitemap-feed' ),
				'content'  => $content,
				'priority' => 11,
			)
		);

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-robots.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = ob_get_clean();

		get_current_screen()->add_help_tab(
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
	public function sitemaps_settings_field() {
		if ( get_option( 'blog_public' ) ) {
			// The actual fields for data entry.
			include XMLSF_DIR . '/views/admin/field-sitemaps.php';
		} else {
			esc_html_e( 'XML Sitemaps are not available because of your site&#8217;s visibility settings (above).', 'xml-sitemap-feed' );
		}
	}

	/**
	 * Domain settings field
	 */
	public function domains_settings_field() {
		$domains = get_option( 'xmlsf_domains' );

		if ( ! is_array( $domains ) ) {
			$domains = array();
		}

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-domains.php';
	}

	/**
	 * ROBOTS
	 */
	public function robots_settings_field() {
		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-robots.php';
	}

	/**
	 * Clear settings
	 */
	public function clear_settings() {
		// TODO reset Settings > Reading options here...

		add_settings_error(
			'notice_clear_settings',
			'notice_clear_settings',
			__( 'Settings reset to the plugin defaults.', 'xml-sitemap-feed' ),
			'updated'
		);
	}

	/**
	 * Check for static sitemap files
	 *
	 * @param bool $found_only Whether to give feedback when no files are found.
	 */
	public function check_static_files( $found_only = false ) {

		$home_path = trailingslashit( get_home_path() );

		foreach ( (array) $this->conflicting_files as $pretty ) {
			if ( ! empty( $pretty ) && file_exists( $home_path . $pretty ) ) {
				$found = true;
				add_settings_error(
					'static_files_notice',
					'static_files',
					sprintf( /* translators: %1$s file name, %2$s is XML Sitemap (linked to options-reading.php) */
						esc_html__( 'A conflicting static file has been found: %1$s. Either delete it or disable the corresponding %2$s.', 'xml-sitemap-feed' ),
						esc_html( $pretty ),
						'<a href="' . esc_url( admin_url( 'options-reading.php' ) ) . '#xmlsf_sitemaps">' . esc_html__( 'XML Sitemap', 'xml-sitemap-feed' ) . '</a>'
					),
					'warning'
				);
			}
		}

		// Tell me all is OK.
		isset( $found ) || $found_only || add_settings_error(
			'static_files_notice',
			'static_files',
			__( 'No conflicting static files found.', 'xml-sitemap-feed' ),
			'success'
		);
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
	 * Admin sidbar help section
	 */
	public function admin_sidebar_help() {
		include XMLSF_DIR . '/views/admin/sidebar-help.php';
	}

	/**
	 * Admin sidbar contribute section
	 */
	public function admin_sidebar_contribute() {
		include XMLSF_DIR . '/views/admin/sidebar-contribute.php';
	}

	/**
	 * Tools actions
	 */
	public function tools_actions() {
		if ( ! isset( $_POST['_xmlsf_help_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_xmlsf_help_nonce'] ), XMLSF_BASENAME . '-help' ) ) {
			return;
		}

		// TODO
		if ( isset( $_POST['xmlsf-clear-settings-general'] ) ) {
			$this->clear_settings();
		}

		if ( isset( $_POST['xmlsf-flush-rewrite-rules'] ) ) {
			// Flush rewrite rules.
			flush_rewrite_rules();
			add_settings_error(
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
	public function notices_actions() {
		if ( ! isset( $_POST['_xmlsf_notice_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_xmlsf_notice_nonce'] ), XMLSF_BASENAME . '-notice' ) ) {
			return;
		}

		if ( isset( $_POST['xmlsf-dismiss'] ) ) {
			// Store user notice dismissal.
			$dismissed = sanitize_key( $_POST['xmlsf-dismiss'] );
			add_user_meta(
				get_current_user_id(),
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
	public function add_action_link( $links ) {
		$settings_link = '<a href="' . admin_url( 'options-reading.php' ) . '#xmlsf_sitemaps">' . translate( 'Settings' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Add plugin meta links
	 *
	 * @param array  $links Array of links.
	 * @param string $file Plugin file name.
	 */
	public function plugin_meta_links( $links, $file ) {
		if ( XMLSF_BASENAME === $file ) {
			$links[] = '<a target="_blank" href="https://wordpress.org/support/plugin/xml-sitemap-feed/">' . __( 'Support', 'xml-sitemap-feed' ) . '</a>';
			$links[] = '<a target="_blank" href="https://wordpress.org/support/plugin/xml-sitemap-feed/reviews/?filter=5#new-post">' . __( 'Rate ★★★★★', 'xml-sitemap-feed' ) . '</a>';
		}
		return $links;
	}
}
