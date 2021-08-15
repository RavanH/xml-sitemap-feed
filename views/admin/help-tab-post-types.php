<p>
	<strong><?php _e( 'Include...', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php _e( 'Activate these to include a post type sitemap in the sitemap index.', 'xml-sitemap-feed' ); ?>
	<?php _e( 'Make sure that post types are public by following links in the sitemap in an anonymous browser window or after logging out.', 'xml-sitemap-feed' ); ?>
	<?php _e( 'Some post types or posts may be carrying noindex headers. Make sure to NOT include those post types or posts.', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<strong><?php _e( 'Split by', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php _e( 'Choose Split by Month if you experience errors or slow sitemaps.', 'xml-sitemap-feed' ); ?>
	<?php echo apply_filters(
		'xmlsf_posttype_archive_field_description',
		sprintf( /* Translators: XML Sitemap Advanced */ __( 'More options available in %s.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/xml-sitemap-advanced/" target="_blank">'.__('XML Sitemap Advanced','xml-sitemap-feed').'</a>' ) ); ?>
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
