<?php
/**
 * Help tab: News source labels
 *
 * @package XML Sitemap & Google News
 */

?>
<p>
	<?php esc_html_e( 'Source labels provide more information about the content of your articles.', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<?php printf( /* translators: Google NEws Publisher Center (linked to https://publishercenter.google.com/) */ esc_html__( 'Source labels inside a News Sitemap are no longer supported by Google News. To manage your site\'s labels, please go to the %s.', 'xml-sitemap-feed' ), '<a href="https://publishercenter.google.com/" target="_blank">' . esc_html__( 'Google News Publisher Center', 'xml-sitemap-feed' ) . '</a>' ); ?>
	<?php printf( /* translators: What does each label mean? (linked to https://support.google.com/news/publisher-center/answer/9606542) */ esc_html__( 'Read more about source labels on %s', 'xml-sitemap-feed' ), '<a href="https://support.google.com/news/publisher-center/answer/9606542" target="_blank">' . esc_html__( 'What does each label mean?', 'xml-sitemap-feed' ) . '</a>' ); ?>
</p>
