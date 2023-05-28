<?php

/**
 * Get root pages data
 * @return array
 */
function xmlsf_get_root_data() {

	// language roots
	global $sitepress;

	// Polylang and WPML compat
	if ( function_exists('pll_the_languages') && function_exists('pll_home_url') ) {
		$languages = pll_languages_list();
		if ( is_array($languages) ) {
			foreach ( $languages as $language ) {
				$url = pll_home_url( $language );
				$data[$url] = array(
					'priority' => '1.0',
					'lastmod' => get_date_from_gmt( get_lastpostdate('GMT'), DATE_W3C )
					// TODO make lastmod date language specific
				);
			}
		}
	} elseif ( is_object($sitepress) && method_exists($sitepress, 'get_languages') && method_exists($sitepress, 'language_url') ) {
		foreach ( array_keys ( $sitepress->get_languages(false,true) ) as $term ) {
			$url = $sitepress->language_url($term);
			$data[$url] = array(
				'priority' => '1.0',
				'lastmod' => get_date_from_gmt( get_lastpostdate('GMT'), DATE_W3C )
				// TODO make lastmod date language specific
			);
		}
	} else {
		// single site root
		$data = array(
			trailingslashit( home_url() ) => array(
				'priority' => '1.0',
				'lastmod' => get_date_from_gmt( get_lastpostdate('GMT'), DATE_W3C )
			)
		);
	}

	return apply_filters( 'xmlsf_root_data', $data );

}

/**
 * Author data
 *
 * @return array
 */
function xmlsf_get_author_data() {

	$author_settings = get_option( 'xmlsf_author_settings' );

	$args = (array) apply_filters(
		'xmlsf_get_author_args',
		array(
			'orderby'             => 'post_count',
			'order'               => 'DESC',
			'number'              => ! empty( $author_settings['term_limit'] ) && is_numeric( $author_settings['term_limit'] ) ? $author_settings['term_limit'] : '1000',
			'fields'              => array( 'ID' ), // must be an array
			'has_published_posts' => true, // means all post types
			'who'                 => 'authors'
		)
	);
	// make sure 'fields' is an array and includes 'ID'
	$args['fields'] = array_merge( (array) $args['fields'], array( 'ID' ) );

	$users = get_users( $args );

	$priority = ! empty( $author_settings['priority'] ) ? $author_settings['priority'] : '';

	$post_type = ( empty( $args['has_published_posts'] ) || true === $args['has_published_posts'] ) ? 'any' : $args['has_published_posts'];

	$data = array();
	foreach ( $users as $user ) {
		$url = get_author_posts_url( $user->ID );

		// allow filtering of users
		if ( apply_filters( 'xmlsf_skip_user', false, $user ) ) continue;

		// last publication date
		$posts = get_posts(
			array(
				'author' => $user->ID,
				'post_type' => $post_type,
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'order' => 'DESC',
				'orderby ' => 'post_date',
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'update_cache' => false,
				'lang' => '', // TODO make multilanguage compatible
			)
		);

		$lastmod = '';
		if ( $posts ) {
			$lastmod = get_the_date( DATE_W3C, $posts[0] );
		}

		$data[$url] = array( 'priority' => $priority, 'lastmod' => $lastmod );
	}

	return apply_filters( 'xmlsf_author_data', $data );

}

/**
 * Do tags
 *
 * @param string $type
 *
 * @return array
 */
function xmlsf_do_tags( $type = 'post' ) {

	$post_types = get_option( 'xmlsf_post_types' );

	// make sure it's an array we are returning
	return (
		is_string($type) &&
		is_array($post_types) &&
		!empty($post_types[$type]['tags'])
	) ? (array) $post_types[$type]['tags'] : array();

}

/**
 * Do authors
 *
 * @return bool
 */
function xmlsf_do_authors() {

	$settings = get_option( 'xmlsf_author_settings', xmlsf()->defaults('author_settings') );

	return is_array( $settings ) && ! empty( $settings['active'] );
}

/**
 * Get front pages
 * @return array
 */
function xmlsf_get_frontpages() {

	if ( null === xmlsf()->frontpages ) :

		$frontpages = array();
		if ( 'page' == get_option('show_on_front') ) {
			$frontpage = (int) get_option('page_on_front');
			$frontpages = array_merge( (array) $frontpage, xmlsf_get_translations($frontpage) );
		}
		xmlsf()->frontpages = $frontpages;

	endif;

	return xmlsf()->frontpages;

}

/**
 * Get blog_pages
 * @return array
 */
function xmlsf_get_blogpages() {

	if ( null === xmlsf()->blogpages ) :
		$blogpages = array();
		if ( 'page' == get_option('show_on_front') ) {
			$blogpage = (int) get_option('page_for_posts');
			if ( !empty($blogpage) ) {
				$blogpages = array_merge( (array) $blogpage, xmlsf_get_translations($blogpage) );
			}
		}
		xmlsf()->blogpages = $blogpages;
	endif;

	return xmlsf()->blogpages;

}

/**
 * Get translations
 *
 * @param $post_id
 *
 * @return array
 */
function xmlsf_get_translations( $post_id ) {

	global $sitepress;
	$translation_ids = array();

	// Polylang compat
	if ( function_exists('pll_get_post_translations') ) {

		$translations = pll_get_post_translations($post_id);

		foreach ( $translations as $slug => $id ) {
			if ( $post_id != $id ) $translation_ids[] = $id;
		}

	// WPML compat
	} elseif ( is_object($sitepress) && method_exists($sitepress, 'get_languages') && method_exists($sitepress, 'get_object_id') ) {

		foreach ( array_keys ( $sitepress->get_languages(false,true) ) as $term ) {
			$id = $sitepress->get_object_id($post_id,'page',false,$term);
			if ( $post_id != $id ) $translation_ids[] = $id;
		}

	}

	return $translation_ids;

}

/**
 * Post Modified
 *
 * @return string GMT date
 */
function xmlsf_get_post_modified() {

	global $post;

	// if blog or home page then simply look for last post date
	if ( $post->post_type == 'page' && ( in_array( $post->ID, xmlsf_get_blogpages() ) || in_array( $post->ID, xmlsf_get_frontpages() ) ) ) {

		$lastmod = get_lastpostdate( 'GMT' );

	} else {

		$lastmod = $post->post_modified_gmt;

		// make sure lastmod is not older than publication date (happens on scheduled posts)
		if ( isset( $post->post_date_gmt ) && strtotime( $post->post_date_gmt ) > strtotime( $lastmod ) ) {
			$lastmod = $post->post_date_gmt;
		};

		// maybe update lastmod to latest comment
		$options = (array) get_option( 'xmlsf_post_types', array() );

		if ( !empty($options[$post->post_type]['update_lastmod_on_comments']) ) {
			// assuming post meta data has been primed here
			$lastcomment = get_post_meta( $post->ID, '_xmlsf_comment_date_gmt', true ); // only get one

			if ( ! empty( $lastcomment ) && strtotime( $lastcomment ) > strtotime( $lastmod ) )
				$lastmod = $lastcomment;
		}

	}

	return ! empty( $lastmod ) ? get_date_from_gmt( $lastmod, DATE_W3C ) : false;

}

/**
 * Term Modified
 *
 * @param object $term
 *
 * @return string
 */
function xmlsf_get_term_modified( $term ) {

	/*
	* Getting ALL meta here because if checking for single key, we cannot
	* distiguish between empty value or non-exisiting key as both return ''.
	*/
	$meta = get_term_meta( $term->term_id );

	if ( ! array_key_exists( 'term_modified', $meta ) ) {

		// get the latest post in this taxonomy item, to use its post_date as lastmod
		$posts = get_posts (
			array(
				'post_type' => 'any',
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'update_cache' => false,
				'lang' => '',
				'has_password' => false,
				'tax_query' => array(
					array(
						'taxonomy' => $term->taxonomy,
						'field' => 'slug',
						'terms' => $term->slug
					)
				)
			)
		);

		$lastmod = isset($posts[0]->post_date) ? $posts[0]->post_date : '';
		// get post date here, not modified date because we're only
		// concerned about new entries on the (first) taxonomy page

		add_term_meta( $term->term_id, 'term_modified', $lastmod );

	} else {

		$lastmod = get_term_meta( $term->term_id, 'term_modified', true ); // only get one

	}

	return ! empty( $lastmod ) ? mysql2date( DATE_W3C, $lastmod, false ) : false;

}

/**
 * Taxonmy Modified
 *
 * @param string $taxonomy
 *
 * @return string
 */
function xmlsf_get_taxonomy_modified( $taxonomy ) {

	$obj = get_taxonomy( $taxonomy );

	$lastmodified = array();
	foreach ( (array)$obj->object_type as $object_type ) {
		$lastmodified[] = get_lastpostdate( 'GMT', $object_type );
		// get last post date here, not modified date because we're only
		// concerned about new entries on the (first) taxonomy page
	}

	sort( $lastmodified );
	$lastmodified = array_filter( $lastmodified );
	$lastmod = end( $lastmodified );

	return get_date_from_gmt( $lastmod, DATE_W3C );

}

/**
 * Get post priority
 *
 * @return float
 */
function xmlsf_get_post_priority() {
	// locale LC_NUMERIC should be set to C for these calculations
	// it is assumed to be done once at the request filter
	//setlocale( LC_NUMERIC, 'C' );

	global $post;
	$options = get_option( 'xmlsf_post_types' );
	$priority = isset($options[$post->post_type]['priority']) && is_numeric($options[$post->post_type]['priority']) ? floatval($options[$post->post_type]['priority']) : 0.5;

	if ( in_array( $post->ID, xmlsf_get_frontpages() ) ) {

		$priority = 1;

	} elseif ( $priority_meta = get_post_meta( $post->ID, '_xmlsf_priority', true ) ) {

		$priority = floatval(str_replace(',','.',$priority_meta));

	} elseif ( !empty($options[$post->post_type]['dynamic_priority']) ) {

		$post_modified = mysql2date('U',$post->post_modified);

		// reduce by age
		// NOTE : home/blog page gets same treatment as sticky post, i.e. no reduction by age
		if ( xmlsf()->timespan > 0 && ! is_sticky( $post->ID ) && ! in_array( $post->ID, xmlsf_get_blogpages() ) ) {
			$priority -= $priority * ( xmlsf()->lastmodified - $post_modified ) / xmlsf()->timespan;
		}

		// increase by relative comment count
		if ( $post->comment_count > 0 && $priority < 1 && xmlsf()->comment_count > 0 ) {
			$priority += 0.1 + ( 1 - $priority ) * $post->comment_count / xmlsf()->comment_count;
		}

	}

	$priority = apply_filters( 'xmlsf_post_priority', $priority, $post->ID );

	// a final check for limits and round it
	return xmlsf_sanitize_priority( $priority );

}

/**
 * Get taxonomy priority
 *
 * @param WP_Term|string $term
 *
 * @return float
 */
function xmlsf_get_term_priority( $term = '' ) {

	// locale LC_NUMERIC should be set to C for these calculations
	// it is assumed to be done at the request filter
	//setlocale( LC_NUMERIC, 'C' );

	$options = get_option( 'xmlsf_taxonomy_settings' );

	$priority = isset( $options['priority'] ) && is_numeric( $options['priority'] ) ? floatval( $options['priority'] ) : 0.5 ;

	if ( !empty($options['dynamic_priority']) && $priority > 0.1 && is_object($term) ) {
		// set first and highest term post count as maximum
		if ( null == xmlsf()->taxonomy_termmaxposts ) {
			xmlsf()->taxonomy_termmaxposts = $term->count;
		}

		$priority -= ( xmlsf()->taxonomy_termmaxposts - $term->count ) * ( $priority - 0.1 ) / xmlsf()->taxonomy_termmaxposts;
	}

	$priority = apply_filters( 'xmlsf_term_priority', $priority, $term->slug );

	// a final check for limits and round it
	return xmlsf_sanitize_priority( $priority );

}

/**
 * Get post images
 *
 * @param string $which
 *
 * @return array
 */
function xmlsf_get_post_images( $which ) {
	global $post;

	// assuming images post meta has been primed here
	$images = get_post_meta( $post->ID, '_xmlsf_image_'.$which );

	return (array) apply_filters( 'xmlsf_post_images_'.$which, $images );
}

/**
 * Terms arguments filter
 * Does not check if we are really in a sitemap feed.
 *
 * @param $args
 *
 * @return array
 */
function xmlsf_set_terms_args( $args ) {
	// https://developer.wordpress.org/reference/classes/wp_term_query/__construct/

	$options = get_option('xmlsf_taxonomy_settings');
	$args['number'] = isset($options['term_limit']) ? intval($options['term_limit']) : 5000;
	if ( $args['number'] < 1 || $args['number'] > 50000 ) $args['number'] = 50000;

	$args['order'] = 'DESC';
	$args['orderby'] = 'count';
	$args['pad_counts'] = true;
	$args['lang'] = '';
	$args['hierarchical'] = 0;
	$args['suppress_filter'] = true;

	return $args;
}

/**
 * Filter request for sitemaps
 *
 * @param array $request
 * @param array $feed
 *
 * @return array $request filtered
 */
function xmlsf_sitemap_filter_request( $request ) {

	/** FILTER HOOK FOR PLUGIN COMPATIBILITIES */
	$request = apply_filters( 'xmlsf_request', $request );
	/**
	 * Developers: add your actions that should run when a sitemap request is with:
	 *
	 * add_filter( 'xmlsf_request', 'your_filter_function' );
	 *
	 * Filters hooked here already:
	 * xmlsf_polylang_request - Polylang compatibility
	 * xmlsf_wpml_request - WPML compatibility
	 * xmlsf_bbpress_request - bbPress compatibility
	 */

	$feed = explode( '-' , $request['feed'], 3 );

	switch( isset( $feed[1] ) ? $feed[1] : '' ) {

		case 'posttype':

			if ( ! isset( $feed[2] ) ) break;

			// try to raise memory limit, context added for filters
			wp_raise_memory_limit( 'sitemap-posttype-'.$feed[2] );

			$options = (array) get_option( 'xmlsf_post_types', array() );

			// prepare priority calculation
			if ( ! empty($options[$feed[2]]['dynamic_priority']) ) {

				// last of this post type modified date in Unix seconds
				xmlsf()->lastmodified = get_date_from_gmt( get_lastpostmodified( 'GMT', $feed[2] ), 'U' );

				// calculate time span, uses get_firstpostdate() function defined in xml-sitemap/inc/functions.php !
				xmlsf()->timespan = xmlsf()->lastmodified - get_date_from_gmt( get_firstpostdate( 'GMT', $feed[2]), 'U' );

				// total post type comment count
				xmlsf()->comment_count = wp_count_comments()->approved;

				// TODO count comments per post type https://wordpress.stackexchange.com/questions/134338/count-all-comments-of-a-custom-post-type
				// TODO cache this more persistently than wp_cache_set does in https://developer.wordpress.org/reference/functions/wp_count_comments/
			};

			// setup filters
			add_filter( 'post_limits', function() { return 'LIMIT 0, 50000'; } );

			// modify request
			$request['post_type'] = $feed[2];
			$request['orderby'] = 'modified';
			$request['order'] = 'DESC';

			// prevent term cache update query unless needed for permalinks
			if ( strpos( get_option( 'permalink_structure' ), '%category%' ) === false )
				$request['update_post_term_cache'] = false;

			// make sure to update meta cache for:
			// 1. excluded posts
			// 2. image data (if activated)
			// 3. lasmod on comments (if activated)
			$request['update_post_meta_cache'] = true;

			break;

		case 'taxonomy':

			if ( !isset( $feed[2] ) ) break;

			// try to raise memory limit, context added for filters
			wp_raise_memory_limit( 'sitemap-taxonomy-'.$feed[2] );

			// pass on taxonomy name via request
			$request['taxonomy'] = $feed[2];

			// set terms args
			add_filter( 'get_terms_args', 'xmlsf_set_terms_args' );

			// disable default feed query
			add_filter( 'posts_request', '__return_false' );

			break;

		default:

			add_filter( 'posts_request', '__return_false' );

	}

	return $request;
}