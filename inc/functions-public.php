<?php
/**
 * Public global functions
 *
 * @package XML Sitemap & Google News
 */

/**
 * Response headers filter
 * Does not check if we are really in a sitemap feed.
 *
 * @param array $headers Response headers.
 *
 * @return array
 */
function xmlsf_headers( $headers ) {
	// Force status 200.
	$headers['Status'] = '200';

	// Set noindex.
	$headers['X-Robots-Tag'] = 'noindex, follow';

	// Force content type.
	$headers['Content-Type'] = 'application/xml; charset=' . get_bloginfo( 'charset' );

	// And return, merged with nocache headers.
	return array_merge( $headers, wp_get_nocache_headers() );
}

add_filter( 'wp_headers', 'xmlsf_headers' );

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
function xmlsf_load_template( $is_comment_feed, $feed ) {

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
		$parts[] = basename( $part ); // Patch unauthenticated file inclusion - CVE-2024-4441 reported by Foxyyy.
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
	locate_template( $templates, true );

	// Still here? Then fall back on plugin template file.
	$template = XMLSF_DIR . '/views/feed-' . implode( '-', array_slice( $parts, 0, 2 ) ) . '.php';
	if ( file_exists( $template ) ) {
		load_template( $template );
	} else {
		// No template? Then fall back on index.
		load_template( XMLSF_DIR . '/views/feed-sitemap.php' );
	}
}

/**
 * Try to turn on ob_gzhandler output compression
 */
function xmlsf_output_compression() {
	// Try to enable zlib.output_compression or fall back to output buffering with ob_gzhandler.
	if ( false !== ini_set( 'zlib.output_compression', 'On' ) ) {
		// If zlib.output_compression turned on, then make sure to remove wp_ob_end_flush_all.
		remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );
	} else {
		ob_get_length()
		|| in_array( 'ob_gzhandler', ob_list_handlers(), true )
		|| ob_start( 'ob_gzhandler' );
	}

	do_action( 'xmlsf_output_compression' );
}

/**
 * Generator info
 */
function xmlsf_generator() {
	echo '<!-- generated-on="' . esc_xml( gmdate( 'c' ) ) . '" -->' . PHP_EOL;
	echo '<!-- generator="XML Sitemap & Google News for WordPress" -->' . PHP_EOL;
	echo '<!-- generator-url="https://status301.net/wordpress-plugins/xml-sitemap-feed/" -->' . PHP_EOL;
	echo '<!-- generator-version="' . esc_xml( XMLSF_VERSION ) . '" -->' . PHP_EOL;
}

add_action( 'xmlsf_generator', 'xmlsf_generator' );

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
 * Get translations
 *
 * @param int $post_id Post id.
 *
 * @return array
 */
function xmlsf_get_translations( $post_id ) {

	global $sitepress;
	$translation_ids = array();

	// Polylang compat.
	if ( function_exists( 'pll_get_post_translations' ) ) {
		$translations = pll_get_post_translations( $post_id );

		foreach ( $translations as $slug => $id ) {
			if ( $post_id !== $id ) {
				$translation_ids[] = $id;
			}
		}
	}

	// WPML compat.
	if ( is_object( $sitepress ) && method_exists( $sitepress, 'get_languages' ) && method_exists( $sitepress, 'get_object_id' ) ) {

		foreach ( array_keys( $sitepress->get_languages( false, true ) ) as $term ) {
			$id = $sitepress->get_object_id( $post_id, 'page', false, $term );
			if ( $post_id !== $id ) {
				$translation_ids[] = $id;
			}
		}
	}

	return $translation_ids;
}

add_filter( 'xmlsf_blogpages', 'xmlsf_get_translations' );
add_filter( 'xmlsf_frontpages', 'xmlsf_get_translations' );

/**
 * Polylang compatibility hooked into xml request filter
 *
 * @param array $request The request.
 *
 * @return array
 */
function xmlsf_polylang_request( $request ) {

	if ( function_exists( 'pll_languages_list' ) ) {
		$request['lang'] = 'all';
		// Prevent language redirections.
		add_filter( 'pll_check_canonical_url', '__return_false' );
	}

	return $request;
}

add_filter( 'xmlsf_request', 'xmlsf_polylang_request' );
add_filter( 'xmlsf_news_request', 'xmlsf_polylang_request' );

/**
 * WPML compatibility hooked into xml request filter
 *
 * @param array $request The request.
 *
 * @return array
 */
function xmlsf_wpml_request( $request ) {
	global $sitepress, $wpml_query_filter;

	if ( is_object( $sitepress ) ) {
		// Remove filters for tax queries.
		remove_filter( 'get_terms_args', array( $sitepress, 'get_terms_args_filter' ) );
		remove_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1 );
		remove_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ) );
		// Set language to all.
		$sitepress->switch_lang( 'all' );
	}

	if ( $wpml_query_filter ) {
		// Remove query filters.
		remove_filter( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ), 10, 2 );
		remove_filter( 'posts_where', array( $wpml_query_filter, 'posts_where_filter' ), 10, 2 );
	}

	$request['lang'] = ''; // Strip off potential lang url parameter.

	return $request;
}

add_filter( 'xmlsf_request', 'xmlsf_wpml_request' );
add_filter( 'xmlsf_news_request', 'xmlsf_wpml_request' );

/**
 * WPML: switch language
 *
 * @see https://wpml.org/wpml-hook/wpml_post_language_details/
 */
function xmlsf_wpml_language_switcher() {
	global $sitepress, $post;

	if ( is_object( $sitepress ) ) {
		$language = apply_filters(
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

add_action( 'xmlsf_url', 'xmlsf_wpml_language_switcher' );
add_action( 'xmlsf_news_url', 'xmlsf_wpml_language_switcher' );

/**
 * BBPress compatibility hooked into xml request filter
 *
 * @param array $request The request.
 *
 * @return array
 */
function xmlsf_bbpress_request( $request ) {

	remove_filter( 'bbp_request', 'bbp_request_feed_trap' );

	return $request;
}

add_filter( 'xmlsf_request', 'xmlsf_bbpress_request' );
add_filter( 'xmlsf_news_request', 'xmlsf_bbpress_request' );
