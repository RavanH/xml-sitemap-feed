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
		<input type="checkbox" name="" id="xmlsf_news_notifier" value="1"<?php disabled( ! apply_filters( 'xmlsf_news_advanced_enabled', false ) ); ?> />
		<?php printf( /* translators: %s: Google Search Console */ esc_html__( 'Submit to %s', 'xml-sitemap-feed' ), esc_html__( 'Google Search Console', 'xml-sitemap-feed' ) ); ?>
	</label>

	<p class="description">
		<?php esc_html_e( 'Notify Google by automatically resubmitting your Google News sitemap upon each new publication.', 'xml-sitemap-feed' ); ?>
		<?php apply_filters( 'xmlsf_news_advanced_enabled', false ) || printf( /* Translators: %s: Google News Advanced (with link) */ esc_html__( 'Available in %s.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/google-news-advanced/" target="_blank">' . esc_html__( 'Google News Advanced', 'xml-sitemap-feed' ) . '</a>' ); ?>
	</p>
</fieldset>
