<?php
/**
 * Admin notice: Squirrly SEO sitemap
 *
 * @package XML Sitemap & Google News
 */

?>
<div class="notice notice-warning fade is-dismissible">
	<p>
		<?php
		printf( /* translators: Conflicting Plugn name, Plugin name */
			esc_html__( 'The %1$s XML Sitemap is not compatible with %2$s.', 'xml-sitemap-feed' ),
			esc_html__( 'Squirrly SEO', 'squirrly-seo' ),
			esc_html__( 'XML Sitemap & Google News', 'xml-sitemap-feed' )
		);
		?>
		<?php
		printf( /* translators: Sitemap page name (linked to Squirrly SEO plugin settings), XML Sitemap Index, Reading Settings admin page (linked to Reading settings) */
			esc_html__( 'Please either disable the XML Sitemap under %1$s in your SEO settings or disable the option %2$s on %3$s.', 'xml-sitemap-feed' ),
			'<a href="' . esc_url( admin_url( 'admin.php' ) ) . '?page=sq_features">' . esc_html__( 'Miscellaneous Features', 'squirrly-seo' ) . '</a>',
			esc_html__( 'XML Sitemap Index', 'xml-sitemap-feed' ),
			'<a href="' . esc_url( admin_url( 'options-reading.php' ) ) . '#xmlsf_sitemaps">' . esc_html( translate( 'Reading Settings' ) ) . '</a>'
		);
		?>
	</p>
	<form action="" method="post">
		<?php wp_nonce_field( XMLSF_BASENAME . '-notice', '_xmlsf_notice_nonce' ); ?>
		<p>
			<input type="hidden" name="xmlsf-dismiss" value="squirrly_seo_sitemap" />
			<input type="submit" class="button button-small" name="xmlsf-dismiss-submit" value="<?php echo esc_attr( translate( 'Dismiss' ) ); ?>" />
		</p>
	</form>
</div>
