<?php
/* -------------------------------------
 *     CONDITIONAL FUNCTIONS
 * ------------------------------------- */

 /**
 * Is the query for a sitemap?
 *
 * @since 4.8
 *
 * @global XMLSitemapFeed $xmlsf Global XML Sitemap Feed instance.
 * @return bool
 */
 function is_sitemap() {
	global $xmlsf;

	if ( ! isset( $xmlsf ) ) {
		_doing_it_wrong( __FUNCTION__, __( 'Conditional sitemap tags do not work before the sitemap request filter is run. Before then, they always return false.' ), '4.8' );
		return false;
	}

	return $xmlsf->is_sitemap();
}

/**
* Is the query for a news sitemap?
*
* @since 4.8
*
* @global XMLSitemapFeed $xmlsf Global XML Sitemap Feed instance.
* @return bool
*/
function is_news() {
	global $xmlsf;

	if ( ! isset( $xmlsf ) ) {
		_doing_it_wrong( __FUNCTION__, __( 'Conditional sitemap tags do not work before the sitemap request filter is run. Before then, they always return false.' ), '4.8' );
		return false;
	}

	return $xmlsf->is_news();
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
 *
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
 *
 * @param string $timezone The location to get the time. Can be 'gmt', 'blog', or 'server'.
 * @return string The date of the oldest modified post.
 */
if( !function_exists('get_lastmodified') ) {
 function get_lastmodified( $timezone = 'server', $post_type = 'any', $m = '' ) {
	return apply_filters( 'get_lastmodified', _get_post_time( $timezone, 'modified', $post_type, 'last', $m ), $timezone );
 }
}

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

	if ( $post_type == 'any' ) {
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
