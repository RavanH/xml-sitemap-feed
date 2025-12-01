<?php
/**
 * Help tab: News notifier
 *
 * @package XML Sitemap & Google News
 */

?>
<p>
	<?php esc_html_e( 'The sitemap notifier automaticly resubmits your news sitemap upon each new publication. This replaces the abandoned Sitemap Ping feature.', 'xml-sitemap-feed' ); ?>
	<?php apply_filters( 'xmlsf_news_advanced_enabled', false ) || printf( /* Translators: %s: XML Sitemap Advanced (with link) */ esc_html__( 'Available in %s.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/google-news-advanced/" target="_blank">' . esc_html__( 'Google News Advanced', 'xml-sitemap-feed' ) . '</a>' ); ?>
</p>
<p>
	<?php esc_html_e( 'Note: Sitemap notifications need a Google Cloud Platform project with the Google Search Console API enabled. Instructions will be provided during configuration.', 'xml-sitemap-feed' ); ?>
</p>
