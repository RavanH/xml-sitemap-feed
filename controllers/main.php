<?php

/**
 * Add sitemap rewrite rules
 *
 * @uses object $wp_rewrite
 *
 * @return void
 */
function xmlsf_rewrite_rules() {
	global $wp_rewrite;

	$sitemaps = get_option( 'xmlsf_sitemaps' );

	if ( isset($sitemaps['sitemap']) ) {
		/* One rule to ring them all */
		add_rewrite_rule('sitemap([a-z0-9_-]+)?\.([0-9]+)?\.?xml$', $wp_rewrite->index . '?feed=sitemap$matches[1]&m=$matches[2]', 'top');
	}

	if( isset($sitemaps['sitemap-news']) ) {
		add_rewrite_rule('sitemap-news\.xml$', $wp_rewrite->index . '?feed=sitemap-news', 'top');
	}
}

/**
 * Cache delete on clean_post_cache
 *
 * @param $post_ID
 * @param $post
 */
function xmlsf_clean_post_cache( $post_ID, $post ) {
	// are we moving the post in or out of published status?
	wp_cache_delete('xmlsf_get_archives', 'general');

	// TODO get year / month here to delete specific keys too !!!!
	$m = mysql2date('Ym',$post->post_date_gmt, false);
	$y = substr($m, 0, 4);

	// clear possible last post modified cache keys
	wp_cache_delete( 'lastpostmodified:gmt', 'timeinfo' ); // should be handled by WP core?
	wp_cache_delete( 'lastpostmodified'.$y.':gmt', 'timeinfo' );
	wp_cache_delete( 'lastpostmodified'.$m.':gmt', 'timeinfo' );
	wp_cache_delete( 'lastpostmodified'.$y.':gmt:'.$post->post_type, 'timeinfo' );
	wp_cache_delete( 'lastpostmodified'.$m.':gmt:'.$post->post_type, 'timeinfo' );

	// clear possible last post date cache keys
	wp_cache_delete( 'lastpostdate:gmt', 'timeinfo' );
	wp_cache_delete( 'lastpostdate:gmt:'.$post->post_type, 'timeinfo' );

	// clear possible fist post date cache keys
	wp_cache_delete( 'firstpostdate:gmt', 'timeinfo' );
	wp_cache_delete( 'firstpostdate:gmt:'.$post->post_type, 'timeinfo' );
}

/**
 * Do pings, hooked to transition post status
 *
 * @param $new_status
 * @param $old_status
 * @param $post
 */
function xmlsf_do_pings( $new_status, $old_status, $post ) {
	// are we publishing?
	if ( $old_status == 'publish' || $new_status != 'publish' )
		return;

	$sitemaps = get_option( 'xmlsf_sitemaps' );
	$ping = get_option( 'xmlsf_ping' );

	if ( !is_array($sitemaps) || empty($sitemaps) || !is_array($ping) || empty($ping) )
		return;

	if ( isset( $sitemaps['sitemap-news'] ) ) {

		// check if we've got a post type that is included in our news sitemap
		$news_tags = get_option('xmlsf_news_tags');
		if ( !empty($news_tags['post_type']) && is_array($news_tags['post_type']) && in_array($post->post_type,$news_tags['post_type']) ) {

			// Google ?
			if ( in_array( 'google', $ping ) ) {
				// check if we did not ping already within the last hour
				if ( false === get_transient('xmlsf_ping_google_sitemap_news') ) {
					// Ping !
					$uri = add_query_arg( 'sitemap', urlencode( trailingslashit( get_bloginfo( 'url' ) ) . $sitemaps['sitemap-news'] ), 'https://www.google.com/ping' );
					$response = wp_remote_request( $uri );
					$code = wp_remote_retrieve_response_code( $response );
					if ( 200 === $code ) {
						set_transient( 'xmlsf_ping_google_sitemap_news', $sitemaps['sitemap-news'], 5 * MINUTE_IN_SECONDS );
					} elseif ( defined('WP_DEBUG') && WP_DEBUG == true ) {
						error_log( 'Ping to '. $uri .' failed with response code: ' . $code );
					}
				} elseif ( defined('WP_DEBUG') && WP_DEBUG == true ) {
					error_log( 'Ping skipped: previous News Sitemap was sent to Google less than ' . 5 * MINUTE_IN_SECONDS . ' seconds ago.' );
				}
			}

			// Bing ?
			// nope...
		}
	}

	if ( isset( $sitemaps['sitemap'] ) ) {

		// check if we've got a post type that is included in our sitemap
		$post_types = get_option( 'xmlsf_post_types' );
		if ( is_array( $post_types ) && array_key_exists( $post->post_type, $post_types ) ) {

			// Google ?
			if ( in_array( 'google', $ping ) ) {
				// check if we did not ping already within the last hour
				if ( false === get_transient('xmlsf_ping_google_sitemap') ) {
					// Ping !
					$uri = add_query_arg( 'sitemap', urlencode( trailingslashit( get_bloginfo( 'url' ) ) . $sitemaps['sitemap'] ), 'https://www.google.com/ping' );
					$response = wp_remote_request( $uri );
					$code = wp_remote_retrieve_response_code( $response );
					if ( 200 === $code ) {
						set_transient( 'xmlsf_ping_google_sitemap', $sitemaps['sitemap'], HOUR_IN_SECONDS );
					} elseif ( defined('WP_DEBUG') && WP_DEBUG == true ) {
						error_log( 'Ping to '. $uri .' failed with response code: ' . $code );
					}
				} elseif ( defined('WP_DEBUG') && WP_DEBUG == true ) {
					error_log( 'Ping skipped: previous XML Sitemap was sent to Google less than ' . HOUR_IN_SECONDS . ' seconds ago.' );
				}
			}

			// Bing ?
			if ( in_array( 'bing', $ping ) ) {
				// check if we did not ping already within the last hour
				if ( false === get_transient('xmlsf_ping_bing_sitemap') ) {
					// Ping !
					$uri = add_query_arg( 'sitemap', urlencode( trailingslashit( get_bloginfo( 'url' ) ) . $sitemaps['sitemap'] ), 'https://www.bing.com/ping' );
					$response = wp_remote_request( $uri );
					$code = wp_remote_retrieve_response_code( $response );
					if ( 200 === $code ) {
						set_transient( 'xmlsf_ping_bing_sitemap', $sitemaps['sitemap'], HOUR_IN_SECONDS );
					} elseif ( defined('WP_DEBUG') && WP_DEBUG == true ) {
						error_log( 'Ping to '. $uri .' failed with response code: ' . $code );
					}
				} elseif ( defined('WP_DEBUG') && WP_DEBUG == true ) {
					error_log( 'Ping skipped: previous XML Sitemap was sent to Bing less than ' . HOUR_IN_SECONDS . ' seconds ago.' );
				}
			}
		}
	}
}

/**
 * Update term modified meta, hooked to transition post status
 *
 * @param $new_status
 * @param $old_status
 * @param $post
 */
function update_term_modified_meta( $new_status, $old_status, $post ) {

	$taxonomies = get_option( 'xmlsf_taxonomies' );

	if ( empty( $taxonomies ) )
		return;

	// are we not publishing or unpublishing?
	if ( $old_status == $new_status || $old_status != 'publish' && $new_status != 'publish' )
		return;

	$term_ids = array();
	foreach ( $taxonomies as $tax_name ) {
		$terms = wp_get_post_terms( $post->ID, $tax_name, array( 'fields' => 'ids' ));
		if ( !is_wp_error($terms) ) {
			$term_ids = array_merge( $term_ids, $terms );
		}
	}

	$time = date('Y-m-d H:i:s');

	foreach( $term_ids as $id ) {
		update_term_meta( $id, 'term_modified_gmt', $time );
	}
}
