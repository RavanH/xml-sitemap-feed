<?php
/**
 * Polylang compatibility functions
 *
 * @package XML Sitemap & Google News
 */

/**
 * Polylang compatibility hooked into xml request filters
 *
 * @param array $request The request.
 *
 * @return array
 */
function xmlsf_polylang_request( $request ) {

	$request['lang'] = '';

	return $request;
}

\add_filter( 'xmlsf_request', 'xmlsf_polylang_request' );
\add_filter( 'xmlsf_core_request', 'xmlsf_polylang_request' );
\add_filter( 'xmlsf_news_request', 'xmlsf_polylang_request' );

// Remove Polylang filters to place all languages in the same sitemaps.
\remove_filter( 'pll_set_language_from_query', array( $polylang->sitemaps, 'set_language_from_query' ) );
\remove_filter( 'rewrite_rules_array', array( $polylang->sitemaps, 'rewrite_rules' ) );
\remove_filter( 'wp_sitemaps_add_provider', array( $polylang->sitemaps, 'replace_provider' ) );

add_action(
	'xmlsf_sitemap_loaded',
	function () {
		// Prevent language redirections.
		\add_filter( 'pll_check_canonical_url', '__return_false' );
	}
);

/**
 * News publication name filter for Polylang.
 *
 * @param string $name Publication name.
 * @param int    $post_id Post ID.
 *
 * @return string
 */
function polylang_news_name( $name, $post_id ) {
	return \pll_translate_string( $name, \pll_get_post_language( $post_id, 'locale' ) );
}

\add_filter( 'xmlsf_news_publication_name', __NAMESPACE__ . '\polylang_news_name', 10, 2 );

/**
 * Post language filter for Polylang.
 *
 * @param string $locale Locale.
 * @param int    $post_id Post ID.
 *
 * @return string
 */
function polylang_post_language_filter( $locale, $post_id ) {
	return \function_exists( 'pll_get_post_language' ) ? \pll_get_post_language( $post_id, 'locale' ) : $locale;
}

\add_filter( 'xmlsf_news_language', __NAMESPACE__ . '\polylang_post_language_filter', 10, 2 );
