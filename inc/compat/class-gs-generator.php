<?php
/**
 * Google Sitemap Generator compatibility
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Compat;

/**
 * Class
 */
class GS_Generator {
	/**
	 * Admin notices.
	 */
	public static function admin_notices() {
		if ( ! \current_user_can( 'manage_options' ) || \in_array( 'gsgenerator_sitemap', (array) \get_user_meta( \get_current_user_id(), 'xmlsf_dismissed' ), true ) ) {
			return;
		}

		include XMLSF_DIR . '/views/admin/notice-google-sitemap-generator.php';
	}
}
