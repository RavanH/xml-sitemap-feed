<div class="notice notice-error fade is-dismissible">
	<p>
        <strong><?php _e('XML Sitemap & Google News','xml-sitemap-feed'); ?></strong>
    </p>
    <p>
        <?php printf( /* Translators: Feed Redirect URL (Theme option name), Plugn name, Theme Options, Customizer (linked to Customizer page) */
			__( 'The Catch Box theme option %1$s is not compatible with %2$s. Please go to %3$s in the %4$s and remove it.', 'xml-sitemap-feed'),
				'<strong>' . translate('Feed Redirect URL', 'catch-box') . '</strong>',
				__('XML Sitemap & Google News','xml-sitemap-feed'),
				'<strong>' . translate('Theme Options', 'catch-box') . '</strong>',
				'<a href="' . admin_url('customize.php') . '" target="_blank">' . translate('Customizer') . '</a>'
			);
		?>
    </p>
	<form action="" method="post">
		<?php wp_nonce_field( XMLSF_BASENAME.'-notice', '_xmlsf_notice_nonce' ); ?>
		<p>
			<input type="hidden" name="xmlsf-dismiss" value="catchbox_feed_redirect" />
			<input type="submit" class="button button-small" name="xmlsf-dismiss-submit" value="<?php echo translate('Dismiss'); ?>" />
		</p>
	</form>
</div>
