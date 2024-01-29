<?php
/**
 * Settings Sanitization
 *
 * @package XML Sitemap & Google News
 */

/**
 * Sanitization Class
 **/
class XMLSF_Admin_Sanitize {

	/**
	 * Sanitize sitemap settings
	 *
	 * @param array $save Settings array.
	 *
	 * @return array
	 */
	public static function sitemaps_settings( $save ) {
		if ( '1' !== get_option( 'blog_public' ) ) {
			return '';
		}
		$save      = (array) $save;
		$old       = (array) get_option( 'xmlsf_sitemaps' );
		$sanitized = array();

		if ( $old !== $save ) {
			// When sitemaps are added or removed, make rewrite rules REGENERATE on next page load.
			set_transient( 'xmlsf_flush_rewrite_rules', true );

			// Switched on news sitemap.
			if ( ! empty( $save['sitemap-news'] ) && empty( $old['sitemap-news'] ) ) {
				// Check news tag settings.
				if ( ! get_option( 'xmlsf_news_tags' ) ) {
					add_option( 'xmlsf_news_tags', xmlsf()->default_news_tags );
				}
			}
		}

		if ( ! empty( $save['sitemap'] ) ) {
			$sanitized['sitemap'] = apply_filters( 'xmlsf_sitemap_filename', $save['sitemap'] );
		}

		if ( ! empty( $save['sitemap-news'] ) ) {
			$sanitized['sitemap-news'] = apply_filters( 'xmlsf_sitemap_news_filename', $save['sitemap-news'] );
		}

		return $sanitized;
	}
}
