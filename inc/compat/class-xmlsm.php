<?php
/**
 * XML Sitemaps Manager compatibility
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Compat;

/**
 * Class
 */
class XMLSM {
	/**
	 * XML Sitemaps Manager compatibility hooked into plugins_loaded action.
	 */
	public static function disable() {
		\remove_action( 'init', 'xmlsm_init', 9 );
		\remove_action( 'admin_init', 'xmlsm_admin_init' );
		\remove_action( 'init', array( 'XMLSitemapsManager\Load', 'front' ), 9 );
		\remove_action( 'admin_init', array( 'XMLSitemapsManager\Load', 'admin' ) );
	}

	/**
	 * Admin notices.
	 */
	public static function admin_notices() {
		if ( ! \XMLSF\sitemaps_enabled( 'sitemap' ) || ! \current_user_can( 'manage_options' ) || \in_array( 'xml_sitemaps_manager', (array) \get_user_meta( \get_current_user_id(), 'xmlsf_dismissed' ), true ) ) {
			return;
		}

		include XMLSF_DIR . '/views/admin/notice-xml-sitemaps-manager.php';
	}
}
