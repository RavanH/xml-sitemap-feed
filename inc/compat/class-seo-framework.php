<?php
/**
 * SEO Framework compatibility
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Compat;

/**
 * Class
 */
class SEO_Framework {
	/**
	 * Admin notices.
	 */
	public static function admin_notices() {
		// check sfw sitemap module.
		if ( ! \current_user_can( 'manage_options' ) || ! \XMLSF\sitemaps_enabled( 'sitemap' ) || \in_array( 'seoframework_sitemap', (array) \get_user_meta( \get_current_user_id(), 'xmlsf_dismissed' ), true ) ) {
			return;
		}

		$sfw_options = (array) \get_option( 'autodescription-site-settings' );

		if ( ! empty( $sfw_options['sitemaps_output'] ) ) {
			// sitemap module on.
			include XMLSF_DIR . '/views/admin/notice-seoframework-sitemap.php';
		}
	}
}
