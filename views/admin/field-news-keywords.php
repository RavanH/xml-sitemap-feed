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
		<?php printf( /* Translators: Sitemap tag name, Advanced plugin name */ __('%1$s are provided by the %2$s module.','xml-sitemap-feed'), __('Keywords','xml-sitemap-feed') ,'<a href="https://premium.status301.com/downloads/google-news-advanced/" target="_blank">'.__('Google News Advanced','xml-sitemap-feed').'</a>'); ?>
	</p>
</fieldset>
