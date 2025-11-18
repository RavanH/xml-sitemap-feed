<?php
/**
 * Hierarchical post types field
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset>
	<legend class="screen-reader-text">
		<?php esc_html_e( 'Hierarchical post types', 'xml-sitemap-feed' ); ?>
	</legend>
	<label>
		<input type="checkbox" name="" id="xmlsf_news_hierarchical" value="1" disabled="disabled" />
		<?php esc_html_e( 'Allow hierarchical post types', 'xml-sitemap-feed' ); ?>
	</label>

	<p class="description">
		<?php printf( /* Translators: Pages, General */ esc_html__( 'Activating this option will make all hierarchical post types like %1$s available on the %2$s tab.', 'xml-sitemap-feed' ), esc_html( translate( 'Pages' ) ), esc_html( translate( 'General' ) ) ); // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction ?>
		<?php printf( /* Translators: %s: Google News Advanced (with link) */ esc_html__( 'Available in %s.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/google-news-advanced/" target="_blank">' . esc_html__( 'Google News Advanced', 'xml-sitemap-feed' ) . '</a>' ); ?>
	</p>
</fieldset>
