<?php
/**
 * Help tab: News categories
 *
 * @package XML Sitemap & Google News
 */

?>
<p>
	<?php esc_html_e( 'If you wish to allow hierarchical post types to be included in your Google News sitemap, activate this option. By default, only non-hierarchical post types are allowed.', 'xml-sitemap-feed' ); ?>
	<?php
	if ( ! is_plugin_active( 'xml-sitemap-feed-advanced-news/xml-sitemap-feed-advanced-news.php' ) ) {
		printf( /* Translators: Sitemap tag name, Advanced plugin name */ esc_html__( '%1$s are provided by the %2$s module.', 'xml-sitemap-feed' ), esc_html__( 'Hierarchical post types', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/google-news-advanced/" target="_blank">' . esc_html__( 'Google News Advanced', 'xml-sitemap-feed' ) . '</a>' );
	}
	?>
</p>
