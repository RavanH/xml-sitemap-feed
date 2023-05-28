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
	// prevent proxy caches serving a cached news sitemap
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
 * Filter request for news sitemap
 *
 * @param $request
 *
 * @return mixed
 */
function xmlsf_sitemap_news_filter_request( $request ) {

	// REPSONSE HEADERS filtering
 	add_filter( 'nocache_headers', 'xmlsf_news_nocache_headers' );

	/** FILTER HOOK FOR PLUGIN COMPATIBILITIES */
	$request = apply_filters( 'xmlsf_news_request', $request );
	/**
	 * Developers: add your actions that should run when a news sitemap request is found with:
	 *
	 * add_filter( 'xmlsf_news_request', 'your_filter_function' );
	 *
	 * Filters hooked here already:
	 * xmlsf_polylang_request - Polylang compatibility
	 * xmlsf_wpml_request - WPML compatibility
	 * xmlsf_bbpress_request - bbPress compatibility
	 */

	// prepare for news and return modified request
	$options = get_option( 'xmlsf_news_tags' );
	$post_types = is_array($options) && !empty($options['post_type']) ? $options['post_type'] : array('post');
	$post_types = apply_filters( 'xmlsf_news_post_types', $post_types);

	// disable caching
	$request['cache_results'] = false;
	if ( ! defined('DONOTCACHEPAGE') ) define('DONOTCACHEPAGE', true);
	if ( ! defined('DONOTCACHEDB') ) define('DONOTCACHEDB', true);

	// set up query filters
	$live = false;
	foreach ( $post_types as $post_type ) {
		if ( strtotime( get_lastpostdate( 'gmt', $post_type ) ) > strtotime( gmdate( 'Y-m-d H:i:s', strtotime('-48 hours') ) ) ) {
			$live = true;
			break;
		}
	}

	if ( $live ) {
		add_filter( 'post_limits', function() { return 'LIMIT 0, 1000';	} );
		add_filter( 'posts_where', 'xmlsf_news_filter_where', 10, 1 );
	} else {
		add_filter( 'post_limits', function() { return 'LIMIT 0, 1'; } );
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
 * Get images
 *
 * @param string $which
 *
 * @return array
 */
/*
function xmlsf_news_get_images( $which ) {
	global $post;
	$images = array();

	if ( 'attached' == $which ) {
		$args = array( 'post_type' => 'attachment', 'post_mime_type' => 'image', 'numberposts' => 1, 'post_status' =>'inherit', 'post_parent' => $post->ID );
		$attachments = get_posts($args);
		if ( ! empty( $attachments[0] ) ) {
			$url = wp_get_attachment_image_url( $attachments[0]->ID, 'full' );
			$url = xmlsf_get_absolute_url( $url );
			if ( !empty($url) ) {
				$images[] = array(
					'loc' => esc_attr( esc_url_raw( $url ) ),
					'title' => apply_filters( 'the_title_xmlsitemap', $attachments[0]->post_title ),
					'caption' => apply_filters( 'the_title_xmlsitemap', $attachments[0]->post_excerpt )
					// 'caption' => apply_filters( 'the_title_xmlsitemap', get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ) )
				);
			}
		}
	} elseif ( 'featured' == $which ) {
		if ( has_post_thumbnail( $post->ID ) ) {
			$attachment = get_post( get_post_thumbnail_id( $post->ID ) );
			$url = wp_get_attachment_image_url( get_post_thumbnail_id( $post->ID ), 'full' );
			$url = xmlsf_get_absolute_url( $url );
			if ( !empty($url) ) {
				$images[] = array(
					'loc' => esc_attr( esc_url_raw( $url ) ),
					'title' => apply_filters( 'the_title_xmlsitemap', $attachment->post_title ),
					'caption' => apply_filters( 'the_title_xmlsitemap', $attachment->post_excerpt )
					// 'caption' => apply_filters( 'the_title_xmlsitemap', get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ) )
				);
			}
		}
	}

	return $images;
}
*/

/**
 * Post language filter for Polylang
 *
 * @param $language
 * @param $post_id
 *
 * @return string
 */
function xmlsf_polylang_post_language_filter( $language, $post_id ) {

	if ( function_exists('pll_get_post_language') ) {
		$language = pll_get_post_language( $post_id, 'slug' );
	}

	return $language;
}
add_filter( 'xmlsf_news_language', 'xmlsf_polylang_post_language_filter', 10, 2 );

/**
 * Post language filter for WPML
 *
 * @param $language
 * @param $post_id
 * @param $post_type
 *
 * @return string
 */
function xmlsf_wpml_post_language_filter( $language, $post_id, $post_type = 'post' ) {

 	global $sitepress;

	if ( $sitepress )
		$language = apply_filters( 'wpml_element_language_code', $language, array( 'element_id' => $post_id, 'element_type' => $post_type ) );

	return $language;
}
add_filter( 'xmlsf_news_language', 'xmlsf_wpml_post_language_filter', 10, 3 );

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
	$lang = str_replace( '_', '-', $lang );

	// no hyphens except...
	if ( 0 === strpos( $lang, 'zh' ) ) {
		$lang = strpos( $lang, 'hant' ) || strpos( $lang, 'hk' ) || strpos( $lang, 'tw' ) ? 'zh-tw' : 'zh-cn';
	} else {
		// explode on hyphen and use only first part
		$expl = explode('-', $lang);
		$lang = $expl[0];
	}

	return !empty($lang) ? $lang : 'en';
}
add_filter( 'xmlsf_news_language', 'xmlsf_parse_language_string', 99 );
