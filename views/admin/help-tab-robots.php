<?php
/**
 * Help tab: Robots.txt
 *
 * @package XML Sitemap & Google News
 */

?>
<p>
	<?php esc_html_e( 'These rules will not have effect when you are using a static robots.txt file.', 'xml-sitemap-feed' ); ?>
	<br>
	<span style="color: red" class="warning">
		<?php esc_html_e( 'Only add rules here when you know what you are doing, otherwise you might break search engine access to your site.', 'xml-sitemap-feed' ); ?>
	</span>
</p>
<p>
	<a href="https://search.google.com/search-console/settings/robots-txt?resource_id=sc-domain%3A<?php echo esc_url_raw( wp_parse_url( get_bloginfo( 'url' ), PHP_URL_HOST ) ); ?>" target="_blank" class="button"><?php esc_html_e( 'Open robots.txt Tester', 'xml-sitemap-feed' ); ?></a>
</p>
<p>
	<?php printf( /* translators: Learn about robots.txt (linked to https://developers.google.com/search/docs/crawling-indexing/robots/intro), Robots.txt Specification (linked to https://developers.google.com/search/docs/crawling-indexing/robots/robots_txt) */ esc_html__( 'For more help see %1$s and %2$s.', 'xml-sitemap-feed' ), '<a href="https://developers.google.com/search/docs/crawling-indexing/robots/intro" target="_blank">' . esc_html__( 'Learn about robots.txt files', 'xml-sitemap-feed' ) . '</a>', '<a href="https://developers.google.com/search/docs/crawling-indexing/robots/robots_txt" target="_blank">' . esc_html__( 'Robots.txt Specifications', 'xml-sitemap-feed' ) . '</a>' ); ?>
</p>
