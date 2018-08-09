<fieldset>
    <legend class="screen-reader-text"><?php echo translate('Images'); ?></legend>
		<label><?php _e('Add image tags for','xml-sitemap-feed'); ?>
            <select name="xmlsf_news_tags[image]">
				<option value=""><?php echo translate('None'); ?></option>
				<option value="featured"<?php echo selected( $image == "featured", true, false); ?>><?php echo translate_with_gettext_context('Featured Image','post'); ?></option>
				<option value="attached"<?php echo selected( $image == "attached", true, false); ?>><?php _e('Attached images','xml-sitemap-feed'); ?></option>
		    </select>
        </label>
	<p class="description">
        <?php _e('Note: Google News prefers at most one image per article in the News Sitemap. If multiple valid images are specified, the crawler will have to pick one arbitrarily. Images in News Sitemaps should be in jpeg or png format.','xml-sitemap-feed'); ?>
        <?php printf(__('Read more on %s.','xml-sitemap-feed'),'<a href="https://support.google.com/news/publisher/answer/13369" target="_blank">'.__('Prevent missing or incorrect images','xml-sitemap-feed').'</a>'); ?>
    </p>
</fieldset>
