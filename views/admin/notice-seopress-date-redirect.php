<div class="notice notice-error fade is-dismissible">
  <p>
		<?php printf( /* translators: conflicting plugin name */
			__( 'A setting in the %s plugin causes all date based sitemaps to redirect to the main page.', 'xml-sitemap-feed'),
			translate('SEOPress','wp-seopress')
		); ?>
    <?php printf( /* translators: Date archives, Archives (linked to WP SEO plugin settings), Split by, None, Included post types (linked to Sitemap settings) */
			__( 'Please either enable <strong>%1$s</strong> under %2$s in your SEO settings or set all <strong>%3$s</strong> options to <strong>%4$s</strong> under %5$s in your XML Sitemap settings.', 'xml-sitemap-feed'),
			translate('Date archives','wp-seopress'),
			'<a href="' . admin_url('admin.php') . '?page=seopress-titles#tab=tab_seopress_titles_archives">' . translate('Archives','wp-seopress') . '</a>',
			__( 'Split by', 'xml-sitemap-feed' ),
			translate('None'),
			'<a href="' . admin_url('options-general.php') . '?page=xmlsf">' . __('Included post types','xml-sitemap-feed') . '</a>'
		); ?>
  </p>
</div>
