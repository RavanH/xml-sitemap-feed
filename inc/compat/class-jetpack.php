<?php
/**
 * Jetpack compatibility
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Compat;

/**
 * Class
 */
class Jetpack {
	/**
	 * Admin notices.
	 */
	public static function admin_notices() {
		if ( ! \current_user_can( 'manage_options' ) || ! \XMLSF\sitemaps_enabled( 'sitemap' ) || \in_array( 'jetpack_sitemap', (array) \get_user_meta( \get_current_user_id(), 'xmlsf_dismissed' ), true ) ) {
			return;
		}

		$modules = (array) \get_option( 'jetpack_active_modules' );

		if ( in_array( 'sitemaps', $modules, true ) ) {
			// sitemap module on.
			include XMLSF_DIR . '/views/admin/notice-jetpack-sitemap.php';
		}
	}
}
