<fieldset id="xmlsf_taxonomies">
    <legend class="screen-reader-text">
        <?php _e('XML Sitemaps for taxonomies','xml-sitemap-feed'); ?>
    </legend>
    <?php echo implode( '<br/>
    ', $tax_list); ?>
    <br>
    <span class="description">
        <a id="xmlsf_taxonomy_settings_link" href="#xmlsf_taxonomy_settings">
            <?php echo translate('Settings'); ?>
        </a>
    </span>
    <script type="text/javascript">
        jQuery( document ).ready( function() {
            jQuery("#xmlsf_taxonomy_settings").hide();
            jQuery("#xmlsf_taxonomy_settings_link").click( function(event) {
                    event.preventDefault();
                jQuery("#xmlsf_taxonomy_settings").toggle("fast");
            });
        });
    </script>
    <ul id="xmlsf_taxonomy_settings" class="xmlsf_settings">
        <li>
            <label>
                <?php _e('Priority','xml-sitemap-feed'); ?> 
                <input type="number" step="0.1" min="0.1" max="0.9" name="xmlsf_taxonomy_settings[priority]" id="xmlsf_taxonomy_priority" value="<?php echo ( isset($taxonomy_settings['priority']) ? $taxonomy_settings['priority'] : '' ); ?>" class="small-text" />
            </label>
        </li>
        <li>
            <label>
                <input type="checkbox" name="xmlsf_taxonomy_settings[dynamic_priority]" id="xmlsf_taxonomy_dynamic_priority" value="1"<?php echo checked( !empty($taxonomy_settings['dynamic_priority']), true, false ); ?> />
                <?php _e('Automatic Priority calculation.','xml-sitemap-feed'); ?>
            </label>
        </li>
        <li>
            <label>
                <?php _e('Maximum number of terms per taxonomy sitemap','xml-sitemap-feed'); ?> 
                <input type="number" name="xmlsf_taxonomy_settings[term_limit]" id="xmlsf_taxonomy_term_limit" value="<?php echo ( isset($taxonomy_settings['term_limit']) ? $taxonomy_settings['term_limit'] : '' ); ?>" class="small-text" />
            </label> 
            <span class="description">
                <?php _e('Set to 0 for unlimited.','xml-sitemap-feed'); ?>
            </span>
        </li>
    </ul> 
</fieldset>