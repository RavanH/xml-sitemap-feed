<?php
/**
 * Public Sitemap Functions
 *
 * @package XML Sitemap & Google News
 */

/**
 * Get root pages data
 *
 * @return array
 */
function xmlsf_get_root_data() {

	// language roots
	global $sitepress;

	// Polylang and WPML compat
	if ( function_exists('pll_languages_list') && function_exists('pll_home_url') ) {
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
 * User Priority
 *
 * @since 5.4
 *
 * @param int $user User ID
 * @return float
 */
function xmlsf_get_user_priority( $user ) {

	$author_settings = get_option( 'xmlsf_author_settings' );

	$priority = isset( $author_settings['priority'] ) && is_numeric( $author_settings['priority'] ) ? floatval( $author_settings['priority'] ) : 0.5 ;

	// TODO dynamic priority calculation?

	$priority = apply_filters( 'xmlsf_user_priority', $priority, $user );

	// a final check for limits and round it
	return xmlsf_sanitize_priority( $priority );
}

/**
 * User Modified
 *
 * @since 5.4
 *
 * @param WP_User $user
 * @return string|false GMT date
 */
function xmlsf_get_user_modified( $user ) {

	// last publication date
	$posts = get_posts(
		array(
			'author' => $user->ID,
			'post_type' => apply_filters( 'xmlsf_author_post_types', array( 'post' ) ),
			'post_status' => 'publish',
			'posts_per_page' => 1,
			'numberposts' => 1,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'update_cache' => false,
			'lang' => '' // TODO make multilanguage compatible
		)
	);

	return $posts ? get_the_date( DATE_W3C, $posts[0] ) : false;
}

/**
 * Do tags
 *
 * @param string $type
 * @return array
 */
function xmlsf_do_tags( $type = 'post' ) {

	$post_types = get_option( 'xmlsf_post_types' );

	// Make sure it's an array we are returning
	$tags = (
		is_string( $type ) &&
		is_array( $post_types ) &&
		! empty( $post_types[$type]['tags'] )
	) ? (array) $post_types[$type]['tags'] : array();

	return $tags;
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
 * 
 * @return array
 */
function xmlsf_get_frontpages() {

	if ( null === xmlsf()->frontpages ) :

		$frontpages = array();
		if ( 'page' == get_option('show_on_front') ) {
			$frontpage = (int) get_option('page_on_front');
			$frontpages = (array) apply_filters( 'xmlsf_frontpages', $frontpage );
		}
		xmlsf()->frontpages = $frontpages;

	endif;

	return xmlsf()->frontpages;

}

/**
 * Get blog_pages
 * 
 * @return array
 */
function xmlsf_get_blogpages() {

	if ( null === xmlsf()->blogpages ) :
		$blogpages = array();
		if ( 'page' == get_option('show_on_front') ) {
			$blogpage = (int) get_option('page_for_posts');
			$blogpages = (array) apply_filters( 'xmlsf_blogpages', $blogpage );
		}
		xmlsf()->blogpages = $blogpages;
	endif;

	return xmlsf()->blogpages;

}

/**
 * Post Modified
 *
 * @param WP_Post $post
 * @return string|false GMT date
 */
function xmlsf_get_post_modified( $post ) {

	// if blog or home page then simply look for last post date
	if ( $post->post_type == 'page' && ( in_array( $post->ID, xmlsf_get_blogpages() ) || in_array( $post->ID, xmlsf_get_frontpages() ) ) ) {

		$lastmod = get_lastpostdate( 'GMT', 'post' );

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
 * @param WP_Term|int $term
 * @return string|false
 */
function xmlsf_get_term_modified( $term ) {
	if ( is_numeric($term) ) {
		$term = get_term( $term );
	}

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
 * Taxonomy Modified
 *
 * @param string $taxonomy
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
 * @param WP_Post $post
 * @return float
 */
function xmlsf_get_post_priority( $post ) {
	// locale LC_NUMERIC should be set to C for these calculations
	// it is assumed to be done once at the request filter
	//setlocale( LC_NUMERIC, 'C' );

	$options = get_option( 'xmlsf_post_types' );
	$priority = isset($options[$post->post_type]['priority']) && is_numeric($options[$post->post_type]['priority']) ? floatval($options[$post->post_type]['priority']) : 0.5;

	if ( in_array( $post->ID, xmlsf_get_frontpages() ) ) {

		$priority = 1;

	} elseif ( $priority_meta = get_post_meta( $post->ID, '_xmlsf_priority', true ) ) {

		$priority = floatval(str_replace(',','.',$priority_meta));

	} elseif ( ! empty($options[$post->post_type]['dynamic_priority']) ) {

		$post_modified = mysql2date('U',$post->post_modified);

		// Reduce by age.
		// NOTE : home/blog page gets same treatment as sticky post, i.e. no reduction by age
		if ( xmlsf()->timespan > 0 && ! is_sticky( $post->ID ) && ! in_array( $post->ID, xmlsf_get_blogpages() ) ) {
			$priority -= $priority * ( xmlsf()->lastmodified - $post_modified ) / xmlsf()->timespan;
		}

		// Increase by relative comment count.
		if ( $post->comment_count > 0 && $priority < 1 && xmlsf()->comment_count > 0 ) {
			$priority += 0.1 + ( 1 - $priority ) * $post->comment_count / xmlsf()->comment_count;
		}

	}

	$priority = apply_filters( 'xmlsf_post_priority', $priority, $post->ID );

	// A final check for limits and round it.
	return xmlsf_sanitize_priority( $priority );
}

/**
 * Get taxonomy priority
 *
 * @param WP_Term|int $term
 *
 * @return float
 */
function xmlsf_get_term_priority( $term ) {
	// locale LC_NUMERIC should be set to C for these calculations
	// it is assumed to be done at the request filter
	//setlocale( LC_NUMERIC, 'C' );

	$options = get_option( 'xmlsf_taxonomy_settings' );

	$priority = isset( $options['priority'] ) && is_numeric( $options['priority'] ) ? floatval( $options['priority'] ) : 0.5 ;

	if ( is_numeric($term) ) {
		$term = get_term( $term );
	}

	if ( !empty($options['dynamic_priority']) && $priority > 0.1 ) {
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
