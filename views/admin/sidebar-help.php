<h3><span class="dashicons dashicons-sos"></span> <?php echo translate('Help'); ?></h3>
<p>
	<?php printf (
		/* translators: Support forum URL on WordPress.org */
		__( 'You can find instructions on the help tab above. If you still have questions, please go to the <a href="%s" target="_blank">Support forum</a>.', 'xml-sitemap-feed' ),
		'https://wordpress.org/support/plugin/xml-sitemap-feed'
	); ?>
	<?php printf (
		/* translators: links to Google News Help Center and Publisher Help Forum */
		__( 'More general help can be found on %1$s and %2$s.', 'xml-sitemap-feed' ),
		'<a href="https://support.google.com/googlenews/" target="_blank">' . __( /* translators: Site title https://support.google.com/googlenews/ */ 'Google News Help Center', 'xml-sitemap-feed' ) . '</a>',
		'<a href="https://support.google.com/googlenews/community" target="_blank">' . __( /* Translators: Forum title https://support.google.com/googlenews/community */ 'Publisher Help Forum', 'xml-sitemap-feed' ) . '</a>'
	); ?>
</p>
