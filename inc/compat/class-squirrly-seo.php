<?php
/**
 * Squirrly SEO compatibility
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Compat;

/**
 * Class
 */
class Squirrly_SEO {
	/**
	 * Admin notices.
	 */
	public static function admin_notices() {
		if ( ! \current_user_can( 'manage_options' ) || \in_array( 'squirrly_seo_sitemap', (array) \get_user_meta( \get_current_user_id(), 'xmlsf_dismissed', true ), true ) ) {
			return;
		}

		$squirrly = \json_decode( \get_option( 'sq_options', '' ) );

		// check squirrly sitemap module.
		if ( is_object( $squirrly ) && $squirrly->sq_auto_sitemap ) {
			// sitemap module on.
			?>
			<div class="notice notice-warning fade is-dismissible">
				<p>
					<?php
					printf( /* translators: Conflicting Plugn name, Plugin name */
						\esc_html__( 'The %1$s XML Sitemap is not compatible with %2$s.', 'xml-sitemap-feed' ),
						\esc_html__( 'Squirrly SEO', 'squirrly-seo' ),
						\esc_html__( 'XML Sitemap & Google News', 'xml-sitemap-feed' )
					);
					?>
					<?php
					printf( /* translators: Sitemap page name (linked to Squirrly SEO plugin settings), XML Sitemap Index, Reading Settings admin page (linked to Reading settings) */
						\esc_html__( 'Please either disable the XML Sitemap under %1$s in your SEO settings or disable the option %2$s on %3$s.', 'xml-sitemap-feed' ),
						'<a href="' . \esc_url( \admin_url( 'admin.php' ) ) . '?page=sq_features">' . \esc_html__( 'Miscellaneous Features', 'squirrly-seo' ) . '</a>',
						\esc_html__( 'XML Sitemap Index', 'xml-sitemap-feed' ),
						'<a href="' . \esc_url( \admin_url( 'options-reading.php' ) ) . '#xmlsf_sitemaps">' . \esc_html( \translate( 'Reading Settings' ) ) . '</a>'
					);
					?>
				</p>
				<form action="" method="post">
					<?php \wp_nonce_field( XMLSF_BASENAME . '-notice', '_xmlsf_notice_nonce' ); ?>
					<p>
						<input type="hidden" name="xmlsf-dismiss" value="squirrly_seo_sitemap" />
						<input type="submit" class="button button-small" name="xmlsf-dismiss-submit" value="<?php echo \esc_attr( \translate( 'Dismiss' ) ); ?>" />
					</p>
				</form>
			</div>
			<?php
		}
	}

	/**
	 * Admin notices.
	 */
	public static function news_admin_notices() {
		if ( ! \current_user_can( 'manage_options' ) || \in_array( 'squirrly_seo_sitemap_news', (array) \get_user_meta( \get_current_user_id(), 'xmlsf_dismissed', true ), true ) ) {
			return;
		}

		$squirrly = \json_decode( \get_option( 'sq_options', '' ) );

		// check aioseop sitemap module.
		if ( is_object( $squirrly ) && ! empty( $squirrly->sq_sitemap->{'sitemap-news'}[1] ) ) {
			// sitemap module on.
			?>
			<div class="notice notice-warning fade is-dismissible">
				<p>
					<strong><?php \esc_html_e( 'XML Sitemap & Google News', 'xml-sitemap-feed' ); ?></strong>
				</p>
				<form action="" method="post">
					<?php \wp_nonce_field( XMLSF_BASENAME . '-notice', '_xmlsf_notice_nonce' ); ?>
					<input type="hidden" name="xmlsf-dismiss" value="xmlsf_advanced_news" />
					<input type="submit" class="button button-small alignright" name="xmlsf-dismiss-submit" value="<?php echo \esc_html( \translate( 'Dismiss' ) ); ?>" />
				</form>
				<?php include XMLSF_DIR . '/views/admin/section-advanced-news-compat-message.php'; ?>
			</div>
			<?php
		}
	}
}
