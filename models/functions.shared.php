<?php

/**
 * Filter request
 *
 * @param $request
 *
 * @return mixed
 */
function xmlsf_filter_request( $request ) {

	// short-circuit if request has already been filtered
	if ( xmlsf()->request_filtered ) return $request;

	if ( isset($request['feed']) && strpos($request['feed'],'sitemap') === 0 ) :

		// make sure we have the proper locale setting for calculations
		setlocale( LC_NUMERIC, 'C' );

		// include shared functions
		require_once XMLSF_DIR . '/models/functions.public-shared.php';

		// set the sitemap conditional flag
		xmlsf()->is_sitemap = true;

		// REPSONSE HEADERS filtering
		add_filter( 'wp_headers', 'xmlsf_headers' );

		// Remove filters to prevent stuff like cdn urls for xml stylesheet and images
		remove_all_filters( 'plugins_url' );
		remove_all_filters( 'wp_get_attachment_url' );
		remove_all_filters( 'image_downsize' );

		// modify request parameters
		$request['post_status'] = 'publish';
		$request['no_found_rows'] = true; // found rows calc is slow and only needed for pagination

		// PLUGIN COMPATIBILITIES
		// Polylang
		$request['lang'] = '';
		// WPML compat
		global $wpml_query_filter;
		if ( is_object($wpml_query_filter) ) {
			remove_filter( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ) );
			remove_filter( 'posts_where', array( $wpml_query_filter, 'posts_where_filter' ) );
			add_action( 'the_post', 'xmlsf_wpml_language_switcher' );
		}
		// bbPress
		remove_filter( 'bbp_request', 'bbp_request_feed_trap' );

		// check for gz request
		if ( substr($request['feed'], -3) == '.gz' ) {
			$request['feed'] = substr($request['feed'], 0, -3);
			xmlsf_output_compression();
		}

		if ( strpos($request['feed'],'sitemap-news') === 0 ) {
			// set the news sitemap conditional flag
			xmlsf()->is_news = true;

			require_once XMLSF_DIR . '/models/functions.public-sitemap-news.php';
			$request = xmlsf_sitemap_news_parse_request( $request );
		} else {
			require_once XMLSF_DIR . '/models/functions.public-sitemap.php';
			$request = xmlsf_sitemap_parse_request( $request );
		}

	endif;

	xmlsf()->request_filtered = true;

	return $request;
}

/**
 * Remove the trailing slash from permalinks that have an extension,
 * such as /sitemap.xml (thanks to Permalink Editor plugin for WordPress)
 *
 * @param string $request
 *
 * @return mixed
 */
function xmlsf_untrailingslash( $request ) {
	return pathinfo($request, PATHINFO_EXTENSION) ? untrailingslashit($request) : $request;
}

/**
 * Ping
 *
 * @since 5.1
 * @param $se google|bing
 * @param $sitemap sitemap
 * @param $interval seconds
 * @return string ping response|999 (skipped)
 */
function xmlsf_ping( $se, $sitemap, $interval ) {
	if ( 'google' == $se ) {
		$url = 'https://www.google.com/ping';
	} elseif ( 'bing' == $se ) {
		$url = 'https://www.bing.com/ping';
	} else {
		return '';
	}
	$url = add_query_arg( 'sitemap', urlencode( trailingslashit( get_bloginfo( 'url' ) ) . $sitemap ), $url );

	// check if we did not ping already within the interval
	if ( false === get_transient( 'xmlsf_ping_'.$se.'_'.$sitemap ) ) {
		// Ping !
		$response = wp_remote_request( $url );
		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 === $code ) {
			set_transient( 'xmlsf_ping_'.$se.'_'.$sitemap, '', $interval );
		}
	} else {
		// Skip !
		$code = 999;
	}

	do_action( 'xmlsf_ping', $se, $sitemap, $url, $code );

	return $code;
}

/**
 * Nginx helper purge urls
 * adds sitemap urls to the purge array.
 *
 * @param $urls array
 * @param $redis bool|false
 *
 * @return $urls array
 */
function xmlsf_nginx_helper_purge_urls( $urls = array(), $redis = false ) {

	if ( $redis ) {
		// wildcard allowed, this makes everything simple
		$urls[] = '/sitemap*.xml';
	} else {
		// no wildcard, go through the motions
		$sitemaps = get_option( 'xmlsf_sitemaps' );

		if ( !empty( $sitemaps['sitemap-news'] ) ) {
			$urls[] = '/sitemap-news.xml';
		}

		if ( !empty( $sitemaps['sitemap'] ) ) {
			$urls[] = '/sitemap.xml';
			$urls[] = '/sitemap-home.xml';
			$urls[] = '/sitemap-custom.xml';

			// add public post types sitemaps
			$post_types = get_option( 'xmlsf_post_types' );
			if ( is_array($post_types) ) {
				foreach ( $post_types as $post_type => $settings ) {
					$archive = !empty($settings['archive']) ? $settings['archive'] : '';
					foreach ( xmlsf_get_archives($post_type,$archive) as $url ) {
						$urls[] = parse_url( $url, PHP_URL_PATH);
					}
				}
			}

			// add public post taxonomies sitemaps
			$taxonomies = get_option('xmlsf_taxonomies');
			if ( is_array($taxonomies) ) {
				foreach ( $taxonomies as $taxonomy ) {
					$urls[] = parse_url( xmlsf_get_index_url('taxonomy',$taxonomy), PHP_URL_PATH);
				}
			}
		}
	}

	return $urls;
}
