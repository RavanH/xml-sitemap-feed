<?php

/**
 * Filter news WHERE
 * only posts from the last 48 hours
 *
 * @param string $where
 *
 * @return string
 */
function xmlsf_news_filter_where( $where = '' ) {
	return $where . ' AND post_date_gmt > \'' . date('Y-m-d H:i:s', strtotime('-48 hours')) . '\'';
}

/**
 * Filter news limits
 * override default feed limit for GN
 * @return string
 */
function xmlsf_news_filter_limits( $limits ) {
	return 'LIMIT 0, 1000';
}

/**
 * Filter no news limits
 * in case there is no news, just take the latest post
 * @return string
 */
function xmlsf_news_filter_no_news_limits( $limits ) {
	return 'LIMIT 0, 1';
}

/**
 * Filter request
 *
 * @param $request
 *
 * @return mixed
 */
function xmlsf_sitemap_news_filter_request( $request ) {

	// prepare for news and return modified request
	$options = get_option( 'xmlsf_news_tags' );
	$post_types = is_array($options) && !empty($options['post_type']) ? $options['post_type'] : array('post');
	$post_types = apply_filters( 'xmlsf_news_post_types', $post_types);

	// disable caching
	define('DONOTCACHEPAGE', true);
	define('DONOTCACHEDB', true);

	// set up query filters
	$live = false;
	foreach ($post_types as $post_type) {
		if ( get_lastpostdate('gmt', $post_type) > date('Y-m-d H:i:s', strtotime('-48 hours')) ) {
			$live = true;
			break;
		}
	}

	if ( $live ) {
		add_filter( 'post_limits', 'xmlsf_news_filter_limits' );
		add_filter( 'posts_where', 'xmlsf_news_filter_where', 10, 1 );
	} else {
		add_filter( 'post_limits', 'xmlsf_news_filter_no_news_limits' );
	}

	// post type
	$request['post_type'] = $post_types;

	// categories
	if ( is_array($options) && isset($options['categories']) && is_array($options['categories']) ) {
		$request['cat'] = implode( ',', $options['categories'] );
	}

	return $request;
}

/**
 * Get language used in News Sitemap
 *
 * @param $post_id
 *
 * @return string
 */
function xmlsf_get_language( $post_id ) {

	$language = xmlsf()->blog_language();

	// WPML compat
	global $sitepress;
	// Polylang
	if ( function_exists('pll_get_post_language') ) {
		$lang = pll_get_post_language( $post_id, 'slug' );
		if ( !empty($lang) )
			$language = xmlsf_parse_language_string( $lang );
	} elseif ( is_object($sitepress) && method_exists($sitepress, 'get_language_for_element') ) {
		$post_type = (array) get_query_var( 'post_type', 'post' );
		$lang = $sitepress->get_language_for_element( $post_id, 'post_'.$post_type[0] );
		//apply_filters( 'wpml_element_language_code', null, array( 'element_id' => $post_id, 'element_type' => $post_type ) );
		if ( !empty($lang) )
			$language = xmlsf_parse_language_string( $lang );
	}

	return apply_filters( 'xmlsf_post_language', $language, $post_id );
}

/**
 * Parse language string
 *
 * @param string $lang unformatted language string
 *
 * @return string
 */
function xmlsf_parse_language_string( $lang ) {
	$lang = convert_chars( strtolower( strip_tags( $lang ) ) );

	// no underscores
	if ( strpos( $lang, '_' ) ) {
		$expl = explode('_', $lang);
		$lang = $expl[0];
	}

	// no hyphens except...
	if ( strpos( $lang, '-' ) && !in_array( $lang, array('zh-cn','zh-tw') ) ) {
		// explode on hyphen and use only first part
		$expl = explode('-', $lang);
		$lang = $expl[0];
	}

	return !empty($lang) ? $lang : 'en';
}
