<?php
/**
 * Help tab: Authors General Settings
 *
 * @package XML Sitemap & Google News
 */

?>
<p>
	<strong><?php esc_html_e( 'Priority', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php esc_html_e( 'Priority can be used to signal the importance of author archives relative to other content like posts, pages or taxonomy term archives.', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<strong><?php esc_html_e( 'Maximum authors per sitemap', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php esc_html_e( 'The number of entries per sitemap is limited to 2000 by default, to prevent running into slow response times or server memory issues. You may try a higher value but if you experience errors or slow sitemaps, make sure to reduce this number.', 'xml-sitemap-feed' ); ?>
	<br />
	<?php esc_html_e( 'Authors are ordered by number of posts, starting with the most published posts down to the least. Authors without any posts will not appear in the sitemap.', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<?php echo apply_filters( 'xmlsf_author_settings_description', sprintf( /* Translators: XML Sitemap Advanced */ esc_html__( 'More options available in %s.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/xml-sitemap-advanced/" target="_blank">' . esc_html__( 'XML Sitemap Advanced', 'xml-sitemap-feed' ) . '</a>' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</p>
