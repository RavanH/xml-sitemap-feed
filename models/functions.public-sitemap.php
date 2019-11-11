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
		$languages = pll_the_languages( array( 'raw' => 1 ) );
		if ( is_array($languages) ) {
			foreach ( $languages as $language ) {
				$url = pll_home_url( $language['slug'] );
				$data[$url] = array(
					'priority' => '1.0',
					'lastmod' => mysql2date( DATE_W3C, get_lastpostdate('blog') )
					// TODO make lastmod date language specific
				);
			}
		}
	} elseif ( is_object($sitepress) && method_exists($sitepress, 'get_languages') && method_exists($sitepress, 'language_url') ) {
		foreach ( array_keys ( $sitepress->get_languages(false,true) ) as $term ) {
			$url = $sitepress->language_url($term);
			$data[$url] = array(
				'priority' => '1.0',
				'lastmod' => mysql2date( DATE_W3C, get_lastpostdate('blog') )
				// TODO make lastmod date language specific
			);
		}
	} else {
		// single site root
		$data = array(
			trailingslashit( home_url() ) => array(
				'priority' => '1.0',
				'lastmod' => mysql2date( DATE_W3C, get_lastpostdate('blog') )
			)
		);
	}

	return $data;

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

		$lastmod = get_lastpostmodified( 'blog' );

	} else {

		$lastmod = $post->post_modified;

		// make sure lastmod is not older than publication date (happens on scheduled posts)
		if ( isset( $post->post_date ) && strtotime( $post->post_date ) > strtotime( $lastmod ) ) {
			$lastmod = $post->post_date;
		};

		// maybe update lastmod to latest comment
		$options = (array) get_option( 'xmlsf_post_types', array() );

		if ( !empty($options[$post->post_type]['update_lastmod_on_comments']) ) {
			// assuming post meta data has been primed here
			$lastcomment = get_post_meta( $post->ID, '_xmlsf_comment_date', true ); // only get one

			if ( ! empty( $lastcomment ) && strtotime( $lastcomment ) > strtotime( $lastmod ) )
				$lastmod = $lastcomment;
		}

	}

	return ! empty( $lastmod ) ? mysql2date( DATE_W3C, $lastmod ) : false;

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

	return ! empty( $lastmod ) ? mysql2date( DATE_W3C, $lastmod ) : false;

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
		$lastmodified[] = get_lastpostdate( 'blog', $object_type );
		// get last post date here, not modified date because we're only
		// concerned about new entries on the (first) taxonomy page
	}

	sort( $lastmodified );
	$lastmodified = array_filter( $lastmodified );
	$lastmod = end( $lastmodified );

	return mysql2date( DATE_W3C, $lastmod );

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

		$post_modified = mysql2date('U',$post->post_modified, false);

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
	return xmlsf_sanitize_priority( $priority, 0.1, 1 );

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
	return xmlsf_sanitize_priority( $priority, 0.1, 1 );

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
 * Filter limits
 * override default feed limit
 * @return string
 */
function xmlsf_filter_limits( $limit ) {
	return 'LIMIT 0, 50000';
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
 * Filter request
 *
 * @param $request
 *
 * @return mixed
 */
function xmlsf_sitemap_parse_request( $request ) {

	$feed = explode( '-' ,$request['feed'], 3 );

	if ( !isset( $feed[1] ) ) return $request;

	switch( $feed[1] ) {

		case 'posttype':

			if ( ! isset( $feed[2] ) ) break;

			// try to raise memory limit, context added for filters
			wp_raise_memory_limit( 'sitemap-posttype-'.$feed[2] );

			$options = (array) get_option( 'xmlsf_post_types', array() );

			// prepare priority calculation
			if ( ! empty($options[$feed[2]]['dynamic_priority']) ) {
				// last posts or page modified date in Unix seconds
				xmlsf()->lastmodified = mysql2date( 'U', get_lastpostmodified( 'blog', $feed[2]), false );
				// calculate time span, uses get_firstpostdate() function defined in xml-sitemap/inc/functions.php !
				xmlsf()->timespan = xmlsf()->lastmodified - mysql2date( 'U', get_firstpostdate( 'blog', $feed[2]), false );
				// total post type comment count
				xmlsf()->comment_count = wp_count_comments($feed[2])->approved;
			};

			// setup filter
			add_filter( 'post_limits', 'xmlsf_filter_limits' );

			$request['post_type'] = $feed[2];
			$request['orderby'] = 'modified';
			$request['order'] = 'DESC';
			$request['is_date'] = false;

			break;

		case 'taxonomy':

			if ( !isset( $feed[2] ) ) break;

			// try to raise memory limit, context added for filters
			wp_raise_memory_limit( 'sitemap-taxonomy-'.$feed[2] );

			// WPML compat
			global $sitepress;
			if ( is_object($sitepress) ) {
				remove_filter( 'get_terms_args', array($sitepress,'get_terms_args_filter') );
				remove_filter( 'get_term', array($sitepress,'get_term_adjust_id'), 1 );
				remove_filter( 'terms_clauses', array($sitepress,'terms_clauses') );
				$sitepress->switch_lang('all');
			}

			add_filter( 'get_terms_args', 'xmlsf_set_terms_args' );

			// pass on taxonomy name via request
			$request['taxonomy'] = $feed[2];

			break;

		default:
		// do nothing
	}

	return $request;
}

/* -------------------------------------
 *      MISSING WORDPRESS FUNCTIONS
 * ------------------------------------- */

/**
 * Retrieve first or last post type date data based on timezone.
 * Variation of function _get_last_post_time
 *
 * @param string $timezone The location to get the time. Can be 'gmt', 'blog', or 'server'.
 * @param string $field Field to check. Can be 'date' or 'modified'.
 * @param string $post_type Post type to check. Defaults to 'any'.
 * @param string $which Which to check. Can be 'first' or 'last'. Defaults to 'last'.
 * @param string $m year, month or day period. Can be empty or integer.
 * @return string The date.
 */
if( !function_exists('_get_post_time') ) {
 function _get_post_time( $timezone, $field, $post_type = 'any', $which = 'last', $m = '' ) {

	global $wpdb;

	if ( !in_array( $field, array( 'date', 'modified' ) ) ) {
		return false;
	}

	$timezone = strtolower( $timezone );

	$m = preg_replace('|[^0-9]|', '', $m);

	$key = "{$which}post{$field}{$m}:$timezone";

	if ( 'any' !== $post_type ) {
		$key .= ':' . sanitize_key( $post_type );
	}

	$date = wp_cache_get( $key, 'timeinfo' );
	if ( false !== $date ) {
		return $date;
	}

	if ( $post_type === 'any' ) {
		$post_types = get_post_types( array( 'public' => true ) );
		array_walk( $post_types, array( &$wpdb, 'escape_by_ref' ) );
		$post_types = "'" . implode( "', '", $post_types ) . "'";
	} elseif ( is_array($post_type) ) {
		$types = get_post_types( array( 'public' => true ) );
		foreach ( $post_type as $type )
			if ( !in_array( $type, $types ) )
				return false;
		array_walk( $post_type, array( &$wpdb, 'escape_by_ref' ) );
		$post_types = "'" . implode( "', '", $post_type ) . "'";
	} else {
		if ( !in_array( $post_type, get_post_types( array( 'public' => true ) ) ) )
			return false;
		$post_types = "'" . addslashes($post_type) . "'";
	}

	$where = "post_status='publish' AND post_type IN ({$post_types}) AND post_date_gmt";

	// If a period is specified in the querystring, add that to the query
	if ( !empty($m) ) {
		$where .= " AND YEAR(post_date)=" . substr($m, 0, 4);
		if ( strlen($m) > 5 ) {
			$where .= " AND MONTH(post_date)=" . substr($m, 4, 2);
			if ( strlen($m) > 7 ) {
				$where .= " AND DAY(post_date)=" . substr($m, 6, 2);
			}
		}
	}

	$order = ( $which == 'last' ) ? 'DESC' : 'ASC';

	/* CODE SUGGESTION BY Frédéric Demarle
   * to make this language aware:
  "SELECT post_{$field}_gmt FROM $wpdb->posts" . PLL()->model->post->join_clause()
  ."WHERE post_status = 'publish' AND post_type IN ({$post_types})" . PLL()->model->post->where_clause( $lang )
  . ORDER BY post_{$field}_gmt DESC LIMIT 1
  */
	switch ( $timezone ) {
		case 'gmt':
			$date = $wpdb->get_var("SELECT post_{$field}_gmt FROM $wpdb->posts WHERE $where ORDER BY post_{$field}_gmt $order LIMIT 1");
				break;
		case 'blog':
			$date = $wpdb->get_var("SELECT post_{$field} FROM $wpdb->posts WHERE $where ORDER BY post_{$field}_gmt $order LIMIT 1");
			break;
		case 'server':
			$add_seconds_server = date('Z');
			$date = $wpdb->get_var("SELECT DATE_ADD(post_{$field}_gmt, INTERVAL '$add_seconds_server' SECOND) FROM $wpdb->posts WHERE $where ORDER BY post_{$field}_gmt $order LIMIT 1");
			break;
	}

	if ( $date ) {
        wp_cache_set( $key, $date, 'timeinfo' );

        return $date;
    }

    return false;

 }
}

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
