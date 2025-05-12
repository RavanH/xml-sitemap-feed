<?php
/**
 * Yoast SEO compatibility
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Compat;

/**
 * Class
 */
class WP_SEO {
	/**
	 * Admin notices.
	 */
	public static function admin_notices() {
		if ( ! \current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( \XMLSF\sitemaps_enabled( 'sitemap' ) ) {
			// check date archive redirection.
			$wpseo_titles = \get_option( 'wpseo_titles' );
			if ( ! empty( $wpseo_titles['disable-date'] ) && \xmlsf()->sitemap->uses_date_archives() ) {
				include XMLSF_DIR . '/views/admin/notice-wpseo-date-redirect.php';
			}

			// check wpseo sitemap option.
			if ( ! \in_array( 'wpseo_sitemap', (array) \get_user_meta( \get_current_user_id(), 'xmlsf_dismissed' ), true ) ) {
				$wpseo = \get_option( 'wpseo' );
				if ( ! empty( $wpseo['enable_xml_sitemap'] ) ) {
					include XMLSF_DIR . '/views/admin/notice-wpseo-sitemap.php';
				}
			}
		}

		if ( \XMLSF\sitemaps_enabled( 'sitemap-news' ) ) {
			// Check Remove category feeds option. TODO move to google news.
			$wpseo = \get_option( 'wpseo' );
			if ( ! empty( $wpseo['remove_feed_categories'] ) ) {
				// check if Google News sitemap is limited to categories.
				$news_tags = \get_option( 'xmlsf_news_tags' );
				if ( ! empty( $news_tags['categories'] ) ) {
					include XMLSF_DIR . '/views/admin/notice-wpseo-category-feed-redirect.php';
				}
			}
		}
	}
}
