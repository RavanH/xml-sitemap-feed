<fieldset id="xmlsf_taxonomy_settings">
  <legend class="screen-reader-text">
    <?php echo translate( 'General' ); ?>
  </legend>
  <p>
    <label>
      <input type="checkbox" name="xmlsf_taxonomy_settings[active]" id="xmlsf_taxonomy_active" value="1"<?php checked( !empty( $taxonomy_settings['active'] ), true); ?> />
      <?php _e( 'Include taxonomies', 'xml-sitemap-feed' ); ?> (<?php echo count( $this->public_taxonomies() ); ?>)
    </label>
    <?php if ( ! $this->public_taxonomies() ) { ?>
      <p class="description warning" style="color: red;">
        <?php _e( 'No taxonomies available for the currently included post types.', 'xml-sitemap-feed' ); ?>
      </p>
    <?php } ?>
  </p>
  <p>
    <label>
      <?php _e('Priority','xml-sitemap-feed'); ?>
      <input type="number" step="0.1" min="0.1" max="0.9" name="xmlsf_taxonomy_settings[priority]" id="xmlsf_taxonomy_priority" value="<?php echo ( isset($taxonomy_settings['priority']) ? $taxonomy_settings['priority'] : '' ); ?>" class="small-text" />
    </label>
  </p>
  <p>
    <label>
      <input type="checkbox" name="xmlsf_taxonomy_settings[dynamic_priority]" id="xmlsf_taxonomy_dynamic_priority" value="1"<?php echo checked( !empty($taxonomy_settings['dynamic_priority']), true, false ); ?> />
      <?php _e('Automatic Priority calculation.','xml-sitemap-feed'); ?>
    </label>
  </p>
  <p>
    <label>
      <?php _e('Maximum terms per sitemap','xml-sitemap-feed'); ?>
      <input type="number" step="100" min="0" max="50000" name="xmlsf_taxonomy_settings[term_limit]" id="xmlsf_taxonomy_term_limit" value="<?php echo ( isset($taxonomy_settings['term_limit']) ? $taxonomy_settings['term_limit'] : '' ); ?>" class="medium-text" />
    </label>
  </p>
</fieldset>
