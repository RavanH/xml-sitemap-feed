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

// Remove Polylang filters.
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
