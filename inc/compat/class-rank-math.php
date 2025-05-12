<?php
/**
 * Rank Math compatibility
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Compat;

/**
 * Class
 */
class Rank_Math {
	/**
	 * Admin notices.
	 */
	public static function admin_notices() {
		if ( ! \current_user_can( 'manage_options' ) || ! \XMLSF\sitemaps_enabled( 'sitemap' ) ) {
			return;
		}

		// check date archive redirection.
		$rankmath_titles = \get_option( 'rank-math-options-titles' );
		if ( ! empty( $rankmath_titles['disable_date_archives'] ) && 'on' === $rankmath_titles['disable_date_archives'] && \xmlsf()->sitemap->uses_date_archives() ) {
			include XMLSF_DIR . '/views/admin/notice-rankmath-date-redirect.php';
		}

		// check rank math sitemap option.
		if ( ! \in_array( 'rankmath_sitemap', (array) \get_user_meta( \get_current_user_id(), 'xmlsf_dismissed' ), true ) ) {
			$rankmath_modules = (array) \get_option( 'rank_math_modules' );
			if ( \in_array( 'sitemap', $rankmath_modules, true ) ) {
				include XMLSF_DIR . '/views/admin/notice-rankmath-sitemap.php';
			}
		}
	}
}
