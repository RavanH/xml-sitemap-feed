<div class="notice notice-warning fade is-dismissible">
	<p>
        <strong><?php _e('XML Sitemap & Google News','xml-sitemap-feed'); ?></strong>
    </p>
    <p>
        <?php printf( /* translators: Plugn name, Features (linked to WP SEO plugin settings), XML Sitemap Index, Reading Settings admin page (linked to Reading settings) */
			__( 'The WordPress SEO plugin may be used in conjunction with %1$s but it is not advised to have the same feature enabled in both plugins. Please either disable the Yoast sitemap under %2$s in your SEO settings or disable the option %3$s on %4$s.', 'xml-sitemap-feed'),
				__('XML Sitemap & Google News','xml-sitemap-feed'),
				'<a href="' . admin_url('admin.php') . '?page=wpseo_dashboard#top#features">' . translate('Features','wordpress-seo') . '</a>',
				__('XML Sitemap Index','xml-sitemap-feed'),
				'<a href="' . admin_url('options-reading.php') . '#blog_public">' . translate('Reading Settings') . '</a>'
			);
		?>
    </p>
	<form action="" method="post">
		<?php wp_nonce_field( XMLSF_BASENAME.'-notice', '_xmlsf_notice_nonce' ); ?>
		<p>
			<input type="hidden" name="xmlsf-dismiss" value="wpseo_sitemap" />
			<input type="submit" class="button button-small" name="xmlsf-dismiss-submit" value="<?php echo translate('Dismiss'); ?>" />
		</p>
	</form>
</div>
