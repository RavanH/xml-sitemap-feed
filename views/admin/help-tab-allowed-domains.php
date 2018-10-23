<p>
	<?php printf( /* translators: WordPress site domain */ __( 'By default, only the domain %s as used in your WordPress site address is allowed.','xml-sitemap-feed'), '<strong>'.parse_url( home_url(), PHP_URL_HOST ).'</strong>' ); ?>
	<?php _e( 'This means that all URLs that use another domain (custom URLs or using a plugin like Page Links To) are filtered from the XML Sitemap. However, if you are the verified owner of other domains in your Google/Bing Webmaster Tools account, you can include these in the same sitemap. Add these domains, without protocol (http/https) each on a new line. Note that if you enter a domain with www, all URLs without it or with other subdomains will be filtered.','xml-sitemap-feed'); ?>
</p>
