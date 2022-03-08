<fieldset id="xmlsf_sitemap_server">
	<legend class="screen-reader-text">
		<?php _e( 'XML Sitemap server', 'xml-sitemap-feed' ); ?>
	</legend>
	<p>
		<label>
			<input type="checkbox" name="xmlsf_core_sitemap" id="xmlsf_core_sitemap" value="1"<?php checked( !empty( $server ), true); ?> />
			<?php _e( 'Use WordPress core XML sitemaps (recommended)', 'xml-sitemap-feed' ); ?>
		</label>
	</p>
	<p class="description">
		<?php _e( 'Disabling this option will cause an alternative XML sitemap server to be used. The alternative server is provided by the plugin XML Sitemaps & Google News. It generates the sitemap in a different way, allowing some additional configuration options. However, it is not garanteed to be compatible with your specific WordPress setup.', 'xml-sitemap-feed' ); ?>
	</p>
</fieldset>
