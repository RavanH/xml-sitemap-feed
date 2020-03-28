<p>
	<strong><?php _e( 'Include taxonomies', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php _e( 'Activate this to include a taxonomy terms sitemap in the sitemap index.', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<strong><?php _e( 'Priority', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php echo __( 'Priority can be used to signal the importance of taxonomy term archives relative to other content like posts, pages or author archives.', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<strong><?php _e( 'Automatic Priority calculation.', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php echo __('Adjusts the Priority of each taxonomy term based on the relative number of attributed posts.','xml-sitemap-feed'); ?>
</p>
<p>
	<strong><?php _e( 'Maximum terms per sitemap', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php _e( 'The absolute maximum allowed is 50.000 per sitemap. Reduce this number if you experience errors or slow sitemaps.', 'xml-sitemap-feed' ); ?>
	<?php _e( 'Terms are ordered by number of posts, starting with the most used terms down to the least used. Terms without any posts will not appear in the sitemap.', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<strong><?php _e('Limit to these taxonomies:','xml-sitemap-feed'); ?></strong>
	<br />
	<?php _e( 'Select the taxonomies to include in the sitemap index. Select none to automatically include all public taxonomies.', 'xml-sitemap-feed' ); ?>
</p>
