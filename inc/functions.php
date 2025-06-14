<?php
/**
 * Global Functions
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF;

/**
 * Sitemap loaded
 *
 * Common actions to prepare sitemap loading.
 * Hooked into xmlsf_sitemap_loaded action.
 */
function sitemap_loaded() {
	// Prepare headers.
	\add_filter( 'wp_headers', __NAMESPACE__ . '\headers' );

	// Set the sitemap conditional flag.
	\xmlsf()->is_sitemap = true;

	// Make sure we have the proper locale setting for calculations.
	\setlocale( LC_NUMERIC, 'C' );

	// Save a few db queries.
	\add_filter( 'split_the_query', '__return_false' );

	// Don't go redirecting anything from now on...
	\remove_action( 'template_redirect', 'redirect_canonical' );

	/** GENERAL MISC. PREPARATIONS */

	// Prevent public errors breaking xml.
	@\ini_set( 'display_errors', 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.PHP.IniSet.display_errors_Disallowed

	// Remove filters to prevent stuff like cdn urls for xml stylesheet and images.
	\remove_all_filters( 'plugins_url' );
	\remove_all_filters( 'wp_get_attachment_url' );
	\remove_all_filters( 'image_downsize' );

	// Remove actions that we do not need.
	\remove_all_actions( 'widgets_init' );
	\remove_all_actions( 'wp_footer' );
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
function get_stylesheet_url( $sitemap = false ) {
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
	if ( \file_exists( \get_stylesheet_directory() . '/' . $file ) ) {
		$url = \get_stylesheet_directory_uri() . '/' . $file;
	} elseif ( \file_exists( \get_template_directory() . '/' . $file ) ) {
		$url = \get_template_directory_uri() . '/' . $file;
	} elseif ( \file_exists( XMLSF_DIR . '/' . $file ) ) {
		$url = \plugins_url( $file, XMLSF_BASENAME );
	} else {
		$url = false;
	}

	return \apply_filters( 'xmlsf_stylesheet_url', $url );
}

/**
 * Are any sitemaps enabled?
 *
 * @since 5.4
 *
 * @param string $which Which sitemap to check for. Default any sitemap.
 *
 * @return bool
 */
function sitemaps_enabled( $which = 'any' ) {
	$sitemaps = (array) \get_option( 'xmlsf_sitemaps', array() );

	if ( 1 !== (int) \get_option( 'blog_public' ) || empty( $sitemaps ) ) {
		$return = false;
	} elseif ( 'any' === $which ) {
		$return = true;
	} else {
		$key    = 'news' === $which ? 'sitemap-news' : $which;
		$return = \array_key_exists( $key, $sitemaps );
	}

	return \apply_filters( 'xmlsf_sitemaps_enabled', $return, $which );
}

/**
 * Response headers filter
 * Does not check if we are really in a sitemap feed.
 *
 * @param array $headers Response headers.
 *
 * @return array
 */
function headers( $headers ) {
	// Force status 200.
	$headers['Status'] = '200';

	// Set noindex.
	$headers['X-Robots-Tag'] = 'noindex, follow';

	// Force content type.
	$headers['Content-Type'] = 'application/xml; charset=' . \get_bloginfo( 'charset' );

	// And return, merged with nocache headers.
	return \array_merge( $headers, \wp_get_nocache_headers() );
}

/**
 * Santize number value
 * Expects proper locale setting for calculations: setlocale( LC_NUMERIC, 'C' );
 *
 * Returns a float or integer within the set limits.
 *
 * @since 5.2
 *
 * @param float|int|string $number   Number value.
 * @param float|int        $min      Minimum value.
 * @param float|int        $max      Maximum value.
 * @param int              $decimals Formating, can be float or integer.
 *
 * @return float|int
 */
function sanitize_number( $number, $min = .1, $max = 1, $decimals = 1 ) {
	if ( ! is_numeric( $number ) ) {
		return $number;
	}

	\setlocale( LC_NUMERIC, 'C' );

	$number = $decimals ? \str_replace( ',', '.', $number ) : \str_replace( ',', '', $number );
	$number = $decimals ? \floatval( $number ) : \intval( $number );

	$number = \min( \max( $min, $number ), $max );

	return \number_format( $number, $decimals, '.', '' );
}

/**
 * Filter robots.txt rules
 *
 * @param string $output Default robots.txt content.
 *
 * @return string
 */
function robots_txt( $output ) {
	$robots = \trim( \get_option( 'xmlsf_robots', '' ) );
	if ( $robots ) {
		$output .= PHP_EOL . $robots . PHP_EOL;
	}

	return $output;
}

/**
 * Default options
 *
 * @param bool $key Which key to get.
 *
 * @return array|string|bool|null
 */
function get_default_settings( $key = false ) {
	$defaults = xmlsf()->defaults();

	if ( $key ) {
		$return = ( isset( $defaults[ $key ] ) ) ? $defaults[ $key ] : null;
	} else {
		$return = $defaults;
	}

	return \apply_filters( 'xmlsf_defaults', $return, $key, $defaults );
}
