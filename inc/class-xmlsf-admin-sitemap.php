<?php
/**
 * Admin for Sitemap
 *
 * @package XML Sitemap & Google News
 */

/**
 * Admin Sitemap Class
 */
class XMLSF_Admin_Sitemap {
	/**
	 * Holds the values to be used in the fields callbacks
	 *
	 * @var array $screen_id Admin screen id array.
	 */
	private $screen_id;

	/**
	 * Start up
	 */
	public function __construct() {
		// SETTINGS.
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_metadata' ) );

		// ACTIONS & CHECKS.
		add_action( 'admin_init', array( $this, 'tools_actions' ), 9 );
		add_action( 'admin_init', array( $this, 'check_conflicts' ), 11 );
		add_action( 'update_option_xmlsf_server', array( $this, 'update_server' ), 10, 2 );
		add_action( 'update_option_xmlsf_disabled_providers', array( $this, 'update_disabled_providers' ), 10, 2 );
		add_action( 'update_option_xmlsf_post_types', array( $this, 'update_post_types' ), 10, 2 );

		// Placeholders for advanced options.
		add_action( 'xmlsf_posttype_archive_field_options', array( 'XMLSF_Admin_Sitemap_Fields', 'advanced_archive_field_options' ) );
	}

	/**
	 * Update actions for General Settings
	 *
	 * @param mixed $old   Old option value.
	 * @param mixed $value Saved option value.
	 */
	public function update_server( $old, $value ) {

		if ( $old !== $value ) {
			global $xmlsf_sitemap;

			// Check static file.
			$filename = is_object( $xmlsf_sitemap ) ? $xmlsf_sitemap->index() : apply_filters( 'xmlsf_sitemap_filename', ( 'core' === $value ) ? 'wp-sitemap.xml' : 'sitemap.xml' );
			xmlsf_admin()->check_static_files( $filename, 1 );

			// Flush rewrite rules on next init.
			delete_option( 'rewrite_rules' );
		}
	}

	/**
	 * Update actions for General Settings
	 *
	 * @param mixed $old   Old option value.
	 * @param mixed $value Saved option value.
	 */
	public function update_disabled_providers( $old, $value ) {

		if ( $old === $value ) {
			return;
		}

		// When taxonomies have been disabled...
		if ( in_array( 'taxonomies', (array) $value, true ) && ! in_array( 'taxonomies', (array) $old, true ) ) {
			xmlsf_clear_metacache( 'terms' );
		}

		// TODO Clear user meta cache if deactivating...
	}


	/**
	 * Update actions for Post Types setting
	 *
	 * @param mixed $old   Old option value.
	 * @param mixed $value Saved option value.
	 */
	public function update_post_types( $old, $value ) {
		$old            = (array) $old;
		$clear_images   = false;
		$clear_comments = false;
		foreach ( (array) $value as $post_type => $settings ) {
			// Poll for changes that warrant clearing meta data.
			if ( isset( $old[ $post_type ] ) && is_array( $old[ $post_type ] ) ) {

				if ( empty( $settings['active'] ) ) {
					if ( ! empty( $old[ $post_type ]['active'] ) ) {
						$clear_images   = true;
						$clear_comments = true;
					}
				} else {
					if ( isset( $old[ $post_type ]['tags'] ) && is_array( $old[ $post_type ]['tags'] ) && isset( $old[ $post_type ]['tags']['image'] ) && $old[ $post_type ]['tags']['image'] !== $settings['tags']['image'] ) {
						$clear_images = true;
					}
					if ( ! empty( $old[ $post_type ]['update_lastmod_on_comments'] ) && empty( $settings['update_lastmod_on_comments'] ) ) {
						$clear_comments = true;
					}
				}
			}
		}

		// Clear images meta caches...
		if ( $clear_images ) {
			xmlsf_clear_metacache( 'images' );
		}

		// Clear comments meta caches...
		if ( $clear_comments ) {
			xmlsf_clear_metacache( 'comments' );
		}
	}

	/**
	 * Clear settings
	 */
	public function clear_settings() {
		$defaults = xmlsf()->defaults();

		unset( $defaults['sitemaps'] );

		foreach ( $defaults as $option => $settings ) {
			update_option( 'xmlsf_' . $option, $settings );
		}
	}

	/**
	 * Tools actions
	 */
	public function tools_actions() {
		if ( ! isset( $_POST['_xmlsf_help_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_xmlsf_help_nonce'] ), XMLSF_BASENAME . '-help' ) ) {
			return;
		}

		if ( isset( $_POST['xmlsf-check-conflicts-sitemap'] ) ) {
			// Reset ignored warnings.
			delete_user_meta( get_current_user_id(), 'xmlsf_dismissed' );

			// When core sitemap server is used.
			global $xmlsf_sitemap;
			if ( is_object( $xmlsf_sitemap ) ) {
				xmlsf_admin()->check_static_files( $xmlsf_sitemap->index() );
			}
		}

		if ( isset( $_POST['xmlsf-clear-settings-sitemap'] ) ) {
			$this->clear_settings();
			add_settings_error(
				'notice_clear_settings',
				'notice_clear_settings',
				__( 'Settings reset to the plugin defaults.', 'xml-sitemap-feed' ),
				'updated'
			);
		}

		if ( isset( $_POST['xmlsf-clear-term-meta'] ) ) {
			// Remove terms metadata.
			xmlsf_clear_metacache( 'terms' );

			add_settings_error(
				'clear_meta_notice',
				'clear_meta_notice',
				__( 'Sitemap term meta cache has been cleared.', 'xml-sitemap-feed' ),
				'success'
			);
		}

		if ( isset( $_POST['xmlsf-clear-user-meta'] ) ) {
			// Remove terms metadata.
			xmlsf_clear_metacache( 'users' );

			add_settings_error(
				'clear_meta_notice',
				'clear_meta_notice',
				__( 'Sitemap author meta cache has been cleared.', 'xml-sitemap-feed' ),
				'success'
			);
		}

		if ( isset( $_POST['xmlsf-clear-post-meta'] ) ) {
			// Remove metadata.
			xmlsf_clear_metacache( 'images' );
			xmlsf_clear_metacache( 'comments' );

			add_settings_error(
				'clear_meta_notice',
				'clear_meta_notice',
				__( 'Sitemap post meta caches have been cleared.', 'xml-sitemap-feed' ),
				'updated'
			);
		}
	}

	/**
	 * Check for conflicting plugins and their settings
	 */
	public function check_conflicts() {
		if ( wp_doing_ajax() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// TODO:
		// Google (XML) Sitemaps Generator Plugin for WordPress and Google News Sitemap incompatibility.

		// WP SEO conflict notices.
		if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			// check date archive redirection.
			if ( 'plugin' === get_option( 'xmlsf_server' ) ) {
				$wpseo_titles = get_option( 'wpseo_titles' );
				if ( ! empty( $wpseo_titles['disable-date'] ) ) {
					// check if Split by option is set anywhere.
					foreach ( (array) get_option( 'xmlsf_post_types', array() ) as $type => $settings ) {
						if ( ! empty( $settings['active'] ) && ! empty( $settings['archive'] ) ) {
							add_action(
								'admin_notices',
								function () {
									include XMLSF_DIR . '/views/admin/notice-wpseo-date-redirect.php';
								}
							);
							break;
						}
					}
				}
			}

			// check wpseo sitemap option.
			if ( ! in_array( 'wpseo_sitemap', (array) get_user_meta( get_current_user_id(), 'xmlsf_dismissed' ), true ) ) {
				$wpseo = get_option( 'wpseo' );
				if ( ! empty( $wpseo['enable_xml_sitemap'] ) ) {
					add_action(
						'admin_notices',
						function () {
							include XMLSF_DIR . '/views/admin/notice-wpseo-sitemap.php';
						}
					);
				}
			}
		}

		// SEOPress conflict notices.
		if ( is_plugin_active( 'wp-seopress/seopress.php' ) ) {

			// check date archive redirection.
			$seopress_toggle = get_option( 'seopress_toggle' );

			if ( 'plugin' === get_option( 'xmlsf_server' ) ) {
				$seopress_titles = get_option( 'seopress_titles_option_name' );
				if ( ! empty( $seopress_toggle['toggle-titles'] ) && ! empty( $seopress_titles['seopress_titles_archives_date_disable'] ) ) {
					// check if Split by option is set anywhere.
					foreach ( (array) get_option( 'xmlsf_post_types', array() ) as $type => $settings ) {
						if ( ! empty( $settings['active'] ) && ! empty( $settings['archive'] ) ) {
							add_action(
								'admin_notices',
								function () {
									include XMLSF_DIR . '/views/admin/notice-seopress-date-redirect.php';
								}
							);
							break;
						}
					}
				}
			}

			// check seopress sitemap option.
			if ( ! in_array( 'seopress_sitemap', (array) get_user_meta( get_current_user_id(), 'xmlsf_dismissed' ), true ) ) {
				$seopress_xml_sitemap = get_option( 'seopress_xml_sitemap_option_name' );
				if ( ! empty( $seopress_toggle['toggle-xml-sitemap'] ) && ! empty( $seopress_xml_sitemap['seopress_xml_sitemap_general_enable'] ) ) {
					add_action(
						'admin_notices',
						function () {
							include XMLSF_DIR . '/views/admin/notice-seopress-sitemap.php';
						}
					);
				}
			}
		}

		// Rank Math conflict notices.
		if ( is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ) {

			// check date archive redirection.
			if ( 'plugin' === get_option( 'xmlsf_server' ) ) {
				$rankmath_titles = get_option( 'rank-math-options-titles' );
				if ( ! empty( $rankmath_titles['disable_date_archives'] ) && 'on' === $rankmath_titles['disable_date_archives'] ) {
					// check if Split by option is set anywhere.
					foreach ( (array) get_option( 'xmlsf_post_types', array() ) as $type => $settings ) {
						if ( ! empty( $settings['active'] ) && ! empty( $settings['archive'] ) ) {
							add_action(
								'admin_notices',
								function () {
									include XMLSF_DIR . '/views/admin/notice-rankmath-date-redirect.php';
								}
							);
							break;
						}
					}
				}
			}

			// check rank math sitemap option.
			if ( ! in_array( 'rankmath_sitemap', (array) get_user_meta( get_current_user_id(), 'xmlsf_dismissed' ), true ) ) {
				$rankmath_modules = (array) get_option( 'rank_math_modules' );
				if ( in_array( 'sitemap', $rankmath_modules, true ) ) {
					add_action(
						'admin_notices',
						function () {
							include XMLSF_DIR . '/views/admin/notice-rankmath-sitemap.php';
						}
					);
				}
			}
		}

		// All in One SEO Pack conflict notices.
		if ( is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' ) && ! in_array( 'aioseop_sitemap', (array) get_user_meta( get_current_user_id(), 'xmlsf_dismissed' ), true ) ) {
			// check aioseop sitemap module.
			$aioseop_options = (array) get_option( 'aioseop_options' );

			if ( isset( $aioseop_options['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_sitemap'] ) && 'on' === $aioseop_options['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_sitemap'] ) {
				// sitemap module on.
				add_action(
					'admin_notices',
					function () {
						include XMLSF_DIR . '/views/admin/notice-aioseop-sitemap.php';
					}
				);
			}
		}

		// XML Sitemap Generator for Google conflict notices.
		if ( is_plugin_active( 'google-sitemap-generator/sitemap.php' ) && ! in_array( 'gsgenerator_sitemap', (array) get_user_meta( get_current_user_id(), 'xmlsf_dismissed' ), true ) ) {
			add_action(
				'admin_notices',
				function () {
					include XMLSF_DIR . '/views/admin/notice-google-sitemap-generator.php';
				}
			);
		}

		// SEO Framework conflict notices
		// autodescription-site-settings[sitemaps_output].
		if ( is_plugin_active( 'autodescription/autodescription.php' ) ) {
			// check sfw sitemap module.
			if ( ! in_array( 'seoframework_sitemap', (array) get_user_meta( get_current_user_id(), 'xmlsf_dismissed' ), true ) ) {
				$sfw_options = (array) get_option( 'autodescription-site-settings' );

				if ( ! empty( $sfw_options['sitemaps_output'] ) ) {
					// sitemap module on.
					add_action(
						'admin_notices',
						function () {
							include XMLSF_DIR . '/views/admin/notice-seoframework-sitemap.php';
						}
					);
				}
			}
		}
	}

	/**
	 * META BOXES
	 */

	/**
	 * Adds a XML Sitemap box to the side column
	 */
	public function add_meta_box() {
		$post_types = get_option( 'xmlsf_post_types' );
		if ( ! is_array( $post_types ) ) {
			return;
		}

		foreach ( $post_types as $post_type => $settings ) {
			// Only include metaboxes on post types that are included.
			if ( isset( $settings['active'] ) ) {
				add_meta_box(
					'xmlsf_section',
					__( 'XML Sitemap', 'xml-sitemap-feed' ),
					array( $this, 'meta_box' ),
					$post_type,
					'side',
					'low'
				);
			}
		}
	}

	/**
	 * Adds a XML Sitemap box to the side column
	 *
	 * @param WP_Post $post Post object.
	 */
	public function meta_box( $post ) {
		// Use nonce for verification.
		wp_nonce_field( XMLSF_BASENAME, '_xmlsf_nonce' );

		// Use get_post_meta to retrieve an existing value from the database and use the value for the form.
		$exclude  = get_post_meta( $post->ID, '_xmlsf_exclude', true );
		$priority = get_post_meta( $post->ID, '_xmlsf_priority', true );
		$disabled = false;

		// value prechecks to prevent "invalid form control not focusable" when meta box is hidden.
		$priority = is_numeric( $priority ) ? xmlsf_sanitize_number( $priority ) : '';

		// disable options and (visibly) set excluded to true for private posts.
		if ( 'private' === $post->post_status ) {
			$disabled = true;
			$exclude  = true;
		}

		// disable options and (visibly) set priority to 1 for front page.
		if ( (int) get_option( 'page_on_front' ) === $post->ID ) {
			$disabled = true;
			$exclude  = false;
			$priority = '1'; // force priority to 1 for front page.
		}

		$description = sprintf(
			/* translators: Settings admin menu name, XML Sitemap admin page name */
			esc_html__( 'Leave empty for automatic Priority as configured on %1$s > %2$s.', 'xml-sitemap-feed' ),
			esc_html( translate( 'Settings' ) ),
			'<a href="' . admin_url( 'options-general.php' ) . '?page=xmlsf">' . esc_html__( 'XML Sitemap', 'xml-sitemap-feed' ) . '</a>'
		);

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/meta-box.php';
	}

	/**
	 * When the post is saved, save our meta data
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_metadata( $post_id ) {
		if (
			// verify nonce.
			! isset( $_POST['_xmlsf_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_xmlsf_nonce'] ) ), XMLSF_BASENAME ) ||
			// user not allowed.
			! current_user_can( 'edit_post', $post_id )
		) {
			return;
		}

		// _xmlsf_priority
		if ( empty( $_POST['xmlsf_priority'] ) || ! is_numeric( $_POST['xmlsf_priority'] ) ) {
			delete_post_meta( $post_id, '_xmlsf_priority' );
		} else {
			update_post_meta( $post_id, '_xmlsf_priority', xmlsf_sanitize_number( sanitize_key( $_POST['xmlsf_priority'] ) ) );
		}

		// _xmlsf_exclude
		if ( empty( $_POST['xmlsf_exclude'] ) ) {
			delete_post_meta( $post_id, '_xmlsf_exclude' );
		} else {
			update_post_meta( $post_id, '_xmlsf_exclude', sanitize_key( $_POST['xmlsf_exclude'] ) );
		}
	}

	/**
	 * Add options page
	 */
	public function add_settings_page() {
		// This page will be under "Settings".
		$this->screen_id = add_options_page(
			__( 'XML Sitemap', 'xml-sitemap-feed' ),
			__( 'XML Sitemap', 'xml-sitemap-feed' ),
			'manage_options',
			'xmlsf',
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function settings_page() {
		/**
		 * ADD SECTIONS & FIELDS
		 */

		/** GENERAL */
		// Sections.
		add_settings_section(
			'general', // Section ID.
			'', // Title.
			'', // Intro callback.
			'xmlsf_general' // Page slug.
		);
		// Fields.
		add_settings_field(
			'server',
			translate( 'Server' ),
			array( 'XMLSF_Admin_Sitemap_Fields', 'server_field' ),
			'xmlsf_general',
			'general'
		);
		add_settings_field(
			'disabled_providers',
			translate( 'Deactivate' ),
			array( 'XMLSF_Admin_Sitemap_Fields', 'disable_fields' ),
			'xmlsf_general',
			'general'
		);

		/** POST TYPES */
		add_settings_section(
			'xml_sitemap_post_types_section',
			'',
			'',
			'xmlsf_post_types'
		);

		xmlsf_uses_core_server() && add_settings_field(
			'xmlsf_sitemap_post_types_limit',
			translate( 'General' ),
			array( 'XMLSF_Admin_Sitemap_Fields', 'post_types_general_fields' ),
			'xmlsf_post_types',
			'xml_sitemap_post_types_section'
		);

		$post_types = (array) apply_filters( 'xmlsf_post_types', get_post_types( array( 'public' => true ) ) );
		// Make sure post types are allowed and publicly viewable.
		$post_types = array_diff( $post_types, xmlsf()->disabled_post_types() );
		$post_types = array_filter( $post_types, 'is_post_type_viewable' );

		if ( is_array( $post_types ) && ! empty( $post_types ) ) :
			foreach ( $post_types as $post_type ) {
				$obj = get_post_type_object( $post_type );
				if ( ! is_object( $obj ) ) {
					continue;
				}
				add_settings_field(
					'xmlsf_post_type_' . $obj->name,
					$obj->label,
					array( 'XMLSF_Admin_Sitemap_Fields', 'post_type_fields' ),
					'xmlsf_post_types',
					'xml_sitemap_post_types_section',
					$post_type
				);
				// Note: (ab)using section name parameter to pass post type name.
			}
		endif;

		/** TAXONOMIES */
		add_settings_section(
			'xml_sitemap_taxonomies_section',
			/*'<a name="xmlsf"></a>'.__( 'XML Sitemap', 'xml-sitemap-feed' )*/ '',
			'',
			'xmlsf_taxonomies'
		);
		add_settings_field(
			'xmlsf_taxonomy_settings',
			translate( 'General' ),
			array( 'XMLSF_Admin_Sitemap_Fields', 'taxonomy_settings_field' ),
			'xmlsf_taxonomies',
			'xml_sitemap_taxonomies_section'
		);
		add_settings_field(
			'xmlsf_taxonomies',
			__( 'Taxonomies', 'xml-sitemap-feed' ),
			array( 'XMLSF_Admin_Sitemap_Fields', 'taxonomies_field' ),
			'xmlsf_taxonomies',
			'xml_sitemap_taxonomies_section'
		);

		/** AUTHORS */
		add_settings_section(
			'xml_sitemap_authors_section',
			/*'<a name="xmlsf"></a>'.__( 'XML Sitemap', 'xml-sitemap-feed' )*/ '',
			'',
			'xmlsf_authors'
		);
		add_settings_field(
			'xmlsf_author_settings',
			translate( 'General' ),
			array( 'XMLSF_Admin_Sitemap_Fields', 'author_settings_field' ),
			'xmlsf_authors',
			'xml_sitemap_authors_section'
		);
		add_settings_field(
			'xmlsf_authors',
			__( 'Authors', 'xml-sitemap-feed' ),
			array( 'XMLSF_Admin_Sitemap_Fields', 'authors_field' ),
			'xmlsf_authors',
			'xml_sitemap_authors_section'
		);

		/** ADVANCED */
		add_settings_section(
			'xml_sitemap_advanced_section',
			/*'<a name="xmlsf"></a>'.__( 'XML Sitemap', 'xml-sitemap-feed' )*/ '',
			'',
			'xmlsf_advanced'
		);
		// custom name.
		add_settings_field(
			'xmlsf_sitemap_name',
			'<label for="xmlsf_sitemap_name">' . __( 'XML Sitemap URL', 'xml-sitemap-feed' ) . '</label>',
			array( 'XMLSF_Admin_Sitemap_Fields', 'xmlsf_sitemap_name_field' ),
			'xmlsf_advanced',
			'xml_sitemap_advanced_section'
		);
		// custom urls.
		add_settings_field(
			'xmlsf_urls',
			__( 'External web pages', 'xml-sitemap-feed' ),
			array( 'XMLSF_Admin_Sitemap_Fields', 'urls_settings_field' ),
			'xmlsf_advanced',
			'xml_sitemap_advanced_section'
		);
		// custom sitemaps.
		add_settings_field(
			'xmlsf_custom_sitemaps',
			__( 'External XML Sitemaps', 'xml-sitemap-feed' ),
			array( 'XMLSF_Admin_Sitemap_Fields', 'custom_sitemaps_settings_field' ),
			'xmlsf_advanced',
			'xml_sitemap_advanced_section'
		);

		/**
		 * PREPARE VIEW
		 */

		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';

		do_action( 'xmlsf_add_settings', $active_tab );

		// prepare sitemap link url.
		$sitemap_url = xmlsf_sitemap_url();

		// Sidebar actions.
		add_action(
			'xmlsf_admin_sidebar',
			function () {
				include XMLSF_DIR . '/views/admin/sidebar-links.php';
			},
			9
		);

		$disabled = get_option( 'xmlsf_disabled_providers', xmlsf()->defaults( 'disabled_providers' ) );

		// The actual settings page.
		include XMLSF_DIR . '/views/admin/page-sitemap.php';
	}

	/**
	 * Register and add settings
	 */
	public function register_settings() {
		// Help tabs.
		add_action( 'load-' . $this->screen_id, array( $this, 'help_tabs' ) );

		// general.
		register_setting(
			'xmlsf_general',
			'xmlsf_server',
			array( 'XMLSF_Admin_Sitemap_Sanitize', 'server' )
		);
		register_setting(
			'xmlsf_general',
			'xmlsf_disabled_providers',
			array( 'XMLSF_Admin_Sitemap_Sanitize', 'disabled_providers' )
		);
		// post_types.
		register_setting(
			'xmlsf_post_types',
			'xmlsf_post_types',
			array( 'XMLSF_Admin_Sitemap_Sanitize', 'post_types' )
		);
		// taxonomies.
		register_setting(
			'xmlsf_taxonomies',
			'xmlsf_taxonomy_settings',
			array( 'XMLSF_Admin_Sitemap_Sanitize', 'taxonomy_settings' )
		);
		register_setting(
			'xmlsf_taxonomies',
			'xmlsf_taxonomies',
			array( 'XMLSF_Admin_Sitemap_Sanitize', 'taxonomies' )
		);
		// authors.
		register_setting(
			'xmlsf_authors',
			'xmlsf_author_settings',
			array( 'XMLSF_Admin_Sitemap_Sanitize', 'author_settings' )
		);
		register_setting(
			'xmlsf_authors',
			'xmlsf_authors',
			array( 'XMLSF_Admin_Sitemap_Sanitize', 'authors' )
		);
		// custom urls.
		register_setting(
			'xmlsf_advanced',
			'xmlsf_urls',
			array( 'XMLSF_Admin_Sitemap_Sanitize', 'custom_urls_settings' )
		);
		// custom sitemaps.
		register_setting(
			'xmlsf_advanced',
			'xmlsf_custom_sitemaps',
			array( 'XMLSF_Admin_Sitemap_Sanitize', 'custom_sitemaps_settings' )
		);
	}

	/**
	 * XML SITEMAP SECTION
	 */

	/**
	 * Help tabs
	 */
	public function help_tabs() {
		$screen     = get_current_screen();
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-sitemaps.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = ob_get_clean();

		$screen->add_help_tab(
			array(
				'id'      => 'sitemap-settings',
				'title'   => __( 'XML Sitemap', 'xml-sitemap-feed' ),
				'content' => $content,
			)
		);

		switch ( $active_tab ) {
			case 'general':
				// Server.
				$content  = '<p>' . esc_html__( 'Select your XML Sitemap generator here.', 'xml-sitemap-feed' ) . '</p>';
				$content .= '<p><strong>' . esc_html( translate( 'WordPress' ) ) . '</strong></p>';
				$content .= '<p>' . esc_html__( 'The default sitemap server is light-weight, effective and compatible with most installations. But it is also limited. The XML Sitemaps & Google News plugin adds some essential features and options to the default sitemap generator but if these are not enough, try the plugin sitemap server.', 'xml-sitemap-feed' ) . '</p>';
				$content .= '<p><strong>' . esc_html( translate( 'Plugin' ) ) . '</strong></p>';
				$content .= '<p>' . esc_html__( 'The plugin sitemap server generates the sitemap in a different way, allowing some additional features and configuration options. However, it is not guaranteed to be compatible with your specific WordPress installation.', 'xml-sitemap-feed' ) . '</p>';
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-general-server',
						'title'   => translate( 'Server' ),
						'content' => $content,
					)
				);
				// Disable.
				$content  = '<p>' . esc_html__( 'By default, all public content types, taxonomy archives and author archives are included in the XML Sitemap index. If you wish to exclude any content or archive types, you can disable them here.', 'xml-sitemap-feed' ) . '</p>';
				$content .= '<p>' . sprintf( /* translators: %1$s Taxonomies, %2$s Taxonomies linked to the respective tab */
					esc_html__( 'Select %1$s here to exclude then all taxonomy archives from the sitemap index. To exclude only a particular taxonomy, please go to the %2$s tab.', 'xml-sitemap-feed' ),
					'<strong>' . esc_html__( 'Taxonomies', 'xml-sitemap-feed' ) . '</strong>',
					'<a href="?page=xmlsf&tab=taxonomies">' . esc_html__( 'Taxonomies', 'xml-sitemap-feed' ) . '</a>'
				) . '</p>';
				$content .= '<p>' . sprintf( /* translators: %1$s Authors, %2$s Authors linked to the respective tab  */
					esc_html__( 'Select %1$s here to exclude all author archives from the sitemap index. To exclude only a particular author or user group, please go to the %2$s tab.', 'xml-sitemap-feed' ),
					'<strong>' . esc_html__( 'Authors', 'xml-sitemap-feed' ) . '</strong>',
					'<a href="?page=xmlsf&tab=authors">' . esc_html__( 'Authors', 'xml-sitemap-feed' ) . '</a>'
				) . '</p>';
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-general-disable',
						'title'   => translate( 'Deactivate' ),
						'content' => $content,
					)
				);
				break;

			case 'post_types':
				if ( xmlsf_uses_core_server() ) {
					ob_start();
					include XMLSF_DIR . '/views/admin/help-tab-post-types-general.php';
					$content = ob_get_clean();
					// General Settings.
					$screen->add_help_tab(
						array(
							'id'      => 'sitemap-settings-post-type-general',
							'title'   => translate( 'General' ),
							'content' => $content,
						)
					);
				}
				ob_start();
				include XMLSF_DIR . '/views/admin/help-tab-post-types.php';
				$content = ob_get_clean();
				// Post type options.
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-settings-post-types',
						'title'   => __( 'Post types', 'xml-sitemap-feed' ),
						'content' => $content,
					)
				);
				break;

			case 'taxonomies':
				// Settings.
				ob_start();
				include XMLSF_DIR . '/views/admin/help-tab-taxonomies.php';
				$content = ob_get_clean();
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-settings-taxonomies-general',
						'title'   => translate( 'General' ),
						'content' => $content,
					)
				);
				// Taxonomies.
				$content  = '<p><strong>' . esc_html__( 'Limit to these taxonomies:', 'xml-sitemap-feed' ) . '</strong></p>';
				$content .= '<p>' . esc_html__( 'Select the taxonomies to include in the sitemap index. Select none to automatically include all public taxonomies.', 'xml-sitemap-feed' ) . '</p>';
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-settings-taxonomies',
						'title'   => __( 'Taxonomies', 'xml-sitemap-feed' ),
						'content' => $content,
					)
				);
				break;

			case 'authors':
				ob_start();
				include XMLSF_DIR . '/views/admin/help-tab-authors.php';
				$content = ob_get_clean();
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-settings-authors-general',
						'title'   => translate( 'General' ),
						'content' => $content,
					)
				);
				// Authors.
				$content  = '<p><strong>' . esc_html__( 'Limit to these authors:', 'xml-sitemap-feed' ) . '</strong></p>';
				$content .= '<p>' . esc_html__( 'Select the authors to include in the author sitemap. Select none to automatically include all authors.', 'xml-sitemap-feed' ) . '</p>';
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-settings-authors',
						'title'   => __( 'Authors', 'xml-sitemap-feed' ),
						'content' => $content,
					)
				);
				break;

			case 'advanced':
				ob_start();
				include XMLSF_DIR . '/views/admin/help-tab-advanced.php';
				$content = ob_get_clean();
				// Add help tab.
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-settings-advanced',
						'title'   => translate( 'Advanced' ),
						'content' => $content,
					)
				);
				break;
		}

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-sidebar.php';
		$content = ob_get_clean();

		$screen->set_help_sidebar( $content );
	}
}
