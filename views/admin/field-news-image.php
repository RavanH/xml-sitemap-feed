<fieldset>
    <legend class="screen-reader-text"><?php echo translate('Images'); ?></legend>
	<label><?php _e('Add image tags for','xml-sitemap-feed'); ?>
        <select name="xmlsf_news_tags[image]">
			<option value=""><?php echo translate('None'); ?></option>
			<option value="featured"<?php echo selected( $image == "featured", true, false); ?>><?php echo translate_with_gettext_context('Featured Image','post'); ?></option>
			<option value="attached"<?php echo selected( $image == "attached", true, false); ?>><?php _e('Attached images','xml-sitemap-feed'); ?></option>
	    </select>
    </label>
</fieldset>
