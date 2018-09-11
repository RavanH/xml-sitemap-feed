<?php

/**
 * Filter request
 *
 * @param $request
 *
 * @return mixed
 */
function xmlsf_filter_request( $request ) {

	if ( isset($request['feed']) && strpos($request['feed'],'sitemap') === 0 ) :

		// make sure we have the proper locale setting for calculations
		setlocale( LC_NUMERIC, 'C' );

		require_once XMLSF_DIR . '/controllers/public/shared.php';
		require_once XMLSF_DIR . '/models/public/shared.php';

		// set the sitemap conditional flag
		xmlsf()->is_sitemap = true;

		// REPSONSE HEADERS filtering
		add_filter( 'wp_headers', 'xmlsf_headers');

		// modify request parameters
		$request['post_status'] = 'publish';
		$request['no_found_rows'] = true;
		$request['cache_results'] = false;
		$request['update_post_term_cache'] = false;
		$request['update_post_meta_cache'] = false;

		// Polylang compat
		$request['lang'] = '';
		// WPML compat
		global $wpml_query_filter;
		if ( is_object($wpml_query_filter) ) {
			remove_filter( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ) );
			remove_filter( 'posts_where', array( $wpml_query_filter, 'posts_where_filter' ) );
			add_action( 'the_post', 'xmlsf_wpml_language_switcher' );
		}

		if ( $request['feed'] == 'sitemap-news' ) {
			// set the news sitemap conditional flag
			xmlsf()->is_news = true;

			require_once XMLSF_DIR . '/controllers/public/sitemap-news.php';
			require_once XMLSF_DIR . '/models/public/sitemap-news.php';
			$request = xmlsf_sitemap_news_filter_request( $request );
		} else {
			require_once XMLSF_DIR . '/controllers/public/sitemap.php';
			require_once XMLSF_DIR . '/models/public/sitemap.php';
			xmlsf_feed_templates();
			$request = xmlsf_sitemap_filter_request( $request );
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
 * Filter sitemap post types
 *
 * @since 5.0
 * @param $post_types array
 * @return array
 */
function xmlsf_filter_post_types( $post_types ) {
	foreach ( xmlsf()->disabled_post_types() as $post_type ) {
		if ( isset( $post_types[$post_type]) )
			unset( $post_types[$post_type] );
	}

	return array_filter( $post_types );
}

/**
 * Filter news post types
 *
 * @since 5.0
 * @param $post_types array
 * @return array
 */
function xmlsf_news_filter_post_types( $post_types ) {
	foreach ( array('attachment','page') as $post_type ) {
		if ( isset( $post_types[$post_type]) )
			unset( $post_types[$post_type] );
	}

	return array_filter( $post_types );
}
