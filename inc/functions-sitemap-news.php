<?php
/**
 * Public News Sitemap Functions
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF;

defined( 'XMLSF_GOOGLE_NEWS_NAME' ) || define( 'XMLSF_GOOGLE_NEWS_NAME', false );

/**
 * Response headers filter
 * Does not check if we are really in a sitemap feed.
 *
 * @param array $headers The headers array.
 *
 * @return array
 */
function news_nocache_headers( $headers ) {
	// Prevent proxy caches serving a cached news sitemap.
	$headers['Cache-Control'] .= ', no-store';

	return $headers;
}

\add_filter( 'nocache_headers', __NAMESPACE__ . '\news_nocache_headers' );

/**
 * Filter news WHERE
 * only posts from the last 48 hours
 *
 * @param string $where DB Query where clause.
 *
 * @return string
 */
function news_filter_where( $where = '' ) {
	$hours  = (int) \apply_filters( 'xmlsf_news_hours_old', 48 );
	$hours  = \XMLSF\sanitize_number( $hours, 1, 168, 0 );
	$where .= ' AND post_date_gmt > \'' . \gmdate( 'Y-m-d H:i:s', \strtotime( '-' . $hours . ' hours' ) ) . '\'';

	return $where;
}

/**
 * Parse language string into two or three letter ISO 639 code.
 *
 * @param string $lang Unformatted language string.
 *
 * @return string
 */
function parse_language_string( $lang ) {
	// Lower case, no tags.
	$lang = \convert_chars( \strtolower( \wp_strip_all_tags( $lang ) ) );

	// Convert underscores.
	$lang = \str_replace( '_', '-', $lang );

	// No hyphens except...
	if ( \strpos( $lang, '-' ) ) :
		if ( 0 === \strpos( $lang, 'zh' ) ) {
			$lang = \strpos( $lang, 'hk' ) || \strpos( $lang, 'tw' ) || \strpos( $lang, 'hant' ) ? 'zh-tw' : 'zh-cn';
		} else {
			// Explode on hyphen and use only first part.
			$expl = \explode( '-', $lang );
			$lang = $expl[0];
		}
	endif;

	// Make sure it's max 3 letters.
	$lang = \substr( $lang, 0, 2 );

	return $lang;
}

\add_filter( 'xmlsf_news_language', __NAMESPACE__ . '\parse_language_string', 99 );
