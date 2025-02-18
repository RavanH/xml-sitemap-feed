<?php
/**
 * Pluggable Functions
 *
 * @package XML Sitemap & Google News
 */

/**
 * COMPATIBILITY
 */

if ( ! function_exists( 'esc_xml' ) ) :
	/**
	 * Quick and dirty XML escaping function for WordPress pre-5.5 compatibility.
	 *
	 * @param string $text The input to be escaped.
	 */
	function esc_xml( $text ) {
		$text = ent2ncr( $text );
		$text = wp_strip_all_tags( $text );
		$text = esc_html( $text );

		return $text;
	}
endif;

/**
 * MISSING WORDPRESS FUNCTIONS
 */

if ( ! function_exists( '_get_post_time' ) ) :
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
	 * @return string|false The date.
	 */
	function _get_post_time( $timezone, $field, $post_type = 'any', $which = 'last', $m = '', $w = '' ) {

		global $wpdb;

		if ( ! in_array( $field, array( 'date', 'modified' ), true ) ) {
			return false;
		}

		$timezone = strtolower( $timezone );

		$m = preg_replace( '|[^0-9]|', '', $m );

		if ( ! empty( $w ) ) {
			// When a week number is set make sure 'm' is year only.
			$m = substr( $m, 0, 4 );
			// And append 'w' to the cache key.
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

		if ( 'any' === $post_type ) {
			$post_types = get_post_types( array( 'public' => true ) );
			array_walk( $post_types, array( $wpdb, 'escape_by_ref' ) );
			$post_types = "'" . implode( "', '", $post_types ) . "'";
		} elseif ( is_array( $post_type ) ) {
			$types = get_post_types( array( 'public' => true ) );
			foreach ( $post_type as $type ) {
				if ( ! in_array( $type, $types ) ) {
					return false;
				}
			}
			array_walk( $post_type, array( $wpdb, 'escape_by_ref' ) );
			$post_types = "'" . implode( "', '", $post_type ) . "'";
		} else {
			if ( ! in_array( $post_type, get_post_types( array( 'public' => true ) ), true ) ) {
				return false;
			}
			$post_types = "'" . addslashes( $post_type ) . "'";
		}

		$where = "post_status='publish' AND post_type IN ({$post_types}) AND post_date_gmt";

		// If a period is specified in the querystring, add that to the query.
		if ( ! empty( $m ) ) {
			$where .= ' AND YEAR(post_date)=' . substr( $m, 0, 4 );
			if ( strlen( $m ) > 5 ) {
				$where .= ' AND MONTH(post_date)=' . substr( $m, 4, 2 );
				if ( strlen( $m ) > 7 ) {
					$where .= ' AND DAY(post_date)=' . substr( $m, 6, 2 );
				}
			} elseif ( ! empty( $w ) ) {
				$week   = _wp_mysql_week( 'post_date' );
				$where .= " AND $week=$w";
			}
		}

		$order = ( 'last' === $which ) ? 'DESC' : 'ASC';

		/**
		 * CODE SUGGESTION BY Frédéric Demarle
		 * to make this language aware:
		 * "SELECT post_{$field}_gmt FROM $wpdb->posts" . PLL()->model->post->join_clause()
		 * ."WHERE post_status = 'publish' AND post_type IN ({$post_types})" . PLL()->model->post->where_clause( $lang )
		 * . ORDER BY post_{$field}_gmt DESC LIMIT 1
		 */
		switch ( $timezone ) {
			case 'gmt':
				$date = $wpdb->get_var( "SELECT `post_{$field}_gmt` FROM `$wpdb->posts` WHERE $where ORDER BY `post_{$field}_gmt` $order LIMIT 1" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				break;

			case 'blog':
				$date = $wpdb->get_var( "SELECT `post_{$field}` FROM `$wpdb->posts` WHERE $where ORDER BY `post_{$field}` $order LIMIT 1" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				break;

			case 'server':
				$sec  = gmdate( 'Z' );
				$date = $wpdb->get_var( "SELECT DATE_ADD(`post_{$field}_gmt`, INTERVAL '$sec' SECOND) FROM `$wpdb->posts` WHERE $where ORDER BY `post_{$field}_gmt` $order LIMIT 1" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				break;
		}

		if ( $date ) {
			wp_cache_set( $key, $date, 'timeinfo' );

			return $date;
		}

		return false;
	}
endif;

if ( ! function_exists( 'get_firstpostdate' ) ) :
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
	function get_firstpostdate( $timezone = 'server', $post_type = 'any' ) {
		return apply_filters( 'get_firstpostdate', _get_post_time( $timezone, 'date', $post_type, 'first' ), $timezone );
	}
endif;

if ( ! function_exists( 'get_lastmodified' ) ) :
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
	 * @param string $m The period to check in. Defaults to any, can be YYYY, YYYYMM or YYYYMMDD.
	 * @param string $w The week to check in. Defaults to any, can be one or two digit week number. Must be used with $m in YYYY format.
	 *
	 * @return string|false The date of the latest modified post.
	 */
	function get_lastmodified( $timezone = 'server', $post_type = 'any', $m = '', $w = '' ) {

		// Get last post publication and modification dates.
		$date     = _get_post_time( $timezone, 'date', $post_type, 'last', $m, $w );
		$modified = _get_post_time( $timezone, 'modified', $post_type, 'last', $m, $w );

		// Make sure post date is not later than modified date. Can happen when scheduling publication of posts.
		$date_time = strtotime( $date );
		$mod_time  = strtotime( $modified );
		if ( ! $mod_time || ( $date_time && $date_time > $mod_time ) ) {
			$modified = $date;
		}

		return apply_filters( 'get_lastmodified', $modified, $timezone );
	}
endif;

/**
 * CONDITIONAL TAGS
 */

if ( ! function_exists( 'is_sitemap' ) ) {
	/**
	 * Is the query for a sitemap?
	 *
	 * @since 4.8
	 * @return bool
	 */
	function is_sitemap() {
		if ( false === xmlsf()->request_filtered ) {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Conditional sitemap tags do not work before the sitemap request filter is run. Before then, they always return false.', 'xml-sitemap-feed' ), '4.8' );
			return false;
		}
		return xmlsf()->is_sitemap;
	}
}

if ( ! function_exists( 'is_news' ) ) {
	/**
	 * Is the query for a news sitemap?
	 *
	 * @since 4.8
	 * @return bool
	 */
	function is_news() {
		if ( false === xmlsf()->request_filtered_news ) {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Conditional sitemap tags do not work before the sitemap request filter is run. Before then, they always return false.', 'xml-sitemap-feed' ), '4.8' );
			return false;
		}
		return xmlsf()->is_news;
	}
}
