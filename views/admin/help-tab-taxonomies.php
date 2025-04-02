<?php
/**
 * Help tab: Taxonomies
 *
 * @package XML Sitemap & Google News
 */

?>
<p>
	<strong><?php esc_html_e( 'Priority', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php esc_html_e( 'Priority can be used to signal the importance of taxonomy term archives relative to other content like posts, pages or author archives.', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<strong><?php esc_html_e( 'Automatic Priority calculation.', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php esc_html_e( 'Adjusts the Priority of each taxonomy term based on the relative number of attributed posts.', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<strong><?php esc_html_e( 'Maximum terms per sitemap', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php esc_html_e( 'The number of entries per sitemap is limited to 2000 by default, to prevent running into slow response times or server memory issues. You may try a higher value but if you experience errors or slow sitemaps, make sure to reduce this number.', 'xml-sitemap-feed' ); ?>
	<br />
	<?php esc_html_e( 'Terms are ordered by number of posts, starting with the most used terms down to the least used. Terms without any posts will not appear in the sitemap.', 'xml-sitemap-feed' ); ?>
</p>
