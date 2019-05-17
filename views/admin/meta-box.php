<p>
  <label>
    <?php _e('Priority','xml-sitemap-feed'); ?>
    <input type="number" step="0.1" min="0.1" max="1" name="xmlsf_priority" id="xmlsf_priority" value="<?php echo $priority; ?>" class="small-text"<?php disabled( $disabled )?> />
  </label>
  <span class="description">
    <?php printf(
    	__('Leave empty for automatic Priority as configured on %1$s > %2$s.','xml-sitemap-feed'),
    	translate('Settings'),
    	'<a href="' . admin_url('options-general.php') . '?page=xmlsf">' . __('XML Sitemap','xml-sitemap-feed') . '</a>'
    ); ?>
	</span>
</p>
<p>
  <label>
    <input type="checkbox" name="xmlsf_exclude" id="xmlsf_exclude" value="1"<?php checked( !empty($exclude) ); disabled( $disabled ); ?> />
		<?php _e('Exclude from XML Sitemap','xml-sitemap-feed'); ?>
  </label>
</p>
