<?php

/**
 * Get index url
 *
 * @param string $sitemap
 * @param string $type
 * @param string $parm
 *
 * @return string
 */
function xmlsf_get_index_url( $sitemap = 'home', $type = false, $param = false ) {

	if ( xmlsf()->plain_permalinks() ) {
		$name = '?feed=sitemap-'.$sitemap;
		$name .= $type ? '-'.$type : '';
		$name .= $param ? '&m='.$param : '';
	} else {
		$name = 'sitemap-'.$sitemap;
		$name .= $type ? '-'.$type : '';
		$name .= $param ? '.'.$param : '';
		$name .= '.xml';
	}

	return esc_url( trailingslashit( home_url() ) . $name );
}

/**
 * Get last modified date
 *
 * @param string $sitemap
 * @param string $term
 *
 * @return string|bool date|false
 */
function xmlsf_get_lastmod( $sitemap = 'post_type', $term = '' ) {
	$modified = trim( mysql2date( 'Y-m-d\TH:i:s+00:00', xmlsf_modified( $sitemap, $term ), false ) );
	return !empty($modified) ? $modified : false;
}

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
					'lastmod' => mysql2date( 'Y-m-d\TH:i:s+00:00', get_lastpostdate('gmt'), false )
					// TODO make lastmod date language specific
				);
			}
		}
	} elseif ( is_object($sitepress) && method_exists($sitepress, 'get_languages') && method_exists($sitepress, 'language_url') ) {
		foreach ( array_keys ( $sitepress->get_languages(false,true) ) as $term ) {
			$url = $sitepress->language_url($term);
			$data[$url] = array(
				'priority' => '1.0',
				'lastmod' => mysql2date( 'Y-m-d\TH:i:s+00:00', get_lastpostdate('gmt'), false )
				// TODO make lastmod date language specific
			);
		}
	} else {
		// single site root
		$data = array(
			trailingslashit( home_url() ) => array(
				'priority' => '1.0',
				'lastmod' => mysql2date( 'Y-m-d\TH:i:s+00:00', get_lastpostdate('gmt'), false )
			)
		);
	}

	// TODO custom post type root pages here

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
 * Get translations
 *
 * @param $post_id
 *
 * @return array
 */
function xmlsf_get_translations( $post_id ) {
	$translation_ids = array();

	// WPML compat
	global $sitepress;
	// Polylang compat
	if ( function_exists('pll_get_post_translations') ) {
		$translations = pll_get_post_translations($post_id);
		foreach ( $translations as $slug => $id ) {
			if ( $post_id != $id ) $translation_ids[] = $id;
		}
	} elseif ( is_object($sitepress) && method_exists($sitepress, 'get_languages') && method_exists($sitepress, 'get_object_id') ) {
		foreach ( array_keys ( $sitepress->get_languages(false,true) ) as $term ) {
			$id = $sitepress->get_object_id($post_id,'page',false,$term);
			if ( $post_id != $id ) $translation_ids[] = $id;
		}
	}

	return $translation_ids;
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
 * Is home?
 *
 * @param $post_id
 *
 * @return bool
 */
function xmlsf_is_home( $post_id ) {
	return in_array( $post_id, xmlsf_get_blogpages() ) || in_array( $post_id, xmlsf_get_frontpages() );
}

/**
 * Modified
 *
 * @param string $sitemap
 * @param string $term
 *
 * @return string
 */
function xmlsf_modified( $sitemap = 'post_type', $term = '' ) {
	$lastmod = '';

	if ( 'post_type' == $sitemap ) :
		global $post;

		// if blog page then look for last post date
		if ( $post->post_type == 'page' && xmlsf_is_home($post->ID) ) {
			return get_lastpostmodified('gmt');
		}

		$lastmod = get_post_modified_time( 'Y-m-d H:i:s', true, $post->ID );

		$options = get_option('xmlsf_post_types');

		if ( is_array($options) && !empty($options[$post->post_type]['update_lastmod_on_comments']) ) {
			$lastcomment = get_comments( array(
				'status' => 'approve',
				'number' => 1,
				'post_id' => $post->ID,
			) );

			if ( isset($lastcomment[0]->comment_date_gmt) )
				if ( mysql2date( 'U', $lastcomment[0]->comment_date_gmt, false ) > mysql2date( 'U', $lastmod, false ) )
					$lastmod = $lastcomment[0]->comment_date_gmt;
		}

		// make sure lastmod is not older than publication date (happens on scheduled posts)
		if ( isset($post->post_date_gmt) && strtotime($post->post_date_gmt) > strtotime($lastmod) ) {
			$lastmod = $post->post_date_gmt;
		};

	elseif ( 'taxonomy' == $sitemap ) :

		if ( is_object($term) ) {
			$lastmod = get_term_meta( $term->term_id, 'term_modified_gmt', true );

			if ( empty($lastmod) ) {
				// get the latest post in this taxonomy item, to use its post_date as lastmod
				$posts = get_posts (
					array(
						'post_type' => 'any',
						'numberposts' => 1,
						'no_found_rows' => true,
						'update_post_meta_cache' => false,
						'update_post_term_cache' => false,
						'update_cache' => false,
						'tax_query' => array(
							array(
								'taxonomy' => $term->taxonomy,
								'field' => 'slug',
								'terms' => $term->slug
							)
						)
					)
				);
				$lastmod = isset($posts[0]->post_date_gmt) ? $posts[0]->post_date_gmt : '';
				// get post date here, not modified date because we're only
				// concerned about new entries on the (first) taxonomy page

				update_term_meta( $term->term_id, 'term_modified_gmt', $lastmod );
			}
		} else {

			$obj = get_taxonomy($term);

			$lastmodified = array();
			foreach ( (array)$obj->object_type as $object_type ) {
				$lastmodified[] = get_lastpostdate( 'gmt', $object_type );
				// get post date here, not modified date because we're only
				// concerned about new entries on the (first) taxonomy page
			}

			sort($lastmodified);
			$lastmodified = array_filter($lastmodified);
			$lastmod = end( $lastmodified );
		};

	endif;

	return $lastmod;
}

/**
 * Get priority
 *
 * @param string $sitemap
 * @param WP_Term|string $term
 *
 * @return floatval
 */
function xmlsf_get_priority( $sitemap = 'post_type', $term = '' ) {

	// locale LC_NUMERIC should be set to C for these calculations
	// it is assumed to be done at the request filter
	//setlocale( LC_NUMERIC, 'C' );

	$priority = 0.5;

	if ( 'post_type' == $sitemap ) :

		global $post;
		$options = get_option( 'xmlsf_post_types' );
		$priority = isset($options[$post->post_type]['priority']) && is_numeric($options[$post->post_type]['priority']) ? floatval($options[$post->post_type]['priority']) : 0.5;

		if ( $priority_meta = get_metadata( 'post', $post->ID, '_xmlsf_priority', true ) ) {
			$priority = floatval(str_replace(',','.',$priority_meta));
		} elseif ( !empty($options[$post->post_type]['dynamic_priority']) ) {
			$post_modified = mysql2date('U',$post->post_modified_gmt, false);

			// reduce by age
			// NOTE : home/blog page gets same treatment as sticky post, i.e. no reduction by age
			if ( !is_sticky($post->ID) && !xmlsf_is_home($post->ID) && xmlsf()->timespan > 0 ) {
				$priority -= $priority * ( xmlsf()->lastmodified - $post_modified ) / xmlsf()->timespan;
			}

			// increase by relative comment count
			if ( $post->comment_count > 0 && $priority <= 0.9 ) {
				$priority += 0.1 + ( 0.9 - $priority ) * $post->comment_count / wp_count_comments($post->post_type)->approved;
			}
		}

		$priority = apply_filters( 'xmlsf_post_priority', $priority, $post->ID );

	elseif ( 'taxonomy' == $sitemap ) :

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

	endif;

	// a final check for limits
	if ( (float) $priority < 0.1 ) {
		$priority = 0.1;
	}
	if ( (float) $priority > 1 ) {
		$priority = 1;
	}

	return round( (float) $priority, 1 );
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
	$args['hierachical'] = 0;
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
function xmlsf_sitemap_filter_request( $request ) {

	$feed = explode( '-' ,$request['feed'], 3 );

	if ( !isset( $feed[1] ) ) return $request;

	switch( $feed[1] ) {

		case 'posttype':

			if ( !isset( $feed[2] ) ) break;

			$options = get_option( 'xmlsf_post_types' );

			// prepare priority calculation
			if ( is_array($options) && !empty($options[$feed[2]]['dynamic_priority']) ) {
				// last posts or page modified date in Unix seconds
				xmlsf()->lastmodified = mysql2date( 'U', get_lastpostmodified('gmt',$feed[2]), false );
				// uses get_firstpostdate() function defined in xml-sitemap/inc/functions.php !
				xmlsf()->timespan = xmlsf()->lastmodified - mysql2date( 'U', get_firstpostdate('gmt',$feed[2]), false );
			};

			// setup filter
			add_filter( 'post_limits', 'xmlsf_filter_limits' );

			$request['post_type'] = $feed[2];
			$request['orderby'] = 'modified';
			$request['is_date'] = false;

			break;

		case 'taxonomy':

			if ( !isset( $feed[2] ) ) break;

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

/**
 * Get archives
 *
 * @param string $post_type
 * @param string $type
 *
 * @return array
 */
function xmlsf_get_archives( $post_type = 'post', $type = '' ) {
	global $wpdb;
	$return = array();

	if ( 'monthly' == $type ) :

		$query = "SELECT YEAR(post_date) AS `year`, LPAD(MONTH(post_date),2,'0') AS `month`, count(ID) as posts FROM {$wpdb->posts} WHERE post_type = '{$post_type}' AND post_status = 'publish' GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date DESC";
		$key = md5($query);
		$cache = wp_cache_get( 'xmlsf_get_archives' , 'general');

		if ( !isset( $cache[ $key ] ) ) {
			$arcresults = $wpdb->get_results($query);
			$cache[ $key ] = $arcresults;
			wp_cache_set( 'xmlsf_get_archives', $cache, 'general' );
		} else {
			$arcresults = $cache[ $key ];
		}

		if ( $arcresults ) {
			foreach ( (array) $arcresults as $arcresult ) {
				$return[$arcresult->year.$arcresult->month] = xmlsf_get_index_url( 'posttype', $post_type, $arcresult->year . $arcresult->month );
			}
		};

	elseif ( 'yearly' == $type ) :

		$query = "SELECT YEAR(post_date) AS `year`, count(ID) as posts FROM {$wpdb->posts} WHERE post_type = '{$post_type}' AND post_status = 'publish' GROUP BY YEAR(post_date) ORDER BY post_date DESC";
		$key = md5($query);
		$cache = wp_cache_get( 'xmlsf_get_archives' , 'general');

		if ( !isset( $cache[ $key ] ) ) {
			$arcresults = $wpdb->get_results($query);
			$cache[ $key ] = $arcresults;
			wp_cache_set( 'xmlsf_get_archives', $cache, 'general' );
		} else {
			$arcresults = $cache[ $key ];
		}

		if ( $arcresults ) {
			foreach ( (array) $arcresults as $arcresult) {
				$return[$arcresult->year] = xmlsf_get_index_url( 'posttype', $post_type, $arcresult->year );
			}
		};

	else :

		$return[0] = xmlsf_get_index_url('posttype', $post_type); // $sitemap = 'home', $type = false, $param = false

	endif;

	return $return;
}

/**
 * Get taxonomies
 * Returns an array of taxonomy names to be included in the index
 *
 * @since 5.0
 * @param void
 * @return array
 */
function xmlsf_get_taxonomies() {
	$taxonomy_settings = get_option('xmlsf_taxonomy_settings');
	$tax_array = array();
	if ( !empty( $taxonomy_settings['active'] ) ) {
		$taxonomies = get_option('xmlsf_taxonomies');
		if ( is_array($taxonomies) ) {
			foreach ( $taxonomies as $taxonomy ) {
				$count = wp_count_terms( $taxonomy, array('hide_empty'=>true) );
				if ( !is_wp_error($count) && $count > 0 )
					$tax_array[] = $taxonomy;
			}
		} else {
			foreach ( xmlsf_public_taxonomies() as $name => $label )
				if ( 0 < wp_count_terms( $name, array('hide_empty'=>true) ) )
					$tax_array[] = $name;
		}
	}
	return $tax_array;
}

/**
 * Get all public (and not empty) taxonomies
 * Returns an array associated taxonomy object names and labels.
 *
 * @since 5.0
 * @param void
 * @return array
 */
function xmlsf_public_taxonomies() {
	$tax_array = array();

	foreach ( (array) get_option( 'xmlsf_post_types' ) as $post_type => $settings ) {
		if ( empty($settings['active']) ) continue;

		$taxonomies = get_object_taxonomies( $post_type, 'objects' );
		// check each tax public flag and term count and append name to array
		foreach ( $taxonomies as $taxonomy ) {
			if ( !empty( $taxonomy->public ) && !in_array( $taxonomy->name, xmlsf()->disabled_taxonomies() ) )
				$tax_array[$taxonomy->name] = $taxonomy->label;
		}
	}

	return $tax_array;
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
/* CODE SUGGESTION BY Frédéric Demarle
 * to make this language aware.

"SELECT post_{$field}_gmt FROM $wpdb->posts" . PLL()->model->post->join_clause()
."WHERE post_status = 'publish' AND post_type IN ({$post_types})" . PLL()->model->post->where_clause( $lang )
. ORDER BY post_{$field}_gmt DESC LIMIT 1
*/
