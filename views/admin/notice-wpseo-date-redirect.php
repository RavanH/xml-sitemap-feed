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
			esc_html__( 'A setting in the %s plugin causes all date based sitemaps to redirect to the main page.', 'xml-sitemap-feed' ),
			esc_html__( 'WordPress SEO', 'wordpress-seo' )
		);
		?>
		<?php
		printf( /* translators: Date archives (linked to WP SEO plugin settings), Split by, None, Included post types (linked to Sitemap settings) */
			esc_html__( 'Please either enable %1$s in your SEO settings or set all %2$s options to %3$s under %4$s in your XML Sitemap settings.', 'xml-sitemap-feed' ),
			'<strong><a href="' . esc_url( admin_url( 'admin.php' ) ) . '?page=wpseo_page_settings#/date-archives">' . esc_html__( 'Date archives', 'wordpress-seo' ) . '</a></strong>',
			'<strong>' . esc_html__( 'Split by', 'xml-sitemap-feed' ) . '</strong>',
			'<strong>' . esc_html( translate( 'None' ) ) . '</strong>',
			'<a href="' . esc_url( admin_url( 'options-general.php' ) ) . '?page=xmlsf&tab=post_types">' . esc_html__( 'Included post types', 'xml-sitemap-feed' ) . '</a>'
		);
		?>
	</p>
</div>
