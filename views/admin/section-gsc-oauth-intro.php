<?php
/**
 * GSC Oauth section intro
 *
 * @package XML Sitemap & Google News - Google News Advanced
 */

?>
<p>
	<?php esc_html_e( 'To allow sitemap data retrieval and submission, a connection between your website and Google Search Console needs to be created. This will be set up in three stages: (I) Creating a Google Cloud Console project, (II) obtaining OAuth credentials and (III) Authorizing the connection.', 'xml-sitemap-feed' ); ?>
	<?php
	printf(
		/* translators: %s: Link to detailed documentation */
		esc_html__( 'For more detailed instructions, please refer to our %s.', 'xml-sitemap-feed' ),
		'<a href="https://premium.status301.com/knowledge-base/xml-sitemap-google-news/automatically-notify-google-on-news-sitemap-updates/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Knowledge Base', 'xml-sitemap-feed' ) . '</a>'
	);
	?>
</p>
