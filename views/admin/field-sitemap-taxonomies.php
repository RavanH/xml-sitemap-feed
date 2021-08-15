<fieldset id="xmlsf_taxonomies">
  <legend class="screen-reader-text">
    <?php _e( 'Taxonomies', 'xml-sitemap-feed' ); ?>
  </legend>
  <p>
    <?php _e('Limit to these taxonomies:','xml-sitemap-feed'); ?>
  </p>
    <?php if ( !empty( $this->public_taxonomies() ) ) { ?>
  <ul class="cat-checklist">
    <?php foreach ( $this->public_taxonomies() as $name => $label ) { ?>
    <li>
      <label>
        <input type="checkbox" name="xmlsf_taxonomies[]" id="xmlsf_taxonomies_'<?php echo $name; ?>" value="<?php echo $name; ?>" <?php checked( in_array( $name, (array) $taxonomies ) ); ?>/>
        <?php echo $label; ?> (<?php echo wp_count_terms( $name ); ?>)
      </label>
    </li>
    <?php } ?>
  </ul>
  <?php } else { ?>
	<p class="description warning" style="color: red;">
		<?php _e( 'No taxonomies available for the currently included post types.', 'xml-sitemap-feed' ); ?>
	</p>
	<?php } ?>
</fieldset>
