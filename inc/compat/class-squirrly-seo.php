<?php
/**
 * Squirrly SEO compatibility
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Compat;

/**
 * Class
 */
class Squirrly_SEO {
	/**
	 * Admin notices.
	 */
	public static function admin_notices() {
		if ( ! \current_user_can( 'manage_options' ) || \in_array( 'squirrly_seo_sitemap', (array) \get_user_meta( \get_current_user_id(), 'xmlsf_dismissed' ), true ) ) {
			return;
		}

		$squirrly = json_decode( \get_option( 'sq_options', '' ) );

		// check squirrly sitemap module.
		if ( is_object( $squirrly ) && $squirrly->sq_auto_sitemap ) {
			// sitemap module on.
			include XMLSF_DIR . '/views/admin/notice-squirrly-seo-sitemap.php';
		}
	}

	/**
	 * Admin notices.
	 */
	public static function news_admin_notices() {
		if ( ! \current_user_can( 'manage_options' ) || \in_array( 'squirrly_seo_sitemap_news', (array) \get_user_meta( \get_current_user_id(), 'xmlsf_dismissed' ), true ) ) {
			return;
		}

		$squirrly = json_decode( \get_option( 'sq_options', '' ) );

		// check aioseop sitemap module.
		if ( is_object( $squirrly ) && ! empty( $squirrly->sq_sitemap->{'sitemap-news'}[1] ) ) {
			// sitemap module on.
			include XMLSF_DIR . '/views/admin/notice-squirrly-seo-sitemap-news.php';
		}
	}
}
