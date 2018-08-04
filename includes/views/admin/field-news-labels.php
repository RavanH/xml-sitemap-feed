<fieldset id="xmlsf_news_labels">
    <legend class="screen-reader-text"><?php _e('Source labels','xml-sitemap-feed'); ?></legend>
	<p><?php _e('Default genre:','xml-sitemap-feed'); ?></p>
	<p>
		<?php echo implode('<br/>
        ',$genre_list); ?>
	</p>
	<p class="description">
		<?php _e('Source labels provide more information about the content of your articles.','xml-sitemap-feed'); ?> 
		<?php _e('The FactCheck label may only be applied if you publish stories with fact-checking content that\'s indicated by schema.org ClaimReview markup.','xml-sitemap-feed'); ?> 
		<?php printf(__('Read more about source labels on %s','xml-sitemap-feed'),'<a href="https://support.google.com/news/publisher/answer/4582731" target="_blank">'.__('What does each source label mean?','xml-sitemap-feed').'</a>'); ?>
	</p>
</fieldset>
