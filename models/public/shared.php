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
	// set noindex
	$headers['X-Robots-Tag'] = 'noindex, follow';
	$headers['Content-Type'] = 'text/xml; charset=' . get_bloginfo('charset');
	return $headers;
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
