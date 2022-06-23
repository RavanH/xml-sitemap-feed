<?php

/**
 * Are we using the WP core server?
 * Returns whether the WordPress core sitemap server name used or not.
 *
 * @since 5.4
 * @param void
 * @return bool
 */
function xmlsf_uses_core_server() {
	// SimpeXML not available.
	if ( ! class_exists( 'SimpleXMLElement' ) ) {
		return false;
	}

	// Sitemap disabled.
	$sitemaps = (array) get_option( 'xmlsf_sitemaps', array() );
	if ( empty( $sitemaps['sitemap'] ) ) {
		return false;
	}

	// Check settings.
	$settings = (array) get_option( 'xmlsf_general_settings', array() );
	if ( ! empty( $settings['server'] ) && 'core' === $settings['server'] ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Get post types.
 * Returns an array of taxonomy names to be included in the index.
 *
 * @since 5.4
 * @param void
 * @return array
 */
function xmlsf_get_post_types() {
	$post_types = (array) apply_filters( 'xmlsf_post_types', get_option( 'xmlsf_post_types', array() ) );

	foreach( $post_types as $post_type => &$settings ) {
		if ( empty( $settings['active'] ) || ! post_type_exists( $post_type ) ) {
			unset( $post_types[$post_type] );
		}
	}
	unset( $settings );

	return $post_types;
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
 * Get post attached | featured image(s)
 *
 * @param object $post
 * @param string $which
 *
 * @return array
 */
function xmlsf_images_data( $post, $which ) {
	$attachments = array();

	if ( 'featured' == $which ) {

		if ( has_post_thumbnail( $post->ID ) ) {
			$featured = get_post( get_post_thumbnail_id( $post->ID ) );
			if ( is_object($featured) ) {
				$attachments[] = $featured;
			}
		}

	} elseif ( 'attached' == $which ) {

		$args = array(
			'post_type' => 'attachment',
			'post_mime_type' => 'image',
			'numberposts' => -1,
			'post_status' =>'inherit',
			'post_parent' => $post->ID
		);

		$attachments = get_posts( $args );

	}

	if ( empty( $attachments ) ) return array();

	// gather all data
	$images_data = array();

	foreach ( $attachments as $attachment ) {

		$url = wp_get_attachment_url( $attachment->ID );

		if ( !empty($url) ) {

			$url = esc_attr( esc_url_raw( $url ) );

			$images_data[$url] = array(
				'loc' => $url,
				'title' => apply_filters( 'the_title_xmlsitemap', $attachment->post_title ),
				'caption' => apply_filters( 'the_title_xmlsitemap', $attachment->post_excerpt )
				// 'caption' => apply_filters( 'the_title_xmlsitemap', get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ) )
			);

		}

	}

	return $images_data;
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

		// check each tax public flag and term count and append name to array
		foreach ( get_object_taxonomies( $post_type, 'objects' ) as $taxonomy ) {

			if ( !empty( $taxonomy->public ) && !in_array( $taxonomy->name, xmlsf()->disabled_taxonomies() ) )
				$tax_array[$taxonomy->name] = $taxonomy->label;

		}

	}

	return $tax_array;
}

/**
 * Santize priority value
 * Expects proper locale setting for calculations: setlocale( LC_NUMERIC, 'C' );
 *
 * Returns a float within the set limits.
 *
 * @since 5.2
 * @param float $priority
 * @param float $min
 * @param float $max
 * @return float
 */
function xmlsf_sanitize_priority( $priority, $min = .1, $max = 1 ) {

	setlocale( LC_NUMERIC, 'C' );

	$priority = (float) $priority;
	$min = (float) $min;
	$max = (float) $max;

	if ( $priority <= $min ) {
		return number_format( $min, 1 );
	} elseif ( $priority >= $max ) {
		return number_format( $max, 1 );
	} else {
		return number_format( $priority, 1 );
	}
}

/**
 * Get active sitemap post types
 *
 * @since 5.4
 * @return array
 */
function xmlsf_active_post_types() {
	$settings = (array) apply_filters( 'xmlsf_post_types', get_option( 'xmlsf_post_types', array() ) );

	$public = get_post_types( array( 'public' => true ) );

	return array_filter( $public, function($post_type) use($settings) { return isset( $settings[$post_type] ) && ! empty( $settings[$post_type]['active'] ); } );
}

/**
 * Usage info for debugging
 *
 * @since
 * @return void
 */
function xmlsf_usage() {
	if ( defined('WP_DEBUG') && WP_DEBUG ) {
		global $wpdb, $EZSQL_ERROR;
		$num = get_num_queries();
		$mem = function_exists('memory_get_peak_usage') ? round( memory_get_peak_usage()/1024/1024, 2 ) . 'M' : false;
		$limit = ini_get('memory_limit');
		// query errors
		$errors = '';
		if ( is_array($EZSQL_ERROR) && count($EZSQL_ERROR) ) {
			$i = 1;
			foreach ( $EZSQL_ERROR AS $e ) {
				$errors .= PHP_EOL . $i . ': ' . implode(PHP_EOL, $e) . PHP_EOL;
				$i += 1;
			}
		}
		// saved queries
		$saved = '';
		if ( defined('SAVEQUERIES') && SAVEQUERIES ) {
			$saved .= PHP_EOL . print_r($wpdb->queries, true);
		}

		require XMLSF_DIR . '/views/_usage.php';
	}
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
 * @param string $w week. Can be empty or integer.
 *
 * @return string The date.
 */
if ( ! function_exists( '_get_post_time' ) ) {
	function _get_post_time( $timezone, $field, $post_type = 'any', $which = 'last', $m = '', $w = '' ) {

	   global $wpdb;

	   if ( !in_array( $field, array( 'date', 'modified' ) ) ) {
		   return false;
	   }

	   $timezone = strtolower( $timezone );

	   $m = preg_replace('|[^0-9]|', '', $m);

	   if ( ! empty( $w ) ) {
		   // when a week number is set make sure 'm' is year only
		   $m = substr( $m, 0, 4 );
		   // and append 'w' to the cache key
		   $key = "{$which}post{$field}{$m}.{$w}:$timezone";
	   } else {
		   $key = "{$which}post{$field}{$m}.{$w}:$timezone";
	   }

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
	   if ( !empty($w) ) {
		   $week     = _wp_mysql_week( 'post_date' );
		   $where .= " AND $week=$w";
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
if ( ! function_exists( 'get_firstpostdate' ) ) {
	function get_firstpostdate( $timezone = 'server', $post_type = 'any' ) {

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
 * @param string $post_type The post type to get the last modified date for.
 * @param string $m The period to check in. Defaults to any, can be YYYY, YYYYMM or YYYYMMDD
 * @param string $w The week to check in. Defaults to any, can be one or two digit week number. Must be used with $m in YYYY format.
 *
 * @return string The date of the latest modified post.
 */
if ( ! function_exists( 'get_lastmodified' ) ) {
	function get_lastmodified( $timezone = 'server', $post_type = 'any', $m = '', $w = '' ) {

	   return apply_filters( 'get_lastmodified', _get_post_time( $timezone, 'modified', $post_type, 'last', $m, $w ), $timezone );

	}
}