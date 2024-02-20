<?php
/**
 * Global Functions
 *
 * @package XML Sitemap & Google News
 */

/**
 * Get the public XML sitemap url.
 *
 * @since 5.4
 *
 * @param string $sitemap Sitemap name.
 * @param array  $args    Arguments array:
 *                        $type - post_type or taxonomy, default false
 *                        $m    - YYYY, YYYYMM, YYYYMMDD
 *                        $w    - week of the year ($m must be YYYY format)
 *                        $gz   - bool for GZ extension (triggers compression verification).
 *
 * @return string|false The sitemap URL or false if the sitemap doesn't exist.
 */
function xmlsf_sitemap_url( $sitemap = 'index', $args = array() ) {

	global $wp_rewrite;

	if ( 'news' === $sitemap ) {
		return $wp_rewrite->using_permalinks() ? esc_url( trailingslashit( home_url() ) . 'sitemap-news.xml' ) : esc_url( trailingslashit( home_url() ) . '?feed=sitemap-news' );
	}

	// Use core function get_sitemap_url if using core sitemaps.
	if ( xmlsf_uses_core_server() ) {
		return get_sitemap_url( $sitemap );
	}

	if ( 'index' === $sitemap ) {
		return $wp_rewrite->using_permalinks() ? esc_url( trailingslashit( home_url() ) . 'sitemap.xml' ) : esc_url( trailingslashit( home_url() ) . '?feed=sitemap' );
	}

	// Get our arguments.
	$args = apply_filters(
		'xmlsf_index_url_args',
		wp_parse_args(
			$args,
			array(
				'type' => false,
				'm'    => false,
				'w'    => false,
				'gz'   => false,
			)
		)
	);

	// Construct file name.
	if ( $wp_rewrite->using_permalinks() ) {
		$name  = 'sitemap-' . $sitemap;
		$name .= $args['type'] ? '-' . $args['type'] : '';
		$name .= $args['m'] ? '.' . $args['m'] : '';
		$name .= $args['w'] ? '.' . $args['w'] : '';
		$name .= '.xml';
		$name .= $args['gz'] ? '.gz' : '';
	} else {
		$name  = '?feed=sitemap-' . $sitemap;
		$name .= $args['gz'] ? '.gz' : '';
		$name .= $args['type'] ? '-' . $args['type'] : '';
		$name .= $args['m'] ? '&m=' . $args['m'] : '';
		$name .= $args['w'] ? '&w=' . $args['w'] : '';
	}

	return esc_url( trailingslashit( home_url() ) . $name );
}

/**
 * Print XML Stylesheet
 *
 * @param string|false $sitemap Optional sitemap name.
 */
function xmlsf_xml_stylesheet( $sitemap = false ) {

	$url = xmlsf_get_stylesheet_url( $sitemap );

	if ( $url ) {
		echo '<?xml-stylesheet type="text/xsl" href="' . esc_url( wp_make_link_relative( $url ) ) . '?ver=' . esc_xml( XMLSF_VERSION ) . '"?>' . PHP_EOL;
	}
}

/**
 * Get XML Stylesheet URL
 *
 * @since 5.4
 *
 * @param string|false $sitemap Optional sitemap name.
 *
 * @return string|false
 */
function xmlsf_get_stylesheet_url( $sitemap = false ) {

	/**
	 * GET STYLESHEET URL
	 *
	 * DEVELOPERS: a custom stylesheet file in the active (parent or child) theme /assets subdirectory, will be used when found there
	 *
	 * Must start with 'sitemap', optionally followed by another designator, separated by a hyphen.
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

	$file = $sitemap ? 'assets/sitemap-' . $sitemap . '.xsl' : 'assets/sitemap.xsl';

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
 * WPML compatibility hooked into xmlsf_add_settings and xmlsf_news_add_settings actions
 *
 * @return void
 */
function xmlsf_wpml_remove_home_url_filter() {
	// Remove WPML home url filter.
	global $wpml_url_filters;
	if ( is_object( $wpml_url_filters ) ) {
		remove_filter( 'home_url', array( $wpml_url_filters, 'home_url_filter' ), - 10 );
	}
}
add_action( 'xmlsf_add_settings', 'xmlsf_wpml_remove_home_url_filter' );
add_action( 'xmlsf_news_add_settings', 'xmlsf_wpml_remove_home_url_filter' );

/**
 * Are we using the WP core server?
 * Returns whether the WordPress core sitemap server is used or not.
 *
 * @since 5.4
 *
 * @return bool
 */
function xmlsf_uses_core_server() {
	// Sitemap disabled.
	if ( ! xmlsf_sitemaps_enabled( 'sitemap' ) || ! function_exists( 'get_sitemap_url' ) ) {
		return false;
	}

	// Check settings.
	$server = get_option( 'xmlsf_server', xmlsf()->defaults( 'server' ) );
	return ! empty( $server ) && 'core' === $server;
}
