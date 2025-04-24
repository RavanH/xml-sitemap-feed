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
	 * Filter sitemap url.
	 *
	 * @since 5.5.4
	 *
	 * @param string $url     URL.
	 * @param string $sitemap Sitemap.
	 * @return string
	 */
	public static function convert_url( $url, $sitemap = 'index' ) {
		global $sitepress;

		if ( 'index' !== $sitemap ) {
			return $url;
		}

		if ( \is_object( $sitepress ) && \method_exists( $sitepress, 'convert_url' ) ) {
			$url = $sitepress->convert_url( $url );
		}

		return $url;
	}

	/**
	 * Filter robots.txt rules
	 *
	 * @since 5.5.4
	 *
	 * @param string $output Output.
	 * @return string
	 */
	public static function robots_txt( $output ) {
		global $sitepress;

		if ( \is_object( $sitepress ) && \method_exists( $sitepress, 'convert_url' ) ) {

			foreach ( \apply_filters( 'wpml_active_languages', null ) as $code => $language ) {
				if ( \apply_filters( 'wpml_default_language', null ) !== $code ) {
					do_action( 'wpml_switch_language', $code );
					\XMLSF\sitemaps_enabled( 'sitemap' ) && $output      .= 'Sitemap: ' . \xmlsf()->sitemap->get_sitemap_url() . PHP_EOL;
					\XMLSF\sitemaps_enabled( 'sitemap-news' ) && $output .= 'Sitemap: ' . \xmlsf()->sitemap_news->get_sitemap_url() . PHP_EOL;
					do_action( 'wpml_switch_language', null );
				}
			}
		}

		return $output;
	}
}
