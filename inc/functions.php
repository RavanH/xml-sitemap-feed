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
 * Print XML Stylesheet
 *
 * @param string|false $sitemap Optional sitemap name.
 */
function xml_stylesheet( $sitemap = false ) {

	$url = namespace\get_stylesheet_url( $sitemap );

	if ( $url ) {
		echo '<?xml-stylesheet type="text/xsl" href="' . \esc_url( \wp_make_link_relative( $url ) ) . '?ver=' . \esc_xml( XMLSF_VERSION ) . '"?>' . PHP_EOL;
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
 * Load feed template
 *
 * Hooked into do_feed_{sitemap...}. First checks for a child/parent theme template file, then falls back to plugin template
 *
 * @since 5.3
 *
 * @param bool   $is_comment_feed Unused.
 * @param string $feed            Feed type.
 */
function load_template( $is_comment_feed, $feed ) {
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
	 */

	$parts     = \explode( '-', $feed, 3 );
	$templates = array();
	$found     = false;

	// Possible theme template file names.
	if ( ! empty( $parts[1] ) && \in_array( $parts[1], array( 'news', 'posttype', 'taxonomy', 'author', 'custom' ), true ) ) {
		if ( ! empty( $parts[2] ) ) {
			$templates[] = "sitemap-{$parts[1]}-{$parts[2]}.php";
		}
		$templates[] = "sitemap-{$parts[1]}.php";
	}
	$templates[] = 'sitemap.php';

	// Locate and load theme template file or use plugin template.
	if ( ! \locate_template( $templates, true ) ) {
		foreach ( $templates as $template ) {
			$file = XMLSF_DIR . '/views/feed-' . $template;
			if ( \file_exists( $file ) ) {
				\load_template( $file );
				break;
			}
		}
	}
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
 * Clear cache metadata
 *
 * @param string $type The metadata type to clear.
 */
function clear_metacache( $type = '' ) {
	switch ( $type ) {
		case 'images':
			// Clear all images meta caches...
			\delete_metadata( 'post', 0, '_xmlsf_image_attached', '', true );
			\delete_metadata( 'post', 0, '_xmlsf_image_featured', '', true );
			\set_transient( 'xmlsf_images_meta_primed', array() );
			break;

		case 'comments':
			\delete_metadata( 'post', 0, '_xmlsf_comment_date_gmt', '', true );
			\set_transient( 'xmlsf_comments_meta_primed', array() );
			break;

		case 'terms':
			\delete_metadata( 'term', 0, 'term_modified', '', true );
			break;

		case 'users':
			\delete_metadata( 'user', 0, 'user_modified', '', true );
			break;

		default:
			$all_types = array( 'images', 'comments', 'terms', 'users' );
			foreach ( $all_types as $_type ) {
				namespace\clear_metacache( $_type );
			}
	}
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
 * Plugin compatibility hooks and filters.
 */
function plugin_compat() {
	$active_plugins = (array) get_option( 'active_plugins', array() );

	// Polylang compatibility.
	if ( in_array( 'polylang/polylang.php', $active_plugins, true ) ) {
		\add_filter( 'xmlsf_blogpages', array( __NAMESPACE__ . '\Compat\Polylang', 'get_translations' ) );
		\add_filter( 'xmlsf_frontpages', array( __NAMESPACE__ . '\Compat\Polylang', 'get_translations' ) );
		\add_filter( 'xmlsf_request', array( __NAMESPACE__ . '\Compat\Polylang', 'filter_request' ) );
		\add_filter( 'xmlsf_news_request', array( __NAMESPACE__ . '\Compat\Polylang', 'filter_request' ) );
		\add_action( 'xmlsf_sitemap_loaded', array( __NAMESPACE__ . '\Compat\Polylang', 'request_actions' ) );
		\add_filter( 'xmlsf_news_sitemap_loaded', array( __NAMESPACE__ . '\Compat\Polylang', 'request_actions' ) );
		\add_filter( 'xmlsf_news_publication_name', array( __NAMESPACE__ . '\Compat\Polylang', 'news_name' ), 10, 2 );
		\add_filter( 'xmlsf_news_language', array( __NAMESPACE__ . '\Compat\Polylang', 'post_language_filter' ), 10, 2 );
		\add_action( 'xmlsf_register_sitemap_provider', array( __NAMESPACE__ . '\Compat\Polylang', 'remove_replace_provider' ) );
		\add_action( 'xmlsf_register_sitemap_provider_after', array( __NAMESPACE__ . '\Compat\Polylang', 'add_replace_provider' ) );
		\add_filter( 'xmlsf_root_data', array( __NAMESPACE__ . '\Compat\Polylang', 'root_data' ) );
		\add_filter( 'xmlsf_url_after', array( __NAMESPACE__ . '\Compat\Polylang', 'author_archive_translations' ), 10, 3 );
		\add_filter( 'xmlsf_sitemap_subtype', array( __NAMESPACE__ . '\Compat\Polylang', 'filter_sitemap_subtype' ) );
	}

	// WPML compatibility.
	if ( in_array( 'sitepress-multilingual-cms/sitepress.php', $active_plugins, true ) ) {
		// Make sure we get the correct sitemap URL in language context.
		\add_filter( 'xmlsf_sitemap_url', array( __NAMESPACE__ . '\Compat\WPML', 'convert_url' ), 10, 2 );
		\add_filter( 'xmlsf_sitemap_news_url', array( __NAMESPACE__ . '\Compat\WPML', 'convert_url' ) );
		// Add sitemap in Robots TXT.
		\add_filter( 'robots_txt', array( __NAMESPACE__ . '\Compat\WPML', 'robots_txt' ), 9 );
	}

	// bbPress compatibility.
	if ( in_array( 'bbpress/bbpress.php', $active_plugins, true ) ) {
		\add_filter( 'xmlsf_request', array( __NAMESPACE__ . '\Compat\BBPress', 'filter_request' ) );
		\add_filter( 'xmlsf_news_request', array( __NAMESPACE__ . '\Compat\BBPress', 'filter_request' ) );
	}

	// XMLSM compatibility.
	if ( in_array( 'xml-sitemaps-manager/xml-sitemaps-manager.php', $active_plugins, true ) ) {
		\add_filter( 'plugins_loaded', array( __NAMESPACE__ . '\Compat\XMLSM', 'disable' ), 11 );
	}
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
