<p>
    <label>
        <?php _e('Priority','xml-sitemap-feed'); ?>
		<input type="number" step="0.1" min="0" max="1" name="xmlsf_priority" id="xmlsf_priority" value="<?php echo $priority; ?>" class="small-text"<?php echo disabled( $disabled, true, false )?> />
    </label> 
    <span class="description">
        <?php echo $description; ?>
	</span>
</p>
<p>
    <label>
        <input type="checkbox" name="xmlsf_exclude" id="xmlsf_exclude" value="1"<?php echo checked( !empty($exclude ), true, false) . disabled( $disabled, true, false ); ?> />
		<?php _e('Exclude from XML Sitemap','xml-sitemap-feed'); ?>
    </label>
</p>
