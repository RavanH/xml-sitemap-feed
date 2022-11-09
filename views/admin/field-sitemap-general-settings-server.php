<fieldset id="xmlsf_sitemap_general_settings_server">
	<legend class="screen-reader-text">
		<?php _e( 'XML Sitemap server', 'xml-sitemap-feed' ); ?>
	</legend>
	<p>
		<label>
			<input type="radio" name="xmlsf_general_settings[server]" value="core"<?php disabled(  $nosimplexml, true ); ?><?php checked( $server === 'core' && ! $nosimplexml, true ); ?> />
			<?php _e( 'Use WordPress core XML sitemaps', 'xml-sitemap-feed' ); ?>
		</label>
		<br>
		<label>
			<input type="radio" name="xmlsf_general_settings[server]" value="plugin"<?php checked( $server !== 'core' || $nosimplexml, true ); ?> />
			<?php _e( 'Use alternative XML sitemaps', 'xml-sitemap-feed' ); ?>
		</label>
	</p>
	<p class="description">
		<?php
		if ( $nosimplexml ) {
			printf( __( 'It appears the SimpleXML module is not available. Please use the alternative XML sitemap server or install the missing PHP module. See recommendations on %s.', 'xml-sitemap-feed' ), '<a href="' . admin_url( 'site-health.php' ). '">' . translate('Site Health') . '</a>' );
		} else {
			_e( 'The alternative server is provided by the plugin XML Sitemaps & Google News. It generates the sitemap in a different way, allowing some additional configuration options. However, it is not garanteed to be compatible with your specific WordPress installation and it is generally more resource intensive than the WordPress core sitemap.', 'xml-sitemap-feed' );
		}
		?>
	</p>
</fieldset>
