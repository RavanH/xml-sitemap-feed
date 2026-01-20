<?php
/**
 * GSC Oauth section intro
 *
 * @package XML Sitemap & Google News
 */

?>
<p>
	<?php esc_html_e( 'To allow sitemap data retrieval and submission, a connection between your website and Google Search Console needs to be created. This will be set up in three stages: (I) Creating a Google Cloud Console project, (II) obtaining OAuth credentials and (III) Authorizing the connection.', 'xml-sitemap-feed' ); ?>
	<br>
	<?php
	printf(
		/* translators: %s: Knowledge Base (linked to https://premium.status301.com/knowledge-base/xml-sitemap-google-news/connect-your-site-to-google-search-console/) */
		esc_html__( 'For more detailed instructions, please refer to our %s.', 'xml-sitemap-feed' ),
		'<a href="https://premium.status301.com/knowledge-base/xml-sitemap-google-news/connect-your-site-to-google-search-console/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Knowledge Base', 'xml-sitemap-feed' ) . '</a>'
	);
	?>
</p>
