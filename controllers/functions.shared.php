<?php

/**
 * WPML: switch language
 * @see https://wpml.org/wpml-hook/wpml_post_language_details/
 */
function xmlsf_wpml_language_switcher() {
	global $sitepress, $post;

	$language = apply_filters( 'wpml_element_language_code', NULL, array( 'element_id' => $post->ID, 'element_type' => $post->post_type ) );
	$sitepress->switch_lang( $language );
}
global $sitepress;
if ( is_object( $sitepress ) ) {
	add_action( 'xmlsf_url', 'xmlsf_wpml_language_switcher' );
	add_action( 'xmlsf_news_url', 'xmlsf_wpml_language_switcher' );
}

/**
 * XML Stylesheet
 */
function xmlsf_xml_stylesheet( $sitemap = false ) {

	/**
	 * GET STYLESHEET URL
	 *
	 * DEVELOPERS: a custom stylesheet file in the active (parent or child) theme /assets subdirectory, will be used when found there
	 *
	 * Must start with 'sitemap', optionally followed by another designator, serparated by a hyphen.
	 * It should always end with the xsl extension.
	 *
	 * Examples:
	 * assets/sitemap.xsl
	 * assets/sitemap-root.xsl
	 * assets/sitemap-posttype.xsl
	 * assets/sitemap-taxonomy.xsl
	 * assets/sitemap-authors.xsl
	 * assets/sitemap-custom.xsl
	 * assets/sitemap-news.xsl
	 * assets/sitemap-[custom_sitemap_name].xsl
	**/

	$file = $sitemap ? 'assets/sitemap-'.$sitemap.'.xsl' : 'assets/sitemap.xsl';

	// find theme stylesheet file
	if ( file_exists( get_stylesheet_directory() . '/' . $file ) ) {
		$url = get_stylesheet_directory_uri() . '/' . $file;
	} elseif ( file_exists( get_template_directory() . '/' . $file ) ) {
		$url = get_template_directory_uri() . '/' . $file;
	} else {
		$url = plugins_url( $file, XMLSF_BASENAME );
	}

	echo '<?xml-stylesheet type="text/xsl" href="' . wp_make_link_relative( $url ) . '?ver=' . XMLSF_VERSION . '"?>' . PHP_EOL;
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
	if ( defined('WP_DEBUG') && WP_DEBUG ) {
		global $wpdb, $EZSQL_ERROR;
		$num = get_num_queries();
		$mem = function_exists('memory_get_peak_usage') ? round( memory_get_peak_usage()/1024/1024, 2 ) . 'M' : false;
		$limit = ini_get('memory_limit');
		// query errors
		$errors = '';
		if ( is_array($EZSQL_ERROR) && count($EZSQL_ERROR) ) {
			$i = 1;
			foreach ( $EZSQL_ERROR AS $e ) {
				$errors .= PHP_EOL . $i . ': ' . implode(PHP_EOL, $e) . PHP_EOL;
				$i += 1;
			}
		}
		// saved queries
		$saved = '';
		if ( defined('SAVEQUERIES') && SAVEQUERIES ) {
			$saved .= PHP_EOL . print_r($wpdb->queries, true);
		}

		require XMLSF_DIR . '/views/_usage.php';
	}
}

/**
 * Try to turn on ob_gzhandler output compression
 */
function xmlsf_output_compression() {
	// try to enable zlib.output_compression or fall back to output buffering with ob_gzhandler
	if ( false !== ini_set( 'zlib.output_compression', 'On' ) )
		// if zlib.output_compression turned on, then make sure to remove wp_ob_end_flush_all
		remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );
	else {
		ob_get_length()
		|| in_array('ob_gzhandler', ob_list_handlers())
		|| ob_start('ob_gzhandler');
	}

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

/**
 * Load feed template
 *
 * Hooked into do_feed_{sitemap...}. First checks for a child/parent theme template file, then falls back to plugin template
 *
 * @since 5.3
 *
 * @param bool $is_comment_feed unused
 * @param string $feed feed type
 */
function xmlsf_load_template( $is_comment_feed, $feed ) {

	/**
	 * GET TEMPLATE FILE
	 *
	 * DEVELOPERS: a custom template file in the active (parent or child) theme directory will be used when found there
	 *
	 * Must start with 'sitemap', optionally folowed by other designators, serperated by hyphens.
	 * It should always end with the php extension.
	 *
	 * Examples:
	 * sitemap.php
	 * sitemap-root.php
	 * sitemap-posttype.php
	 * * sitemap-posttype-post.php
	 * * sitemap-posttype-page.php
	 * * sitemap-posttype-[custom_post_type].php
	 * sitemap-taxonomy.php
	 * * sitemap-taxonomy-category.php
	 * * sitemap-taxonomy-post_tag.php
	 * * sitemap-taxonomy-[custom_taxonomy].php
	 * sitemap-authors.php
	 * sitemap-custom.php
	 * sitemap-news.php
	 * sitemap-[custom_sitemap_name].php
	**/

	$parts = explode( '-' , $feed, 3 );

	// possible theme template file names
	$templates = array();
	if ( ! empty( $parts[1] ) ) {
		if ( ! empty( $parts[2] ) ) {
			$templates[] = "{$parts[0]}-{$parts[1]}-{$parts[2]}.php";
		}
		$templates[] = "{$parts[0]}-{$parts[1]}.php";
	} else {
		$templates[] = "{$parts[0]}.php";
	}

	// find theme template file and load that
	locate_template( $templates, true );

	// still here, then fall back on plugin template file
	load_template( XMLSF_DIR . '/views/feed-' . implode( '-', array_slice( $parts, 0, 2 ) ) . '.php' );
}
