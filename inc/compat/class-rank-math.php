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
		if ( ! \current_user_can( 'manage_options' ) ) {
			return;
		}

		// check date archive redirection.
		$rankmath_titles = \get_option( 'rank-math-options-titles' );
		if ( ! empty( $rankmath_titles['disable_date_archives'] ) && 'on' === $rankmath_titles['disable_date_archives'] && \xmlsf()->sitemap->uses_date_archives() ) {
			?>
			<div class="notice notice-error fade is-dismissible">
				<p>
					<?php
					printf( /* translators: conflicting plugin name */
						\esc_html__( 'A setting in the %s plugin causes all date based sitemaps to redirect to the main page.', 'xml-sitemap-feed' ),
						\esc_html__( 'Rank Math', 'rank-math' )
					);
					?>
					<?php
					printf( /* translators: Date archives, Archives (linked to WP SEO plugin settings), Split by, None, post types (linked to Sitemap settings) */
						\esc_html__( 'Please either enable %1$s under %2$s in your SEO settings or set all %3$s options to %4$s under %5$s in your XML Sitemap settings.', 'xml-sitemap-feed' ),
						'<strong>' . \esc_html__( 'Date Archives', 'rank-math' ) . '</strong>',
						'<a href="' . \esc_url( \admin_url( 'admin.php' ) ) . '?page=rank-math-options-titles#setting-panel-misc">' . \esc_html__( 'Misc Pages', 'rank-math' ) . '</a>',
						'<strong>' . \esc_html__( 'Split by', 'xml-sitemap-feed' ) . '</strong>',
						'<strong>' . \esc_html( \translate( 'None' ) ) . '</strong>',
						'<a href="' . \esc_url( \admin_url( 'options-general.php' ) ) . '?page=xmlsf&tab=post_types">' . \esc_html__( 'Post types', 'xml-sitemap-feed' ) . '</a>'
					);
					?>
				</p>
			</div>
			<?php
		}

		// check rank math sitemap option.
		if ( ! \in_array( 'rankmath_sitemap', (array) \get_user_meta( \get_current_user_id(), 'xmlsf_dismissed' ), true ) ) {
			$rankmath_modules = (array) \get_option( 'rank_math_modules' );
			if ( \in_array( 'sitemap', $rankmath_modules, true ) ) {
				?>
				<div class="notice notice-warning fade is-dismissible">
					<p>
						<?php
						printf( /* translators: Conflicting Plugn name, Plugin name */
							\esc_html__( 'The %1$s XML Sitemap is not compatible with %2$s.', 'xml-sitemap-feed' ),
							\esc_html__( 'Rank Math', 'rank-math' ),
							\esc_html__( 'XML Sitemap & Google News', 'xml-sitemap-feed' )
						);
						?>
						<?php
						printf( /* translators: Sitemap page name (linked to SEOPress plugin settings), XML Sitemap Index, Reading Settings admin page (linked to Reading settings) */
							\esc_html__( 'Please either disable the XML Sitemap under %1$s in your SEO settings or disable the option %2$s on %3$s.', 'xml-sitemap-feed' ),
							'<a href="' . \esc_url( \admin_url( 'admin.php' ) ) . '?page=rank-math&view=modules">' . \esc_html__( 'Modules', 'rank-math' ) . '</a>',
							\esc_html__( 'XML Sitemap Index', 'xml-sitemap-feed' ),
							'<a href="' . \esc_url( \admin_url( 'options-reading.php' ) ) . '#xmlsf_sitemaps">' . \esc_html( \translate( 'Reading Settings' ) ) . '</a>'
						);
						?>
					</p>
					<form action="" method="post">
						<?php \wp_nonce_field( XMLSF_BASENAME . '-notice', '_xmlsf_notice_nonce' ); ?>
						<p>
							<input type="hidden" name="xmlsf-dismiss" value="rankmath_sitemap" />
							<input type="submit" class="button button-small" name="xmlsf-dismiss-submit" value="<?php echo \esc_attr( \translate( 'Dismiss' ) ); ?>" />
						</p>
					</form>
				</div>
				<?php
			}
		}
	}
}
