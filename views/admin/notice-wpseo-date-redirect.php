<div class="notice notice-error fade is-dismissible">
	<p>
        <strong><?php _e('XML Sitemap & Google News','xml-sitemap-feed'); ?></strong>
    </p>
    <p>
        <?php printf( /* translators: Date archives, Archives (linked to WP SEO plugin settings), Split by, None, Included post types (linked to Sitemap settings) */
			__( 'A setting in the WordPress SEO plugin causes all date based sitemaps to redirect to the main page. Please either enable <strong>%1$s</strong> under %2$s in your SEO settings or set all <strong>%3$s</strong> options to <strong>%4$s</strong> under %5$s in your XML Sitemap settings.', 'xml-sitemap-feed'),
				translate('Date archives','wordpress-seo'),
				'<a href="' . admin_url('admin.php') . '?page=wpseo_titles#top#archives">' . translate('Archives','wordpress-seo') . '</a>',
				__('Split by','xml-sitemap-feed'),
				translate('None'),
				'<a href="' . admin_url('options-general.php') . '?page=xmlsf">' . __('Included post types','xml-sitemap-feed') . '</a>'
			);
		?>
    </p>
	<form action="" method="post">
		<?php wp_nonce_field( XMLSF_BASENAME.'-notice', '_xmlsf_notice_nonce' ); ?>
		<p>
			<input type="hidden" name="xmlsf-dismiss" value="date_redirect" />
			<input type="submit" class="button button-small" name="xmlsf-date-dismiss" value="<?php echo translate('Dismiss'); ?>" />
		</p>
	</form>
</div>
