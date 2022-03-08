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



	return $request;
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

/*****************
 * COMPATIBILITY *
 ****************/

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
