<?php
/**
 * Help tab: Taxonomies
 *
 * @package XML Sitemap & Google News
 */

?>
<p>
	<strong><?php esc_html_e( 'Maximum terms per sitemap', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php esc_html_e( 'The number of entries per sitemap is limited to 2000 by default, to prevent running into slow response times or server memory issues. You may try a higher value but if you experience errors or slow sitemaps, make sure to reduce this number.', 'xml-sitemap-feed' ); ?>
	<br />
	<?php esc_html_e( 'Terms are ordered by number of posts, starting with the most used terms down to the least used. Terms without any posts will not appear in the sitemap.', 'xml-sitemap-feed' ); ?>
</p>
