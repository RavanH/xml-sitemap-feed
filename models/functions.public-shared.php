<?php

/**
 * Response headers filter
 * Does not check if we are really in a sitemap feed.
 *
 * @param $headers
 *
 * @return array
 */
function xmlsf_headers( $headers ) {
	// Force status 200.
	$headers['Status'] = '200';

	// Set noindex.
	$headers['X-Robots-Tag'] = 'noindex, follow';

	// Force content type
	$headers['Content-Type'] = 'application/xml; charset=' . get_bloginfo('charset');

	// And return, merged with nocache headers
	return array_merge( $headers, wp_get_nocache_headers() );
}

/**
 * Get absolute URL
 * Converts path or protocol relative URLs to absolute ones.
 *
 * @param string $url
 *
 * @return string|bool
 */
function xmlsf_get_absolute_url( $url = false ) {
	// have a string or return false
	if ( empty( $url ) || ! is_string( $url ) ) {
		return false;
	}

	// check for scheme
	if ( strpos( $url, 'http' ) !== 0 ) {
		// check for relative url path
		if ( strpos( $url, '//' ) !== 0 ) {
			return ( strpos( $url, '/' ) === 0 ) ? untrailingslashit( home_url() ) . $url : trailingslashit( home_url() ) . $url;
		}
		return xmlsf()->scheme() . ':' . $url;
	}

	return $url;
}

/**
 * Is allowed domain
 *
 * @param $url
 *
 * @return mixed|void
 */
function xmlsf_is_allowed_domain( $url ) {

	$domains = xmlsf()->get_allowed_domains();

	$return = false;
	$parsed_url = parse_url($url);

	if (isset($parsed_url['host'])) {
		foreach ( $domains as $domain ) {
			if ( $parsed_url['host'] == $domain || strpos($parsed_url['host'],'.'.$domain) !== false ) {
				$return = true;
				break;
			}
		}
	}

	return apply_filters( 'xmlsf_allowed_domain', $return, $url );
}


/**
 * Polylang compatibility hooked into xml request filter
 *
 * @param array $request
 *
 * @return array
 */
function xmlsf_polylang_request( $request ) {

	if ( function_exists('pll_languages_list') ) {
		$request['lang'] = 'all'; // | 'all' | implode( ',', pll_languages_list() );
		// prevent language redirections
		add_filter( 'pll_check_canonical_url', '__return_false' );
	}

	return $request;
}
add_filter( 'xmlsf_request', 'xmlsf_polylang_request' );
add_filter( 'xmlsf_news_request', 'xmlsf_polylang_request' );

/**
 * WPML compatibility hooked into xml request filter
 *
 * @param array $request
 *
 * @return array
 */
function xmlsf_wpml_request( $request ) {
	global $sitepress, $wpml_query_filter;

	if ( is_object($sitepress) ) {
		// remove filters for tax queries
		remove_filter( 'get_terms_args', array($sitepress,'get_terms_args_filter') );
		remove_filter( 'get_term', array($sitepress,'get_term_adjust_id'), 1 );
		remove_filter( 'terms_clauses', array($sitepress,'terms_clauses') );
		// set language to all
		$sitepress->switch_lang('all');
	}

	if ( $wpml_query_filter ) {
		// remove query filters
		remove_filter( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ), 10, 2 );
		remove_filter( 'posts_where', array( $wpml_query_filter, 'posts_where_filter' ), 10, 2 );
	}

	$request['lang'] = ''; // strip off potential lang url parameter

	return $request;
}
add_filter( 'xmlsf_request', 'xmlsf_wpml_request' );
add_filter( 'xmlsf_news_request', 'xmlsf_wpml_request' );

/**
 * BBPress compatibility hooked into xml request filter
 *
 * @param array $request
 *
 * @return array
 */
function xmlsf_bbpress_request( $request ) {

	remove_filter( 'bbp_request', 'bbp_request_feed_trap' );

	return $request;
}
add_filter( 'xmlsf_request', 'xmlsf_bbpress_request' );
add_filter( 'xmlsf_news_request', 'xmlsf_bbpress_request' );
