<fieldset>
    <legend class="screen-reader-text"><?php _e('Reset XML sitemaps','xml-sitemap-feed'); ?></legend>
	<label>
        <input type="checkbox" name="<?php echo $this->prefix; ?>sitemaps[reset]" value="1" 
            onchange="if(this.checked){if(!confirm('<?php _e('Selecting this will clear all XML Sitemap & Google News Sitemap settings after Save Changes. Are you sure?','xml-sitemap-feed'); ?>')){this.checked=false}}" />
        <?php _e('Clear all XML Sitemap & Google News Sitemap settings.','xml-sitemap-feed'); ?>
    </label>
    <p class="description">
        <?php _e('Check this option and Save Changes to start fresh with the default settings.','xml-sitemap-feed'); ?>
    </p>
</fieldset>
