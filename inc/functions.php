<?php
/**
 * Global Functions
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF;

/**
 * Filter robots.txt rules
 *
 * @param string $output Default robots.txt content.
 *
 * @return string
 */
function robots_txt( $output ) {

	// CUSTOM ROBOTS.
	$robots_custom = \get_option( 'xmlsf_robots' );
	$output       .= $robots_custom ? $robots_custom . PHP_EOL : '';

	// SITEMAPS.

	$output .= PHP_EOL . '# XML Sitemap & Google News version ' . XMLSF_VERSION . ' - https://status301.net/wordpress-plugins/xml-sitemap-feed/' . PHP_EOL;
	if ( 1 !== (int) \get_option( 'blog_public' ) ) {
		$output .= '# XML Sitemaps are disabled because of this site\'s visibility settings.' . PHP_EOL;
	} elseif ( ! namespace\sitemaps_enabled() ) {
		$output .= '# No XML Sitemaps are enabled.' . PHP_EOL;
	} else {
		namespace\sitemaps_enabled( 'sitemap' ) && $output .= 'Sitemap: ' . namespace\sitemap_url() . PHP_EOL;
		namespace\sitemaps_enabled( 'news' ) && $output    .= 'Sitemap: ' . namespace\sitemap_url( 'news' ) . PHP_EOL;
	}

	return $output;
}

\add_filter( 'robots_txt', __NAMESPACE__ . '\robots_txt' );

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
 * WPML compatibility hooked into add_settings and news_add_settings actions
 *
 * @return void
 */
function wpml_remove_home_url_filter() {
	// Remove WPML home url filter.
	global $wpml_url_filters;
	if ( \is_object( $wpml_url_filters ) ) {
		\remove_filter( 'home_url', array( $wpml_url_filters, 'home_url_filter' ), - 10 );
	}
}

\add_action( 'xmlsf_add_settings', __NAMESPACE__ . '\wpml_remove_home_url_filter' );
\add_action( 'xmlsf_news_add_settings', __NAMESPACE__ . '\wpml_remove_home_url_filter' );

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
		$return = array_key_exists( $key, $sitemaps );
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
	 * sitemap-[custom_sitemap_name].php
	 */

	$parts = array();
	foreach ( \explode( '-', $feed, 3 ) as $part ) {
		$parts[] = \basename( $part ); // Patch unauthenticated file inclusion - CVE-2024-4441 reported by Foxyyy.
	}

	// Possible theme template file names.
	$templates = array();
	if ( ! empty( $parts[1] ) ) {
		if ( ! empty( $parts[2] ) ) {
			$templates[] = "{$parts[0]}-{$parts[1]}-{$parts[2]}.php";
		}
		$templates[] = "{$parts[0]}-{$parts[1]}.php";
	} else {
		$templates[] = "{$parts[0]}.php";
	}

	// Find theme template file and load that.
	\locate_template( $templates, true );

	// Still here? Then fall back on plugin template file.
	$template = XMLSF_DIR . '/views/feed-' . \implode( '-', \array_slice( $parts, 0, 2 ) ) . '.php';
	if ( \file_exists( $template ) ) {
		\load_template( $template );
	} else {
		// No template? Then fall back on index.
		\load_template( XMLSF_DIR . '/views/feed-sitemap.php' );
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
	\setlocale( LC_NUMERIC, 'C' );

	$number = $decimals ? \str_replace( ',', '.', $number ) : \str_replace( ',', '', $number );
	$number = $decimals ? \floatval( $number ) : \intval( $number );

	$number = \min( \max( $min, $number ), $max );

	return \number_format( $number, $decimals, '.', '' );
}

/**
 * Generator info
 */
function generator() {
	echo '<!-- generated-on="' . \esc_xml( \gmdate( 'c' ) ) . '" -->' . PHP_EOL;
	echo '<!-- generator="XML Sitemap & Google News for WordPress" -->' . PHP_EOL;
	echo '<!-- generator-url="https://status301.net/wordpress-plugins/xml-sitemap-feed/" -->' . PHP_EOL;
	echo '<!-- generator-version="' . \esc_xml( XMLSF_VERSION ) . '" -->' . PHP_EOL;
}

add_action( 'xmlsf_generator', __NAMESPACE__ . '\generator' );

/**
 * Get translations
 *
 * @param int $post_id Post id.
 *
 * @return array
 */
function get_translations( $post_id ) {

	global $sitepress;
	$translation_ids = array();

	// Polylang compat.
	if ( \function_exists( 'pll_get_post_translations' ) ) {
		$translations = \pll_get_post_translations( $post_id );

		foreach ( $translations as $slug => $id ) {
			if ( $post_id !== $id ) {
				$translation_ids[] = $id;
			}
		}
	}

	// WPML compat.
	if ( \is_object( $sitepress ) && \method_exists( $sitepress, 'get_languages' ) && \method_exists( $sitepress, 'get_object_id' ) ) {

		foreach ( \array_keys( $sitepress->get_languages( false, true ) ) as $term ) {
			$id = $sitepress->get_object_id( $post_id, 'page', false, $term );
			if ( $post_id !== $id ) {
				$translation_ids[] = $id;
			}
		}
	}

	return $translation_ids;
}

\add_filter( 'xmlsf_blogpages', __NAMESPACE__ . '\get_translations' );
\add_filter( 'xmlsf_frontpages', __NAMESPACE__ . '\get_translations' );

/**
 * WPML compatibility hooked into xml request filter
 *
 * @param array $request The request.
 *
 * @return array
 */
function wpml_request( $request ) {
	global $sitepress, $wpml_query_filter;

	if ( \is_object( $sitepress ) ) {
		// Remove filters for tax queries.
		\remove_filter( 'get_terms_args', array( $sitepress, 'get_terms_args_filter' ) );
		\remove_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1 );
		\remove_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ) );
		// Set language to all.
		$sitepress->switch_lang( 'all' );
	}

	if ( $wpml_query_filter ) {
		// Remove query filters.
		\remove_filter( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ), 10, 2 );
		\remove_filter( 'posts_where', array( $wpml_query_filter, 'posts_where_filter' ), 10, 2 );
	}

	$request['lang'] = ''; // Strip off potential lang url parameter.

	return $request;
}

\add_filter( 'xmlsf_request', __NAMESPACE__ . '\wpml_request' );
\add_filter( 'xmlsf_news_request', __NAMESPACE__ . '\wpml_request' );

/**
 * WPML: switch language
 *
 * @see https://wpml.org/wpml-hook/wpml_post_language_details/
 */
function wpml_language_switcher() {
	global $sitepress, $post;

	if ( \is_object( $sitepress ) ) {
		$language = \apply_filters(
			'wpml_element_language_code',
			null,
			array(
				'element_id'   => $post->ID,
				'element_type' => $post->post_type,
			)
		);
		$sitepress->switch_lang( $language );
	}
}

\add_action( 'xmlsf_url', __NAMESPACE__ . '\wpml_language_switcher' );
\add_action( 'xmlsf_news_url', __NAMESPACE__ . '\wpml_language_switcher' );

/**
 * BBPress compatibility hooked into xml request filter
 *
 * @param array $request The request.
 *
 * @return array
 */
function bbpress_request( $request ) {
	\remove_filter( 'bbp_request', 'bbp_request_feed_trap' );

	return $request;
}

\add_filter( 'xmlsf_request', __NAMESPACE__ . '\bbpress_request' );
\add_filter( 'xmlsf_news_request', __NAMESPACE__ . '\bbpress_request' );
