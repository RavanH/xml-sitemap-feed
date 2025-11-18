<?php
/**
 * Stock tickers field
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset>
	<legend class="screen-reader-text">
		<?php esc_html_e( 'Stock tickers', 'xml-sitemap-feed' ); ?>
	</legend>
	<label>
		<input type="checkbox" name="" id="xmlsf_news_stock_tickers" value="1" disabled="disabled" />
		<?php esc_html_e( 'Enable stock tickers', 'xml-sitemap-feed' ); ?>
	</label>

	<p class="description">
		<?php esc_html_e( 'Stock tickers are relevant primarily for business articles.', 'xml-sitemap-feed' ); ?>
		<?php printf( /* Translators: %s: Google News Advanced (with link) */ esc_html__( 'Available in %s.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/google-news-advanced/" target="_blank">' . esc_html__( 'Google News Advanced', 'xml-sitemap-feed' ) . '</a>' ); ?>
	</p>
</fieldset>
