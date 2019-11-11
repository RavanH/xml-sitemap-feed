<fieldset>
  <legend class="screen-reader-text">
		<?php _e('Hierarchical post types','xml-sitemap-feed'); ?>
	</legend>
	<label>
		<input type="checkbox" name="" id="xmlsf_news_hierarchical" value="1" disabled="disabled" />
		<?php _e('Allow hierarchical post types', 'xml-sitemap-feed'); ?>
	</label>

	<p class="description">
		<?php printf( /* Translators: Pages, General */ __('Activating this option will make all hierarchical post types like %1$s available on the %2$s tab.','xml-sitemap-feed'), translate('Pages'), translate('General') ); ?> 
		<?php printf( /* Translators: Sitemap tag name, Advanced plugin name */ __('%1$s are provided by the %2$s module.','xml-sitemap-feed'), __('Hierarchical post types','xml-sitemap-feed') ,'<a href="https://premium.status301.com/downloads/google-news-advanced/" target="_blank">'.__('Google News Advanced','xml-sitemap-feed').'</a>'); ?>
	</p>
</fieldset>
