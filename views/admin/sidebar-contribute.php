<h3><span class="dashicons dashicons-thumbs-up"></span> <?php _e('Contribute','xml-sitemap-feed'); ?></h3>
<p>
	<a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=XML%20Sitemap%20Feeds&item_number=version%20<?php echo XMLSF_VERSION; ?>&no_shipping=0&tax=0&charset=UTF%2d8"
		title="<?php printf(__('Donate to keep the free %s plugin development & support going!','xml-sitemap-feed'),__('XML Sitemap & Google News','xml-sitemap-feed')); ?>">
		<img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" style="border:none;float:right;margin:4px 0 0 10px" width="92" height="26" />
	</a>
	<?php printf (
	/* translators: Review page URL and Translation page URL on WordPress.org */
	__( 'If you would like to contribute and share with the rest of the WordPress community, please consider writing a quick <a href="%1$s" target="_blank">Review</a> or help out with <a href="%2$s" target="_blank">Translating</a>!', 'xml-sitemap-feed' ),
	'https://wordpress.org/support/plugin/xml-sitemap-feed/reviews/?filter=5#new-post', 'https://translate.wordpress.org/projects/wp-plugins/xml-sitemap-feed'
	); ?>
</p>
<p>
	<?php printf (
	/* translators: Github project URL */
	__( 'For feature requests, reporting issues or contributing code, you can find and fork this plugin on <a href="%s" target="_blank">Github</a>.', 'xml-sitemap-feed' ),
	'https://github.com/RavanH/xml-sitemap-feed'
	); ?>
</p>
