<p>
	<strong><?php echo translate('Tools'); ?></strong>
</p>
<form action="" method="post">
	<?php wp_nonce_field( XMLSF_BASENAME.'-help', '_xmlsf_help_nonce' ); ?>
	<input type="submit" name="xmlsf-check-conflicts" class="button button-small" value="<?php _e( 'Check for conflicts', 'xml-sitemap-feed' ); ?>" /> &nbsp;
	<input type="submit" name="xmlsf-clear-settings" class="button button-small" value="<?php _e( 'Reset all XML Sitemap settings', 'xml-sitemap-feed' ); ?>" onclick="javascript:return confirm('<?php _e('Clear all XML Sitemap & Google News Sitemap settings.','xml-sitemap-feed'); ?> <?php _e('This will revert all your sitemap settings to the plugin defaults.','xml-sitemap-feed'); ?>\n\n<?php echo translate('Are you sure you want to do this?'); ?>')" />
</form>
