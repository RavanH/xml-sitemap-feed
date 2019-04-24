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

		// Update images post meta
		add_action( 'transition_post_status', array($this,'update_post_images_meta'), 10, 3 );

		// Update last comment date post meta
		add_action( 'transition_comment_status', array($this,'transition_comment_status'), 10, 3 );
		add_action( 'comment_post', array($this,'comment_post'), 10, 3 ); // when comment is not held for moderation

		// PINGING
		add_action( 'transition_post_status', array($this,'do_pings'), 11, 3 ); // must run after post meta data hanling hooked at proirity 9

		// FEED TEMPLATES
		add_action( 'do_feed_sitemap', 'xmlsf_load_template_index', 10, 1 );
		add_action( 'do_feed_sitemap_index', 'xmlsf_load_template_index', 10, 1 );
		add_action( 'do_feed_sitemap-home', 'xmlsf_load_template_home', 10, 1 );
		add_action( 'do_feed_sitemap-custom', 'xmlsf_load_template_custom', 10, 1 );

		$this->post_types = (array) get_option( 'xmlsf_post_types', array() );

		if ( is_array($this->post_types) ) {
			foreach ( $this->post_types as $post_type => $settings ) {
				if ( !empty($settings['active']) )
					// FEED TEMPLATES
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
	public function do_pings( $new_status, $old_status, $post )
 	{
		// bail out when...
		if (
			// already published or not publishing
			$old_status == 'publish' || $new_status != 'publish' ||
			// REST API call without new post data, see Gutenberg issue https://github.com/WordPress/gutenberg/issues/15094
			empty( $_POST ) || ! empty( $_POST['xmlsf_exclude'] ) ||
			// inactive post type
			! array_key_exists( $post->post_type, (array) $this->post_types )
		) return;

		$ping = (array) get_option( 'xmlsf_ping', array() );
		// PING !
		foreach ( $ping as $se ) {
			xmlsf_ping( $se, $this->sitemap, HOUR_IN_SECONDS );
		}
 	}

	/**
	 * Cache delete on clean_post_cache
	 *
	 * @param $post_ID
	 * @param $post
	 */
	public function clean_post_cache( $post_ID, $post )
	{
		// are we moving the post in or out of published status?
		wp_cache_delete( 'xmlsf_get_archives', 'general' );

		// TODO get year / month here to delete specific keys too !!!!
		$m = mysql2date( 'Ym', $post->post_date, false );
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
	public function update_term_modified_meta( $new_status, $old_status, $post )
	{
		// bail when...
		if (
			// no status transition or not moving in or out of 'publish' status
			$old_status == $new_status || ( 'publish' != $new_status && 'publish' != $old_status ) ||
			// inactive post type
			! array_key_exists($post->post_type, $this->post_types) || empty( $this->post_types[$post->post_type]['active'] )
		) return;

		$taxonomy_settings = get_option( 'xmlsf_taxonomy_settings' );

		// bail if no taxonomies activated
		if ( ! is_array($taxonomy_settings) || empty( $taxonomy_settings['active'] ) )
			return;

		$taxonomies = get_option( 'xmlsf_taxonomies' );
		if ( empty( $taxonomies ) )
			$taxonomies = xmlsf_public_taxonomies();

		$term_ids = array();
		foreach ( (array) $taxonomies as $slug => $name ) {
			$terms = wp_get_post_terms( $post->ID, $slug, array( 'fields' => 'ids' ));
			if ( !is_wp_error($terms) ) {
				$term_ids = array_merge( $term_ids, $terms );
			}
		}

		$time = date('Y-m-d H:i:s');

		foreach( $term_ids as $id ) {
			update_term_meta( $id, 'term_modified', $time );
		}
	}

	/**
	 * Update post images meta, hooked to transition post status
	 *
	 * @since 5.2
	 *
	 * @param $new_status
	 * @param $old_status
	 * @param $post
	 */
	public function update_post_images_meta( $new_status, $old_status, $post )
	{
		// bail when...
		if (
			// not publishing or updating
			$new_status != 'publish' ||
			// inactive post type
			! array_key_exists($post->post_type, $this->post_types) || empty( $this->post_types[$post->post_type]['active'] ) ||
			// no image tags active
			empty( $this->post_types[$post->post_type]['tags']['image'] )
		) return;

		$which = $this->post_types[$post->post_type]['tags']['image'];

		// delete old image meta data
		delete_post_meta( $post->ID, '_xmlsf_image_'.$which );

		// get fresh image(s) data
		foreach ( (array) xmlsf_images_data( $post, $this->post_types[$post->post_type]['tags']['image'] ) as $data ) {
			// and save it as meta data
			add_post_meta( $post->ID, '_xmlsf_image_'.$which, $data );
		}
	}

	/**
	 * Update post comment meta, hooked to transition comment status
	 *
	 * @since 5.2
	 *
	 * @param $new_status
	 * @param $old_status
	 * @param $comment
	 */
	public function transition_comment_status( $new_status, $old_status, $comment )
	{
		// bail when not publishing or unpublishing
		if ( $old_status == $new_status || ( $new_status != 'approved' && $old_status != 'approved' ) ) return;

		$post_type = get_post_type( $comment->comment_post_ID );

		// bail when...
		if ( ! array_key_exists($post_type, $this->post_types) // inactive post type
			|| empty( $this->post_types[$post_type]['update_lastmod_on_comments'] ) // comments date irrelevant
		) return;

		// update comment meta data
		update_post_meta( $comment->comment_post_ID, '_xmlsf_comment_date', $comment->comment_date );
	}

	/**
	 * Update post comment meta, hooked to transition comment status
	 *
	 * @since 5.2
	 *
	 * @param $new_status
	 * @param $old_status
	 * @param $comment
	 */
	public function comment_post( $comment_ID, $comment_approved, $commentdata )
	{
		// bail when not published
		if ( $comment_approved !== 1 ) return;

		$post_type = get_post_type( $commentdata['comment_post_ID'] );

		// bail when...
		if ( ! array_key_exists($post_type, $this->post_types) // inactive post type
			|| empty( $this->post_types[$post_type]['update_lastmod_on_comments'] ) // comments date irrelevant
		) return;

		// update comment meta data
		update_post_meta( $commentdata['comment_post_ID'], '_xmlsf_comment_date', $commentdata['comment_date'] );
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
