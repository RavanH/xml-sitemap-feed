<?php
/**
 * XML Sitemaps Manager compatibility
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Compat;

/**
 * BBPress compatibility class
 */
class XMLSM {
	/**
	 * XML Sitemaps Manager compatibility hooked into plugins_loaded action.
	 */
	public static function plugins_loaded() {
		\remove_action( 'init', 'xmlsm_init', 9 );
		\remove_action( 'admin_init', 'xmlsm_admin_init' );
	}
}
