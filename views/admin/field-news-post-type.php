<fieldset>
    <legend class="screen-reader-text"><?php _e('Post type','xml-sitemap-feed'); ?></legend>
	<?php
    foreach ( $post_types as $post_type ) :
		$obj = get_post_type_object( $post_type );
		if ( !is_object( $obj ) )
			continue;
    	?>
		<label>
			<input type="<?php echo $type; ?>" name="xmlsf_news_tags[post_type][]" id="xmlsf_post_type_<?php echo $obj->name; ?>" value="<?php echo $obj->name; ?>"<?php checked( in_array($obj->name, $news_post_type), true ) . disabled( !in_array($obj->name,$allowed), true ); ?> />
			<?php echo $obj->label; ?>
		</label>
		<br/>
    <?php
	endforeach;
	?>
	<?php //printf(__('At least one post type must be selected. By default, the post type %s will be used.','xml-sitemap-feed'),translate('Posts')); ?>
	<?php if ( $do_warning ) { ?>
		<p class="description">
			<?php _e('Custom post types that do <strong>not</strong> use the post category taxonomy cannot be included as long as any category is selected below.','xml-sitemap-feed'); ?>
		</p>
	<?php } ?>
</fieldset>
