<?php

/* ------------------------------
 *    XMLSF Sitemap Core CLASS
 * ------------------------------ */

class XMLSF_Sitemap_Core extends XMLSF_Sitemap
{
	/**
	 * CONSTRUCTOR
	 * Runs on init
	 */
	function __construct( $sitemap )
	{
		$this->sitemap = $sitemap ? $sitemap : 'wp-sitemap.xml';

		$this->post_types = (array) get_option( 'xmlsf_post_types', array() );

		// Pings
		add_action( 'transition_post_status', array( $this, 'do_pings' ), 10, 3 );

		// Cache clearance.
		add_action( 'clean_post_cache', array( $this, 'clean_post_cache'), 99, 2 );

		// Update term meta lastmod date.
		add_action( 'transition_post_status', array( $this, 'update_term_modified_meta' ), 10, 3 );

		// Update user meta lastmod date.
		add_action( 'transition_post_status', array( $this, 'update_user_modified_meta' ), 10, 3 );

		// Update images post meta.
		add_action( 'transition_post_status', array( $this, 'update_post_images_meta' ), 10, 3 );

		// Update last comment date post meta.
		add_action( 'transition_comment_status', array( $this, 'update_post_comment_meta' ), 10, 3 );
		add_action( 'comment_post', array( $this, 'update_post_comment_meta_cp' ), 10, 3 ); // when comment is not held for moderation

		if ( function_exists( 'wp_sitemaps_loaded' ) ) {
			add_action( 'parse_request', array( $this, 'sitemaps_loaded' ), 9);
		} else {
			// MAIN REQUEST filter.
			add_filter( 'request', array( $this, 'filter_request' ) );
			// FIX core sitemap bugs
			add_filter( 'wp_sitemaps_posts_pre_url_list', array( $this, 'posts_url_list' ), 10, 3 );
		}

		// Add lastmod to index.
		add_filter( 'wp_sitemaps_index_entry',               array( $this, 'index_entry' ),        10, 4 );
		// Add lastmod & priority to authors.
		add_filter( 'wp_sitemaps_users_entry',               array( $this, 'users_entry' ),        10, 2 );
		// Add lastmod & priority to terms.
		add_filter( 'wp_sitemaps_taxonomies_entry',          array( $this, 'taxonomies_entry' ),   10, 4 );
		// Add lastmod & priority to posts.
		add_filter( 'wp_sitemaps_posts_entry',               array( $this, 'posts_entry' ),        10, 3 );
		add_filter( 'wp_sitemaps_posts_show_on_front_entry', array( $this, 'posts_show_on_front_entry' ) );

		// Set url limit.
		add_filter( 'wp_sitemaps_max_urls', array( $this, 'max_urls' ), 10, 2 );
		//TODO: for post types

		// Maybe disable taxonomy or author sitemaps.
		add_filter( 'wp_sitemaps_add_provider',     array( $this, 'add_provider' ), 10, 2 );
		// Maybe disable certain post type sitemaps.
		add_filter( 'wp_sitemaps_post_types',       array( $this, 'post_types' ) );
		// Maybe exclude individual posts.
		add_filter(	'wp_sitemaps_posts_query_args',	array( $this, 'posts_query_args' ) );

		// Maybe exclude taxonomies.
		add_filter( 'wp_sitemaps_taxonomies', array( $this, 'taxonomies' ) );

		/**
		 * Add sitemaps.
		 */
		// Maybe add dedicated Media sitemap if image tags are (still) not possible OR completely replace the renderer?
		// TODO

		// TODO
		// add custom post type root pages...
		// EITHER with wp_sitemaps_posts_pre_url_list (replacing the whole posts provider url_list);
			/* if ( $post_type_archive_url = get_post_type_archive_link( $post_type ) ) {
				$sitemap_entry = array( 'loc' => $post_type_archive_url );
				$url_list[] = apply_filters( 'xmlsf_post_type_archive_entry', $sitemap_entry, $post_type );;
			}*/
		// OR with a separate "root" sitemap

		// Additional URLs sitemap provider
		if ( get_option( 'xmlsf_urls' ) ) {
			add_filter(
				'init',
				function() {
					require XMLSF_DIR . '/inc/class.xmlsf-sitemaps-urls.php';
					wp_register_sitemap_provider( 'urls', new XMLSF_Sitemaps_URLs() );
				},
				11
			);
		}
		// External XML Sitemaps provider
		if ( get_option( 'xmlsf_custom_sitemaps' ) ) {
			add_filter(
				'init',
				function() {
					require XMLSF_DIR . '/inc/class.xmlsf-sitemaps-custom.php';
					wp_register_sitemap_provider( 'custom', new XMLSF_Sitemaps_Custom() );
				},
				11
			);
		}

		// Stylesheet
		add_filter( 'wp_sitemaps_stylesheet_index_url', array( $this, 'stylesheet_index_url' ) );
		add_filter( 'wp_sitemaps_stylesheet_url',       array( $this, 'stylesheet_url' ) );

		// NGINX HELPER PURGE URLS
		add_filter( 'rt_nginx_helper_purge_urls', array( $this, 'nginx_helper_purge_urls' ) );
	}

	/**
	 * Filter request
	 *
	 * @param WP $wp
	 *
	 * @return void
	 */
	public function sitemaps_loaded( $wp )
	{
		if ( ! empty( $wp->query_vars['sitemap'] ) ) {
			// Include public sitemap functions.
			require_once XMLSF_DIR . '/inc/functions.public-sitemap.php';

			// Debugging.
			if ( ! is_admin() && current_user_can('manage_options') ) {
				add_action( 'shutdown', 'xmlsf_usage' );
			}
		}
	}

	/**
	 * Filter request
	 *
	 * @param array $request
	 *
	 * @return array $request filtered
	 */
	public function filter_request( $request )
	{
		global $xmlsf;
		$xmlsf->request_filtered = true;

		if ( ! empty( $request['sitemap'] ) ) {
			/** IT'S A SITEMAP */
			// Set the sitemap conditional flag.
			$xmlsf->is_sitemap = true;

			// Make sure we have the proper locale setting for calculations.
			setlocale( LC_NUMERIC, 'C' );

			// save a few db queries
			//add_filter( 'split_the_query', '__return_false' ); // TODO test?

			// Include public sitemap functions.
			require_once XMLSF_DIR . '/inc/functions.public-sitemap.php';

			// Debugging.
			if ( ! is_admin() && current_user_can('manage_options') ) {
				add_action( 'shutdown', 'xmlsf_usage' );
			}

			/** FILTER HOOK FOR PLUGIN COMPATIBILITIES */
			//$request = apply_filters( 'xmlsf_request', $request );
			/**
			 * Developers: add your actions that should run when a sitemap request is with:
			 *
			 * add_filter( 'xmlsf_request', 'your_filter_function' );
			 *
			 * Filters hooked here already:
			 * ! functions.public-sitemap.php
			 */

			$subtype = isset( $request['sitemap-subtype'] ) ? $request['sitemap-subtype'] : '';

			switch( $request['sitemap'] ) {

				case 'posts':
					// Try to raise memory limit, context added for filters.
					wp_raise_memory_limit( 'wp-sitemap-posts-'.$subtype );

					// Alter main query request parameters to fit wp-sitemap.
					$request['orderby']                = 'modified';
					$request['order']                  = 'DESC';
					$request['ignore_sticky_posts']    = true;
					$request['post_type']              = $subtype;
					$request['posts_per_page']         = wp_sitemaps_get_max_urls( 'post' );
					$request['post_status']            = array( 'publish' );
					$request['no_found_rows']          = true;
					$request['update_post_term_cache'] = false;
					$request['update_post_meta_cache'] = false;

					// Apply wp-sitemap filter.
					$request = apply_filters(
						'wp_sitemaps_posts_query_args',
						$request,
						$subtype
					);

					// Prepare priority calculation.
					if ( $subtype && ! empty($this->post_types[$subtype]['dynamic_priority']) ) {
						// Last of this post type modified date in Unix seconds.
						xmlsf()->lastmodified = get_date_from_gmt( get_lastpostmodified( 'GMT', $subtype ), 'U' );
						// Calculate time span, uses get_firstpostdate() function defined in xml-sitemap/inc/functions.php!
						xmlsf()->timespan = xmlsf()->lastmodified - get_date_from_gmt( get_firstpostdate( 'GMT', $subtype), 'U' );
						// Total post type comment count.
						xmlsf()->comment_count = wp_count_comments()->approved;
						// TODO count comments per post type https://wordpress.stackexchange.com/questions/134338/count-all-comments-of-a-custom-post-type
						// TODO cache this more persistently than wp_cache_set does in https://developer.wordpress.org/reference/functions/wp_count_comments/
					};
					break;

				case 'taxonomies':
					// Try to raise memory limit, context added for filters.
					wp_raise_memory_limit( 'wp-sitemap-taxonomies-'.$subtype );
					// Make sure the term query returns term objects, not just ids. See bugreport: https://core.trac.wordpress.org/ticket/55239
					add_filter( 'wp_sitemaps_taxonomies_query_args', function($args) { $args['fields'] = 'all'; return $args; } );
					break;

				case 'users':
					// Try to raise memory limit, context added for filters.
					wp_raise_memory_limit( 'wp-sitemap-users' );
					break;

				case 'urls':
					// Try to raise memory limit, context added for filters.
					wp_raise_memory_limit( 'wp-sitemap-urls' );
					break;

				case 'custom':
				default:
					// Try to raise memory limit, context added for filters.
					wp_raise_memory_limit( 'wp-sitemap-custom' );
					// Do nothing.
			}
		}

		return $request;
	}

	/**
	 * Add lastmod to index entries.
	 * Hooked into wp_sitemaps_index_entry filter.
	 *
	 * @since 5.4
	 *
	 * @param  array  $entry
	 * @param  string $type
	 * @param  string $subtype
	 * @param  int    $page
	 *
	 * @return array  $entry
	 */
	public function index_entry( $entry, $type, $subtype, $page )
	{
		// Add lastmod.
		// TODO account for $page ... but how?
		switch( $type ) {
			case 'post':
				$entry['lastmod'] = get_date_from_gmt( get_lastpostmodified( 'GMT', $subtype ), DATE_W3C );
				break;

			case 'term':
				$entry['lastmod'] = xmlsf_get_taxonomy_modified( $subtype );
				break;

			case 'user':
				// TODO make this xmlsf_author_post_types filter compatible.
				$entry['lastmod'] = get_date_from_gmt( get_lastpostdate( 'GMT', 'post' ), DATE_W3C ); // Absolute last post date.
				break;

			default:
				// Do nothing.
		}

		return $entry;
	}

	/**
	 * Add priority and lastmod to author entries.
	 * Hooked into wp_sitemaps_users_entry filter.
	 *
	 * @since 5.4
	 *
	 * @param  array  $entry
	 * @param  obj    $user_object
	 *
	 * @return array  $entry
	 */
	public function users_entry( $entry, $user_object )
	{
		// Add priority.
		$entry['priority'] = xmlsf_get_user_priority( $user_object );

		// Add lastmod.
		$entry['lastmod'] = xmlsf_get_user_modified( $user_object );

		return $entry;
	}

	/**
	 * Add priority and lastmod to taxonomy entries.
	 * Hooked into wp_sitemaps_taxonomies_entry filter.
	 *
	 * @since 5.4
	 *
	 * @param  array     $entry
	 * @param  int|obj   $term         Either the term ID or the WP_Term object depending on query arguments (WP 5.9)
	 * @param  string    $taxonomy
	 * @param  obj|null  $term_object  The WP_Term object, available starting WP 6.0 otherwise null
	 *
	 * @return array     $entry
	 */
	public function taxonomies_entry( $entry, $term, $taxonomy, $term_object = null )
	{
		// Make sure we have a WP_Term object.
		if ( null === $term_object ) {
			$term_object = get_term( $term );
		}

		// Add priority.
		$entry['priority'] = xmlsf_get_term_priority( $term_object );

		// Add lastmod.
		$entry['lastmod'] = xmlsf_get_term_modified( $term_object );

		return $entry;
	}

	/**
	 * Maybe exclude taxonomies.
	 * Hooked into wp_sitemaps_taxonomies filter.
	 *
	 * @since 5.4
	 *
	 * @param  array $taxonomies
	 * @return array  $taxonomies
	 */
	public function taxonomies( $taxonomies )
	{
		$only = get_option( 'xmlsf_taxonomies' );

		if ( empty( $only ) || ! is_array( $only ) ) {
			return $taxonomies;
		}

		return array_filter( $taxonomies, function($tax) use($only) { return in_array( $tax->name, $only ); } );
	}

	/**
	 * Add priority and lastmod to posts entries.
	 * Hooked into wp_sitemaps_posts_entry filter.
	 *
	 * @since 5.4
	 *
	 * @param  array  $entry
	 * @param  obj    $post_object
	 * @param  string $post_type
	 *
	 * @return array  $entry
	 */
	public function posts_entry( $entry, $post_object, $post_type )
	{
		// Add priority.
		$entry['priority'] = xmlsf_get_post_priority( $post_object );

		// Add lastmod.
		$entry['lastmod'] = xmlsf_get_post_modified( $post_object );

		return $entry;
	}

	/**
	 * Add priority and lastmod to posts show on front entry.
	 * Hooked into wp_sitemaps_posts_show_on_front_entry filter.
	 *
	 * @since 5.4
	 *
	 * @param  array  $entry
	 *
	 * @return array  $entry
	 */
	public function posts_show_on_front_entry( $entry )
	{
		// Front blog page priority always 1.
		$entry['priority'] = '1';

		// Set front blog page lastmod to last modified post.
		$entry['lastmod'] = get_lastpostmodified( 'gmt', 'post' );

		return $entry;
	}

	/**
	 * Filter maximum urls per sitemap. Hooked into wp_sitemaps_max_urls filter.
	 *
	 * @since 5.4
	 *
	 * @param  int    $max_urls
	 * @param  string $object_type
	 *
	 * @return int    $max_urls
	 */
	function max_urls( $max_urls, $object_type )
	{
		switch( $object_type ) {
			case 'user':
				$settings = (array) get_option( 'xmlsf_author_settings' );
				$max_urls = ! empty( $settings['limit'] ) && is_numeric( $settings['limit'] ) ? absint( $settings['limit'] ) : $max_urls;
				break;

			case 'term':
				$settings = (array) get_option( 'xmlsf_taxonomy_settings' );
				$max_urls = ! empty( $settings['limit'] ) && is_numeric( $settings['limit'] ) ? absint( $settings['limit'] ) : $max_urls;
				break;

			case 'post':
			default:
				$settings = (array) get_option( 'xmlsf_general_settings' );
				$max_urls = ! empty( $settings['limit'] ) && is_numeric( $settings['limit'] ) ? absint( $settings['limit'] ) : $max_urls;
		}

		return $max_urls;
	}

	/**
	 * Filter sitemap providers. Hooked into wp_sitemaps_add_provider filter.
	 *
	 * @since 5.4
	 *
	 * @param  obj       $provider
	 * @param  string    $name
	 *
	 * @return false|obj $provider or false if disabled
	 */
	public function add_provider( $provider, $name )
	{
		// Verify author sitemap settings.
		if ( 'users' === $name ) {
			$settings = (array) get_option( 'xmlsf_author_settings' );
			return empty( $settings['active'] ) ? false : $provider;
		}
		// Verify taxonomy sitemaps settings.
		if ( 'taxonomies' === $name ) {
			$settings = (array) get_option( 'xmlsf_taxonomy_settings' );
			$taxonomies = xmlsf_public_taxonomies();
			return empty( $settings['active'] ) || empty( $taxonomies ) ? false : $provider;
		}

		return $provider;
	}

	/**
	 * Filter post types. Hooked into wp_sitemaps_post_types filter.
	 *
	 * @since 5.4
	 *
	 * @param  array $post_types
	 *
	 * @return array $post_types
	 */
	public function post_types( $post_types )
	{
		foreach ( $this->post_types as $post_type => $settings ) {
			if ( empty( $settings['active'] ) ) {
				unset( $post_types[$post_type] );
			} else {
				if ( ! isset( $post_types[$post_type] ) ) {
					$post_types[] = $post_type;
				}
			}
		}

		return $post_types;
	}

	/**
	 * Filter post query arguments. Hooked into wp_sitemaps_posts_query_args filter.
	 *
	 * @since 5.4
	 *
	 * @param  array $args
	 * @return array $args
	 */
	public function posts_query_args( $args )
	{
		// Exclude posts.
		$args['meta_query'] = array(
			array(
				'key' => '_xmlsf_exclude',
				'compare' => 'NOT EXISTS'
			)
		);

		// Update meta cache in one query instead of many, coming from get_post_meta() in xmlsf_get_post_priority().
		$args['update_post_meta_cache'] = true;

		return $args;
	}

	/**
	 * Get alternative stylesheet URL. Hooked into wp_sitemaps_stylesheet_url filter.
	 *
	 * @since 5.4
	 *
	 * @param  string $url
	 * @return string $url
	 */
	public function stylesheet_url( $url )
	{
		//if ( ! get_option( 'xmlsf_core_sitemap_stylesheet' ) ) { // TODO make these match sitemap type
			$url = xmlsf_get_stylesheet_url( 'root' );
		//}

		return $url;
	}

	/**
	 * Get alternative index stylesheet URL. Hooked into wp_sitemaps_stylesheet_index_url filter.
	 *
	 * @since 5.4
	 *
	 * @param  string $url
	 * @return string $url
	 */
	public function stylesheet_index_url( $url )
	{
		//if ( ! get_option( 'xmlsf_core_sitemap_stylesheet' ) ) {
			$url = xmlsf_get_stylesheet_url();
		//}

		return $url;
	}

	/**
	 * Nginx helper purge urls
	 * adds sitemap urls to the purge array.
	 *
	 * @since 5.4
	 *
	 * @param  array $urls
	 * @param  bool  $redis default false
	 *
	 * @return array $urls
	 */
	public function nginx_helper_purge_urls( $urls = array(), $wildcard = false )
	{
		if ( $wildcard ) {
			// wildcard allowed, this makes everything simple
			$urls[] = '/wp-sitemap*.xml';
		} else {
			// no wildcard, go through the motions
			$urls[] = '/wp-sitemap.xml';
			$urls[] = '/wp-sitemap-custom.xml';

			// TODO use wp_get_sitemap_providers for array of provider names (where array key is provider name)
			// then use $provider->get_sitemap_type_data() for nested arrays with max number of sitemaps for each subtype
			// then use that data to build urls... /wp-sitemap-PROVIDER-SUBTYPENAME-PAGENUM++.xml

			$sitemaps = wp_sitemaps_get_server();
			foreach ( $sitemaps->index->get_sitemap_list() as $sitemap ) {
				// Add each element loc value.
				$urls[] = parse_url( $sitemap['loc'], PHP_URL_PATH);
			}
		}

		if ( defined('WP_DEBUG') && WP_DEBUG ) {
			error_log( 'NGINX Helper purge urls array:' );
			error_log( print_r( $urls, true ) );
		}

		return $urls;
	}

	/**
	 * Uses the main query to get the posts URL list before it is generated.
	 * Hooked into wp_sitemaps_posts_pre_url_list.
	 *
	 * @since 5.4
	 *
	 * @param array[]|null $url_list  The URL list. Default null.
	 * @param string       $post_type Post type name.
	 * @param int          $page_num  Page of results.
	 * @return array[]     The URL list.
	 */
	public function posts_url_list( $url_list, $post_type, $page_num )
	{
		global $wp_query;

		if ( null === $wp_query->posts ) {
			return $url_list;
		}

		$url_list = array();

		/*
		 * Add a URL for the homepage in the pages sitemap.
		 * Shows only on the first page if the reading settings are set to display latest posts.
		 */
		if ( 'page' === $post_type && 1 === $page_num && 'posts' === get_option( 'show_on_front' ) ) {
			// Extract the data needed for home URL to add to the array.
			$sitemap_entry = array(
				'loc' => home_url( '/' ),
			);

			/**
			 * Filters the sitemap entry for the home page when the 'show_on_front' option equals 'posts'.
			 *
			 * @since 5.4
			 *
			 * @param array $sitemap_entry Sitemap entry for the home page.
			 */
			$sitemap_entry = apply_filters( 'wp_sitemaps_posts_show_on_front_entry', $sitemap_entry );
			$url_list[]    = $sitemap_entry;
		}

		foreach ( $wp_query->posts as $post ) {
			$sitemap_entry = array(
				'loc' => get_permalink( $post ),
			);

			/**
			 * Filters the sitemap entry for an individual post.
			 *
			 * @since 5.4
			 *
			 * @param array   $sitemap_entry Sitemap entry for the post.
			 * @param WP_Post $post          Post object.
			 * @param string  $post_type     Name of the post_type.
			 */
			$sitemap_entry = apply_filters( 'wp_sitemaps_posts_entry', $sitemap_entry, $post, $post_type );
			$url_list[]    = $sitemap_entry;
		}

		return $url_list;
	}

}
