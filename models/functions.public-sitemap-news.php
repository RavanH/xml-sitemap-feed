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
	return $where . ' AND post_date_gmt > \'' . gmdate( 'Y-m-d H:i:s', strtotime('-48 hours') ) . '\'';
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
function xmlsf_sitemap_news_parse_request( $request ) {

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
	foreach ($post_types as $post_type) {
		if ( strtotime( get_lastpostdate( 'gmt', $post_type ) ) > strtotime( gmdate( 'Y-m-d H:i:s', strtotime('-48 hours') ) ) ) {
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
	if ( is_object($sitepress) && method_exists($sitepress, 'get_language_for_element') ) {
		$post_type = (array) get_query_var( 'post_type', 'post' );
		$lang = $sitepress->get_language_for_element( $post_id, 'post_'.$post_type[0] );
		//apply_filters( 'wpml_element_language_code', null, array( 'element_id' => $post_id, 'element_type' => $post_type ) );
		if ( !empty($lang) )
			$language = xmlsf_parse_language_string( $lang );
	}
	// Polylang
	elseif ( function_exists('pll_get_post_language') ) {
		$lang = pll_get_post_language( $post_id, 'slug' );
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
