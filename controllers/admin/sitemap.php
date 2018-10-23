<?php

class XMLSF_Admin_Sitemap
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
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
     * Gets public taxonomies
     */
    public function public_taxonomies()
	{
		if ( !isset( $this->public_taxonomies ) ) {
			include XMLSF_DIR . '/models/public/sitemap.php';
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
		// SECTIONS & SETTINGS
		// post_types
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

		// taxonomies
		add_settings_section( 'xml_sitemap_taxonomies_section', /*'<a name="xmlsf"></a>'.__('XML Sitemap','xml-sitemap-feed')*/ '', '', 'xmlsf_taxonomies' );
		add_settings_field( 'xmlsf_taxonomy_settings', translate('General'), array($this,'taxonomy_settings_field'), 'xmlsf_taxonomies', 'xml_sitemap_taxonomies_section' );
		$taxonomy_settings = get_option( 'xmlsf_taxonomy_settings' );
		if ( !empty( $taxonomy_settings['active'] ) && get_option( 'xmlsf_taxonomies' ) )
			add_settings_field( 'xmlsf_taxonomies', __('Include taxonomies','xml-sitemap-feed'), array($this,'taxonomies_field'), 'xmlsf_taxonomies', 'xml_sitemap_taxonomies_section' );

		add_settings_section( 'xml_sitemap_advanced_section', /*'<a name="xmlsf"></a>'.__('XML Sitemap','xml-sitemap-feed')*/ '', '', 'xmlsf_advanced' );
		// custom urls
		add_settings_field( 'xmlsf_urls', __('External web pages','xml-sitemap-feed'), array($this,'urls_settings_field'), 'xmlsf_advanced', 'xml_sitemap_advanced_section' );
		// custom sitemaps
		add_settings_field( 'xmlsf_custom_sitemaps', __('External XML Sitemaps','xml-sitemap-feed'), array($this,'custom_sitemaps_settings_field'), 'xmlsf_advanced', 'xml_sitemap_advanced_section' );

		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'post_types';

		$options = (array) get_option( 'xmlsf_sitemaps' );
		$url = trailingslashit(get_bloginfo('url')) . ( xmlsf()->plain_permalinks() ? '?feed=sitemap' : $options['sitemap'] );

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
			'id'      => 'sitemap-settings',
			'title'   => __( 'XML Sitemap', 'xml-sitemap-feed' ),
			'content' => $content
		) );

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-post-types.php';
		$content = ob_get_clean();

		$screen->add_help_tab( array(
			'id'      => 'sitemap-settings-post-types',
			'title'   => __( 'Post types', 'xml-sitemap-feed' ),
			'content' => $content
		) );

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-taxonomies.php';
		$content = ob_get_clean();

		$screen->add_help_tab( array(
			'id'      => 'sitemap-settings-taxonomies',
			'title'   => __( 'Taxonomies', 'xml-sitemap-feed' ),
			'content' => $content,
		) );

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-advanced.php';
		$content = ob_get_clean();

		$screen->add_help_tab( array(
			'id'      => 'sitemap-settings-advanced',
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

		$options = get_option('xmlsf_post_types');

		// The actual fields for data entry
		include XMLSF_DIR . '/views/admin/field-sitemap-post-type.php';
	}

	public function taxonomy_settings_field()
	{
		$taxonomies = get_option( 'xmlsf_taxonomies' );
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

	public function custom_sitemaps_settings_field()
	{
		$custom_sitemaps = get_option( 'xmlsf_custom_sitemaps' );
		$lines = is_array($custom_sitemaps) ? implode( PHP_EOL, $custom_sitemaps ) : $custom_sitemaps;

		// The actual fields for data entry
		include XMLSF_DIR . '/views/admin/field-sitemap-custom.php';
	}

	public function urls_settings_field() {
		$urls = get_option('xmlsf_urls');
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

}

new XMLSF_Admin_Sitemap();
