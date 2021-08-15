<fieldset>
	<legend class="screen-reader-text"><?php _e('XML Sitemap URL','xml-sitemap-feed'); ?></legend>

	<?php echo trailingslashit( get_home_url() ); ?><input type="text" name="xmlsf_sitemap_name" id="xmlsf_sitemap_name" placeholder="<?php echo $default; ?>" value="<?php echo $name; ?>" disabled>
	<p class="description" id="xmlsf-sitemap-name-description">
		<?php printf(
			/* Translators: default sitemap.xml file name */
			__('Set an alternative name for the sitemap index. Leave empty to use the default: %s','xml-sitemap-feed'), '<code>' . apply_filters( 'xmlsf_sitemap_filename', 'sitemap.xml' ) . '</code>'
		); ?><br/>
		<?php printf (
			/* Translators: XML Sitemap Advanced */
			__( 'Available in %s.', 'xml-sitemap-feed' ),
			'<a href="https://premium.status301.com/downloads/xml-sitemap-advanced/" target="_blank">'.__('XML Sitemap Advanced','xml-sitemap-feed').'</a>'
		); ?>
	</p>
</fieldset>
