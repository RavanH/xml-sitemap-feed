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
     * Start up
     */
    public function __construct()
    {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
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

		add_settings_field( 'xmlsf_news_image', translate('Images'), array( $this,'image_field' ), 'xmlsf-news', 'news_sitemap_section' );
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
		include XMLSF_DIR . '/views/admin/help-tab-news-images.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = ob_get_clean();

		$screen->add_help_tab( array(
			'id'      => 'sitemap-news-images',
			'title'   => translate('Images'),
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
		$options = get_option( 'xmlsf_news_tags' );

		$image = !empty( $options['image'] ) ? $options['image'] : '';

		// The actual fields for data entry
		include XMLSF_DIR . '/views/admin/field-news-image.php';
	}

	public function labels_field() {
		// The actual fields for data entry
		include XMLSF_DIR . '/views/admin/field-news-labels.php';
	}

}

new XMLSF_Admin_Sitemap_News();
