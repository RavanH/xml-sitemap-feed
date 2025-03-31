<?php
/**
 * Help tab: News categories
 *
 * @package XML Sitemap & Google News
 */

?>
<p>
	<?php esc_html_e( 'If you wish to add other post types than the default Posts to be included in your Google News sitemap, select them here. By default, only Posts are included.', 'xml-sitemap-feed' ); ?>
	<?php
	if ( ! is_plugin_active( 'xml-sitemap-feed-advanced-news/xml-sitemap-feed-advanced-news.php' ) ) {
		printf( /* Translators: Advanced plugin name */ esc_html__( 'Including multiple post types in the same News Sitemap is provided by the %s module.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/google-news-advanced/" target="_blank">' . esc_html__( 'Google News Advanced', 'xml-sitemap-feed' ) . '</a>' );
	}
	?>
</p>
