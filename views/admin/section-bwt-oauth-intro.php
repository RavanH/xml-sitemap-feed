<?php
/**
 * BWT Oauth section intro
 *
 * @package XML Sitemap & Google News
 */

?>
<p>
	<?php esc_html_e( 'To allow sitemap data retrieval and submission, a Bing Webmaster Tools API key needs to be configured. This will be set up in two stages: (I) Creating and configuring an API key and (II) testing and activating the connection.', 'xml-sitemap-feed' ); ?>
	<br>
	<?php
	printf(
		/* translators: %s: Knowledge Base (linked to https://premium.status301.com/knowledge-base/xml-sitemap-google-news/connect-your-site-to-bing-webmaster-tools/) */
		esc_html__( 'For more detailed instructions, please refer to our %s.', 'xml-sitemap-feed' ),
		'<a href="https://premium.status301.com/knowledge-base/xml-sitemap-google-news/connect-your-site-to-bing-webmaster-tools/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Knowledge Base', 'xml-sitemap-feed' ) . '</a>'
	);
	?>
</p>
