<?php
/**
 * Stock tickers field
 *
 * @package XML Sitemap & Google News
 */

$options = (array) get_option( 'xmlsf_news_advanced', array() );
?>
<fieldset>
	<legend class="screen-reader-text">
		<?php esc_html_e( 'Stock tickers', 'xml-sitemap-feed' ); ?>
	</legend>
	<p>
		<label>
			<input type="checkbox" name="xmlsf_news_advanced[stock_tickers]" id="xmlsf_news_stock_tickers" value="1"<?php checked( ! empty( $options['stock_tickers'] ) ); ?><?php disabled( ! apply_filters( 'xmlsf_news_advanced_enabled', false ) ); ?> />
			<?php esc_html_e( 'Enable stock tickers', 'xml-sitemap-feed' ); ?>
		</label>
	</p>

	<p class="description">
		<?php esc_html_e( 'Stock tickers are relevant primarily for business articles.', 'xml-sitemap-feed' ); ?>
		<?php apply_filters( 'xmlsf_news_advanced_enabled', false ) || printf( /* Translators: %s: Google News Advanced (with link) */ esc_html__( 'Available in %s.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/google-news-advanced/" target="_blank">' . esc_html__( 'Google News Advanced', 'xml-sitemap-feed' ) . '</a>' ); ?>
	</p>
</fieldset>
