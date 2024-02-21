<?php
/**
 * Sitemap Functions
 *
 * @package XML Sitemap & Google News
 */

/**
 * Get post types.
 * Returns an array of post types to be included in the index.
 *
 * @since 5.4
 *
 * @return array
 */
function xmlsf_get_post_types() {
	$active_post_types = array();
	$post_types        = (array) get_option( 'xmlsf_post_types', array() );
	// Get active post types.
	foreach ( $post_types as $post_type => $settings ) {
		if ( ! empty( $settings['active'] ) ) {
			$active_post_types[ $post_type ] = $settings;
		}
	}

	$available = (array) apply_filters( 'xmlsf_post_types', get_post_types( array( 'public' => true ) ) );
	// Make sure post types are allowed and publicly viewable.
	$available = array_diff( $available, xmlsf()->disabled_post_types() );
	$available = array_filter( $available, 'is_post_type_viewable' );

	$post_types_settings = array_intersect_key( $active_post_types, array_flip( $available ) );

	return $post_types_settings;
}

/**
 * Get taxonomies
 * Returns an array of taxonomy names to be included in the index
 *
 * @since 5.0
 *
 * @return array
 */
function xmlsf_get_taxonomies() {
	$disabled = get_option( 'xmlsf_disabled_providers', xmlsf()->defaults( 'disabled_providers' ) );

	if ( ! empty( $disabled ) && in_array( 'taxonomies', (array) $disabled, true ) ) {
		return array();
	}

	$tax_array = array();

	$taxonomies = get_option( 'xmlsf_taxonomies' );

	if ( is_array( $taxonomies ) ) {
		foreach ( $taxonomies as $taxonomy ) {
			$count = wp_count_terms( $taxonomy );
			if ( ! is_wp_error( $count ) && $count > 0 ) {
				$tax_array[] = $taxonomy;
			}
		}
	} else {
		foreach ( xmlsf_public_taxonomies() as $name => $label ) {
			$count = wp_count_terms( $name );
			if ( ! is_wp_error( $count ) && $count > 0 ) {
				$tax_array[] = $name;
			}
		}
	}

	return $tax_array;
}

/**
 * Get post attached | featured image(s)
 *
 * @param object $post  Post object.
 * @param string $which Image type.
 *
 * @return array
 */
function xmlsf_images_data( $post, $which ) {
	$attachments = array();

	if ( 'featured' === $which ) {
		if ( has_post_thumbnail( $post->ID ) ) {
			$featured = get_post( get_post_thumbnail_id( $post->ID ) );
			if ( is_object( $featured ) ) {
				$attachments[] = $featured;
			}
		}
	} elseif ( 'attached' === $which ) {
		$args = array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'numberposts'    => -1,
			'post_status'    => 'inherit',
			'post_parent'    => $post->ID,
		);

		$attachments = get_posts( $args );
	}

	if ( empty( $attachments ) ) {
		return array();
	}

	// Gather all data.
	$images_data = array();

	foreach ( $attachments as $attachment ) {

		$url = wp_get_attachment_url( $attachment->ID );

		if ( ! empty( $url ) ) {
			$url = esc_attr( esc_url_raw( $url ) );

			$images_data[ $url ] = array(
				'loc'     => $url,
				'title'   => $attachment->post_title,
				'caption' => $attachment->post_excerpt,
				// 'caption' => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
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
 *
 * @return array
 */
function xmlsf_public_taxonomies() {

	$tax_array = array();
	$disabled  = (array) xmlsf()->disabled_taxonomies();

	foreach ( (array) get_option( 'xmlsf_post_types' ) as $post_type => $settings ) {

		if ( empty( $settings['active'] ) ) {
			continue;
		}

		// Check each tax public flag and term count and append name to array.
		foreach ( get_object_taxonomies( $post_type, 'objects' ) as $taxonomy ) {
			if ( ! empty( $taxonomy->public ) && ! in_array( $taxonomy->name, $disabled, true ) ) {
				$tax_array[ $taxonomy->name ] = $taxonomy->label;
			}
		}
	}

	return $tax_array;
}

/**
 * Santize number value
 * Expects proper locale setting for calculations: setlocale( LC_NUMERIC, 'C' );
 *
 * Returns a float or integer within the set limits.
 *
 * @since 5.2
 *
 * @param float|int|string $number Number value.
 * @param float|int        $min    Minimum value.
 * @param float|int        $max    Maximum value.
 * @param bool             $_float Formating, can be float or integer.
 *
 * @return float|int
 */
function xmlsf_sanitize_number( $number, $min = .1, $max = 1, $_float = true ) {
	setlocale( LC_NUMERIC, 'C' );

	$number = $_float ? str_replace( ',', '.', $number ) : str_replace( ',', '', $number );
	$number = $_float ? floatval( $number ) : intval( $number );

	$number = min( max( $min, $number ), $max );

	return $_float ? number_format( $number, 1 ) : $number;
}

/**
 * Clear cache metadata
 *
 * @param string $type The metadata type to clear.
 */
function xmlsf_clear_metacache( $type = 'all' ) {
	switch ( $type ) {
		case 'images':
			// Clear all images meta caches...
			delete_metadata( 'post', 0, '_xmlsf_image_attached', '', true );
			delete_metadata( 'post', 0, '_xmlsf_image_featured', '', true );
			set_transient( 'xmlsf_images_meta_primed', array() );
			break;

		case 'comments':
			delete_metadata( 'post', 0, '_xmlsf_comment_date_gmt', '', true );
			set_transient( 'xmlsf_comments_meta_primed', array() );
			break;

		case 'terms':
			delete_metadata( 'term', 0, 'term_modified', '', true );
			break;

		case 'users':
			delete_metadata( 'user', 0, 'user_modified', '', true );
			break;

		case 'all':
		default:
			$all_types = array( 'images', 'comments', 'terms', 'users' );
			foreach ( $all_types as $_type ) {
				xmlsf_clear_metacache( $_type );
			}
	}
}

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
