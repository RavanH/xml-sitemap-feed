<p>
	<strong><?php _e( 'Include authors', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php _e( 'Activate this to include an author sitemap in the sitemap index. Only users of level Contributor and higher, with at least one published post, are included in the author sitemap.', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<strong><?php _e( 'Priority', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php echo __( 'Priority can be used to signal the importance of author archives relative to other content like posts, pages or taxonomy term archives.', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<strong><?php _e( 'Maximum authors per sitemap', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php _e( 'The absolute maximum allowed is 50.000 per sitemap. Reduce this number if you experience errors or slow sitemaps.', 'xml-sitemap-feed' ); ?>
	<?php _e( 'Authors are ordered by number of posts, starting with the most published posts down to the least. Authors without any posts will not appear in the sitemap.', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<?php echo apply_filters (
		'xmlsf_author_settings_description',
		sprintf (
			/* Translators: XML Sitemap Advanced */
			__( 'More options available in %s.', 'xml-sitemap-feed' ),
			'<a href="https://premium.status301.com/downloads/xml-sitemap-advanced/" target="_blank">'.__('XML Sitemap Advanced','xml-sitemap-feed').'</a>'
			)
		);
	?>
</p>
