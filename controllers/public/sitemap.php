<?php
/**
* FEED TEMPLATES
*/

/**
 * Set up the sitemap index template
 */
function xmlsf_load_template_index() {
	load_template( XMLSF_DIR . '/views/feed-sitemap.php' );
}

/**
 * set up the sitemap home page(s) template
 */
function xmlsf_load_template_home() {
	load_template( XMLSF_DIR . '/views/feed-sitemap-home.php' );
}

/**
 * set up the post types sitemap template
 */
function xmlsf_load_template() {
	load_template( XMLSF_DIR . '/views/feed-sitemap-post_type.php' );
}

/**
 * set up the taxonomy sitemap template
 */
function xmlsf_load_template_taxonomy() {
	load_template( XMLSF_DIR . '/views/feed-sitemap-taxonomy.php' );
}

/**
 * set up the custom sitemap template
 */
function xmlsf_load_template_custom() {
	load_template( XMLSF_DIR . '/views/feed-sitemap-custom.php' );
}

/**
 * Do feed templates
 */
function xmlsf_feed_templates() {
	$sitemaps = get_option( 'xmlsf_sitemaps' );

	if ( is_array($sitemaps) && isset($sitemaps['sitemap'])) {
		// setup feed templates
		add_action( 'do_feed_sitemap', 'xmlsf_load_template_index', 10, 1 );
		add_action( 'do_feed_sitemap_index', 'xmlsf_load_template_index', 10, 1 );
		add_action( 'do_feed_sitemap-home', 'xmlsf_load_template_home', 10, 1 );
		add_action( 'do_feed_sitemap-custom', 'xmlsf_load_template_custom', 10, 1 );

		$post_types = get_option('xmlsf_post_types');
		if ( is_array($post_types) ) {
			foreach ( $post_types as $post_type => $settings ) {
				if ( !empty($settings['active']) )
					add_action( 'do_feed_sitemap-posttype-'.$post_type, 'xmlsf_load_template', 10, 1 );
			}
		}
		foreach ( xmlsf_get_taxonomies() as $name ) {
			add_action( 'do_feed_sitemap-taxonomy-'.$name, 'xmlsf_load_template_taxonomy', 10, 1 );
		}
	}
}

/* -------------------------------------
 *      MISSING WORDPRESS FUNCTIONS
 * ------------------------------------- */

/**
 * Retrieve the date that the first post/page was published.
 * Variation of function get_lastpostdate, uses _get_post_time
 *
 * The server timezone is the default and is the difference between GMT and
 * server time. The 'blog' value is the date when the last post was posted. The
 * 'gmt' is when the last post was posted in GMT formatted date.
 *
 * @uses apply_filters() Calls 'get_firstpostdate' filter
 * @param string $timezone The location to get the time. Can be 'gmt', 'blog', or 'server'.
 * @param string $post_type Post type to check.
 * @return string The date of the last post.
 */
if( !function_exists('get_firstpostdate') ) {
 function get_firstpostdate($timezone = 'server', $post_type = 'any') {
	return apply_filters( 'get_firstpostdate', _get_post_time( $timezone, 'date', $post_type, 'first' ), $timezone );
 }
}

/**
 * Retrieve last post/page modified date depending on timezone.
 * Variation of function get_lastpostmodified, uses _get_post_time
 *
 * The server timezone is the default and is the difference between GMT and
 * server time. The 'blog' value is the date when the last post was posted. The
 * 'gmt' is when the last post was posted in GMT formatted date.
 *
 * @uses apply_filters() Calls 'get_lastmodified' filter
 * @param string $timezone The location to get the time. Can be 'gmt', 'blog', or 'server'.
 * @return string The date of the oldest modified post.
 */
if( !function_exists('get_lastmodified') ) {
 function get_lastmodified( $timezone = 'server', $post_type = 'any', $m = '' ) {
	return apply_filters( 'get_lastmodified', _get_post_time( $timezone, 'modified', $post_type, 'last', $m ), $timezone );
 }
}
