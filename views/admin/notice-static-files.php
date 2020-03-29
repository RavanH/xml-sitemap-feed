<div class="notice notice-warning fade is-dismissible">
	<p>
		<strong><?php _e('XML Sitemap & Google News','xml-sitemap-feed'); ?></strong>
	</p>
	<p>
	<?php
	$number = count( self::$static_files );
	printf( /* translators: %1$s number of files, %2$s is Reading Settings URL */ _n(
		'The following static file has been found. Either delete it or disable the conflicting <a href="%2$s">sitemap</a>.',
		'The following %1$s static files have been found. Either delete them or disable the conflicting <a href="%2$s">sitemaps</a>.',
		$number,'xml-sitemap-feed'), number_format_i18n($number), admin_url('options-reading.php') . '#xmlsf_sitemaps'
	); ?>
	</p>
	<form action="" method="post">
		<?php wp_nonce_field( XMLSF_BASENAME.'-notice', '_xmlsf_notice_nonce' ); ?>
		<ul>
			<?php foreach ( self::$static_files as $name => $file) { ?>
			<li>
				<label><input type="checkbox" name="xmlsf-delete[]" value="<?php echo $name; ?>" /> <strong><?php echo $name; ?></strong> (<?php echo $file; ?>)</label>
			</li>
			<?php } ?>
		</ul>
		<p>
			<input type="submit" class="button button-small" name="xmlsf-delete-submit" value="<?php _e('Delete selected files','xml-sitemap-feed'); ?>" onclick="return confirm('<?php _e('Attempt to delete selected conflicting files.','xml-sitemap-feed'); ?>\n\n<?php echo translate('Are you sure you want to do this?'); ?>')" />
			&nbsp;
			<input type="hidden" name="xmlsf-dismiss" value="static_files" />
			<input type="submit" class="button button-small" name="xmlsf-dismiss-submit" value="<?php echo translate('Dismiss'); ?>" />
	  </p>
	</form>
</div>
