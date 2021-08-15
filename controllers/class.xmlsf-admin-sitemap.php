<?php

class XMLSF_Admin_Sitemap extends XMLSF_Admin
{
	/**
	* Holds the values to be used in the fields callbacks
	*/
	private $screen_id;

	/**
	* Holds the public taxonomies array
	*/
	private $public_taxonomies;

	/**
	* Start up
	*/
	public function __construct()
	{
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'ping_sitemap' ) );
		add_action( 'admin_init', array( $this, 'check_conflicts' ), 11 );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_metadata' ) );

		// placeholders for advanced options
		add_action( 'xmlsf_posttype_archive_field_options', array( $this, 'advanced_archive_field_options' ) );
	}

	/**
	 * Ping sitemaps
	 */
	public function ping_sitemap()
	{
		if ( ! isset( $_POST['xmlsf-ping-sitemap'] ) || ! xmlsf_verify_nonce('help') )
			return;

		$sitemaps = get_option( 'xmlsf_sitemaps' );

		foreach ( array('google','bing') as $se ) {
			$result = xmlsf_ping( $se, $sitemaps['sitemap'], HOUR_IN_SECONDS );

			$se_name = 'google' == $se ? __('Google','xml-sitemap-feed') : __('Bing & Yahoo','xml-sitemap-feed');

			switch( $result ) {
				case 200:
				$msg = sprintf( /* Translators: Search engine / Service name */ __( 'Pinged %s with success.', 'xml-sitemap-feed' ), $se_name );
				$type = 'updated';
				break;

				case 999:
				$msg = sprintf( /* Translators: Search engine / Service name, interval number */ __( 'Ping %s skipped: Sitemap already sent within the last %d minutes.', 'xml-sitemap-feed' ), $se_name, 60 );
				$type = 'notice-warning';
				break;

				case '':
				$msg = sprintf( translate('Oops: %s'), translate('Something went wrong.') );
				$type = 'error';
				break;

				default:
				$msg = sprintf( /* Translators: Search engine / Service name, response code number */ __( 'Ping %s failed with response code: %d', 'xml-sitemap-feed' ), $se_name, $result );
				$type = 'error';
			}

			add_settings_error( 'ping_sitemap', 'ping_sitemap', $msg, $type );
		}

	}

	/**
	 * Check for conflicting plugins and their settings
	 */
	public function check_conflicts()
	{
	if ( wp_doing_ajax() || ! current_user_can( 'manage_options' ) ) return;

		// TODO:
		// Google (XML) Sitemaps Generator Plugin for WordPress and Google News Sitemap incompatibility

		// WP SEO conflict notices
		if ( is_plugin_active('wordpress-seo/wp-seo.php') ) {
			// check date archive redirection
			$wpseo_titles = get_option( 'wpseo_titles' );
			if ( !empty( $wpseo_titles['disable-date'] ) ) {
				// check if Split by option is set anywhere
				foreach ( (array) get_option( 'xmlsf_post_types', array() ) as $type => $settings ) {
					if ( !empty( $settings['active'] ) && !empty( $settings['archive'] ) ) {
						add_action(
							'admin_notices',
							function() { include XMLSF_DIR . '/views/admin/notice-wpseo-date-redirect.php'; }
						);
						break;
					}
				}
			}

			// check wpseo sitemap option
			if ( !in_array( 'wpseo_sitemap', parent::$dismissed ) ) {
				$wpseo = get_option( 'wpseo' );
				if ( !empty( $wpseo['enable_xml_sitemap'] ) ) {
					add_action(
						'admin_notices',
						function() { include XMLSF_DIR . '/views/admin/notice-wpseo-sitemap.php'; }
					);
				}
			}
		}

		// SEOPress conflict notices
		if ( is_plugin_active('wp-seopress/seopress.php') ) {

			// check date archive redirection
			$seopress_toggle = get_option( 'seopress_toggle' );

			$seopress_titles = get_option( 'seopress_titles_option_name' );
			if ( ! empty( $seopress_toggle['toggle-titles'] ) && ! empty( $seopress_titles['seopress_titles_archives_date_disable'] ) ) {
				// check if Split by option is set anywhere
				foreach ( (array) get_option( 'xmlsf_post_types', array() ) as $type => $settings ) {
					if ( !empty( $settings['active'] ) && !empty( $settings['archive'] ) ) {
						add_action(
							'admin_notices',
							function() { include XMLSF_DIR . '/views/admin/notice-seopress-date-redirect.php'; }
						);
						break;
					}
				}
			}


			// check seopress sitemap option
			if ( !in_array( 'seopress_sitemap', parent::$dismissed ) ) {
				$seopress_xml_sitemap = get_option( 'seopress_xml_sitemap_option_name' );
				if ( ! empty( $seopress_toggle['toggle-xml-sitemap'] ) && !empty( $seopress_xml_sitemap['seopress_xml_sitemap_general_enable'] ) ) {
					add_action(
						'admin_notices',
						function() { include XMLSF_DIR . '/views/admin/notice-seopress-sitemap.php'; }
					);
				}
			}
		}

		// Rank Math conflict notices
		if ( is_plugin_active('seo-by-rank-math/rank-math.php') ) {

			// check date archive redirection
			$rankmath_titles = get_option( 'rank-math-options-titles' );
			if ( ! empty( $rankmath_titles['disable_date_archives'] ) && $rankmath_titles['disable_date_archives'] == 'on' ) {
				// check if Split by option is set anywhere
				foreach ( (array) get_option( 'xmlsf_post_types', array() ) as $type => $settings ) {
					if ( !empty( $settings['active'] ) && !empty( $settings['archive'] ) ) {
						add_action(
							'admin_notices',
							function() { include XMLSF_DIR . '/views/admin/notice-rankmath-date-redirect.php'; }
						);
						break;
					}
				}
			}

			// check rank math sitemap option
			if ( !in_array( 'rankmath_sitemap', parent::$dismissed ) ) {
				$rankmath_modules = (array) get_option( 'rank_math_modules' );
				if ( in_array( 'sitemap', $rankmath_modules ) ) {
					add_action(
						'admin_notices',
						function() { include XMLSF_DIR . '/views/admin/notice-rankmath-sitemap.php'; }
					);
				}
			}
		}

		// All in One SEO Pack conflict notices
		if ( is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php') ) {
			// check aioseop sitemap module
			if ( !in_array( 'aioseop_sitemap', parent::$dismissed ) ) {
				$aioseop_options = (array) get_option( 'aioseop_options' );

				if ( isset( $aioseop_options['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_sitemap'] ) && "on" === $aioseop_options['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_sitemap'] ) {
					// sitemap module on
					add_action(
						'admin_notices',
						function() { include XMLSF_DIR . '/views/admin/notice-aioseop-sitemap.php'; }
					);
				}
			}
		}

		// SEO Framework conflict notices
		//autodescription-site-settings[sitemaps_output]
		//
		if ( is_plugin_active('autodescription/autodescription.php') ) {
			// check sfw sitemap module
			if ( !in_array( 'seoframework_sitemap', parent::$dismissed ) ) {
				$sfw_options = (array) get_option( 'autodescription-site-settings' );

				if ( ! empty( $sfw_options['sitemaps_output'] ) ) {
					// sitemap module on
					add_action(
						'admin_notices',
						function() { include XMLSF_DIR . '/views/admin/notice-seoframework-sitemap.php'; }
					);
				}
			}
		}

	}

	/**
	* META BOXES
	*/

	/* Adds a XML Sitemap box to the side column */
	public function add_meta_box()
	{
		$post_types = get_option( 'xmlsf_post_types' );
		if ( !is_array( $post_types ) ) return;

		foreach ( $post_types as $post_type => $settings ) {
			// Only include metaboxes on post types that are included
			if ( isset( $settings["active"] ) )
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

	public function meta_box( $post )
	{
		// Use nonce for verification
		wp_nonce_field( XMLSF_BASENAME, '_xmlsf_nonce' );

		// Use get_post_meta to retrieve an existing value from the database and use the value for the form
		$exclude = get_post_meta( $post->ID, '_xmlsf_exclude', true );
		$priority = get_post_meta( $post->ID, '_xmlsf_priority', true );
		$disabled = false;

	// value prechecks to prevent "invalid form control not focusable" when meta box is hidden
	$priority = is_numeric($priority) ? xmlsf_sanitize_priority( $priority ): '';

		// disable options and (visibly) set excluded to true for private posts
		if ( 'private' == $post->post_status ) {
			$disabled = true;
			$exclude = true;
		}

		// disable options and (visibly) set priority to 1 for front page
		if ( $post->ID == get_option('page_on_front') ) {
			$disabled = true;
			$exclude = false;
			$priority = '1'; // force priority to 1 for front page
		}

		$description = sprintf(
			__('Leave empty for automatic Priority as configured on %1$s > %2$s.','xml-sitemap-feed'),
			translate('Settings'),
			'<a href="' . admin_url('options-general.php') . '?page=xmlsf">' . __('XML Sitemap','xml-sitemap-feed') . '</a>'
		);

		// The actual fields for data entry
		include XMLSF_DIR . '/views/admin/meta-box.php';
	}

	/* When the post is saved, save our meta data */
	public function save_metadata( $post_id )
	{
		if (
	  // verify nonce
	  ! isset($_POST['_xmlsf_nonce']) || ! wp_verify_nonce($_POST['_xmlsf_nonce'], XMLSF_BASENAME) ||
	  // user not allowed
	  ! current_user_can( 'edit_post', $post_id )
	) return;

		// _xmlsf_priority
		if ( empty($_POST['xmlsf_priority']) || ! is_numeric($_POST['xmlsf_priority']) )
			delete_post_meta($post_id, '_xmlsf_priority');
		else
			update_post_meta($post_id, '_xmlsf_priority', xmlsf_sanitize_priority( $_POST['xmlsf_priority'] ) );

		// _xmlsf_exclude
		if ( empty($_POST['xmlsf_exclude']) )
			delete_post_meta($post_id, '_xmlsf_exclude');
		else
			update_post_meta($post_id, '_xmlsf_exclude', $_POST['xmlsf_exclude']);
	}

	/**
	* Gets public taxonomies
	*/
	public function public_taxonomies()
	{
		if ( !isset( $this->public_taxonomies ) ) {
			$this->public_taxonomies = xmlsf_public_taxonomies();
		}

		return $this->public_taxonomies;
	}

	/**
	* Add options page
	*/
	public function add_settings_page()
	{
		// This page will be under "Settings"
		$this->screen_id = add_options_page(
		  __('XML Sitemap','xml-sitemap-feed'),
		  __('XML Sitemap','xml-sitemap-feed'),
		  'manage_options',
		  'xmlsf',
		  array( $this, 'settings_page' )
		);
	}

	/**
	* Options page callback
	*/
	public function settings_page()
	{
		/**
		 * SECTIONS & SETTINGS
		 */

		/** POST TYPES */
		add_settings_section( 'xml_sitemap_post_types_section', /*'<a name="xmlsf"></a>'.__('XML Sitemap','xml-sitemap-feed')*/ '', '', 'xmlsf_post_types' );
		$post_types = apply_filters( 'xmlsf_post_types', get_post_types( array( 'public' => true ) /*,'objects'*/) );

		if ( is_array($post_types) && !empty($post_types) ) :
			foreach ( $post_types as $post_type ) {
				$obj = get_post_type_object( $post_type );
				if ( !is_object( $obj ) )
					continue;
				add_settings_field( 'xmlsf_post_type_'.$obj->name, $obj->label, array($this,'post_types_settings_field'), 'xmlsf_post_types', 'xml_sitemap_post_types_section', $post_type );
				// Note: (ab)using section name parameter to pass post type name
			}
		endif;

		/** TAXONOMIES */
		add_settings_section( 'xml_sitemap_taxonomies_section', /*'<a name="xmlsf"></a>'.__('XML Sitemap','xml-sitemap-feed')*/ '', '', 'xmlsf_taxonomies' );
		add_settings_field( 'xmlsf_taxonomy_settings', translate('General'), array($this,'taxonomy_settings_field'), 'xmlsf_taxonomies', 'xml_sitemap_taxonomies_section' );
		add_settings_field( 'xmlsf_taxonomies', __('Taxonomies','xml-sitemap-feed'), array($this,'taxonomies_field'), 'xmlsf_taxonomies', 'xml_sitemap_taxonomies_section' );

		/** AUTHORS */
		add_settings_section( 'xml_sitemap_authors_section', /*'<a name="xmlsf"></a>'.__('XML Sitemap','xml-sitemap-feed')*/ '', '', 'xmlsf_authors' );
		add_settings_field( 'xmlsf_author_settings', translate('General'), array($this,'author_settings_field'), 'xmlsf_authors', 'xml_sitemap_authors_section' );

		/** ADVANCED */
		add_settings_section( 'xml_sitemap_advanced_section', /*'<a name="xmlsf"></a>'.__('XML Sitemap','xml-sitemap-feed')*/ '', '', 'xmlsf_advanced' );
		// custom name
		add_settings_field( 'xmlsf_sitemap_name', '<label for="xmlsf_sitemap_name">' . __('XML Sitemap URL','xml-sitemap-feed') . '</label>', array($this,'xmlsf_sitemap_name_field'), 'xmlsf_advanced', 'xml_sitemap_advanced_section' );
		// custom urls
		add_settings_field( 'xmlsf_urls', __('External web pages','xml-sitemap-feed'), array($this,'urls_settings_field'), 'xmlsf_advanced', 'xml_sitemap_advanced_section' );
		// custom sitemaps
		add_settings_field( 'xmlsf_custom_sitemaps', __('External XML Sitemaps','xml-sitemap-feed'), array($this,'custom_sitemaps_settings_field'), 'xmlsf_advanced', 'xml_sitemap_advanced_section' );

		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'post_types';

		do_action( 'xmlsf_add_settings', $active_tab );

		// prepare sitemap link url
		$sitemaps = (array) get_option( 'xmlsf_sitemaps', array() );

		$sitemap = xmlsf()->plain_permalinks() ? '?feed=sitemap' : $sitemaps['sitemap'];

		// remove WPML home url filter
		global $wpml_url_filters;
		if ( is_object($wpml_url_filters) )
			remove_filter( 'home_url', array( $wpml_url_filters, 'home_url_filter' ), - 10 );

		include XMLSF_DIR . '/views/admin/page-sitemap.php';
	}

	/**
	* Register and add settings
	*/
	public function register_settings()
	{
		// Help tab
		add_action( 'load-'.$this->screen_id, array($this,'help_tab') );

		// post_types
		register_setting( 'xmlsf_post_types', 'xmlsf_post_types', array('XMLSF_Admin_Sitemap_Sanitize','post_types_settings') );
		// taxonomies
		register_setting( 'xmlsf_taxonomies', 'xmlsf_taxonomy_settings', array('XMLSF_Admin_Sitemap_Sanitize','taxonomy_settings') );
		register_setting( 'xmlsf_taxonomies', 'xmlsf_taxonomies', array('XMLSF_Admin_Sitemap_Sanitize','taxonomies') );
		// authors
		register_setting( 'xmlsf_authors', 'xmlsf_author_settings', array('XMLSF_Admin_Sitemap_Sanitize','author_settings') );
		// custom urls
		register_setting( 'xmlsf_advanced', 'xmlsf_urls', array('XMLSF_Admin_Sitemap_Sanitize','custom_urls_settings') );
		// custom sitemaps
		register_setting( 'xmlsf_advanced', 'xmlsf_custom_sitemaps', array('XMLSF_Admin_Sitemap_Sanitize','custom_sitemaps_settings') );
	}

	/**
	* XML SITEMAP SECTION
	*/

	public function help_tab()
	{
		$screen = get_current_screen();

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-sitemaps.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = ob_get_clean();

		$screen->add_help_tab( array(
			'id'	  => 'sitemap-settings',
			'title'   => __( 'XML Sitemap', 'xml-sitemap-feed' ),
			'content' => $content
		) );

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-post-types.php';
		$content = ob_get_clean();

		$screen->add_help_tab( array(
			'id'	  => 'sitemap-settings-post-types',
			'title'   => __( 'Post types', 'xml-sitemap-feed' ),
			'content' => $content
		) );

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-taxonomies.php';
		$content = ob_get_clean();

		$screen->add_help_tab( array(
			'id'	  => 'sitemap-settings-taxonomies',
			'title'   => __( 'Taxonomies', 'xml-sitemap-feed' ),
			'content' => $content,
		) );

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-authors.php';
		$content = ob_get_clean();

		$screen->add_help_tab( array(
			'id'	  => 'sitemap-settings-authors',
			'title'   => __( 'Authors', 'xml-sitemap-feed' ),
			'content' => $content,
		) );

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-advanced.php';
		$content = ob_get_clean();

		$screen->add_help_tab( array(
			'id'	  => 'sitemap-settings-advanced',
			'title'   => translate( 'Advanced' ),
			'content' => $content
		) );

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-sidebar.php';
		$content = ob_get_clean();

		$screen->set_help_sidebar( $content );
	}

	public function post_types_settings_field( $post_type )
	{
		// post type slug passed as section name
		$obj = get_post_type_object( $post_type );

		$count = wp_count_posts( $obj->name );

		$options = get_option( 'xmlsf_post_types' );

		// The actual fields for data entry
		include XMLSF_DIR . '/views/admin/field-sitemap-post-type.php';
	}

	public function taxonomy_settings_field()
	{
		$taxonomy_settings = get_option( 'xmlsf_taxonomy_settings' );

		// The actual fields for data entry
		include XMLSF_DIR . '/views/admin/field-sitemap-taxonomy-settings.php';
	}

	public function taxonomies_field()
	{
		$taxonomies = get_option( 'xmlsf_taxonomies' );

		// The actual fields for data entry
		include XMLSF_DIR . '/views/admin/field-sitemap-taxonomies.php';
	}

	public function author_settings_field()
	{
		$author_settings = get_option( 'xmlsf_author_settings' );

		// The actual fields for data entry
		include XMLSF_DIR . '/views/admin/field-sitemap-author-settings.php';
	}

	public function authors_field()
	{
		include XMLSF_DIR . '/views/admin/field-sitemap-authors.php';
	}

	// Advanced tab fields
	public function xmlsf_sitemap_name_field()
	{
		$sitemaps = (array) get_option( 'xmlsf_sitemaps' );
		$default = apply_filters( 'xmlsf_sitemap_filename', 'sitemap.xml' );

		$name = !empty( $sitemaps['sitemap'] ) && $sitemaps['sitemap'] != $default ? $sitemaps['sitemap'] : '';

		// The actual fields for data entry
		include XMLSF_DIR . '/views/admin/field-sitemap-name.php';
	}

	public function custom_sitemaps_settings_field()
	{
		$custom_sitemaps = get_option( 'xmlsf_custom_sitemaps' );
		$lines = is_array($custom_sitemaps) ? implode( PHP_EOL, $custom_sitemaps ) : $custom_sitemaps;

		// The actual fields for data entry
		include XMLSF_DIR . '/views/admin/field-sitemap-custom.php';
	}

	public function urls_settings_field()
	{
		$urls = get_option( 'xmlsf_urls' );
		$lines = array();

		if( is_array($urls) && !empty($urls) ) {
			foreach( $urls as $arr ) {
				if( is_array($arr) )
					$lines[] = implode( " ", $arr );
			}
		}

		// The actual fields for data entry
		include XMLSF_DIR . '/views/admin/field-sitemap-urls.php';
	}

	public function advanced_archive_field_options()
	{
		?>
		<option value=""<?php echo disabled( true ); ?>>
			<?php echo __('Week','xml-sitemap-feed'); ?>
		</option>
		<?php
	}
}

new XMLSF_Admin_Sitemap();
