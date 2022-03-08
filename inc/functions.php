<?php

/**
 * Ping Search Engine
 *
 * @since 5.1
 *
 * @param $se google|bing
 * @param $sitemap sitemap
 * @param $interval seconds
 *
 * @return int|null ping response code, or 999 when skipped or null when search engine is unknown
 */
function xmlsf_ping( $se, $sitemap, $interval ) {
	$se_urls = array(
		'google' => 'https://www.google.com/ping',
		'bing'   => 'https://www.bing.com/webmaster/ping.aspx'
	);

	if ( ! array_key_exists( $se, $se_urls ) ) {
		return '';
	}

	$url = add_query_arg( 'sitemap', urlencode( xmlsf_sitemap_url() ), $se_urls[$se] );

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
		$response = '';
		$code = 999;
	}

	do_action( 'xmlsf_ping', $se, $sitemap, $url, $code, $response );

	return $code;
}

/**
 * Get the public XML sitemap url.
 *
 * @since 5.4
 * @param string $sitemap
 * @param array $args arguments:
 *                    $type - post_type or taxonomy, default false
 *                    $m    - YYYY, YYYYMM, YYYYMMDD
 *                    $w    - week of the year ($m must be YYYY format)
 *                    $gz   - bool for GZ extension (triggers compression verification)
 *
 * @return string|false The sitemap URL or false if the sitemap doesn't exist.
 */
function xmlsf_sitemap_url( $sitemap = 'index', $args = array() ) {

	global $wp_rewrite;

	if ( 'news' === $sitemap ) {
		return $wp_rewrite->using_permalinks() ? esc_url( trailingslashit( home_url() ) . 'sitemap-news.xml' ) : esc_url( trailingslashit( home_url() ) . '?feed=sitemap-news' );
	}

	if ( get_option( 'xmlsf_core_sitemap' ) ) {
		return get_sitemap_url( $sitemap );
	}

	if ( 'index' === $sitemap ) {
		return $wp_rewrite->using_permalinks() ? esc_url( trailingslashit( home_url() ) . 'sitemap.xml' ) : esc_url( trailingslashit( home_url() ) . '?feed=sitemap' );
	}

	// Get our arguments.
	$args = apply_filters( 'xmlsf_index_url_args', wp_parse_args( $args, array( 'type' => false, 'm' => false, 'w' => false, 'gz' => false) ) );
	extract( $args );

	// Construct file name.
	if ( $wp_rewrite->using_permalinks() ) {
		$name = 'sitemap-'.$sitemap;
		$name .= $type ? '-'.$type : '';
		$name .= $m ? '.'.$m : '';
		$name .= $w ? '.'.$w : '';
		$name .= '.xml';
		$name .= $gz ? '.gz' : '';
	} else {
		$name = '?feed=sitemap-'.$sitemap;
		$name .= $gz ? '.gz' : '';
		$name .= $type ? '-'.$type : '';
		$name .= $m ? '&m='.$m : '';
		$name .= $w ? '&w='.$w : '';
	}

	return esc_url( trailingslashit( home_url() ) . $name );
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
 * Print XML Stylesheet
 * 
 * @param string|false $sitemap
 */
function xmlsf_xml_stylesheet( $sitemap = false ) {

	$url = xmlsf_get_stylesheet_url( $sitemap );

	if ( $url ) {
		echo '<?xml-stylesheet type="text/xsl" href="' . wp_make_link_relative( $url ) . '?ver=' . XMLSF_VERSION . '"?>' . PHP_EOL;
	}
}

/**
 * Get XML Stylesheet URL
 * 
 * @since 5.4
 * 
 * @param string|false $sitemap
 * @return string|false
 */
function xmlsf_get_stylesheet_url( $sitemap = false ) {

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
	 * assets/sitemap-author.xsl
	 * assets/sitemap-custom.xsl
	 * assets/sitemap-news.xsl
	 * assets/sitemap-[custom_sitemap_name].xsl
	 */

	$file = $sitemap ? 'assets/sitemap-'.$sitemap.'.xsl' : 'assets/sitemap.xsl';

	// Find theme stylesheet file.
	if ( file_exists( get_stylesheet_directory() . '/' . $file ) ) {
		$url = get_stylesheet_directory_uri() . '/' . $file;
	} elseif ( file_exists( get_template_directory() . '/' . $file ) ) {
		$url = get_template_directory_uri() . '/' . $file;
	} elseif ( file_exists( XMLSF_DIR . '/' . $file ) ) {
		$url = plugins_url( $file, XMLSF_BASENAME );
	} else {
		$url = false;
	}

	return apply_filters( 'xmlsf_stylesheet_url', $url );
}

/**
 * Error messages for ping
 */
function xmlsf_debug_ping( $se, $sitemap, $ping_url, $response_code, $response = '' ) {
	if ( defined('WP_DEBUG') && WP_DEBUG == true ) {
		if ( $response_code == 999 ) {
			error_log( 'Ping '. $se .' skipped.' );
		} else {
			error_log( 'Pinged '. $ping_url .' with response code: ' . $response_code );
		}

		if ( ! empty( $response ) ) {
			error_log( 'Response: ' . print_r( $response, true ) );
		}
	}
}

/**
 * Filter sitemap post types
 *
 * @since 5.0
 * @param $post_types array
 * @return array
 */
function xmlsf_filter_post_types( $post_types ) {
	$post_types = (array) $post_types;

	// Always exclude attachment and reply post types (bbPress)
	unset( $post_types['attachment'], $post_types['reply'] );

	return array_filter( $post_types );
}
