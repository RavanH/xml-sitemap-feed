<?php

/* ------------------------------
 *      XMLSF Controller CLASS
 * ------------------------------ */

class XMLSF_Sitemap_Controller
{
	/**
	 * Sitemap index name
	 * @var string
	 */
	private $sitemap = 'sitemap.xml';

	/**
	 * Post types included in sitemap index
	 * @var array
	 */
	private $post_types;

	/**
	 * CONSTRUCTOR
	 * Runs on init
	 */

	function __construct( $sitemap )
	{
		$this->sitemap = $sitemap;

		// Cache clearance
		add_action( 'clean_post_cache', array($this,'clean_post_cache'), 99, 2 );

		// Update term meta lastmod date
		add_action( 'transition_post_status', array($this,'update_term_modified_meta'), 10, 3 );

		// PINGING
		add_action( 'transition_post_status', array($this,'do_pings'), 10, 3 );

		// FEED TEMPLATES
		add_action( 'do_feed_sitemap', 'xmlsf_load_template_index', 10, 1 );
		add_action( 'do_feed_sitemap_index', 'xmlsf_load_template_index', 10, 1 );
		add_action( 'do_feed_sitemap-home', 'xmlsf_load_template_home', 10, 1 );
		add_action( 'do_feed_sitemap-custom', 'xmlsf_load_template_custom', 10, 1 );

		$this->post_types = get_option( 'xmlsf_post_types' );

		if ( is_array($this->post_types) ) {
			foreach ( $this->post_types as $post_type => $settings ) {
				if ( !empty($settings['active']) )
					add_action( 'do_feed_sitemap-posttype-'.$post_type, 'xmlsf_load_template', 10, 1 );
			}
		}

		foreach ( xmlsf_get_taxonomies() as $name ) {
			add_action( 'do_feed_sitemap-taxonomy-'.$name, 'xmlsf_load_template_taxonomy', 10, 1 );
		}
	}

	/**
	 * Do pings, hooked to transition post status
	 *
	 * @param $new_status
	 * @param $old_status
	 * @param $post
	 */
	public function do_pings( $new_status, $old_status, $post ) {
		// are we publishing?
		if ( $old_status == 'publish' || $new_status != 'publish' )
			return;

		$ping = get_option( 'xmlsf_ping' );

		if ( empty( $ping ) )
			return;

		// check if we've got a post type that is included in our sitemap
		$post_types = get_option( 'xmlsf_post_types' );
		if ( array_key_exists( $post->post_type, (array) $this->post_types ) ) {

			foreach ( $ping as $se ) {
				xmlsf_ping( $se, $this->sitemap, HOUR_IN_SECONDS );
			}
		}
	}

	/**
	 * Cache delete on clean_post_cache
	 *
	 * @param $post_ID
	 * @param $post
	 */
	public function clean_post_cache( $post_ID, $post ) {
		// are we moving the post in or out of published status?
		wp_cache_delete( 'xmlsf_get_archives', 'general' );

		// TODO get year / month here to delete specific keys too !!!!
		$m = mysql2date( 'Ym', $post->post_date_gmt, false );
		$y = substr( $m, 0, 4 );

		// clear possible last post modified cache keys
		wp_cache_delete( 'lastpostmodified:gmt', 'timeinfo' ); // should be handled by WP core?
		wp_cache_delete( 'lastpostmodified'.$y.':gmt', 'timeinfo' );
		wp_cache_delete( 'lastpostmodified'.$m.':gmt', 'timeinfo' );
		wp_cache_delete( 'lastpostmodified'.$y.':gmt:'.$post->post_type, 'timeinfo' );
		wp_cache_delete( 'lastpostmodified'.$m.':gmt:'.$post->post_type, 'timeinfo' );

		// clear possible last post date cache keys
		wp_cache_delete( 'lastpostdate:gmt', 'timeinfo' );
		wp_cache_delete( 'lastpostdate:gmt:'.$post->post_type, 'timeinfo' );

		// clear possible fist post date cache keys
		wp_cache_delete( 'firstpostdate:gmt', 'timeinfo' );
		wp_cache_delete( 'firstpostdate:gmt:'.$post->post_type, 'timeinfo' );
	}

	/**
	 * Update term modified meta, hooked to transition post status
	 *
	 * @param $new_status
	 * @param $old_status
	 * @param $post
	 */
	public function update_term_modified_meta( $new_status, $old_status, $post ) {

		// bail out on inactive post types
		if ( ! array_key_exists($post->post_type, $this->post_types) || empty( $this->post_types[$post->post_type]['active'] ) )
			return;

		// bail out when not publishing or unpublishing or editing a live post
		// note: prepend " $old_status == $new_status || " to exclude live editong too
		if ( $new_status != 'publish' && $old_status != 'publish' )
			return;

		$taxonomy_settings = get_option( 'xmlsf_taxonomy_settings' );

		// bail if no taxonomies activated
		if ( ! is_array($taxonomy_settings) || empty( $taxonomy_settings['active'] ) )
			return;

		require_once XMLSF_DIR . '/models/public/sitemap.php';

		$taxonomies = get_option( 'xmlsf_taxonomies' );
		if ( empty( $taxonomies ) )
			$taxonomies = xmlsf_public_taxonomies();

		$term_ids = array();
		foreach ( (array) $taxonomies as $tax_name ) {
			$terms = wp_get_post_terms( $post->ID, $tax_name, array( 'fields' => 'ids' ));
			if ( !is_wp_error($terms) ) {
				$term_ids = array_merge( $term_ids, $terms );
			}
		}

		$time = date('Y-m-d H:i:s');

		foreach( $term_ids as $id ) {
			update_term_meta( $id, 'term_modified_gmt', $time );
		}
	}

}

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
