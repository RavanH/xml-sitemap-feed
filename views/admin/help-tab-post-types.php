<p>
	<strong><?php _e( 'Include post types in the sitemap index. ', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php _e( 'Activate the option Include... for any post type you wish to create a sitemap for. If you have more post types than the standard Posts and Pages, make sure they are public by following links in the sitemap in an anonymous browser window or after logging out. When you end up on a 404 Not Found page, then come back here and deactivate the post type in question.', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<strong><?php _e( 'Split by', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php _e( 'Choose Split by Month or Week if you experience errors or slow sitemaps.', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<strong><?php _e( 'Priority', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php echo __( 'Priority can be used to signal the relative importance of post types in general and individual posts in particular.', 'xml-sitemap-feed' ); ?>
	<?php echo __( 'Priority can be overridden on individual posts.', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<strong><?php _e( 'Automatic Priority calculation.', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php echo __( 'Adjusts the Priority based on factors like age, comments, sticky post or blog page.', 'xml-sitemap-feed' ); ?>
	<?php echo __( 'Please note: this option can make sitemap generation slower and more resource intensive.', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<strong><?php _e( 'Update the Last Changed date on each new comment.', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php echo __( 'The Last Changed timestamp will be updated whenever a comment is added. Useful for sites where user interaction like comments play a large role and give added content value. But otherwise this is not advised.', 'xml-sitemap-feed' ); ?>
	<?php echo __( 'Please note: this option can make sitemap generation slower and more resource intensive.', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<strong><?php _e( 'Add image tags for', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php echo __( 'Choose which images should be added to the sitemap. Note that images can be present in a post while not being attached to that post. If you have images in your Library that are not attached to any post, or not used as featured image, then those will not be present in your sitemap.', 'xml-sitemap-feed' ); ?>
</p>
