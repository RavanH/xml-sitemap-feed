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
	private $sitemaps = array();

	/**
	 * Static files conflicting with this plugin
	 *
	 * @var array
	 */
	public static $static_files = null;

	/**
	 * Dismissed notices array
	 *
	 * @var array
	 */
	public static $dismissed = array();

	/**
	 * Minimal compatible pro version
	 *
	 * @var float
	 */
	public static $compat_pro_min = '1.2';

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		require XMLSF_DIR . '/inc/class-xmlsf-admin-sanitize.php';

		$this->sitemaps = (array) get_option( 'xmlsf_sitemaps', array() );

		if ( isset( $this->sitemaps['sitemap'] ) ) {
			require XMLSF_DIR . '/inc/class-xmlsf-admin-sitemap-sanitize.php';
			require XMLSF_DIR . '/inc/class-xmlsf-admin-sitemap.php';
		}

		if ( isset( $this->sitemaps['sitemap-news'] ) ) {
			require XMLSF_DIR . '/inc/class-xmlsf-admin-sitemap-news-sanitize.php';
			require XMLSF_DIR . '/inc/class-xmlsf-admin-sitemap-news.php';
		}

		// ACTION LINK.
		add_filter( 'plugin_action_links_' . XMLSF_BASENAME, array( $this, 'add_action_link' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_meta_links' ), 10, 2 );

		// REGISTER SETTINGS.
		add_action( 'admin_init', array( $this, 'register_settings' ), 0 );

		// ACTIONS & CHECKS.
		add_action( 'admin_init', array( $this, 'notices_actions' ) );
		add_action( 'admin_init', array( $this, 'tools_actions' ) );
		add_action( 'admin_init', array( $this, 'static_files' ) );
		add_action( 'admin_init', array( $this, 'check_conflicts' ), 11 );
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
		if ( isset( $this->sitemaps['sitemap'] ) || isset( $this->sitemaps['sitemap-news'] ) ) {
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
	 *
	 * @param string $sitemap Sitemap.
	 */
	public function clear_settings( $sitemap = '' ) {
		$defaults = ( 'sitemap-news' === $sitemap ) ? array( 'news_tags' => xmlsf()->default_news_tags ) : xmlsf()->defaults();

		unset( $defaults['sitemaps'] );

		foreach ( $defaults as $option => $settings ) {
			update_option( 'xmlsf_' . $option, $settings );
		}

		add_settings_error(
			'notice_clear_settings',
			'notice_clear_settings',
			__( 'Settings reset to the plugin defaults.', 'xml-sitemap-feed' ),
			'updated'
		);
	}

	/**
	 * Delete static sitemap files
	 *
	 * @param array $files Array of file names to delete.
	 */
	public function delete_static_files( $files ) {
		$allowed_files = array(
			'wp-sitemap.xml',
			'sitemap.xml',
			'sitemap-news.xml',
			'robots.txt',
		);

		if ( null === self::$static_files ) {
			self::$static_files = get_transient( 'xmlsf_static_files' );
			delete_transient( 'xmlsf_static_files' );
		}

		foreach ( $files as $name ) {
			if ( ! in_array( $name, $allowed_files ) ) {
				unset( self::$static_files[ $name ] );
				add_settings_error(
					'static_files',
					'file_not_allowed',
					sprintf( /* Translators: static file name */ __( 'File %s not in the list of allowed files!', 'xml-sitemap-feed' ), '<em>' . $name . '</em>' )
				);
				continue;
			}
			if ( ! isset( self::$static_files[ $name ] ) ) {
				// do nothing and be quiet about it...
				continue;
			}
			if ( wp_delete_file( self::$static_files[ $name ] ) ) {
				unset( self::$static_files[ $name ] );
				add_settings_error(
					'static_files',
					'file_deleted_' . $name,
					sprintf( /* Translators: static file name */ __( 'Static file %s succesfully deleted.', 'xml-sitemap-feed' ), '<em>' . $name . '</em>' ),
					'updated'
				);
			} else {
				add_settings_error(
					'static_files',
					'file_failed_' . $name,
					sprintf(
						/* Translators: static file name */
						__( 'Static file %s deletion failed.', 'xml-sitemap-feed' ),
						'<em>' . $name . '</em>'
					) . ' ' . sprintf(
						/* Translators: static file full path and name */
						__( 'This is probably due to insufficient rights. Please try to remove %s manually via FTP or your hosting provider control panel.', 'xml-sitemap-feed' ),
						self::$static_files[ $name ]
					)
				);
			}
		}

		$this->check_static_files();
	}

	/**
	 * Check for static sitemap files
	 */
	public function static_files() {
		if ( ( is_multisite() && ! is_super_admin() ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( null === self::$static_files ) {
			self::$static_files = get_transient( 'xmlsf_static_files' );
		}

		if ( ! empty( self::$static_files ) && ! in_array( 'static_files', self::$dismissed ) ) {
			add_action(
				'admin_notices',
				function () {
					include XMLSF_DIR . '/views/admin/notice-static-files.php';
				}
			);
		}
	}

	/**
	 * Check for static sitemap files
	 */
	public function check_static_files() {
		$home_path = trailingslashit( get_home_path() );
		self::$static_files = array();

		// Add activated sitemaps.
		$check_for = $this->sitemaps;

		// Add robots.txt file.
		if ( get_option( 'xmlsf_robots' ) ) {
			$check_for['robots'] = 'robots.txt';
		}

		// When core sitemap server is used.
		$settings = (array) get_option( 'xmlsf_general_settings', array() );
		if ( ! empty( $check_for['sitemap'] ) && ! empty( $settings['server'] ) && 'core' === $settings['server'] ) {
			$check_for['sitemap'] = 'wp-sitemap.xml';
		}

		foreach ( $check_for as $name => $pretty ) {
			if ( ! empty( $pretty ) && file_exists( $home_path . $pretty ) ) {
				self::$static_files[ $pretty ] = $home_path . $pretty;
			}
		}

		if ( ! empty( self::$static_files ) ) {
			set_transient( 'xmlsf_static_files', self::$static_files );
		} else {
			delete_transient( 'xmlsf_static_files' );
		}
	}

	/**
	 * Check for conflicting themes and their settings
	 */
	public function check_conflicts() {
		// Google News Advanced incompatibility notice.
		if ( is_plugin_active( 'xml-sitemap-feed-advanced-news/xml-sitemap-advanced-news.php' ) ) {
			// Check version.
			if ( ! in_array( 'xmlsf_advanced_news', self::$dismissed ) ) {
				if (
					! defined( 'XMLSF_NEWS_ADV_VERSION' ) ||
					version_compare( XMLSF_NEWS_ADV_VERSION, self::$compat_pro_min, '<' )
				) {
					add_action(
						'admin_notices',
						function () {
							include XMLSF_DIR . '/views/admin/notice-xmlsf-advanced-news.php';
						}
					);
				}
			}
		}

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
	 * Tools actions
	 */
	public function tools_actions() {
		if ( ! isset( $_POST['_xmlsf_help_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_xmlsf_help_nonce'] ), XMLSF_BASENAME . '-help' ) ) {
			return;
		}

		if ( isset( $_POST['xmlsf-clear-settings-submit'] ) && isset( $_POST['xmlsf-clear-settings'] ) ) {
			$this->clear_settings( sanitize_key( $_POST['xmlsf-clear-settings'] ) );
		}

		if ( isset( $_POST['xmlsf-check-conflicts'] ) ) {
			// Reset ignored warnings.
			delete_user_meta( get_current_user_id(), 'xmlsf_dismissed' );
			self::$dismissed = array();

			$this->check_static_files();
			if ( empty( self::$static_files ) ) {
				add_settings_error(
					'static_files_notice',
					'static_files',
					__( 'No conflicting static files found.', 'xml-sitemap-feed' ),
					'notice-info'
				);
			}
		}

		if ( isset( $_POST['xmlsf-flush-rewrite-rules'] ) ) {
			// Flush rewrite rules.
			flush_rewrite_rules();
			add_settings_error(
				'flush_admin_notice',
				'flush_admin_notice',
				__( 'WordPress rewrite rules have been flushed.', 'xml-sitemap-feed' ),
				'updated'
			);
		}

		if ( isset( $_POST['xmlsf-clear-term-meta'] ) ) {
			// Remove terms metadata.
			global $wpdb;
			$wpdb->delete(
				$wpdb->prefix . 'termmeta',
				array( 'meta_key' => 'term_modified' )
			);
			add_settings_error(
				'clear_meta_notice',
				'clear_meta_notice',
				__( 'Sitemap term meta cache has been cleared.', 'xml-sitemap-feed' ),
				'updated'
			);
		}

		if ( isset( $_POST['xmlsf-clear-post-meta'] ) ) {
			// TODO replace this with delete_metadata( string $meta_type, int $object_id, string $meta_key, mixed $meta_value = ”, bool $delete_all = false )
			// with $delete_all set to true...

			// Remove metadata.
			global $wpdb;

			// Images meta.
			$wpdb->delete(
				$wpdb->prefix . 'postmeta',
				array( 'meta_key' => '_xmlsf_image_attached' )
			);
			$wpdb->delete(
				$wpdb->prefix . 'postmeta',
				array( 'meta_key' => '_xmlsf_image_featured' )
			);
			update_option( 'xmlsf_images_meta_primed', array() );

			// Comments meta.
			$wpdb->delete(
				$wpdb->prefix . 'postmeta',
				array( 'meta_key' => '_xmlsf_comment_date_gmt' )
			);
			update_option( 'xmlsf_comments_meta_primed', array() );

			add_settings_error(
				'clear_meta_notice',
				'clear_meta_notice',
				__( 'Sitemap post meta caches have been cleared.', 'xml-sitemap-feed' ),
				'updated'
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

		self::$dismissed = (array) get_user_meta( get_current_user_id(), 'xmlsf_dismissed' );

		if ( isset( $_POST['xmlsf-delete-submit'] ) ) {
			if ( isset( $_POST['xmlsf-dismiss'] ) ) {
				// Store user notice dismissal.
				$dismissed = sanitize_key( $_POST['xmlsf-dismiss'] );
				add_user_meta(
					get_current_user_id(),
					'xmlsf_dismissed',
					$dismissed,
					false
				);
				self::$dismissed[] = $dismissed;
			} else {
				$files = ( isset( $_POST['xmlsf-delete'] ) ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['xmlsf-delete'] ) ) : array();
				if ( empty( $files ) ) {
					add_settings_error(
						'static_files',
						'none_selected',
						__( 'No files selected for deletion!', 'xml-sitemap-feed' ),
						'notice-warning'
					);
				} else {
					// Delete selected files.
					$this->delete_static_files( $files );
				}
			}
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
