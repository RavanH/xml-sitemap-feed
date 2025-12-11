<?php
/**
 * BWT Oauth section intro
 *
 * @package XML Sitemap & Google News
 */

?>
<p>
	<?php esc_html_e( 'To allow sitemap data retrieval and submission, a connection between your website and Bing Webmaster Tools needs to be created. This will be set up in two stages: (I) Obtaining OAuth credentials and (II) Authorizing the connection.', 'xml-sitemap-feed' ); ?>
	<?php
	printf(
		/* translators: %s: Link to detailed documentation */
		esc_html__( 'For more detailed instructions, please refer to our %s.', 'xml-sitemap-feed' ),
		'<a href="https://premium.status301.com/knowledge-base/xml-sitemap-google-news/connect-your-site-to-bing-webmaster-tools/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Knowledge Base', 'xml-sitemap-feed' ) . '</a>'
	);
	?>
</p>
