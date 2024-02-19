<?php
/**
 * Sidebar: News links
 *
 * @package XML Sitemap & Google News
 */

?>
<h3><span class="dashicons dashicons-admin-links"></span> <?php echo esc_html( translate( 'Links' ) ); ?></h3>
<ul>
	<li>
		<a href="https://publishercenter.google.com/publications" target="_blank"><?php esc_html_e( /* Translators: Site title https://publishercenter.google.com/publications */ 'Google News Publisher Center', 'xml-sitemap-feed' ); ?></a>
	</li>
	<li>
		<a href="https://search.google.com/search-console" target="_blank"><?php esc_html_e( /* Translators: Site title https://search.google.com/search-console */ 'Google Search Console', 'xml-sitemap-feed' ); ?></a>
	</li>
	<li>
		<a href="https://www.xml-sitemaps.com/validate-xml-sitemap.html" target="_blank"><?php esc_html_e( /* Translators: Site title https://www.xml-sitemaps.com/validate-xml-sitemap.html */ 'Validate an XML Sitemap', 'xml-sitemap-feed' ); ?></a>
	</li>
	<li>
		<a href="https://news.google.com/search?q=site:<?php echo rawurlencode( home_url() ); ?>+when:7d" target="_blank"><?php esc_html_e( /* Translators: Site title https://news.google.com/search?q=site:site.dom+when:7d */ 'Your news on Google News', 'xml-sitemap-feed' ); ?></a>
	</li>
</ul>
