<?php
/**
 * Admin notice: WPSEO date redirect
 *
 * @package XML Sitemap & Google News
 */

?>
<div class="notice notice-error fade is-dismissible">
	<p>
		<?php
		printf( /* translators: conflicting plugin name */
			esc_html__( 'A setting in the %s plugin causes the Google News sitemap to redirect to a category archive page.', 'xml-sitemap-feed' ),
			esc_html__( 'WordPress SEO', 'wordpress-seo' )
		);
		?>
		<?php
		printf( /* translators: Date archives (linked to WP SEO plugin settings), Split by, None, post types (linked to Sitemap settings) */
			esc_html__( 'Please either disable %1$s in your SEO settings or unselect all %2$s in your Google News sitemap settings.', 'xml-sitemap-feed' ),
			'<strong><a href="' . esc_url( admin_url( 'admin.php' ) ) . '?page=wpseo_page_settings#/crawl-optimization">' . esc_html__( 'Remove category feeds', 'wordpress-seo' ) . '</a></strong>',
			'<a href="' . esc_url( admin_url( 'options-general.php' ) ) . '?page=xmlsf_news&tab=general">' . esc_html( translate( 'Categories' ) ) . '</a>'
		);
		?>
	</p>
</div>
