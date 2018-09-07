<p>
	<?php _e('It is generally not recommended to include taxonomy pages, unless their content brings added value.','xml-sitemap-feed'); ?>
	<?php _e('For example, when you use category descriptions with information that is not present elsewhere on your site or if taxonomy pages list posts with an excerpt that is different from, but complementary to the post content. In these cases you might consider including certain taxonomies. Otherwise, if you fear <a href="http://moz.com/learn/seo/duplicate-content">negative affects of duplicate content</a> or PageRank spread, you might even consider disallowing indexation of taxonomies.','xml-sitemap-feed'); ?>
</p>
<p>
	<strong><?php _e( 'Automatic Priority calculation.', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php echo __('Adjusts the Priority of each taxonomy term based on the relative number of attributed posts.','xml-sitemap-feed'); ?>
</p>
<p>
	<strong><?php _e( 'Maximum per sitemap', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php _e( 'The absolute maximum allowed is 50.000 per sitemap. Reduce this number if you experience errors or slow sitemaps.', 'xml-sitemap-feed' ); ?>
</p>
