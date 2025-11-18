<?php
/**
 * Sitemap notifier field
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset>
	<legend class="screen-reader-text">
		<?php esc_html_e( 'Sitemap notifier', 'xml-sitemap-feed' ); ?>
	</legend>
	<label>
		<input type="checkbox" name="" id="xmlsf_news_notifier" value="1" disabled="disabled" />
		<?php esc_html_e( 'Enable automatic sitemap submission', 'xml-sitemap-feed' ); ?>
	</label>

	<p class="description">
		<?php esc_html_e( 'Notify Google by automaticly resubmitting your news sitemap to Google Search Console upon each new publication.', 'xml-sitemap-feed' ); ?>
		<?php printf( /* Translators: %s: Google News Advanced (with link) */ esc_html__( 'Available in %s.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/google-news-advanced/" target="_blank">' . esc_html__( 'Google News Advanced', 'xml-sitemap-feed' ) . '</a>' ); ?>
	</p>
</fieldset>
