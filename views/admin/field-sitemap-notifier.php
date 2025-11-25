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
		<?php printf( /* translators: %s: XML Sitemap Index */ esc_html__( 'Notify Google by automaticly resubmitting your %s sitemap index to Google Search Console upon each new publication.', 'xml-sitemap-feed' ), esc_html__( 'XML Sitemap Index', 'xml-sitemap-feed' ) ); ?>
		<?php printf( /* Translators: %s: XML Sitemap Advanced (with link) */ esc_html__( 'Available in %s.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/xml-sitemap-advanced/" target="_blank">' . esc_html__( 'XML Sitemap Advanced', 'xml-sitemap-feed' ) . '</a>' ); ?>
	</p>
</fieldset>
