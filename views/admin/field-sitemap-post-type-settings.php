<fieldset id="xmlsf_post_type_settings">
	<legend class="screen-reader-text">
		<?php echo translate( 'General' ); ?>
	</legend>

	<p>
		<label>
			<?php _e('Maximum posts per sitemap', 'xml-sitemap-feed'); ?>
			<input type="number" step="100" min="0" max="50000" name="xmlsf_post_type_settings[limit]" id="xmlsf_post_type_settings_limit" value="<?php echo ( isset($settings['limit']) ? $settings['limit'] : '2000' ); ?>" class="medium-text" />
		</label>
	</p>

</fieldset>
