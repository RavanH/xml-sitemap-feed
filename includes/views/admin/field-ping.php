<fieldset id="xmlsf_ping">
    <legend class="screen-reader-text"><?php echo __('Ping Services','xml-sitemap-feed'); ?></legend>

	<label>
        <input type="checkbox" name="<?php echo $this->prefix; ?>ping[google][active]" id="xmlsf_ping_google" value="1"<?php echo checked( !empty($options['google']['active']), true, false); ?> /> 
		<?php _e('Google','xml-sitemap-feed'); ?>
	</label>

	<span class="description">
        <?php echo $ping_data['google']; ?>
	</span>

    <br>

	<label>
        <input type="checkbox" name="<?php echo $this->prefix; ?>ping[bing][active]" id="xmlsf_ping_bing" value="1"<?php echo checked( !empty($options['bing']['active']), true, false); ?> /> 
		<?php _e('Bing & Yahoo','xml-sitemap-feed'); ?>
	</label>

	<span class="description">
        <?php echo $ping_data['bing']; ?>
	</span>

</fieldset>