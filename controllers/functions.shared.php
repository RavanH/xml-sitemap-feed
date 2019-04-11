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
