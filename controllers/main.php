<?php

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
		// TODO also check category if needed
		$news_tags = get_option('xmlsf_news_tags');
		if ( ! empty( $news_tags['post_type'] ) && in_array( $post->post_type, (array) $news_tags['post_type'] ) ) {
			xmlsf_ping( 'google', $sitemaps['sitemap-news'], 5 * MINUTE_IN_SECONDS );
		}
	}

	if ( isset( $sitemaps['sitemap'] ) ) {
		// check if we've got a post type that is included in our sitemap
		$post_types = get_option( 'xmlsf_post_types' );
		if ( is_array( $post_types ) && array_key_exists( $post->post_type, $post_types ) ) {

			foreach ( $ping as $se ) {
				xmlsf_ping( $se, $sitemaps['sitemap'], HOUR_IN_SECONDS );
			}
		}
	}
}

/**
 * WPML: switch language
 * @see https://wpml.org/wpml-hook/wpml_post_language_details/
 */
function xmlsf_wpml_language_switcher() {
	global $sitepress, $post;
	if ( isset( $sitepress ) ) {
		$post_language = apply_filters( 'wpml_post_language_details', NULL, $post->ID );
		$sitepress->switch_lang( $post_language['language_code'] );
	}
}

/**
 * Generator info
 */
function xmlsf_generator() {
	$date = date('Y-m-d\TH:i:s+00:00');

	require XMLSF_DIR . '/views/_generator.php';
}

/**
 * Usage info for debugging
 */
function xmlsf_usage() {
	if ( defined('WP_DEBUG') && WP_DEBUG == true ) {
		$num = get_num_queries();
		$mem = function_exists('memory_get_peak_usage') ? round(memory_get_peak_usage()/1024/1024,2) : 0;

		require XMLSF_DIR . '/views/_usage.php';
	}
}

/**
 * Try to turn on ob_gzhandler output compression
 */
function xmlsf_ob_gzhandler() {
	in_array('ob_gzhandler', ob_list_handlers())
	|| ob_get_contents()
	|| ini_get("zlib.output_compression")
	|| ( isset($_SERVER['HTTP_X_VARNISH']) && is_numeric($_SERVER['HTTP_X_VARNISH']) )
	|| ob_start("ob_gzhandler");

	if ( defined('WP_DEBUG') && WP_DEBUG == true ) {
		$status = in_array('ob_gzhandler', ob_list_handlers()) ? 'ENABLED' : 'DISABLED';
		error_log('GZhandler output buffer compression '.$status);
	}
}
