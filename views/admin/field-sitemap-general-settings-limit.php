<fieldset id="xmlsf_general_settings_limit">
	<legend class="screen-reader-text">
		<?php _e( 'Maximum URLs per sitemap', 'xml-sitemap-feed' ); ?>
	</legend>

	<p>
		<label>
			<?php _e('Maximum URLs per sitemap', 'xml-sitemap-feed' ); ?>
			<input type="number" step="100" min="0" max="50000" name="xmlsf_general_settings[limit]" id="xmlsf_sitemap_settings_limit" value="<?php echo $limit; ?>" class="medium-text" />
		</label>
	</p>

</fieldset>
