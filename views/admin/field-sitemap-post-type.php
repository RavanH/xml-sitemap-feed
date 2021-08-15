<fieldset id="xmlsf_post_type_<?php echo $obj->name; ?>">
	<legend class="screen-reader-text">
		<?php echo $obj->label; ?>
	</legend>

	<p>
		<label>
			<input type="checkbox" name="xmlsf_post_types[<?php echo $obj->name; ?>][active]" id="xmlsf_post_types_<?php echo $obj->name; ?>" value="1"<?php checked( !empty($options[$obj->name]["active"]), true); ?> />
			<?php printf( /* translators: Post type name and post count */ __( 'Include %s', 'xml-sitemap-feed' ), $obj->label ); ?> (<?php echo $count->publish; ?>)
		</label>
	</p>

	<?php
	if ( empty($obj->hierarchical) ) {
	$archive = isset($options[$obj->name]['archive']) ? $options[$obj->name]['archive'] : 'yearly';
	?>
	<p>
		<label><?php _e( 'Split by', 'xml-sitemap-feed' ); ?>
			<select name="xmlsf_post_types[<?php echo $obj->name; ?>][archive]" id="xmlsf_post_types_'<?php echo $obj->name; ?>_archive">
				<option value="">
					<?php echo translate('None'); ?>
				</option>
				<option value="yearly"<?php echo selected( $archive == 'yearly', true, false ); ?>>
					<?php echo __( 'Year', 'xml-sitemap-feed' ); ?>
				</option>
				<option value="monthly"<?php echo selected( $archive == 'monthly', true, false ); ?>>
					<?php echo __( 'Month', 'xml-sitemap-feed' ); ?>
				</option>
				<?php do_action( 'xmlsf_posttype_archive_field_options', $obj, $archive ); ?>
			</select>
		</label>
		<span class="description"><?php echo apply_filters(
			'xmlsf_posttype_archive_field_description',
			sprintf( /* Translators: XML Sitemap Advanced */ __( 'More options available in %s.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/xml-sitemap-advanced/" target="_blank">'.__('XML Sitemap Advanced','xml-sitemap-feed').'</a>' ) ); ?></span>
	</p>
	<?php
	}

	$priority_val = !empty($options[$obj->name]['priority']) ? $options[$obj->name]['priority'] : '0.5';
	$image = isset($options[$obj->name]['tags']['image']) ? $options[$obj->name]['tags']['image'] : 'attached';
	$context = ( $obj->name === 'page' ) ? 'page' : 'post';
	?>

	<p>
		<label><?php echo __('Priority','xml-sitemap-feed'); ?>
			<input type="number" step="0.1" min="0.1" max="0.9" name="xmlsf_post_types[<?php echo $obj->name; ?>][priority]" id="xmlsf_post_types_<?php echo $obj->name; ?>_priority" value="<?php echo $priority_val; ?>" class="small-text" />
		</label>
	</p>

	<p>
		<label>
			<input type="checkbox" name="xmlsf_post_types[<?php echo $obj->name; ?>][dynamic_priority]" value="1"<?php echo checked( !empty($options[$obj->name]['dynamic_priority']), true, false); ?> />
			<?php echo __('Automatic Priority calculation.','xml-sitemap-feed'); ?>
		</label>
	</p>

	<p>
		<label>
			<input type="checkbox" name="xmlsf_post_types[<?php echo $obj->name; ?>][update_lastmod_on_comments]" value="1"<?php echo checked( !empty($options[$obj->name]["update_lastmod_on_comments"]), true, false); ?> />
			<?php echo __('Update the Last Changed date on each new comment.','xml-sitemap-feed'); ?>
		</label>
	</p>

	<p>
		<label>
			<?php echo __('Add image tags for','xml-sitemap-feed'); ?>
			<select name="xmlsf_post_types[<?php echo $obj->name; ?>][tags][image]">
				<option value="">
					<?php echo translate('None'); ?>
				</option>
				<option value="featured"<?php echo selected( $image == "featured", true, false); ?>>
					<?php echo translate_with_gettext_context('Featured Image',$context); ?>
				</option>
				<option value="attached"<?php echo selected( $image == "attached", true, false); ?>>
					<?php echo __('Attached images','xml-sitemap-feed'); ?>
				</option>
			</select>
		</label>
	</p>

</fieldset>
