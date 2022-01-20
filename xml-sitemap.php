<?php
/*
Plugin Name: XML Sitemap & Google News
Plugin URI: https://status301.net/wordpress-plugins/xml-sitemap-feed/
Description: Feed the hungry spiders in compliance with the XML Sitemap and Google News protocols. Happy with the results? Please leave me a <strong><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=XML%20Sitemap%20Feed">tip</a></strong> for continued development and support. Thanks :)
Version: 5.3.3
Text Domain: xml-sitemap-feed
Requires at least: 4.6
Requires PHP: 5.6
Author: RavanH
Author URI: https://status301.net/
*/

define( 'XMLSF_VERSION', '5.3.3' );
/**
 * Copyright 2021 RavanH
 * https://status301.net/
 * mailto: ravanhagen@gmail.com

 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as
 * published by the Free Software Foundation.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

/**
 * --------------------
 *  AVAILABLE HOOKS
 * --------------------
 *
 *	FILTERS
 *
 *	xmlsf_defaults             -> Filters the default array values for different option groups.
 *	xmlsf_request              -> Filters request when an xml sitemap request is found,
 *	                              can be used for plugin compatibility.
 *	xmlsf_news_request         -> Filters request when a news sitemap request is found
 *	                              can be used for plugin compatibility.
 *	xmlsf_allowed_domain       -> Filters the response when checking the url against allowed domains.
 *	                              Passes variable $url; must return true or false.
 *	xmlsf_index_url_args       -> Filters the index url arguments array
 *	xmlsf_excluded             -> Filters the response when checking the post for exclusion flags in
 *	                              XML Sitemap context. Passes the post exclusion flag and $post_id; must return true or false.
 *	xmlsf_news_excluded        -> Filters the response when checking the post for exclusion flags in
 *	                              Google News sitemap context. Passes variable $post_id; must exclusion flag, return true or false.
 *	xmlsf_news_keywords        -> Filters the news keywords array
 *	xmlsf_news_stock_tickers   -> Filters the news stock tickers array
 *	xmlsf_disabled_taxonomies  -> Filters the taxonomies that should be unavailable for sitemaps
 *	                              Passes an array of taxonomies to exclude; must return an array.
 *	the_title_xmlsitemap       -> Filters the Image title and caption tags.
 *	xmlsf_news_publication_name-> Filters the Google News publication name.
 *	xmlsf_news_title           -> Filters the Google News post title.
 *	xmlsf_root_data            -> Filters the root data urls (with priority and lastmod) array
 *	xmlsf_custom_urls          -> Filters the custom urls array
 *	xmlsf_custom_sitemaps      -> Filters the custom sitemaps array
 *	xmlsf_post_language        -> Filters the post language tag used in the news sitemap.
 *	                              Passes variable $post_id; must return a 2 or 3 letter
 *	                              language ISO 639 code with the exception of zh-cn and zh-tw.
 *	xmlsf_post_types           -> Filters the post types array for the XML sitemaps index.
 *	xmlsf_post_priority        -> Filters a post priority value. Passes variables $priority and $post->ID.
 *	                              Must return a float value between 0.1 and 1.0
 *	xmlsf_term_priority        -> Filters a taxonomy term priority value. Passes variables $priority and $term->slug.
 *	                              Must return a float value between 0.1 and 1.0
 *	xmlsf_news_post_types      -> Filters the post types array for the Google News sitemap settings page.
 *
 *	ACTIONS
 *
 *	xmlsf_ping                 -> Fires when a search engine has been pinged. Carries four arguments:
 *	                              search engine (google|bing), sitemap name, full ping url, ping repsonse code.
 *	xmlsf_urlset               -> Fired inside each sitemap's urlset tag. Can be used to
 *	                              echo additional XML namespaces. Passes parameter home|post_type|taxonomy|custom
 *	                              to allow identification of the current sitemap.
 *	xmlsf_url                  -> Fired inside the XML Sitemap loop at the start of each sitemap url tag. Can be used to
 *	                              echo additional XML namespaces. Passes parameter home|post_type|taxonomy|custom
 *	                              to allow identification of the current sitemap.
 *	xmlsf_image_tags_inner     -> Fired inside the XML Sitemap loop just before each closing </image:image> is generated. Can be used to
 *	                              echo custom <image:image> tags or trigger another action in the background.
 *	xmlsf_tags_after           -> Fired inside the XML Sitemap loop at the end of the tags, just before each
 *	                              closing </url> is generated. Can be used to echo custom tags or trigger another
 *	                              action in the background. Passes parameter home|post_type|taxonomy|custom
 *	                              to allow identification of the current sitemap.
 *	xmlsf_url_after            -> Fired inside the XML Sitemap loop after each url node. Can be used to append
 *	                              alternative url or trigger another action in the background. Passes parameter
 *	                              home|post_type|taxonomy|custom to allow identification of the current sitemap.
 *	xmlsf_news_urlset          -> Fired inside the Google News Sitemap urlset tag. Can be used to
 *	                              echo additional XML namespaces.
 *	xmlsf_news_tags_inner      -> Fired inside the Google News Sitemap loop at the end of the news
 *	                              tags, just before each closing </news:news> is generated. Can be used to
 *	                              echo custom news:news tags or trigger another action in the background.
 *	xmlsf_news_tags_after      -> Fired inside the Google News Sitemap loop at the end of the news
 *	                              tags, just before each closing </url> is generated. Can be used to
 *	                              echo custom news tags or trigger another action in the background.
 *	xmlsf_news_url_after       -> Fired inside the Google News Sitemap loop after each news url node.
 *	                              Can be used to append alternative url or trigger another action in the background.
 *	xmlsf_news_settings_before -> Fired before the Google News Sitemap settings form
 *	xmlsf_news_settings_after  -> Fired after the Google News Sitemap settings form
 *
 * ---------------------
 *  AVAILABLE FUNCTIONS
 * ---------------------
 *
 *  is_sitemap() -> conditional, returns bolean, true if the request is for an xml sitemap
 *  is_news()    -> conditional, returns bolean, true if the request is for an xml news sitemap
 *
 *  Feel free to request, suggest or submit more :)
 */

if ( ! defined( 'WPINC' ) ) die;

define( 'XMLSF_DIR', dirname(__FILE__) );

define( 'XMLSF_BASENAME', plugin_basename(__FILE__) );

// main plugin init
add_action( 'init', 'xmlsf_init', 9 );

register_activation_hook( __FILE__, 'xmlsf_activate' );

register_deactivation_hook( __FILE__, 'xmlsf_deactivate' );

/***********************
 *     CONTROLLERS
 ***********************/

/**
 * Plugin initialization
 *
 * @since 1.0
 * @return void
 */
function xmlsf_init() {

	// add robots.txt filter
	add_filter( 'robots_txt', 'xmlsf_robots_txt', 9 );

	// Upgrade/install, maybe...
	$db_version = get_option( 'xmlsf_version', 0 );
	if ( ! version_compare( XMLSF_VERSION, $db_version, '=' ) ) {
		require_once XMLSF_DIR . '/upgrade.php';
		new XMLSitemapFeed_Upgrade( $db_version );
	}

	if ( is_admin() ) {
		require XMLSF_DIR . '/controllers/class.xmlsf-admin.php';
		new XMLSF_Admin();
	}

	$sitemaps = get_option( 'xmlsf_sitemaps' );

	// return if nothing enabled
	if ( empty( $sitemaps ) ) return;

	// main model functions
	require XMLSF_DIR . '/models/functions.shared.php';

	// force remove url trailing slash
	add_filter( 'user_trailingslashit', 'xmlsf_untrailingslash' );

	// MAIN REQUEST filter
	add_filter( 'request', 'xmlsf_filter_request', 0 );

	// NGINX HELPER PURGE URLS
	add_filter( 'rt_nginx_helper_purge_urls', 'xmlsf_nginx_helper_purge_urls', 10, 2 );

	// main controller functions
	require XMLSF_DIR . '/controllers/functions.shared.php';

	add_action( 'xmlsf_ping', 'xmlsf_debug_ping', 9, 4 );

	// include and instantiate main class
	xmlsf();

	if ( ! empty( $sitemaps['sitemap'] ) ) {
		//add_rewrite_rule('sitemap(?:_index)?(\-[a-z0-9\-_]+)?(\.\d{4,6})?(\.\d{1,2})?\.xml(\.gz)?$', 'index.php?feed=sitemap$matches[1]$matches[4]&m=$matches[2]&w=$matches[3]', 'top');

		require XMLSF_DIR . '/models/functions.sitemap.php';
		add_filter( 'xmlsf_post_types', 'xmlsf_filter_post_types' );

		// sitemap title element filters
		add_filter( 'the_title_xmlsitemap', 'strip_tags' );
		add_filter( 'the_title_xmlsitemap', 'ent2ncr', 8 );
		add_filter( 'the_title_xmlsitemap', 'esc_html' );

		global $xmlsf_sitemap;
		require XMLSF_DIR . '/controllers/class.xmlsf-sitemap.php';
		$xmlsf_sitemap = new XMLSF_Sitemap( $sitemaps['sitemap'] );

		// replace core sitemap
		remove_action( 'init', 'wp_sitemaps_get_server' );
	}

	if ( ! empty( $sitemaps['sitemap-news'] ) ) {
		//add_rewrite_rule('sitemap-news\.xml(\.gz)?$', 'index.php?feed=sitemap-news$matches[1]', 'top');

		require XMLSF_DIR . '/models/functions.sitemap-news.php';
		add_filter( 'xmlsf_news_post_types', 'xmlsf_news_filter_post_types' );

		// common sitemap element filters
		add_filter( 'xmlsf_news_publication_name', 'strip_tags' );
		add_filter( 'xmlsf_news_publication_name', 'ent2ncr', 8 );
		add_filter( 'xmlsf_news_publication_name', 'esc_html' );
		add_filter( 'xmlsf_news_title', 'strip_tags' );
		add_filter( 'xmlsf_news_title', 'ent2ncr', 8 );
		add_filter( 'xmlsf_news_title', 'esc_html' );

		require XMLSF_DIR . '/controllers/class.xmlsf-sitemap-news.php';
		new XMLSF_Sitemap_News( $sitemaps['sitemap-news'] );
	}

}

/**
 * Plugin activation
 *
 * @since 5.0
 * @return void
 */
function xmlsf_activate() {
	set_transient( 'xmlsf_flush_rewrite_rules', '' );
	set_transient( 'xmlsf_check_static_files', '' );
}

/**
 * Plugin de-activation
 *
 * @since 5.0
 * @return void
 */
function xmlsf_deactivate() {
	delete_transient( 'xmlsf_flush_rewrite_rules' );
	delete_transient( 'xmlsf_check_static_files' );

	// remove metadata
	global $wpdb;
	// posts meta
	$wpdb->delete( $wpdb->prefix.'postmeta', array( 'meta_key' => '_xmlsf_image_attached' ) );
	$wpdb->delete( $wpdb->prefix.'postmeta', array( 'meta_key' => '_xmlsf_image_featured' ) );
	$wpdb->delete( $wpdb->prefix.'postmeta', array( 'meta_key' => '_xmlsf_comment_date_gmt' ) );
	// terms meta
	$wpdb->delete( $wpdb->prefix.'termmeta', array( 'meta_key' => 'term_modified' ) );

	// remove rules so they can be REGENERATED on the next page load (without this plugin active)
	delete_option( 'rewrite_rules' );
	
}

/*****************
 *     MODELS
 *****************/

/**
 * Get instantiated sitemap class
 *
 * @since 5.0
 *
 * @global XMLSitemapFeed $xmlsf
 * @return XMLSitemapFeed object
 */
function xmlsf() {
	global $xmlsf;

	if ( ! isset( $xmlsf ) ) {
		if ( ! class_exists( 'XMLSitemapFeed' ) )
			require XMLSF_DIR . '/models/class-xmlsitemapfeed.php';

		$xmlsf = new XMLSitemapFeed();
	}

	return $xmlsf;
}

/**
 * Filter robots.txt rules
 *
 * @param $output
 * @return string
 */
function xmlsf_robots_txt( $output ) {
	$url = trailingslashit( get_bloginfo('url') );

	$sitemaps = get_option( 'xmlsf_sitemaps' );

	// PRE
	$pre = '# XML Sitemap & Google News version ' . XMLSF_VERSION . ' - https://status301.net/wordpress-plugins/xml-sitemap-feed/' . PHP_EOL;
	if ( '1' != get_option('blog_public') )
		$pre .= '# XML Sitemaps are disabled because of this site\'s privacy settings.' . PHP_EOL;
	elseif( !is_array($sitemaps) || empty( $sitemaps ) )
		$pre .= '# No XML Sitemaps are enabled on this site.' . PHP_EOL;
	else
		foreach ( $sitemaps as $pretty )
			$pre .= 'Sitemap: ' . $url . $pretty . PHP_EOL;
	$pre .= PHP_EOL;

	// DEFAULT
	if ( substr($output, -1) !== PHP_EOL ) $output .= PHP_EOL;

	// POST
	$post = get_option('xmlsf_robots');
	if ( $post !== '' ) $post .= PHP_EOL;

	return $pre . $output . $post;
}

/* --------------------------------
 *     CONDITIONAL FUNCTIONS
 * -------------------------------- */

/**
 * Is the query for a sitemap?
 *
 * @since 4.8
 * @return bool
 */
function is_sitemap() {
	global $xmlsf;
	if ( ! is_object( $xmlsf ) || $xmlsf->request_filtered === false ) {
		_doing_it_wrong( __FUNCTION__, __( 'Conditional sitemap tags do not work before the sitemap request filter is run. Before then, they always return false.', 'xml-sitemap-feed' ), '4.8' );
		return false;
	}
	return $xmlsf->is_sitemap;
}

/**
 * Is the query for a news sitemap?
 *
 * @since 4.8
 * @return bool
 */
function is_news() {
	global $xmlsf;
	if ( ! is_object( $xmlsf ) || $xmlsf->request_filtered === false ) {
		_doing_it_wrong( __FUNCTION__, __( 'Conditional sitemap tags do not work before the sitemap request filter is run. Before then, they always return false.', 'xml-sitemap-feed' ), '4.8' );
		return false;
	}
	return $xmlsf->is_news;
}

// TODO start with namespacing and autoload
// http://justintadlock.com/archives/2018/12/14/php-namespaces-for-wordpress-developers
/*
spl_autoload_register( function( $class ) {

	$namespace = 'XMLSF\\';

	// Bail if the class is not in our namespace.
	if ( 0 !== strpos( $class, $namespace ) ) {
		return;
	}

	// Build the filename.
	$class = str_replace( $namespace, '', $class );
	$class = strtolower( $class );
	$class = str_replace( '_', '-', $class );
	$file = realpath( __DIR__ ) . DIRECTORY_SEPARATOR . str_replace( '\\', DIRECTORY_SEPARATOR, $class ) . '.php';

	// If the file exists for the class name, load it.
	if ( file_exists( $file ) ) {
		include( $file );
	}
} );
*/
