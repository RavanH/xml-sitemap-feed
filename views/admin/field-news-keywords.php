<fieldset>
    <legend class="screen-reader-text">
		<?php _e('Keywords','xml-sitemap-feed'); ?>
	</legend>
	<label><?php _e('Use keywords from','xml-sitemap-feed'); ?>
        <select name="" disabled="disabled">
			<option value=""><?php echo translate('None'); ?></option>
	    </select>
    </label>
	<p class="description">
		<?php printf( /* Translators: Sitemap tag name, Advanced plugin name */ __('%1$s are available in the %2$s module.','xml-sitemap-feed'), __('Keywords','xml-sitemap-feed') ,'<a href="https://premium.status301.net/downloads/google-news-advanced/" target="_blank">'.__('Google News Advanced','xml-sitemap-feed').'</a>'); ?>
	</p>
</fieldset>
<fieldset>
    <legend class="screen-reader-text">
		<?php _e('Stock tickers','xml-sitemap-feed'); ?>
	</legend>
	<label>
		<input type="checkbox" name="" id="xmlsf_news_stock_tickers" value="1" disabled="disabled" />
		<?php _e('Enable stock tickers', 'xml-sitemap-feed'); ?>
	</label>

	<p class="description">
		<?php _e('Stock tickers are relevant primarily for business articles.','xml-sitemap-feed'); ?>
		<br />
		<?php printf( /* Translators: Sitemap tag name, Advanced plugin name */ __('%1$s are available in the %2$s module.','xml-sitemap-feed'), __('Stock tickers','xml-sitemap-feed') ,'<a href="https://premium.status301.net/downloads/google-news-advanced/" target="_blank">'.__('Google News Advanced','xml-sitemap-feed').'</a>'); ?>
	</p>
</fieldset>
