<p>
	<?php _e( 'Google News displays images associated with articles included in their index.', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<?php _e( 'Note: Google News prefers at most one image per article in the News Sitemap. If multiple valid images are specified, the crawler will have to pick one arbitrarily. Images in News Sitemaps should be in jpeg or png format.', 'xml-sitemap-feed' ); ?>
	<?php printf( /* translators: Prevent missing or incorrect images help page */ __( 'Read more on %s.', 'xml-sitemap-feed' ), '<a href="https://support.google.com/news/publisher/answer/13369" target="_blank">'.__( /* translators: PAge title https://support.google.com/news/publisher/answer/13369 */ 'Prevent missing or incorrect images','xml-sitemap-feed').'</a>' ); ?>
</p>
