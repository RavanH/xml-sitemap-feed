<?php

/* ------------------------------
 *      XMLSF Controller CLASS
 * ------------------------------ */

class XMLSF_Sitemap
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
	private $post_types = array();

	/**
	 * Rewrite rules
	 * @var array
	 */
	public $rewrite_rules = array(
		'regex' => 'sitemap(?:_index)?(-[a-z0-9\-_]+)?(?:\.([0-9]{4,8}))?(?:\.([0-9]{1,2}))?\.xml(\.gz)?$',
		'query' => '?feed=sitemap$matches[1]$matches[4]&m=$matches[2]&w=$matches[3]'
	);

	/**
	 * CONSTRUCTOR
	 * Runs on init
	 */

	function __construct( $sitemap )
	{
		if ( $sitemap ) $this->sitemap = $sitemap;

		$this->post_types = (array) get_option( 'xmlsf_post_types', array() );

		// Rewrite rules filter.
		add_filter( 'rewrite_rules_array', array( $this, 'rewrite_rules' ), 99, 1 );

		// Redirect wp-sitemap requests.
		add_action( 'template_redirect', array( $this, 'redirect'),	0 );

		// Cache clearance.
		add_action( 'clean_post_cache', array( $this, 'clean_post_cache'), 99, 2 );

		// Update term meta lastmod date.
		add_action( 'transition_post_status', array( $this, 'update_term_modified_meta' ), 10, 3 );

		// Update images post meta.
		add_action( 'transition_post_status', array( $this, 'update_post_images_meta' ), 10, 3 );

		// Update last comment date post meta.
		add_action( 'transition_comment_status', array( $this, 'update_post_comment_meta' ), 10, 3 );
		add_action( 'comment_post', array( $this, 'update_post_comment_meta_cp' ), 10, 3 ); // when comment is not held for moderation

		// PINGING
		add_action( 'transition_post_status', array( $this, 'do_pings' ), 10, 3 );

	}

	/**
	 * Add sitemap rewrite rules
	 * 
	 * Hooked into rewrite_rules_array filter
	 *
	 * @param array $rewrite_rules
	 * @return array $rewrite_rules
	 */
	public function rewrite_rules( $rewrite_rules ) {
		global $wp_rewrite;

		$rewrite_rules = array_merge( array( $this->rewrite_rules['regex'] => $wp_rewrite->index . $this->rewrite_rules['query'] ), $rewrite_rules );

		return $rewrite_rules;
	}

	/**
	 * Do WP core sitemap index redirect
	 *
	 * @uses wp_redirect()
	 */
	public function redirect() { 
		if ( ! empty( $_SERVER['REQUEST_URI'] ) && substr( $_SERVER['REQUEST_URI'], 0, 15) === '/wp-sitemap.xml' ) { 
			wp_redirect( home_url( $this->sitemap ), 301, 'XML Sitemap & Google News for WordPress' ); 
			exit(); 
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
		// bail out when already published or not publishing
		if ( $old_status == 'publish' || $new_status != 'publish' ) return;

		// bail out when REST API call without new post data, see Gutenberg issue https://github.com/WordPress/gutenberg/issues/15094
		// NO ! Don't bail out now because there will be no other chance as long as bug is not fixed...
		// ... we'll have to make do without $_POST data so potentially incorrect get_post_meta() information.
		//if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) return;

		// bail out when inactive post type
		if ( ! array_key_exists( $post->post_type, $this->post_types ) ) return;

		// we're saving from post edit screen (f.e. 'inline-save' would be from quick edit)
		if ( ! empty( $_POST ) && ! empty( $_POST['action'] ) && 'editpost' == $_POST['action'] ) {
			// bail out when exclude field is checked
			if ( ! empty( $_POST['_xmlsf_exclude'] ) ) return;
		} else {
			// fall back on exclude meta data from DB whic may be outdated (see bug)
			if ( get_post_meta( $post->ID, '_xmlsf_exclude' ) ) return;
		}

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
		$m = get_date_from_gmt( $post->post_date_gmt, 'Ym' );
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

		$this->_add_images_meta( $post, $which );

	}

	/**
	 * Update post comment meta, hooked to transition comment status
	 *
	 * @since 5.2
	 *
	 * @param string $new_status
	 * @param string $old_status
	 * @param object $comment
	 */
	public function update_post_comment_meta( $new_status, $old_status, $comment )
	{
		// bail when not publishing or unpublishing
		if ( $old_status == $new_status || ( $new_status != 'approved' && $old_status != 'approved' ) ) return;

		$post_type = get_post_type( $comment->comment_post_ID );

		// bail when...
		if ( ! array_key_exists($post_type, $this->post_types) // inactive post type
			|| empty( $this->post_types[$post_type]['update_lastmod_on_comments'] ) // comments date irrelevant
		) return;

		$tz = date_default_timezone_get();
  		date_default_timezone_set('UTC');
		update_post_meta( $comment->comment_post_ID, '_xmlsf_comment_date_gmt', date('Y-m-d H:i:s') );
		date_default_timezone_set($tz);

		// update comment meta data
	}

	/**
	 * Update post comment meta, hooked to comment post
	 *
	 * @since 5.2
	 *
	 * @param int $comment_ID
	 * @param int $comment_approved
	 * @param array $commentdata
	 */
	public function update_post_comment_meta_cp( $comment_ID, $comment_approved, $commentdata )
	{
		// bail when not published
		if ( $comment_approved !== 1 ) return;

		$post_type = get_post_type( $commentdata['comment_post_ID'] );

		// bail when...
		if ( ! array_key_exists($post_type, $this->post_types) // inactive post type
			|| empty( $this->post_types[$post_type]['update_lastmod_on_comments'] ) // comments date irrelevant
		) return;

		// update comment meta data
		update_post_meta( $commentdata['comment_post_ID'], '_xmlsf_comment_date_gmt', $commentdata['comment_date_gmt'] );
	}

	/**
	 * Prefetch all queried posts image and comment meta data
	 *
	 * @since 5.2
	 * @uses global $wp_query
	 */
	public function prefetch_posts_meta()
	{
		global $wp_query;

		$post_type = $wp_query->get( 'post_type' );

		if ( empty($post_type) || ! is_string($post_type) ) {
			set_transient( 'xmlsf_prefetch_post_meta_failed', 'Unexpected post type in WP_Query: '.print_r($post_type, true) );
		};

		// bail if post type not set
		if ( ! isset($this->post_types[$post_type]) ) return;

		$y = $wp_query->get( 'year' );
		$m = $wp_query->get( 'm' );
		if ( empty($m) ) $m = 'all';

		// if image tag active then prefetch images
		if (
			isset($this->post_types[$post_type]['tags']) &&
			is_array( $this->post_types[$post_type]['tags'] ) &&
			!empty( $this->post_types[$post_type]['tags']['image'] )
		) {
			$primed = (array) get_option( 'xmlsf_images_meta_primed', array() );

			if (
				! isset( $primed[$post_type] ) ||
				! is_array( $primed[$post_type] ) ||
				(
					! in_array( $m, $primed[$post_type] ) &&
					! in_array( $y, $primed[$post_type] ) &&
					! in_array( 'all', $primed[$post_type] )
				)
			) {
				// prime images meta data
				foreach ( $wp_query->posts as $post ) {
					$this->_add_images_meta( $post, $this->post_types[$post_type]['tags']['image'] );
				}

				// add query to primed array
				$primed[$post_type][] = $m;

				// update
				update_option( 'xmlsf_images_meta_primed', $primed );
			}
		}

		// if update_lastmod_on_comments active then prefetch comments
		if ( !empty($this->post_types[$post_type]['update_lastmod_on_comments']) ) {
			$primed = (array) get_option( 'xmlsf_comments_meta_primed', array() );

			if (
				! isset( $primed[$post_type] ) ||
				! is_array( $primed[$post_type] ) ||
				(
					! in_array( $m, $primed[$post_type] ) &&
					! in_array( $y, $primed[$post_type] ) &&
					! in_array( 'all', $primed[$post_type] )
				)
			) {
				// prime comment meta data
				foreach ( $wp_query->posts as $post ) {
					$this->_add_comment_meta( $post );
				}

				// add query to primed array
				$primed[$post_type][] = $m;

				// update
				update_option( 'xmlsf_comments_meta_primed', $primed );
			}
		}

	}

	/**
	 * Set posts images meta data
	 *
	 * @since 5.2
	 * @param array $post Post object
	 * @param string $which
	 */
	private function _add_images_meta( $post, $which )
	{
		if ( ! is_object($post) || ! isset( $post->ID ) ) return;

		$stored = (array) get_post_meta( $post->ID, '_xmlsf_image_'.$which );

		// populate images and add as meta data
		foreach ( xmlsf_images_data( $post, $which ) as $data ) {
			if ( ! in_array( $data, $stored ) )
				add_post_meta( $post->ID, '_xmlsf_image_'.$which, $data );
		}
	}

	/**
	 * Set post comment meta data
	 *
	 * @since 5.2
	 * @param array $post Post object
	 */
	private function _add_comment_meta( $post )
	{
		if ( ! is_object( $post ) || ! isset( $post->ID ) ) return;

		// get latest post comment
		$comments = get_comments( array(
			'status' => 'approve',
			'number' => 1,
			'post_id' => $post->ID,
		) );

		if ( isset( $comments[0]->comment_date_gmt ) )
			update_post_meta( $post->ID, '_xmlsf_comment_date_gmt', $comments[0]->comment_date_gmt );
	}

}
