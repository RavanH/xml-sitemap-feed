<?php

class XMLSF_Admin_Sitemap_News
{
	/**
     * Holds the values to be used in the fields callbacks
     */
    private $screen_id;

	/**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

	/**
     * Advanced module available?
     */
    private $advanced = false;

    /**
     * Start up
     */
    public function __construct()
    {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_metadata' ) );
    }

	/**
	* META BOXES
	*/

	/* Adds a News Sitemap box to the side column */
	public function add_meta_box()
	{
		$news_tags = get_option('xmlsf_news_tags');
		$news_post_types = !empty($news_tags['post_type']) && is_array($news_tags['post_type']) ? $news_tags['post_type'] : array('post');

		foreach ( $news_post_types as $post_type ) {
      // Only include metabox on post types that are included
			add_meta_box(
				'xmlsf_news_section',
				__( 'Google News', 'xml-sitemap-feed' ),
				array( $this, 'meta_box' ),
				$post_type,
				'side'
			);
		}
	}

	public function meta_box( $post )
	{
		// Use nonce for verification
		wp_nonce_field( XMLSF_BASENAME, '_xmlsf_news_nonce' );

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

		if ( !current_user_can( 'edit_post', $post_id ) || !isset($_POST['_xmlsf_news_nonce']) || !wp_verify_nonce($_POST['_xmlsf_news_nonce'], XMLSF_BASENAME) )
			return;

		// _xmlsf_news_exclude
		if ( isset($_POST['xmlsf_news_exclude']) && $_POST['xmlsf_news_exclude'] != '' ) {
			update_post_meta($post_id, '_xmlsf_news_exclude', $_POST['xmlsf_news_exclude']);
		} else {
			delete_post_meta($post_id, '_xmlsf_news_exclude');
		}
	}

	/**
     * Add options page
     */
    public function add_settings_page()
	{
        // This page will be under "Settings"
        $this->screen_id = add_options_page(
			__('Google News Sitemap','xml-sitemap-feed'),
            __('Google News','xml-sitemap-feed'),
            'manage_options',
            'xmlsf-news',
            array( $this, 'settings_page' )
        );
    }

    /**
     * Options page callback
     */
    public function settings_page()
    {
		$this->options = get_option( 'xmlsf_news_tags', array() );

		$this->advanced = is_plugin_active('xml-sitemap-feed-advanced-news/xml-sitemap-advanced-news.php');

		// SECTION
		add_settings_section( 'news_sitemap_section', /* '<a name="xmlnf"></a>'.__('Google News Sitemap','xml-sitemap-feed') */ '', '', 'xmlsf-news' );

		// SETTINGS
		add_settings_field( 'xmlsf_news_name', '<label for="xmlsf_news_name">'.__('Publication name','xml-sitemap-feed').'</label>', array($this,'name_field'), 'xmlsf-news', 'news_sitemap_section' );
		add_settings_field( 'xmlsf_news_post_type', __('Post type','xml-sitemap-feed'), array($this,'post_type_field'), 'xmlsf-news', 'news_sitemap_section' );

		global $wp_taxonomies;
		$news_post_type = isset( $this->options['post_type'] ) && !empty( $this->options['post_type'] ) ? (array) $this->options['post_type'] : array('post');
		$post_types = ( isset( $wp_taxonomies['category'] ) ) ? $wp_taxonomies['category']->object_type : array();

		foreach ( $news_post_type as $post_type ) {
			if ( in_array( $post_type, $post_types ) ) {
				add_settings_field( 'xmlsf_news_categories', translate('Categories'), array($this,'categories_field'), 'xmlsf-news', 'news_sitemap_section' );
				break;
			}
		}

		// Images
		add_settings_field( 'xmlsf_news_image', translate('Images'), array( $this,'image_field' ), 'xmlsf-news', 'news_sitemap_section' );

		// Keywords
		add_settings_field( 'xmlsf_news_keywords', __('Keywords', 'xml-sitemap-feed' ), array( $this,'keywords_field' ), 'xmlsf-news', 'news_sitemap_section' );

		// Stock tickers
		add_settings_field( 'xmlsf_news_stock_tickers', __('Stock tickers', 'xml-sitemap-feed' ), array( $this,'stock_tickers_field' ), 'xmlsf-news', 'news_sitemap_section' );

		// Source labels - deprecated
		add_settings_field( 'xmlsf_news_labels', __('Source labels', 'xml-sitemap-feed' ), array($this,'labels_field'), 'xmlsf-news', 'news_sitemap_section' );

		$options = (array) get_option( 'xmlsf_sitemaps' );
		$url = trailingslashit(get_bloginfo('url')) . ( xmlsf()->plain_permalinks() ? '?feed=sitemap-news' : $options['sitemap-news'] );

		include XMLSF_DIR . '/views/admin/page-sitemap-news.php';
    }

    /**
     * Register and add settings
     */
    public function register_settings()
    {
		// Help tab
		add_action( 'load-'.$this->screen_id, array($this,'help_tab') );

		register_setting( 'xmlsf-news', 'xmlsf_news_tags', array('XMLSF_Admin_Sitemap_News_Sanitize','news_tags_settings') );
    }

	/**
	* GOOGLE NEWS SITEMAP SECTION
	*/

	public function help_tab() {
		$screen = get_current_screen();

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-news.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = ob_get_clean();

		$screen->add_help_tab( array(
			'id'      => 'sitemap-news-settings',
			'title'   => __( 'Google News Sitemap', 'xml-sitemap-feed' ),
			'content' => $content
		) );

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-news-name.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = ob_get_clean();

		$screen->add_help_tab( array(
			'id'      => 'sitemap-news-name',
			'title'   => __( 'Publication name', 'xml-sitemap-feed' ),
			'content' => $content
		) );

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-news-categories.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = ob_get_clean();

		$screen->add_help_tab( array(
			'id'      => 'sitemap-news-categories',
			'title'   => translate('Categories'),
			'content' => $content
		) );

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-news-images.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = ob_get_clean();

		$screen->add_help_tab( array(
			'id'      => 'sitemap-news-images',
			'title'   => translate('Images'),
			'content' => $content
		) );

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-news-keywords.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = ob_get_clean();

		$screen->add_help_tab( array(
			'id'      => 'sitemap-news-keywords',
			'title'   => __( 'Keywords', 'xml-sitemap-feed' ),
			'content' => $content
		) );

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-news-stocktickers.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = ob_get_clean();

		$screen->add_help_tab( array(
			'id'      => 'sitemap-news-stocktickers',
			'title'   => __( 'Stock tickers', 'xml-sitemap-feed' ),
			'content' => $content
		) );

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-news-labels.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = ob_get_clean();

		$screen->add_help_tab( array(
			'id'      => 'sitemap-news-labels',
			'title'   => __( 'Source labels', 'xml-sitemap-feed' ),
			'content' => $content
		) );

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-news-sidebar.php';
		$content = ob_get_clean();

		$screen->set_help_sidebar( $content );
	}

	public function name_field()
	{
		$name = !empty($this->options['name']) ? $this->options['name'] : '';

		// The actual fields for data entry
		include XMLSF_DIR . '/views/admin/field-news-name.php';
	}

	public function post_type_field()
	{
		global $wp_taxonomies;
		$post_types = apply_filters( 'xmlsf_news_post_types', get_post_types( array( 'public' => true ) /*,'objects'*/) );

		if ( is_array($post_types) && !empty($post_types) ) :

			$news_post_type = isset($this->options['post_type']) && !empty( $this->options['post_type'] ) ? (array) $this->options['post_type'] : array('post');

			$type = ( 1 == count( $news_post_type ) ) ? 'radio' : 'checkbox';

			$allowed = ( !empty( $this->options['categories'] ) && isset( $wp_taxonomies['category'] ) ) ? $wp_taxonomies['category']->object_type : $post_types;

			$do_warning = !empty( $this->options['categories'] ) && count($post_types) > 1 ? true : false;

			// The actual fields for data entry
			include XMLSF_DIR . '/views/admin/field-news-post-type.php';

		else :

			echo '<p class="description warning">'.__('There appear to be no post types available.','xml-sitemap-feed').'</p>';

		endif;
	}

	public function categories_field()
	{
		$selected_categories = isset( $this->options['categories'] ) && is_array( $this->options['categories'] ) ? $this->options['categories'] : array();

		$cat_list = str_replace('name="post_category[]"','name="'.'xmlsf_news_tags[categories][]"', wp_terms_checklist( null, array( 'taxonomy' => 'category', 'selected_cats' => $selected_categories, 'echo' => false ) ) );

		// The actual fields for data entry
		include XMLSF_DIR . '/views/admin/field-news-categories.php';
	}

	public function image_field() {
		$image = !empty( $this->options['image'] ) ? $this->options['image'] : '';

		// The actual fields for data entry
		include XMLSF_DIR . '/views/admin/field-news-image.php';
	}

	public function keywords_field() {
		// The actual fields for data entry
		include XMLSF_DIR . '/views/admin/field-news-keywords.php';
	}

	public function stock_tickers_field() {
		$stock_tickers = apply_filters( 'xmlsf_news_enable_stock_tickers', '' );

		// The actual fields for data entry
		include XMLSF_DIR . '/views/admin/field-news-stocktickers.php';
	}

	public function labels_field() {
		// The actual fields for data entry
		include XMLSF_DIR . '/views/admin/field-news-labels.php';
	}

}

new XMLSF_Admin_Sitemap_News();
