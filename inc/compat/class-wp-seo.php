<?php
/**
 * Yoast SEO compatibility
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Compat;

/**
 * Class
 */
class WP_SEO {
	/**
	 * Admin notices.
	 */
	public static function admin_notices() {
		if ( ! \current_user_can( 'manage_options' ) ) {
			return;
		}

		// check date archive redirection.
		$wpseo_titles = \get_option( 'wpseo_titles' );
		if ( ! empty( $wpseo_titles['disable-date'] ) && \xmlsf()->sitemap->uses_date_archives() ) {
			?>
			<div class="notice notice-error fade is-dismissible">
				<p>
				<?php
				\printf( /* translators: conflicting plugin name */
					\esc_html__( 'A setting in the %s plugin causes all date based sitemaps to redirect to the main page.', 'xml-sitemap-feed' ),
					\esc_html__( 'WordPress SEO', 'wordpress-seo' )
				);
				?>
				<?php
				\printf( /* translators: Date archives (linked to WP SEO plugin settings), Split by, None, post types (linked to Sitemap settings) */
					\esc_html__( 'Please either enable %1$s in your SEO settings or set all %2$s options to %3$s under %4$s in your XML Sitemap settings.', 'xml-sitemap-feed' ),
					'<strong><a href="' . \esc_url( \admin_url( 'admin.php' ) ) . '?page=wpseo_page_settings#/date-archives">' . \esc_html__( 'Date archives', 'wordpress-seo' ) . '</a></strong>',
					'<strong>' . \esc_html__( 'Split by', 'xml-sitemap-feed' ) . '</strong>',
					'<strong>' . \esc_html( \translate( 'None' ) ) . '</strong>',
					'<a href="' . \esc_url( \admin_url( 'options-general.php' ) ) . '?page=xmlsf&tab=post_types">' . \esc_html__( 'Post types', 'xml-sitemap-feed' ) . '</a>'
				);
				?>
				</p>
			</div>
			<?php
		}

		// check wpseo sitemap option.
		if ( ! \in_array( 'wpseo_sitemap', (array) \get_user_meta( \get_current_user_id(), 'xmlsf_dismissed', true ), true ) ) {
			$wpseo = \get_option( 'wpseo' );
			if ( ! empty( $wpseo['enable_xml_sitemap'] ) ) {
				?>
				<div class="notice notice-warning fade is-dismissible">
					<p>
						<?php
						\printf( /* translators: Conflicting Plugn name, Plugin name */
							\esc_html__( 'The %1$s XML Sitemap is not compatible with %2$s.', 'xml-sitemap-feed' ),
							\esc_html__( 'Yoast SEO', 'xml-sitemap-feed' ),
							\esc_html__( 'XML Sitemap & Google News', 'xml-sitemap-feed' )
						);
						?>
						<?php
						\printf( /* translators: Sitemap page name (linked to Yoast SEO plugin settings), XML Sitemap Index, Reading Settings admin page (linked to Reading settings) */
							\esc_html__( 'Please either disable the XML Sitemap from %1$s or disable the option %2$s on %3$s.', 'xml-sitemap-feed' ),
							'<a href="' . \esc_url( \admin_url( 'admin.php' ) ) . '?page=wpseo_page_settings#/site-features#card-wpseo-enable_xml_sitemap">' . \esc_html__( 'Yoast SEO', 'xml-sitemap-feed' ) . '</a>',
							\esc_html__( 'XML Sitemap Index', 'xml-sitemap-feed' ),
							'<a href="' . \esc_url( \admin_url( 'options-reading.php' ) ) . '#xmlsf_sitemaps">' . \esc_html( \translate( 'Reading Settings' ) ) . '</a>'
						);
						?>
					</p>
					<form action="" method="post">
						<?php wp_nonce_field( XMLSF_BASENAME . '-notice', '_xmlsf_notice_nonce' ); ?>
						<p>
							<input type="hidden" name="xmlsf-dismiss" value="wpseo_sitemap" />
							<input type="submit" class="button button-small" name="xmlsf-dismiss-submit" value="<?php echo \esc_html( \translate( 'Dismiss' ) ); ?>" />
						</p>
					</form>
				</div>
				<?php
			}
		}
	}

	/**
	 * Admin notices.
	 */
	public static function news_admin_notice() {
		if ( ! \current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check Remove category feeds option.
		$wpseo = \get_option( 'wpseo' );
		if ( ! empty( $wpseo['remove_feed_categories'] ) ) {
			// check if Google News sitemap is limited to categories.
			$news_tags = \get_option( 'xmlsf_news_tags' );
			if ( ! empty( $news_tags['categories'] ) ) {
				?>
				<div class="notice notice-error fade is-dismissible">
					<p>
						<?php
						\printf( /* translators: conflicting plugin name */
							\esc_html__( 'A setting in the %s plugin causes the Google News sitemap to redirect to a category archive page.', 'xml-sitemap-feed' ),
							\esc_html__( 'Yoast SEO', 'xml-sitemap-feed' )
						);
						?>
						<?php
						\printf( /* translators: Date archives (linked to WP SEO plugin settings), Split by, None, post types (linked to Sitemap settings) */
							\esc_html__( 'Please either disable %1$s in your SEO settings or unselect all %2$s in your Google News sitemap settings.', 'xml-sitemap-feed' ),
							'<a href="' . \esc_url( \admin_url( 'admin.php' ) ) . '?page=wpseo_page_settings#/crawl-optimization#headlessui-label-:r3k:">' . \esc_html__( 'Remove category feeds', 'xml-sitemap-feed' ) . '</a>',
							'<a href="' . \esc_url( \admin_url( 'options-general.php' ) ) . '?page=xmlsf_news&tab=general">' . \esc_html( \translate( 'Categories' ) ) . '</a>'
						);
						?>
					</p>
				</div>
				<?php
			}
		}
	}
}
