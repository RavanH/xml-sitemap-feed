<?php

/**
 * Filter request
 *
 * @param array $request
 *
 * @return array $request filtered
 */
function xmlsf_filter_request( $request ) {

	global $xmlsf;
	$xmlsf->request_filtered = true;

	// short-circuit if request is not a feed or it does not start with 'sitemap'
	if ( empty( $request['feed'] ) || strpos( $request['feed'], 'sitemap' ) !== 0 ) {
		return $request;
	}

	/** IT'S A SITEMAP */

	// set the sitemap conditional flag
	xmlsf()->is_sitemap = true;

	// save a few db queries
	add_filter( 'split_the_query', '__return_false' );

	// include shared public functions
	require_once XMLSF_DIR . '/models/functions.public-shared.php';

	/** COMPRESSION */

	// check for gz request
	if ( substr($request['feed'], -3) == '.gz' ) {
		// pop that .gz
		$request['feed'] = substr($request['feed'], 0, -3);
		// verify/apply compression settings
		xmlsf_output_compression();
	}

	/** PREPARE TO LOAD TEMPLATE */

	add_action (
		'do_feed_' . $request['feed'],
		'xmlsf_load_template',
		10,
		2
	);

	/** MODIFY REQUEST PARAMETERS */

	$request['post_status'] = 'publish';
	$request['no_found_rows'] = true; // found rows calc is slow and only needed for pagination

	// SPECIFIC REQUEST FILTERING AND PREPARATIONS
	if ( strpos( $request['feed'], 'news' ) === 8 ) {
		// set the news sitemap conditional flag
		xmlsf()->is_news = true;
		// include public news functions
		require_once XMLSF_DIR . '/models/functions.public-sitemap-news.php';
		// filter news request
		$request = xmlsf_sitemap_news_filter_request( $request );
	} else {
		// include public sitemap functions
		require_once XMLSF_DIR . '/models/functions.public-sitemap.php';
		// filter sitemap request
		$request = xmlsf_sitemap_filter_request( $request );
	}

	/** GENERAL MISC. PREPARATIONS */

	// prevent public errors breaking xml
	@ini_set( 'display_errors', 0 );

	// make sure we have the proper locale setting for calculations
	setlocale( LC_NUMERIC, 'C' );

	// REPSONSE HEADERS filtering
	add_filter( 'wp_headers', 'xmlsf_headers' );

	// Remove filters to prevent stuff like cdn urls for xml stylesheet and images
	remove_all_filters( 'plugins_url' );
	remove_all_filters( 'wp_get_attachment_url' );
	remove_all_filters( 'image_downsize' );

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
 *
 * @param $se google|bing
 * @param $sitemap sitemap
 * @param $interval seconds
 *
 * @return string ping response|999 (skipped)
 */
function xmlsf_ping( $se, $sitemap, $interval ) {
	if ( 'google' == $se ) {
		$url = 'https://www.google.com/ping';
	} elseif ( 'bing' == $se ) {
		$url = 'https://www.bing.com/webmaster/ping.aspx';
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
			$urls[] = '/sitemap-root.xml';
			$urls[] = '/sitemap-author.xml';
			$urls[] = '/sitemap-custom.xml';

			// add public post types sitemaps
			$post_types = get_option( 'xmlsf_post_types' );
			if ( is_array($post_types) ) {
				foreach ( $post_types as $post_type => $settings ) {
					$archive = !empty($settings['archive']) ? $settings['archive'] : '';
					foreach ( xmlsf_get_index_archive_data( $post_type, $archive ) as $url ) {
						$urls[] = parse_url( $url, PHP_URL_PATH);
					}
				}
			}

			// add public post taxonomies sitemaps
			$taxonomies = get_option('xmlsf_taxonomies');
			if ( is_array($taxonomies) ) {
				foreach ( $taxonomies as $taxonomy ) {
					$urls[] = parse_url( xmlsf_get_index_url( 'taxonomy', array( 'type' => $taxonomy ) ), PHP_URL_PATH );
				}
			}
		}
	}

	return $urls;
}
