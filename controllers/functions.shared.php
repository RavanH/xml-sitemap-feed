<?php

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
	$date = date( 'c' );

	require XMLSF_DIR . '/views/_generator.php';
}

/**
 * Usage info for debugging
 */
function xmlsf_usage() {
	if ( defined('WP_DEBUG') && WP_DEBUG == true ) {
		$num = get_num_queries();
		$mem = function_exists('memory_get_peak_usage') ? round( memory_get_peak_usage()/1024/1024, 2 ) . 'M' : false;
		$limit = ini_get('memory_limit');

		require XMLSF_DIR . '/views/_usage.php';
	}
}

/**
 * Try to turn on ob_gzhandler output compression
 */
function xmlsf_output_compression() {
	// try to enable zlib.output_compression or fall back to output buffering with ob_gzhandler
	( isset($_SERVER['HTTP_X_VARNISH']) && is_numeric($_SERVER['HTTP_X_VARNISH']) )
	|| ini_get( 'zlib.output_compression' )
	|| '' === ini_set( 'zlib.output_compression', '1' )
	|| ob_get_length()
	|| in_array('ob_gzhandler', ob_list_handlers())
	|| ob_start('ob_gzhandler');

	if ( defined('WP_DEBUG') && WP_DEBUG == true ) {
		// zlib
		$zlib = ini_get( 'zlib.output_compression' ) ? 'ENABLED' : 'DISABLED';
		error_log('Zlib output compression '.$zlib);

		// ob_gzhandler
		$gz = in_array('ob_gzhandler', ob_list_handlers()) ? 'ENABLED' : 'DISABLED';
		error_log('GZhandler output buffer compression '.$gz);
	}
}

/**
 * Error messages for ping
 */
function xmlsf_debug_ping( $se, $sitemap, $ping_url, $response_code ) {
	if ( defined('WP_DEBUG') && WP_DEBUG == true ) {
		if ( $response_code == 999 ) {
			error_log( 'Ping '. $se .' skipped.' );
		} else {
			error_log( 'Pinged '. $ping_url .' with response code: ' . $response_code );
		}
	}
}
