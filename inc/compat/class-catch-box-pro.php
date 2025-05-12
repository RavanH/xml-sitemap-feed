<?php
/**
 * Catch Box Pro compatibility
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Compat;

/**
 * Class
 */
class Catch_Box_Pro {
	/**
	 * Admin notices.
	 */
	public static function admin_notices() {

		if ( \current_user_can( 'manage_options' ) && \XMLSF\sitemaps_enabled() && \catchbox_is_feed_url_present( null ) ) {
			include XMLSF_DIR . '/views/admin/notice-catchbox-feed-redirect.php';
		}
	}
}
