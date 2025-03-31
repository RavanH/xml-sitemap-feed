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
	<br />
	<?php esc_html_e( 'Select the post types to include in the sitemap index. Select none to automatically include all public post types.', 'xml-sitemap-feed' ); ?>
	<br />
	<?php esc_html_e( 'Be aware: excluding the Pages sitemap, also means excluding the home aand blog pages!', 'xml-sitemap-feed' ); ?>
</p>
<?php if ( xmlsf()->sitemap->uses_core_server() ) : ?>
<p>
	<strong><?php esc_html_e( 'Maximum posts per sitemap', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<br />
	<?php esc_html_e( 'The absolute maximum allowed is 50.000 per sitemap. Reduce this number if you experience errors or slow sitemaps.', 'xml-sitemap-feed' ); ?>
</p>
<?php endif; ?>
