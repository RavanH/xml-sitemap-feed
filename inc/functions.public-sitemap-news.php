<?php

/**
 * Response headers filter
 * Does not check if we are really in a sitemap feed.
 *
 * @param $headers
 *
 * @return array
 */

function xmlsf_news_nocache_headers( $headers ) {
	// Prevent proxy caches serving a cached news sitemap.
	$headers['Cache-Control'] .= ', no-store';

	return $headers;
}

/**
 * Filter news WHERE
 * only posts from the last 48 hours
 *
 * @param string $where
 *
 * @return string
 */

function xmlsf_news_filter_where( $where = '' ) {
	return $where . ' AND post_date_gmt > \'' . gmdate( 'Y-m-d H:i:s', strtotime('-48 hours') ) . '\'';
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
 * Parse language string into two or three letter ISO 639 code.
 *
 * @param string $lang unformatted language string
 *
 * @return string
 */

function xmlsf_parse_language_string( $lang ) {
	// Lower case, no tags.
	$lang = convert_chars( strtolower( strip_tags( $lang ) ) );

	// Convert underscores.
	$lang = str_replace( '_', '-', $lang );

	// No hyphens except...
	if ( strpos( $lang, '-' ) ) :
		if ( 0 === strpos( $lang, 'zh' ) ) {
			$lang = strpos( $lang, 'hk' ) || strpos( $lang, 'tw' ) || strpos( $lang, 'hant' ) ? 'zh-tw' : 'zh-cn';
		} else {
			// Explode on hyphen and use only first part.
			$expl = explode( '-', $lang );
			$lang = $expl[0];
		}
	endif;

	// Make sure it's max 3 letters.
	$lang = substr( $lang, 0, 2 );

	return $lang;
}

/*****************
 * COMPATIBILITY *
 ****************/

/**
 * Post language filter for Polylang.
 *
 * @param $locale
 * @param $post_id
 *
 * @return string
 */

function xmlsf_polylang_post_language_filter( $locale, $post_id ) {
	return function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $post_id, 'locale' ) : $locale;
}

/**
 * Post language filter for WPML.
 *
 * @param $locale
 * @param $post_id
 * @param $post_type
 *
 * @return string
 */

function xmlsf_wpml_post_language_filter( $locale, $post_id, $post_type = 'post' ) {
 	global $sitepress;
	return $sitepress ? apply_filters( 'wpml_element_language_code', $locale, array( 'element_id' => $post_id, 'element_type' => $post_type ) ) : $locale;
}
