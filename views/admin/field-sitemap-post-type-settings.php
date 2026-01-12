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
		<span class="description"><?php apply_filters( 'xmlsf_advanced_enabled', false ) || printf( /* Translators: XML Sitemap Advanced */ esc_html__( 'More options available in %s.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/xml-sitemap-advanced/" target="_blank">' . esc_html__( 'XML Sitemap Advanced', 'xml-sitemap-feed' ) . '</a>' ); ?></span>
	</p>
		<?php
	}

	$image        = isset( $options[ $obj->name ]['tags']['image'] ) ? $options[ $obj->name ]['tags']['image'] : 'attached';
	?>

	<p>
		<label>
			<input type="checkbox" name="xmlsf_post_type_settings[<?php echo esc_attr( $obj->name ); ?>][update_lastmod_on_comments]" id="xmlsf_post_type_<?php echo esc_attr( $obj->name ); ?>_update_lastmod_on_comments" value="1"<?php echo checked( ! empty( $options[ $obj->name ]['update_lastmod_on_comments'] ), true, false ); ?> />
			<?php esc_html_e( 'Update the Last Modified date on each new comment.', 'xml-sitemap-feed' ); ?>
		</label>
	</p>

	<?php
	if ( 'plugin' === \xmlsf()->sitemap->server_type ) {
		?>
	<p>
		<label>
			<?php esc_html_e( 'Add image tags for', 'xml-sitemap-feed' ); ?>
			<select name="xmlsf_post_type_settings[<?php echo esc_attr( $obj->name ); ?>][tags][image]" id="xmlsf_post_type_<?php echo esc_attr( $obj->name ); ?>_tags_image">
				<option value="">
					<?php esc_html_e( 'None' ); ?>
				</option>
				<option value="featured"<?php echo selected( 'featured' === $image, true, false ); ?>>
					<?php echo ( 'page' === $obj->name ) ? esc_html_x( 'Featured image', 'page' ) : esc_html_x( 'Featured image', 'post' ); ?>
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
