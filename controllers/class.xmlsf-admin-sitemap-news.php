<?php

class XMLSF_Admin_Sitemap_News extends XMLSF_Admin
{
	/**
	* Holds the values to be used in the fields callbacks
	*/
	private $options;

	/**
	* Start up
	*/
	public function __construct()
	{
		// META
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_metadata' ) );

		// SETTINGS
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		// advanced tab options
		add_action( 'xmlsf_news_settings_before', 'xmlsf_news_section_advanced_intro' );
		add_action( 'xmlsf_news_add_settings', array( $this, 'add_settings' ) );

		// TOOLS ACTIONS
		add_action( 'admin_init', array( $this, 'ping_sitemap' ) );
	}

	/**
	* TOOLS ACTIONS
	*/

	public function ping_sitemap()
	{
		if ( ! isset( $_POST['xmlsf-ping-sitemap-news'] ) || ! xmlsf_verify_nonce('help') )
			return;

		$sitemaps = get_option( 'xmlsf_sitemaps' );
		$result = xmlsf_ping( 'google', $sitemaps['sitemap-news'], 5 * MINUTE_IN_SECONDS );

		switch( $result ) {
			case 200:
			$msg = sprintf( /* Translators: Search engine / Service name */ __( 'Pinged %s with success.', 'xml-sitemap-feed' ), __( 'Google News', 'xml-sitemap-feed' ) );
			$type = 'updated';
			break;

			case 999:
			$msg = sprintf( /* Translators: Search engine / Service name, interval number */ __( 'Ping %s skipped: Sitemap already sent within the last %d minutes.', 'xml-sitemap-feed' ), __( 'Google News', 'xml-sitemap-feed' ), 5 );
			$type = 'notice-warning';
			break;

			case '':
			$msg = sprintf( translate('Oops: %s'), translate('Something went wrong.') );
			$type = 'error';
			break;

			default:
			$msg = sprintf( /* Translators: Search engine / Service name, response code number */ __( 'Ping %s failed with response code: %d', 'xml-sitemap-feed' ), __( 'Google News', 'xml-sitemap-feed' ), $result );
			$type = 'error';
		}

		add_settings_error( 'ping_sitemap', 'ping_sitemap', $msg, $type );

	}

	/**
	* META BOXES
	*/

	/* Adds a News Sitemap box to the side column */
	public function add_meta_box()
	{
		$news_tags = get_option( 'xmlsf_news_tags' );
		$news_post_types = !empty($news_tags['post_type']) && is_array($news_tags['post_type']) ? $news_tags['post_type'] : array('post');

		// Only include metabox on post types that are included
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
		if (
			// verify nonce
			! isset($_POST['_xmlsf_news_nonce']) || ! wp_verify_nonce($_POST['_xmlsf_news_nonce'], XMLSF_BASENAME) ||
			// user not allowed
			! current_user_can( 'edit_post', $post_id )
		) return;

		// _xmlsf_news_exclude
		if ( empty($_POST['xmlsf_news_exclude']) )
			delete_post_meta( $post_id, '_xmlsf_news_exclude' );
		else
			update_post_meta( $post_id, '_xmlsf_news_exclude', '1' );
	}

	/**
	* SETTINGS
	*/

	/**
	* Add options page
	*/
	public function add_settings_page()
	{
		// This page will be under "Settings"
		$screen_id = add_options_page(
			__('Google News Sitemap','xml-sitemap-feed'),
			__('Google News','xml-sitemap-feed'),
			'manage_options',
			'xmlsf_news',
			array( $this, 'settings_page' )
		);

		// Help tab
		add_action( 'load-'.$screen_id, array( $this, 'help_tab' ) );
	}

	/**
	* Options page callback
	*/
	public function settings_page()
	{
		$this->options = (array) get_option( 'xmlsf_news_tags', array() );

		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';

		do_action( 'xmlsf_news_add_settings', $active_tab );

		// prepare sitemap link url
		$sitemaps = (array) get_option( 'xmlsf_sitemaps', array() );

		$sitemap = xmlsf()->plain_permalinks() ? '?feed=sitemap-news' : $sitemaps['sitemap-news'];

		// remove WPML home url filter
		global $wpml_url_filters;
		if ( is_object($wpml_url_filters) )
			remove_filter( 'home_url', array( $wpml_url_filters, 'home_url_filter' ), - 10 );

		include XMLSF_DIR . '/views/admin/page-sitemap-news.php';
	}

	/**
	* Add advanced settings
	*/
	public function add_settings( $active_tab = '' )
	{
		if ( 'advanced' == $active_tab ) {
			// ADVANCED SECTION
			add_settings_section(
				'news_sitemap_advanced_section',
				/* '<a name="xmlnf"></a>'.__('Google News Sitemap','xml-sitemap-feed') */
				'',
				'',
				'xmlsf_news_advanced'
			);

			// Hierarchical post types
			add_settings_field(
				'xmlsf_news_hierarchical',
				__( 'Hierarchical post types', 'xml-sitemap-feed' ),
				function() { include XMLSF_DIR . '/views/admin/field-news-hierarchical.php'; },
				'xmlsf_news_advanced',
				'news_sitemap_advanced_section'
			);

			// Keywords
			add_settings_field(
				'xmlsf_news_keywords',
				__( 'Keywords', 'xml-sitemap-feed' ),
				function() { include XMLSF_DIR . '/views/admin/field-news-keywords.php'; },
				'xmlsf_news_advanced',
				'news_sitemap_advanced_section'
			);

			// Stock tickers
			add_settings_field(
				'xmlsf_news_stock_tickers',
				__( 'Stock tickers', 'xml-sitemap-feed' ),
				function() { include XMLSF_DIR . '/views/admin/field-news-stocktickers.php'; },
				'xmlsf_news_advanced',
				'news_sitemap_advanced_section'
			);

			// Ping log
			add_settings_field(
				'xmlsf_news_ping_log',
				__( 'Ping log', 'xml-sitemap-feed' ),
				function() { include XMLSF_DIR . '/views/admin/field-news-ping-log.php'; },
				'xmlsf_news_advanced',
				'news_sitemap_advanced_section'
			);
		} else {
			// GENERAL SECTION
			add_settings_section(
				'news_sitemap_general_section',
				/* '<a name="xmlnf"></a>'.__('Google News Sitemap','xml-sitemap-feed') */
				'',
				'',
				'xmlsf_news_general'
			);

			// SETTINGS
			add_settings_field(
				'xmlsf_news_name',
				'<label for="xmlsf_news_name">'.__('Publication name','xml-sitemap-feed').'</label>',
				array( $this, 'name_field' ),
				'xmlsf_news_general',
				'news_sitemap_general_section'
			);
			add_settings_field(
				'xmlsf_news_post_type',
				__( 'Post type', 'xml-sitemap-feed' ),
				array( $this, 'post_type_field' ),
				'xmlsf_news_general',
				'news_sitemap_general_section'
			);

			global $wp_taxonomies;
			$news_post_type = isset( $this->options['post_type'] ) && !empty( $this->options['post_type'] ) ? (array) $this->options['post_type'] : array('post');
			$post_types = ( isset( $wp_taxonomies['category'] ) ) ? $wp_taxonomies['category']->object_type : array();

			foreach ( $news_post_type as $post_type ) {
				if ( in_array( $post_type, $post_types ) ) {
					add_settings_field( 'xmlsf_news_categories', translate('Categories'), array($this,'categories_field'), 'xmlsf_news_general', 'news_sitemap_general_section' );
					break;
				}
			}

			// Source labels - deprecated
			add_settings_field(
				'xmlsf_news_labels',
				__('Source labels', 'xml-sitemap-feed' ),
				function() { include XMLSF_DIR . '/views/admin/field-news-labels.php'; },
				'xmlsf_news_general',
				'news_sitemap_general_section'
			);
		}
	}

	/**
	* Register settings
	*/
	public function register_settings()
	{
		register_setting(
			'xmlsf_news_general',
			'xmlsf_news_tags',
			array( 'XMLSF_Admin_Sitemap_News_Sanitize', 'news_tags_settings' )
		);
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
			'id'	  => 'sitemap-news-settings',
			'title'   => __( 'Google News Sitemap', 'xml-sitemap-feed' ),
			'content' => $content
		) );

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-news-name.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = ob_get_clean();

		$screen->add_help_tab( array(
			'id'	  => 'sitemap-news-name',
			'title'   => __( 'Publication name', 'xml-sitemap-feed' ),
			'content' => $content
		) );

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-news-categories.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = ob_get_clean();

		$screen->add_help_tab( array(
			'id'	  => 'sitemap-news-categories',
			'title'   => translate('Categories'),
			'content' => $content
		) );

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-news-keywords.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = ob_get_clean();

		$screen->add_help_tab( array(
			'id'	  => 'sitemap-news-keywords',
			'title'   => __( 'Keywords', 'xml-sitemap-feed' ),
			'content' => $content
		) );

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-news-stocktickers.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = ob_get_clean();

		$screen->add_help_tab( array(
			'id'	  => 'sitemap-news-stocktickers',
			'title'   => __( 'Stock tickers', 'xml-sitemap-feed' ),
			'content' => $content
		) );

		ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-news-labels.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = ob_get_clean();

		$screen->add_help_tab( array(
			'id'	  => 'sitemap-news-labels',
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

		$post_types = apply_filters( 'xmlsf_news_post_types', get_post_types( array( 'public' => true, 'hierarchical' => false ) /*,'objects'*/) );

		if ( is_array($post_types) && !empty($post_types) ) :

			$news_post_type = isset($this->options['post_type']) && !empty( $this->options['post_type'] ) ? (array) $this->options['post_type'] : array('post');

			$type = apply_filters( 'xmlsf_news_post_type_field_type', 1 == count( $news_post_type ) ? 'radio' : 'checkbox' );

			$allowed = ( !empty( $this->options['categories'] ) && isset( $wp_taxonomies['category'] ) ) ? $wp_taxonomies['category']->object_type : $post_types;

			$do_warning = !empty( $this->options['categories'] ) && count($post_types) > 1 ? true : false;

			// The actual fields for data entry
			include XMLSF_DIR . '/views/admin/field-news-post-type.php';

		else :

			echo '<p class="description warning">'.__('There appear to be no post types available.','xml-sitemap-feed').'</p>';

		endif;
	}

	public function terms_checklist_language_filter( $args )
	{
		if ( function_exists('pll_languages_list') ) {
			$args['lang'] = implode( ',', pll_languages_list() );
		} else {
			$args['lang'] = '';
		}

		return $args;
	}

	public function categories_field()
	{
		$selected_categories = isset( $this->options['categories'] ) && is_array( $this->options['categories'] ) ? $this->options['categories'] : array();

		if ( function_exists('pll_languages_list') ) {
			add_filter( 'get_terms_args', function( $args ){ $args['lang'] = implode( ',', pll_languages_list() ); return $args; }/*array( $this, 'terms_checklist_language_filter' )*/ );
		}

		global $sitepress;
		if ( $sitepress ) {
			remove_filter( 'get_terms_args', array( $sitepress, 'get_terms_args_filter' ) );
			remove_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1 );
			remove_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ) );
		}

		$cat_list = str_replace('name="post_category[]"','name="'.'xmlsf_news_tags[categories][]"', wp_terms_checklist( null, array( 'taxonomy' => 'category', 'selected_cats' => $selected_categories, 'echo' => false ) ) );

		// The actual fields for data entry
		include XMLSF_DIR . '/views/admin/field-news-categories.php';
	}

}

new XMLSF_Admin_Sitemap_News();

function xmlsf_news_section_advanced_intro( $active_tab = '' ) {
	if ( 'advanced' == $active_tab )
		include XMLSF_DIR . '/views/admin/section-advanced-intro.php';
}
