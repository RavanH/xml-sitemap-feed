<p>
	<strong><?php _e( 'Allowed domains', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php printf( /* translators: WordPress site domain */ __( 'By default, only the domain %s as used in your WordPress site address is allowed.','xml-sitemap-feed'), '<strong>'.$default.'</strong>' ); ?>
	<?php _e( 'This means that all URLs that use another domain (custom URLs or using a plugin like Page Links To) are filtered from the XML Sitemap. However, if you are the verified owner of other domains in your Google/Bing Webmaster Tools account, you can include these in the same sitemap. Add these domains, without protocol (http/https) each on a new line. Note that if you enter a domain with www, all URLs without it or with other subdomains will be filtered.','xml-sitemap-feed'); ?>
</p>
<p>
	<strong><?php _e( 'External web pages', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php _e( 'Add the full URL, including protocol (http/https) and domain.', 'xml-sitemap-feed' ); ?>
	<?php _e( 'Optionally add a priority value between 0 and 1, separated with a space after the URL.', 'xml-sitemap-feed' ); ?>
	<?php _e( 'Start each URL on a new line.', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<strong><?php _e( 'External XML Sitemaps', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php _e('Add the full URL, including protocol (http/https) and domain.','xml-sitemap-feed'); ?>
	<?php _e('Start each URL on a new line.','xml-sitemap-feed'); ?>
	<br>
	<span style="color: red" class="warning">
		<?php _e('Only valid sitemaps are allowed in the Sitemap Index. Use your Google/Bing Webmaster Tools to verify!','xml-sitemap-feed'); ?>
	</span>
</p>
