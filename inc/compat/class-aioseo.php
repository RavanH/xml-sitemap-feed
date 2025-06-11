<?php
/**
 * All in One SEO compatibility
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Compat;

/**
 * Class
 */
class AIOSEO {
	/**
	 * Admin notices.
	 */
	public static function admin_notices() {
		if ( ! \current_user_can( 'manage_options' ) || \in_array( 'aioseop_sitemap', (array) \get_user_meta( \get_current_user_id(), 'xmlsf_dismissed' ), true ) ) {
			return;
		}

		// check aioseop sitemap module.
		$aioseop = json_decode( \get_option( 'aioseo_options', '' ) );

		if ( is_object( $aioseop ) && true === $aioseop->sitemap->general->enable ) {
			// sitemap module on.
			include XMLSF_DIR . '/views/admin/notice-aioseop-sitemap.php';
		}
	}
}
