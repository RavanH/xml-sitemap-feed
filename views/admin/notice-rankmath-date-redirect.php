<?php
/**
 * Admin notice: Rankmath date redirect
 *
 * @package XML Sitemap & Google News
 */

?>
<div class="notice notice-error fade is-dismissible">
	<p>
		<?php
		printf( /* translators: conflicting plugin name */
			esc_html__( 'A setting in the %s plugin causes all date based sitemaps to redirect to the main page.', 'xml-sitemap-feed' ),
			esc_html__( 'Rank Math', 'rank-math' )
		);
		?>
		<?php
		printf( /* translators: Date archives, Archives (linked to WP SEO plugin settings), Split by, None, Included post types (linked to Sitemap settings) */
			esc_html__( 'Please either enable %1$s under %2$s in your SEO settings or set all %3$s options to %4$s under %5$s in your XML Sitemap settings.', 'xml-sitemap-feed' ),
			'<strong>' . esc_html__( 'Date Archives', 'rank-math' ) . '</strong>',
			'<a href="' . esc_url( admin_url( 'admin.php' ) ) . '?page=rank-math-options-titles#setting-panel-misc">' . esc_html__( 'Misc Pages', 'rank-math' ) . '</a>',
			'<strong>' . esc_html__( 'Split by', 'xml-sitemap-feed' ) . '</strong>',
			'<strong>' . esc_html( translate( 'None' ) ) . '</strong>',
			'<a href="' . esc_url( admin_url( 'options-general.php' ) ) . '?page=xmlsf">' . esc_html__( 'Included post types', 'xml-sitemap-feed' ) . '</a>'
		);
		?>
	</p>
</div>
