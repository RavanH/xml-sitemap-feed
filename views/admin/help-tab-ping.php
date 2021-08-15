<p>
	<?php printf (
		/* translators: Plugin name, Reading Settings URL */
		__( 'If desired, %1$s will automatically alert search engines of your updated <a href="%2$s">XML Sitemaps</a> upon each new publication.', 'xml-sitemap-feed' ),
		__('XML Sitemap & Google News','xml-sitemap-feed'), admin_url('options-reading.php')
	); ?>
</p>
<p>
	<?php _e('Pings are limited to once per hour for your XML Sitemap and once per 5 minutes for your Google News Sitemap.', 'xml-sitemap-feed'); ?>
</p>
