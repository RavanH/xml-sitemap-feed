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
 * Get images
 *
 * @param string $sitemap
 *
 * @return array|bool
 */
function xmlsf_get_images( $sitemap = '' ) {
	global $post;
	$images = array();

	if ( 'news' == $sitemap ) {
		$options = get_option('xmlsf_news_tags');
		$which = isset($options['image']) ? $options['image'] : '';
	} else {
		$options = get_option('xmlsf_post_types');
		$which = is_array($options) && isset($options[$post->post_type]['tags']['image']) ? $options[$post->post_type]['tags']['image'] : '';
	}

	if ( 'attached' == $which ) {
		$args = array( 'post_type' => 'attachment', 'post_mime_type' => 'image', 'numberposts' => -1, 'post_status' =>'inherit', 'post_parent' => $post->ID );
		$attachments = get_posts($args);
		if ( $attachments ) {
			foreach ( $attachments as $attachment ) {
				$url = wp_get_attachment_image_url( $attachment->ID, 'full' );
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
	} elseif ( 'featured' == $which ) {
		if ( has_post_thumbnail( $post->ID ) ) {
			$attachment = get_post( get_post_thumbnail_id( $post->ID ) );
			$url = wp_get_attachment_image_url( get_post_thumbnail_id( $post->ID ), 'full' );
			$url = xmlsf_get_absolute_url( $url );
			if ( !empty($url) ) {
				$images[] =  array(
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
