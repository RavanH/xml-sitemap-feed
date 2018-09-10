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
