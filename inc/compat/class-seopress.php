<?php
/**
 * SEOPress compatibility
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Compat;

/**
 * Class
 */
class SEOPress {
	/**
	 * Admin notices.
	 */
	public static function admin_notices() {
		if ( ! \current_user_can( 'manage_options' ) || ! \XMLSF\sitemaps_enabled( 'sitemap' ) ) {
			return;
		}

		// check date archive redirection.
		$seopress_toggle = \get_option( 'seopress_toggle' );

		$seopress_titles = \get_option( 'seopress_titles_option_name' );
		if ( ! empty( $seopress_toggle['toggle-titles'] ) && ! empty( $seopress_titles['seopress_titles_archives_date_disable'] ) && \xmlsf()->sitemap->uses_date_archives() ) {
			include XMLSF_DIR . '/views/admin/notice-seopress-date-redirect.php';
		}

		// check seopress sitemap option.
		if ( ! \in_array( 'seopress_sitemap', (array) \get_user_meta( \get_current_user_id(), 'xmlsf_dismissed' ), true ) ) {
			$seopress_xml_sitemap = \get_option( 'seopress_xml_sitemap_option_name' );
			if ( ! empty( $seopress_toggle['toggle-xml-sitemap'] ) && ! empty( $seopress_xml_sitemap['seopress_xml_sitemap_general_enable'] ) ) {
				include XMLSF_DIR . '/views/admin/notice-seopress-sitemap.php';
			}
		}
	}
}
