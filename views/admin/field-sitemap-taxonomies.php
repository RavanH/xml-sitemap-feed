<fieldset id="xmlsf_taxonomies">
    <legend class="screen-reader-text">
        <?php _e( 'Include taxonomies', 'xml-sitemap-feed' ); ?>
    </legend>

	<?php
		foreach ( $this->public_taxonomies() as $name => $label ) {
			$tax_list[] = '<label><input type="checkbox" name="'.'xmlsf_taxonomies[]" id="xmlsf_taxonomies_' . $name . '" value="' . $name . '"' .
				checked( in_array( $name, (array) $taxonomies ), true, false ).' /> ' . $label . ' (' .  wp_count_terms( $name ) . ')</label>';
		} ?>
		<?php
		echo implode( '<br/>', $tax_list );
	?>

	<?php if ( ! $this->public_taxonomies() ) { ?>
	<p class="description warning" style="color: red;">
		<?php _e( 'No taxonomies available for the currently included post types.', 'xml-sitemap-feed' ); ?>
	</p>
	<?php } ?>
</fieldset>
