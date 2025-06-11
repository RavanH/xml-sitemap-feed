<?php
/**
 * WPML compatibility
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Compat;

/**
 * WPML compatibility class
 */
class WPML {
	/**
	 * Filter sitemap url when requesting the index or news sitemap.
	 *
	 * @since 5.5.4
	 *
	 * @param string $url     URL.
	 * @param string $sitemap Sitemap.
	 * @return string
	 */
	public static function convert_url( $url, $sitemap = 'index' ) {
		return 'index' === $sitemap ? \apply_filters( 'wpml_permalink', $url ) : $url;
	}

	/**
	 * Filter robots.txt rules adding sitemap URLs for each translation langauge.
	 *
	 * @since 5.5.4
	 *
	 * @param string $output Output.
	 * @return string
	 */
	public static function sitemap_robots( $output ) {
		foreach ( \apply_filters( 'wpml_active_languages', null ) as $code => $language ) {
			if ( \apply_filters( 'wpml_default_language', null ) !== $code ) {
				do_action( 'wpml_switch_language', $code );
				$output .= 'Sitemap: ' . \xmlsf()->sitemap->get_sitemap_url() . PHP_EOL;
				do_action( 'wpml_switch_language', null );
			}
		}

		return $output;
	}

	/**
	 * Filter robots.txt rules adding sitemap URLs for each translation langauge.
	 *
	 * @since 5.5.4
	 *
	 * @param string $output Output.
	 * @return string
	 */
	public static function sitemap_news_robots( $output ) {
		foreach ( \apply_filters( 'wpml_active_languages', null ) as $code => $language ) {
			if ( \apply_filters( 'wpml_default_language', null ) !== $code ) {
				do_action( 'wpml_switch_language', $code );
				$output .= 'Sitemap: ' . \xmlsf()->sitemap_news->get_sitemap_url() . PHP_EOL;
				do_action( 'wpml_switch_language', null );
			}
		}

		return $output;
	}
}
