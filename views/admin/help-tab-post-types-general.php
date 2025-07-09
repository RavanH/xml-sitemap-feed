<?php
/**
 * Help tab: Post type Settins
 *
 * @package XML Sitemap & Google News
 */

?>
<p>
	<strong><?php esc_html_e( 'Include these post types', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php esc_html_e( 'Select the post types to include in the sitemap index. Select none to automatically include all public post types.', 'xml-sitemap-feed' ); ?>
	<br />
	<em><?php esc_html_e( 'Be aware: excluding the Pages sitemap, also means excluding the home and blog pages!', 'xml-sitemap-feed' ); ?></em>
</p>
<?php if ( 'core' === \xmlsf()->sitemap->server_type ) : ?>
<p>
	<strong><?php esc_html_e( 'Maximum posts per sitemap', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php esc_html_e( 'The number of entries per sitemap is limited to 2000 by default, to prevent running into slow response times or server memory issues. You may try a higher value but if you experience errors or slow sitemaps, make sure to reduce this number.', 'xml-sitemap-feed' ); ?>
</p>
<?php endif; ?>
