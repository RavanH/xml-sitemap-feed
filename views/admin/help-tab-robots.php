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
	<strong>
	<?php
	printf( /* translators: %1$s Google Search Console, %2$s Bing Webmaster Tools, %3$s Yandex Webmaster (all presented as buttons, opening in new tab) */
		esc_html( 'Verify your robots.txt on %1$s %2$s %3$s' ),
		'<a href="https://search.google.com/search-console/settings/robots-txt" target="_blank" class="button">' . esc_html__( 'Google Search Console', 'xml-sitemap-feed' ) . '</a>',
		'<a href="https://www.bing.com/webmasters/robotstxttester" target="_blank" class="button">' . esc_html__( 'Bing Webmaster Tools', 'xml-sitemap-feed' ) . '</a>',
		'<a href="https://webmaster.yandex.com/tools/robotstxt/" target="_blank" class="button">' . esc_html__( 'Yandex Webmaster', 'xml-sitemap-feed' ) . '</a>'
	);
	?>
	</strong>
</p>
<p>
	<?php printf( /* translators: Learn about robots.txt (linked to https://developers.google.com/search/docs/crawling-indexing/robots/intro), Robots.txt Specification (linked to https://developers.google.com/search/docs/crawling-indexing/robots/robots_txt) */ esc_html__( 'For more help see %1$s and %2$s.', 'xml-sitemap-feed' ), '<a href="https://developers.google.com/search/docs/crawling-indexing/robots/intro" target="_blank">' . esc_html__( 'Learn about robots.txt files', 'xml-sitemap-feed' ) . '</a>', '<a href="https://developers.google.com/search/docs/crawling-indexing/robots/robots_txt" target="_blank">' . esc_html__( 'Robots.txt Specifications', 'xml-sitemap-feed' ) . '</a>' ); ?>
</p>
