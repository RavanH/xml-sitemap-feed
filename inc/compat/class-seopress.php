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
		if ( ! \current_user_can( 'manage_options' ) ) {
			return;
		}

		// check date archive redirection.
		$seopress_toggle = \get_option( 'seopress_toggle' );

		$seopress_titles = \get_option( 'seopress_titles_option_name' );
		if ( ! empty( $seopress_toggle['toggle-titles'] ) && ! empty( $seopress_titles['seopress_titles_archives_date_disable'] ) && \xmlsf()->sitemap->uses_date_archives() ) {
			?>
			<div class="notice notice-error fade is-dismissible">
				<p>
					<?php
					printf( /* translators: conflicting plugin name */
						\esc_html__( 'A setting in the %s plugin causes all date based sitemaps to redirect to the main page.', 'xml-sitemap-feed' ),
						\esc_html__( 'SEOPress', 'wp-seopress' )
					);
					?>
					<?php
					printf( /* translators: Date archives, Archives (linked to WP SEO plugin settings), Split by, None, post types (linked to Sitemap settings) */
						\esc_html__( 'Please either enable %1$s under %2$s in your SEO settings or set all %3$s options to %4$s under %5$s in your XML Sitemap settings.', 'xml-sitemap-feed' ),
						'<strong>' . \esc_html__( 'Date archives', 'wp-seopress' ) . '</strong>',
						'<a href="' . \esc_url( \admin_url( 'admin.php' ) ) . '?page=seopress-titles#tab=tab_seopress_titles_archives">' . \esc_html__( 'Archives', 'wp-seopress' ) . '</a>',
						'<strong>' . \esc_html__( 'Split by', 'xml-sitemap-feed' ) . '</strong>',
						'<strong>' . \esc_html( \translate( 'None' ) ) . '</strong>',
						'<a href="' . \esc_url( \admin_url( 'options-general.php' ) ) . '?page=xmlsf&tab=post_types">' . \esc_html__( 'Post types', 'xml-sitemap-feed' ) . '</a>'
					);
					?>
				</p>
			</div>
			<?php
		}

		// check seopress sitemap option.
		if ( ! \in_array( 'seopress_sitemap', (array) \get_user_meta( \get_current_user_id(), 'xmlsf_dismissed' ), true ) ) {
			$seopress_xml_sitemap = \get_option( 'seopress_xml_sitemap_option_name' );
			if ( ! empty( $seopress_toggle['toggle-xml-sitemap'] ) && ! empty( $seopress_xml_sitemap['seopress_xml_sitemap_general_enable'] ) ) {
				?>
				<div class="notice notice-warning fade is-dismissible">
					<p>
						<?php
						printf( /* translators: Conflicting Plugn name, Plugin name */
							\esc_html__( 'The %1$s XML Sitemap is not compatible with %2$s.', 'xml-sitemap-feed' ),
							\esc_html__( 'SEOPress', 'wp-seopress' ),
							\esc_html__( 'XML Sitemap & Google News', 'xml-sitemap-feed' )
						);
						?>
						<?php
						printf( /* translators: Sitemap page name (linked to SEOPress plugin settings), XML Sitemap Index, Reading Settings admin page (linked to Reading settings) */
							\esc_html__( 'Please either disable the XML Sitemap under %1$s in your SEO settings or disable the option %2$s on %3$s.', 'xml-sitemap-feed' ),
							'<a href="' . \esc_url( \admin_url( 'admin.php' ) ) . '?page=seopress-xml-sitemap">' . \esc_html__( 'XML / HTML Sitemap', 'wp-seopress' ) . '</a>',
							\esc_html__( 'XML Sitemap Index', 'xml-sitemap-feed' ),
							'<a href="' . \esc_url( \admin_url( 'options-reading.php' ) ) . '#xmlsf_sitemaps">' . \esc_html( \translate( 'Reading Settings' ) ) . '</a>'
						);
						?>
					</p>
					<form action="" method="post">
						<?php \wp_nonce_field( XMLSF_BASENAME . '-notice', '_xmlsf_notice_nonce' ); ?>
						<p>
							<input type="hidden" name="xmlsf-dismiss" value="seopress_sitemap" />
							<input type="submit" class="button button-small" name="xmlsf-dismiss-submit" value="<?php echo \esc_attr( \translate( 'Dismiss' ) ); ?>" />
						</p>
					</form>
				</div>
				<?php
			}
		}
	}
}
