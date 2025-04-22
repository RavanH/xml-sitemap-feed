<?php
/**
 * Post type options fields
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset id="xmlsf_post_type_<?php echo esc_attr( $obj->name ); ?>_settings">
	<legend class="screen-reader-text">
		<?php echo esc_html( $obj->label ); ?>
	</legend>

	<?php
	if ( empty( $obj->hierarchical ) && 'plugin' === \xmlsf()->sitemap->server_type ) {
		$archive = isset( $options[ $obj->name ]['archive'] ) ? $options[ $obj->name ]['archive'] : 'yearly';
		?>
	<p>
		<label><?php esc_html_e( 'Split by', 'xml-sitemap-feed' ); ?>
			<select name="xmlsf_post_type_settings[<?php echo esc_attr( $obj->name ); ?>][archive]" id="xmlsf_post_type_settings_'<?php echo esc_attr( $obj->name ); ?>_archive">
				<option value="">
					<?php esc_html_e( 'None' ); ?>
				</option>
				<option value="yearly"<?php echo selected( 'yearly' === $archive, true, false ); ?>>
					<?php esc_html_e( 'Year', 'xml-sitemap-feed' ); ?>
				</option>
				<option value="monthly"<?php echo selected( 'monthly' === $archive, true, false ); ?>>
					<?php esc_html_e( 'Month', 'xml-sitemap-feed' ); ?>
				</option>
				<?php do_action( 'xmlsf_posttype_archive_field_options', $obj, $archive ); ?>
			</select>
		</label>
		<span class="description"><?php echo apply_filters( 'xmlsf_posttype_archive_field_description', sprintf( /* Translators: XML Sitemap Advanced */ esc_html__( 'More options available in %s.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/xml-sitemap-advanced/" target="_blank">' . esc_html__( 'XML Sitemap Advanced', 'xml-sitemap-feed' ) . '</a>' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	</p>
		<?php
	}

	$priority_val = ! empty( $options[ $obj->name ]['priority'] ) ? $options[ $obj->name ]['priority'] : '';
	$image        = isset( $options[ $obj->name ]['tags']['image'] ) ? $options[ $obj->name ]['tags']['image'] : 'attached';
	?>

	<p>
		<label><?php esc_html_e( 'Priority', 'xml-sitemap-feed' ); ?>
			<input type="number" step="0.1" min="0.1" max="0.9" name="xmlsf_post_type_settings[<?php echo esc_attr( $obj->name ); ?>][priority]" id="xmlsf_post_type_<?php echo esc_attr( $obj->name ); ?>_priority" value="<?php echo esc_attr( $priority_val ); ?>" class="small-text" />
		</label>
		<span class="description">
			<?php esc_html_e( 'The default priority value must be between 0 and 1. To disable priority, leave this field empty.', 'xml-sitemap-feed' ); ?>
		</span>
	</p>

	<p>
		<label>
			<input type="checkbox" name="xmlsf_post_type_settings[<?php echo esc_attr( $obj->name ); ?>][dynamic_priority]" id="xmlsf_post_type_<?php echo esc_attr( $obj->name ); ?>_dynamic_priority" value="1"<?php echo checked( ! empty( $options[ $obj->name ]['dynamic_priority'] ), true, false ); ?> />
			<?php esc_html_e( 'Automatic Priority calculation.', 'xml-sitemap-feed' ); ?>
		</label>
	</p>

	<p>
		<label>
			<input type="checkbox" name="xmlsf_post_type_settings[<?php echo esc_attr( $obj->name ); ?>][update_lastmod_on_comments]" id="xmlsf_post_type_<?php echo esc_attr( $obj->name ); ?>_update_lastmod_on_comments" value="1"<?php echo checked( ! empty( $options[ $obj->name ]['update_lastmod_on_comments'] ), true, false ); ?> />
			<?php esc_html_e( 'Update the Last Modified date on each new comment.', 'xml-sitemap-feed' ); ?>
		</label>
	</p>

	<?php
	if ( 'plugin' === \xmlsf()->sitemap->server_type ) {
		$context = ( 'page' === $obj->name ) ? 'page' : 'post';
		?>
	<p>
		<label>
			<?php esc_html_e( 'Add image tags for', 'xml-sitemap-feed' ); ?>
			<select name="xmlsf_post_type_settings[<?php echo esc_attr( $obj->name ); ?>][tags][image]" id="xmlsf_post_type_<?php echo esc_attr( $obj->name ); ?>_tags_image">
				<option value="">
					<?php esc_html_e( 'None' ); ?>
				</option>
				<option value="featured"<?php echo selected( 'featured' === $image, true, false ); ?>>
					<?php echo esc_html_x( 'Featured image', $context ); ?>
				</option>
				<option value="attached"<?php echo selected( 'attached' === $image, true, false ); ?>>
					<?php esc_html_e( 'Attached images', 'xml-sitemap-feed' ); ?>
				</option>
			</select>
		</label>
	</p>
		<?php
	}
	?>
</fieldset>
