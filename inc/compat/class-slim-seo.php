<?php
/**
 * Slim SEO compatibility
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Compat;

/**
 * Class
 */
class Slim_SEO {
	/**
	 * Admin notices.
	 */
	public static function admin_notices() {
		if ( ! \current_user_can( 'manage_options' ) || \in_array( 'slim_seo_sitemap', (array) \get_user_meta( \get_current_user_id(), 'xmlsf_dismissed', true ), true ) ) {
			return;
		}

		$slimseo = \get_option( 'slim_seo' );

		if ( empty( $slimseo ) || ( isset( $slimseo['features'] ) && in_array( 'sitemaps', (array) $slimseo['features'], true ) ) ) {
			include XMLSF_DIR . '/views/admin/notice-slim-seo-sitemap.php';
		}
	}
}
