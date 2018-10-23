<?php
/* ------------------------------
 *      XMLSF Admin CLASS
 * ------------------------------ */

class XMLSF_Admin_Controller
{
	/**
	 * Static files conflicting with this plugin
	 * @var array
	 */
	public static $static_files = null;

	/**
	 * CONSTRUCTOR
	 * Runs on init
	 */

	function __construct()
	{
		require XMLSF_DIR . '/models/admin/main.php';
		require XMLSF_DIR . '/controllers/admin/notices.php';

		$sitemaps = (array) get_option( 'xmlsf_sitemaps' );

		if ( isset($sitemaps['sitemap']) ) {
			require XMLSF_DIR . '/models/admin/sitemap.php';
			require XMLSF_DIR . '/controllers/admin/sitemap.php';
		}

		if ( isset($sitemaps['sitemap-news']) ) {
			require XMLSF_DIR . '/models/admin/sitemap-news.php';
			require XMLSF_DIR . '/controllers/admin/sitemap-news.php';
		}

		// NGINX HELPER PURGE URLS
		add_filter( 'rt_nginx_helper_purge_urls', 'xmlsf_nginx_helper_purge_urls', 10, 2 );

		// ACTION LINK
		add_filter( 'plugin_action_links_' . XMLSF_BASENAME, 'xmlsf_add_action_link' );

		add_action( 'admin_init', array( $this, 'notices_actions' ) );
		add_action( 'admin_init', array( $this, 'transients_actions' ) );
		add_action( 'admin_init', array( $this, 'tools_actions' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_init', array( $this, 'register_meta_boxes' ) );
		if ( ( !is_multisite() && current_user_can( 'manage_options' ) ) || is_super_admin() )
			add_action( 'admin_init', array( $this, 'static_files' ) );
		add_action( 'admin_init', array( $this, 'verify_wpseo_settings' ) );
	}

	/**
	* SETTINGS
	*/

	/**
	 * Register settings and add settings fields
	 */

	public function register_settings()
	{
		$sitemaps = (array) get_option( 'xmlsf_sitemaps' );

		// sitemaps
		register_setting( 'reading', 'xmlsf_sitemaps', array('XMLSF_Admin_Sanitize','sitemaps_settings') );
		add_settings_field( 'xmlsf_sitemaps', __('Enable XML sitemaps','xml-sitemap-feed'), array($this,'sitemaps_settings_field'), 'reading' );

		// custom domains, only when any sitemap is active
		if ( isset($sitemaps['sitemap']) || isset($sitemaps['sitemap-news']) ) {
			register_setting( 'reading', 'xmlsf_domains', array('XMLSF_Admin_Sanitize','domains_settings') );
			add_settings_field( 'xmlsf_domains', __('Allowed domains','xml-sitemap-feed'), array($this,'domains_settings_field'), 'reading' );
		}

		// help tab
		add_action( 'load-options-reading.php', array($this,'xml_sitemaps_help') );

		// robots rules, only when permalinks are set
		$rules = get_option( 'rewrite_rules' );
		if( ! xmlsf()->plain_permalinks() && isset( $rules['robots\.txt$'] ) ) {
			register_setting( 'reading', 'xmlsf_robots', array('XMLSF_Admin_Sanitize','robots_settings') );
			add_settings_field( 'xmlsf_robots', __('Additional robots.txt rules','xml-sitemap-feed'), array($this,'robots_settings_field'), 'reading' );
		}

		// ping, only when any sitemap is active
		if ( isset($sitemaps['sitemap']) || isset($sitemaps['sitemap-news']) ) {
			register_setting( 'writing', 'xmlsf_ping', array('XMLSF_Admin_Sanitize','ping_settings') );
			add_settings_field( 'xmlsf_ping', __('Ping Services','xml-sitemap-feed'), array($this,'ping_settings_field'), 'writing' );
			add_action( 'load-options-writing.php', array($this,'ping_settings_help') );
		}
	}

	/* SITEMAPS */

	public function xml_sitemaps_help()
	{
		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-sitemaps.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = ob_get_clean();

		get_current_screen()->add_help_tab( array(
			'id'      => 'sitemap-settings',
			'title'   => __( 'Enable XML sitemaps', 'xml-sitemap-feed' ),
			'content' => $content,
			'priority' => 11
		) );

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-allowed-domains.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = ob_get_clean();

		get_current_screen()->add_help_tab( array(
			'id'      => 'allowed-domains',
			'title'   =>__( 'Allowed domains', 'xml-sitemap-feed' ),
			'content' => $content,
			'priority' => 11
		) );

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-robots.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = ob_get_clean();

		get_current_screen()->add_help_tab( array(
			'id'      => 'robots',
			'title'   => __( 'Additional robots.txt rules', 'xml-sitemap-feed' ),
			'content' => $content,
			'priority' => 11
		) );
	}

	/**
	 * Sitemap settings fields
	 */

	public function sitemaps_settings_field()
	{
		if ( 1 == get_option('blog_public') ) :

			$options = (array) get_option( 'xmlsf_sitemaps' );

			// The actual fields for data entry
			include XMLSF_DIR . '/views/admin/field-sitemaps.php';

		else :

			_e( 'XML Sitemaps are not available because of your site&#8217;s visibility settings (above).', 'xml-sitemap-feed' );

		endif;
	}

	/**
	 * Domain settings field
	 */

	public function domains_settings_field()
	{
		$domains = get_option('xmlsf_domains');
		if ( !is_array($domains) ) $domains = array();

		// The actual fields for data entry
		include XMLSF_DIR . '/views/admin/field-sitemap-domains.php';
	}

	/* ROBOTS */

	public function robots_settings_field()
	{
		// The actual fields for data entry
		include XMLSF_DIR . '/views/admin/field-robots.php';
	}

	/* PING SETTINGS */

	public function ping_settings_help()
	{
		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-ping.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = ob_get_clean();

		get_current_screen()->add_help_tab( array(
			'id'      => 'ping-services',
			'title'   => __( 'Ping Services', 'xml-sitemap-feed' ),
			'content' => $content,
			'priority' => 11
		) );
	}

	public function ping_settings_field()
	{
		$options = get_option( 'xmlsf_ping' );

		// The actual fields for data entry
		include XMLSF_DIR . '/views/admin/field-ping.php';
	}

	/**
	* META BOXES
	*/

	/**
	 * Register settings and add settings fields
	 */

	public function register_meta_boxes()
	{
		$sitemaps = (array) get_option( 'xmlsf_sitemaps' );

		if ( isset($sitemaps['sitemap-news']) ) {
      		// post meta box
      		add_action( 'add_meta_boxes', array($this,'add_meta_box_news') );
		}

		if ( isset($sitemaps['sitemap']) ) {
			// post meta box
			add_action( 'add_meta_boxes', array($this,'add_meta_box') );
		}

		if ( isset($sitemaps['sitemap']) || isset($sitemaps['sitemap-news']) ) {
	        // save post meta box settings
	        add_action( 'save_post', array($this,'save_metadata') );
		}
	}

	/* Adds a XML Sitemap box to the side column */
	public function add_meta_box ()
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

	/* Adds a News Sitemap box to the side column */
	public function add_meta_box_news ()
	{
		$news_tags = get_option('xmlsf_news_tags');
		$news_post_types = !empty($news_tags['post_type']) && is_array($news_tags['post_type']) ? $news_tags['post_type'] : array('post');

		foreach ( $news_post_types as $post_type ) {
      // Only include metabox on post types that are included
			add_meta_box(
				'xmlsf_news_section',
				__( 'Google News', 'xml-sitemap-feed' ),
				array($this,'meta_box_news'),
				$post_type,
				'side'
			);
		}
	}

	public function meta_box_news( $post )
	{
		// Use nonce for verification
		wp_nonce_field( XMLSF_BASENAME, '_xmlsf_nonce' );

		// Use get_post_meta to retrieve an existing value from the database and use the value for the form
		$exclude = 'private' == $post->post_status || get_post_meta( $post->ID, '_xmlsf_news_exclude', true );
		$disabled = 'private' == $post->post_status;

		// The actual fields for data entry
		include XMLSF_DIR . '/views/admin/meta-box-news.php';
	}

	/* When the post is saved, save our meta data */
	public function save_metadata( $post_id )
	{
		if ( !isset($post_id) )
			$post_id = (int)$_REQUEST['post_ID'];

		if ( !current_user_can( 'edit_post', $post_id ) || !isset($_POST['_xmlsf_nonce']) || !wp_verify_nonce($_POST['_xmlsf_nonce'], XMLSF_BASENAME) )
			return;

		// _xmlsf_priority
		if ( empty($_POST['xmlsf_priority']) && '0' !== $_POST['xmlsf_priority'] ) {
			delete_post_meta($post_id, '_xmlsf_priority');
		} else {
			update_post_meta($post_id, '_xmlsf_priority', XMLSF_Admin_Sitemap_Sanitize::priority($_POST['xmlsf_priority']) );
		}

		// _xmlsf_exclude
		if ( isset($_POST['xmlsf_exclude']) && $_POST['xmlsf_exclude'] != '' ) {
			update_post_meta($post_id, '_xmlsf_exclude', $_POST['xmlsf_exclude']);
		} else {
			delete_post_meta($post_id, '_xmlsf_exclude');
		}

		// _xmlsf_news_exclude
		if ( isset($_POST['xmlsf_news_exclude']) && $_POST['xmlsf_news_exclude'] != '' ) {
			update_post_meta($post_id, '_xmlsf_news_exclude', $_POST['xmlsf_news_exclude']);
		} else {
			delete_post_meta($post_id, '_xmlsf_news_exclude');
		}
	}

	/**
	 * Clear settings
	 */
	public function clear_settings()
	{
		if ( !isset( $_POST['_xmlsf_help_nonce'] ) || !wp_verify_nonce( $_POST['_xmlsf_help_nonce'], XMLSF_BASENAME.'-help' ) ) {
			add_action( 'admin_notices', array('XMLSF_Admin_Notices','notice_nonce_fail') );
			return;
		}

		$defaults = xmlsf()->defaults();
		unset($defaults['sitemaps']);

		foreach ( $defaults as $option => $settings ) {
			update_option( 'xmlsf_' . $option, $settings );
		}

		delete_option( 'xmlsf_static_files' );
		delete_option( 'xmlsf_pong' );

		add_action( 'admin_notices', array('XMLSF_Admin_Notices','notice_clear_settings') );
	}

	/**
	 * Delete static sitemap files
	 */

	public function delete_static_files()
	{
		if ( !isset( $_POST['_xmlsf_notice_nonce'] ) || !wp_verify_nonce( $_POST['_xmlsf_notice_nonce'], XMLSF_BASENAME.'-notice' ) ) {
			add_action( 'admin_notices', array('XMLSF_Admin_Notices','notice_nonce_fail') );
			return;
		}

		if ( empty($_POST['xmlsf-delete']) ) {
			return;
		}

		$allowed_files = array('sitemap.xml','sitemap-news.xml','robots.txt');

		$this->static_files();

		$i = '1';
		foreach ( $_POST['xmlsf-delete'] as $name ) {
			if ( !in_array($name,$allowed_files) ) {
				unset(self::$static_files[$name]);
				add_action( 'admin_notices', array('XMLSF_Admin_Notices','static_files_not_allowed'), $i );
				continue;
			}
			if ( !isset(self::$static_files[$name]) ) {
				// do nothing and be quiet about it...
				continue;
			}
			if ( unlink(self::$static_files[$name]) ) {
				unset(self::$static_files[$name]);
				add_action( 'admin_notices', array('XMLSF_Admin_Notices','static_files_deleted'), $i );
			} else {
				add_action( 'admin_notices', array('XMLSF_Admin_Notices','static_files_failed'), $i );
			}
			$i ++;
		}

		$this->check_static_files();
	}

	/**
	 * Check for conflicting WPSEO settings
	 */
	public function verify_wpseo_settings()
	{
		// WP SEO Plugin conflict notice
		if ( is_plugin_active('wordpress-seo/wp-seo.php') ) {
			// check date archive redirection
			$wpseo_titles = get_option( 'wpseo_titles' );
			if ( !empty( $wpseo_titles['disable-date'] ) ) {
				// check is Split by option is set anywhere
				foreach ( (array) get_option( 'xmlsf_post_types' ) as $type => $settings ) {
					if ( is_array( $settings ) && !empty( $settings['archive'] ) ) {
						add_action( 'admin_notices', array( 'XMLSF_Admin_Notices', 'notice_wpseo_date_redirect' ) );
						break;
					}
				}
			}
			// check wpseo sitemap option
			$wpseo = get_option( 'wpseo' );
			$sitemaps = get_option( 'xmlsf_sitemaps' );
			if ( !empty( $wpseo['enable_xml_sitemap'] ) && !empty( $sitemaps['sitemap'] ) ) {
				add_action( 'admin_notices', array( 'XMLSF_Admin_Notices', 'notice_wpseo_sitemap' ) );
			}
		}
	}

	/**
	 * Check for static sitemap files
	 */
	public function static_files()
	{
		if ( null === self::$static_files )
			self::$static_files = get_option( 'xmlsf_static_files', array() );

		if ( !empty(self::$static_files) )
			add_action( 'admin_notices', array('XMLSF_Admin_Notices','notice_static_files') );
	}

	/**
	 * Check for static sitemap files
	 */
	public function check_static_files()
	{
		$home_path = trailingslashit( get_home_path() );
		$sitemaps = get_option( 'xmlsf_sitemaps' );
		self::$static_files = array();
		$check_for = is_array($sitemaps) ? $sitemaps : array();
		if ( get_option('xmlsf_robots') ) {
			$check_for['robots'] = 'robots.txt';
		}

		foreach ( $check_for as $name => $pretty ) {
			if ( file_exists( $home_path . $pretty ) ) {
				self::$static_files[$pretty] = $home_path . $pretty;
			}
		}

		if ( !empty( self::$static_files ) ) {
			update_option( 'xmlsf_static_files', self::$static_files, false );
		} else {
			delete_option( 'xmlsf_static_files' );
		}
	}

	public function tools_actions()
	{
		if ( isset( $_POST['xmlsf-clear-settings'] ) ) {
			$this->clear_settings();
		}

		if ( isset( $_POST['xmlsf-delete-submit'] ) ) {
			$this->delete_static_files();
		}

		if ( isset( $_POST['xmlsf-check-conflicts'] ) ) {
			if ( isset( $_POST['_xmlsf_help_nonce'] ) && wp_verify_nonce( $_POST['_xmlsf_help_nonce'], XMLSF_BASENAME.'-help' ) ) {
				// reset ignored warnings
				delete_user_meta( get_current_user_id(), 'xmlsf_dismissed' );

				$this->check_static_files();
				if ( empty( self::$static_files ) )
					add_action( 'admin_notices', array('XMLSF_Admin_Notices','static_files_none_found') );

				$this->verify_wpseo_settings();

			} else {
				add_action( 'admin_notices', array('XMLSF_Admin_Notices','notice_nonce_fail') );
			}
		}
	}

	public function notices_actions()
	{
		if ( isset( $_POST['xmlsf-dismiss'] ) ) {
			if ( isset( $_POST['_xmlsf_notice_nonce'] ) && wp_verify_nonce( $_POST['_xmlsf_notice_nonce'], XMLSF_BASENAME.'-notice' ) ) {
				add_user_meta( get_current_user_id(), 'xmlsf_dismissed', $_POST['xmlsf-dismiss'], false );
			} else {
				add_action( 'admin_notices', array('XMLSF_Admin_Notices','notice_nonce_fail') );
			}
		}
	}

	public function transients_actions()
	{
		// CATCH TRANSIENT for flushing rewrite rules after the sitemaps setting has changed
		if ( delete_transient('xmlsf_flush_rewrite_rules') ) {
			flush_rewrite_rules();
			if ( defined('WP_DEBUG') && WP_DEBUG == true ) {
				error_log('Rewrite rules flushed by XML Sitemap Feeds.');
			}
		}

		// CATCH TRANSIENT for static file check
		if ( delete_transient('xmlsf_check_static_files') ) {
			$this->check_static_files();
		}
	}
}

new XMLSF_Admin_Controller();
