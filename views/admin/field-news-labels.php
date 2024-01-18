<?php
/**
 * Source labels field
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset id="xmlsf_news_labels">
	<legend class="screen-reader-text"><?php esc_html_e( 'Source labels', 'xml-sitemap-feed' ); ?></legend>
	<p class="description">
		<?php printf( /* translators: Google Publisher Center, linked. */ esc_html__( 'Source labels inside a News Sitemap are no longer supported by Google News. To manage your site\'s labels, please go to the %s.', 'xml-sitemap-feed' ), '<a href="https://publishercenter.google.com" target="_blank">' . esc_html__( 'Google News Publisher Center', 'xml-sitemap-feed' ) . '</a>' ); ?>
	</p>
</fieldset>
