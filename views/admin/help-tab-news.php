<?php
/**
 * Help tab: News
 *
 * @package XML Sitemap & Google News
 */

?>
<p>
	<?php esc_html_e( 'The options on this page allow you to configure an XML Sitemap dedicated to keep Google News informed of your latest posts.', 'xml-sitemap-feed' ); ?>
	<?php esc_html_e( 'Updates are instantly pinged to Google, who will then crawl your sitemap to find out more. The sitemap adheres to the Google News Sitemap standard and helps Google News to find your news content as quickly as possible. However, it is up to you to to produce high-quality content and comply with Google News content policies!', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<?php
	printf( /* translators: %1$s Google News policies (linked to https://support.google.com/news/publisher-center/answer/6204050), %2$s Google News Publisher Center (linked to https://publishercenter.google.com/) */
		esc_html__( 'When you are done configuring and preparing your news content and you are convinced your site adheres to the %1$s, go ahead and submit your site on %2$s!', 'xml-sitemap-feed' ),
		'<a href="https://support.google.com/news/publisher-center/answer/6204050" target="_blank">' . esc_html__( 'Google News policies', 'xml-sitemap-feed' ) . '</a>',
		'<a href="https://publishercenter.google.com/" target="_blank">' . esc_html__( 'Google News Publisher Center', 'xml-sitemap-feed' ) . '</a>'
	);
	?>
	<?php esc_html_e( 'It is strongly recommended to submit your news sitemap to your Google Search Console account to monitor for warnings or errors.', 'xml-sitemap-feed' ); ?>
</p>
