<?php
/**
 * Global Functions
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF;

/**
 * Get the public XML sitemap url.
 *
 * @since 5.4
 *
 * @param string $sitemap Sitemap name.
 * @param array  $args    Arguments array:
 *                        $type - post_type or taxonomy, default false
 *                        $m    - YYYY, YYYYMM, YYYYMMDD
 *                        $w    - week of the year ($m must be YYYY format)
 *                        $gz   - bool for GZ extension (triggers compression verification).
 *
 * @return string|false The sitemap URL or false if the sitemap doesn't exist.
 */
function sitemap_url( $sitemap = 'index', $args = array() ) {

	global $wp_rewrite;

	if ( 'news' === $sitemap ) {
		return $wp_rewrite->using_permalinks() ? \esc_url( \trailingslashit( \home_url() ) . 'sitemap-news.xml' ) : \esc_url( \trailingslashit( \home_url() ) . '?feed=sitemap-news' );
	}

	// Use core function get_sitemap_url if using core sitemaps.
	if ( namespace\uses_core_server() ) {
		return \get_sitemap_url( $sitemap );
	}

	if ( 'index' === $sitemap ) {
		return $wp_rewrite->using_permalinks() ? \esc_url( \trailingslashit( \home_url() ) . 'sitemap.xml' ) : \esc_url( \trailingslashit( \home_url() ) . '?feed=sitemap' );
	}

	// Get our arguments.
	$args = \apply_filters(
		'xmlsf_index_url_args',
		\wp_parse_args(
			$args,
			array(
				'type' => false,
				'm'    => false,
				'w'    => false,
			)
		)
	);

	// Construct file name.
	if ( $wp_rewrite->using_permalinks() ) {
		$name  = 'sitemap-' . $sitemap;
		$name .= $args['type'] ? '-' . $args['type'] : '';
		$name .= $args['m'] ? '.' . $args['m'] : '';
		$name .= $args['w'] ? '.' . $args['w'] : '';
		$name .= '.xml';
	} else {
		$name  = '?feed=sitemap-' . $sitemap;
		$name .= $args['type'] ? '-' . $args['type'] : '';
		$name .= $args['m'] ? '&m=' . $args['m'] : '';
		$name .= $args['w'] ? '&w=' . $args['w'] : '';
	}

	return \esc_url( \trailingslashit( \home_url() ) . $name );
}

/**
 * Print XML Stylesheet
 *
 * @param string|false $sitemap Optional sitemap name.
 */
function xml_stylesheet( $sitemap = false ) {

	$url = namespace\get_stylesheet_url( $sitemap );

	if ( $url ) {
		echo '<?xml-stylesheet type="text/xsl" href="' . \esc_url( \wp_make_link_relative( $url ) ) . '?ver=' . \esc_xml( XMLSF_VERSION ) . '"?>' . PHP_EOL;
	}
}

/**
 * Get XML Stylesheet URL
 *
 * @since 5.4
 *
 * @param string|false $sitemap Optional sitemap name.
 *
 * @return string|false
 */
function get_stylesheet_url( $sitemap = false ) {
	/**
	 * GET STYLESHEET URL
	 *
	 * DEVELOPERS: a custom stylesheet file in the active (parent or child) theme /assets subdirectory, will be used when found there
	 *
	 * Must start with 'sitemap', optionally followed by another designator, separated by a hyphen.
	 * It should always end with the xsl extension.
	 *
	 * Examples:
	 * assets/sitemap.xsl
	 * assets/sitemap-root.xsl
	 * assets/sitemap-posttype.xsl
	 * assets/sitemap-taxonomy.xsl
	 * assets/sitemap-author.xsl
	 * assets/sitemap-custom.xsl
	 * assets/sitemap-news.xsl
	 * assets/sitemap-[custom_sitemap_name].xsl
	 */

	$file = $sitemap ? 'assets/sitemap-' . $sitemap . '.xsl' : 'assets/sitemap.xsl';

	// Find theme stylesheet file.
	if ( \file_exists( \get_stylesheet_directory() . '/' . $file ) ) {
		$url = \get_stylesheet_directory_uri() . '/' . $file;
	} elseif ( \file_exists( \get_template_directory() . '/' . $file ) ) {
		$url = \get_template_directory_uri() . '/' . $file;
	} elseif ( \file_exists( XMLSF_DIR . '/' . $file ) ) {
		$url = \plugins_url( $file, XMLSF_BASENAME );
	} else {
		$url = false;
	}

	return \apply_filters( 'xmlsf_stylesheet_url', $url );
}

/**
 * Are any sitemaps enabled?
 *
 * @since 5.4
 *
 * @param string $which Which sitemap to check for. Default any sitemap.
 *
 * @return bool
 */
function sitemaps_enabled( $which = 'any' ) {
	$sitemaps = (array) \get_option( 'xmlsf_sitemaps', array() );

	if ( 1 !== (int) \get_option( 'blog_public' ) || empty( $sitemaps ) ) {
		$return = false;
	} elseif ( 'any' === $which ) {
		$return = true;
	} else {
		$key    = 'news' === $which ? 'sitemap-news' : $which;
		$return = \array_key_exists( $key, $sitemaps );
	}

	return \apply_filters( 'xmlsf_sitemaps_enabled', $return, $which );
}

/**
 * Are we using the WP core server?
 * Returns whether the WordPress core sitemap server is used or not.
 *
 * @since 5.4
 *
 * @return bool
 */
function uses_core_server() {
	// Sitemap disabled.
	if ( ! namespace\sitemaps_enabled( 'sitemap' ) || ! \function_exists( 'get_sitemap_url' ) ) {
		return false;
	}

	// Check settings.
	$server = \get_option( 'xmlsf_server', \xmlsf()->defaults( 'server' ) );
	return ! empty( $server ) && 'core' === $server;
}

/**
 * Response headers filter
 * Does not check if we are really in a sitemap feed.
 *
 * @param array $headers Response headers.
 *
 * @return array
 */
function headers( $headers ) {
	// Force status 200.
	$headers['Status'] = '200';

	// Set noindex.
	$headers['X-Robots-Tag'] = 'noindex, follow';

	// Force content type.
	$headers['Content-Type'] = 'application/xml; charset=' . \get_bloginfo( 'charset' );

	// And return, merged with nocache headers.
	return \array_merge( $headers, \wp_get_nocache_headers() );
}

/**
 * Load feed template
 *
 * Hooked into do_feed_{sitemap...}. First checks for a child/parent theme template file, then falls back to plugin template
 *
 * @since 5.3
 *
 * @param bool   $is_comment_feed Unused.
 * @param string $feed            Feed type.
 */
function load_template( $is_comment_feed, $feed ) {
	/**
	 * GET TEMPLATE FILE
	 *
	 * DEVELOPERS: a custom template file in the active (parent or child) theme directory will be used when found there
	 *
	 * Must start with 'sitemap', optionally folowed by other designators, serperated by hyphens.
	 * It should always end with the php extension.
	 *
	 * Examples:
	 * sitemap.php
	 * sitemap-root.php
	 * sitemap-posttype.php
	 * * sitemap-posttype-post.php
	 * * sitemap-posttype-page.php
	 * * sitemap-posttype-[custom_post_type].php
	 * sitemap-taxonomy.php
	 * * sitemap-taxonomy-category.php
	 * * sitemap-taxonomy-post_tag.php
	 * * sitemap-taxonomy-[custom_taxonomy].php
	 * sitemap-authors.php
	 * sitemap-custom.php
	 * sitemap-news.php
	 */

	$parts     = \explode( '-', $feed, 3 );
	$templates = array();
	$found     = false;

	// Possible theme template file names.
	if ( ! empty( $parts[1] ) && \in_array( $parts[1], array( 'news', 'posttype', 'taxonomy', 'author', 'custom' ), true ) ) {
		if ( ! empty( $parts[2] ) ) {
			$templates[] = "sitemap-{$parts[1]}-{$parts[2]}.php";
		}
		$templates[] = "sitemap-{$parts[1]}.php";
	}
	$templates[] = 'sitemap.php';

	// Find and load theme template file or use plugin template.
	if ( ! \locate_template( $templates, true ) ) {
		// Still here? Then fall back on a matching plugin template file.
		foreach ( $templates as $template ) {
			$file = XMLSF_DIR . '/views/feed-' . $template;
			if ( \file_exists( $file ) ) {
				\load_template( $file );
				break;
			}
		}
	}
}

/**
 * Santize number value
 * Expects proper locale setting for calculations: setlocale( LC_NUMERIC, 'C' );
 *
 * Returns a float or integer within the set limits.
 *
 * @since 5.2
 *
 * @param float|int|string $number   Number value.
 * @param float|int        $min      Minimum value.
 * @param float|int        $max      Maximum value.
 * @param int              $decimals Formating, can be float or integer.
 *
 * @return float|int
 */
function sanitize_number( $number, $min = .1, $max = 1, $decimals = 1 ) {
	if ( ! is_numeric( $number ) ) {
		return $number;
	}

	\setlocale( LC_NUMERIC, 'C' );

	$number = $decimals ? \str_replace( ',', '.', $number ) : \str_replace( ',', '', $number );
	$number = $decimals ? \floatval( $number ) : \intval( $number );

	$number = \min( \max( $min, $number ), $max );

	return \number_format( $number, $decimals, '.', '' );
}

/**
 * Clear cache metadata
 *
 * @param string $type The metadata type to clear.
 */
function clear_metacache( $type = 'all' ) {
	switch ( $type ) {
		case 'images':
			// Clear all images meta caches...
			\delete_metadata( 'post', 0, '_xmlsf_image_attached', '', true );
			\delete_metadata( 'post', 0, '_xmlsf_image_featured', '', true );
			\set_transient( 'xmlsf_images_meta_primed', array() );
			break;

		case 'comments':
			\delete_metadata( 'post', 0, '_xmlsf_comment_date_gmt', '', true );
			\set_transient( 'xmlsf_comments_meta_primed', array() );
			break;

		case 'terms':
			\delete_metadata( 'term', 0, 'term_modified', '', true );
			break;

		case 'users':
			\delete_metadata( 'user', 0, 'user_modified', '', true );
			break;

		case 'all':
		default:
			$all_types = array( 'images', 'comments', 'terms', 'users' );
			foreach ( $all_types as $_type ) {
				namespace\clear_metacache( $_type );
			}
	}
}
