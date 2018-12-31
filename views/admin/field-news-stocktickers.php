<fieldset>
    <legend class="screen-reader-text">
		<?php _e('Stock tickers','xml-sitemap-feed'); ?>
	</legend>
	<label>
		<input type="checkbox" name="xmlsf_news_tags[stock_tickers]" id="xmlsf_news_stock_tickers" value="1"<?php checked( !empty($stock_tickers), true); ?><?php disabled( $this->advanced, false ); ?> />
		<?php _e('Enable stock tickers', 'xml-sitemap-feed'); ?>
	</label>

	<p class="description">
		<?php _e('Stock tickers are relevant primarily for business articles.','xml-sitemap-feed'); ?>
<?php if ( ! $this->advanced ) { ?>
		<?php printf( /* Translators: Sitemap tag name, Advanced plugin name */ __('%1$s are available the %2$s module.','xml-sitemap-feed'), __('Stock tickers','xml-sitemap-feed') ,'<a href="https://premium.status301.net/downloads/google-news-advanced/" target="_blank">'.__('Google News Advanced','xml-sitemap-feed').'</a>'); ?>
<?php } ?>
	</p>
</fieldset>
