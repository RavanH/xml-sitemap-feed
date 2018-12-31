<fieldset>
    <legend class="screen-reader-text">
		<?php _e('Keywords','xml-sitemap-feed'); ?>
	</legend>
	<label><?php _e('Use keywords from','xml-sitemap-feed'); ?>
        <select name="xmlsf_news_tags[keywords_from]"<?php disabled( $this->advanced, false ); ?>>
			<option value=""><?php echo translate('None'); ?></option>
			<?php do_action('xmlsf_news_keywords_from_select'); ?>
	    </select>
    </label>
<?php if ( ! $this->advanced ) { ?>
	<p class="description">
		<?php printf( /* Translators: Sitemap tag name, Advanced plugin name */ __('%1$s are available the %2$s module.','xml-sitemap-feed'), __('Keywords','xml-sitemap-feed') ,'<a href="https://premium.status301.net/downloads/google-news-advanced/" target="_blank">'.__('Google News Advanced','xml-sitemap-feed').'</a>'); ?>
	</p>
<?php } ?>
</fieldset>
