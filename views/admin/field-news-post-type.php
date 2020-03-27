<fieldset>
	<legend class="screen-reader-text"><?php _e('Post type','xml-sitemap-feed'); ?></legend>
<?php foreach ( $post_types as $post_type ) : $obj = get_post_type_object( $post_type ); if ( !is_object( $obj ) ) continue; ?>
	<label>
		<input type="<?php echo $type; ?>" name="xmlsf_news_tags[post_type][]" id="xmlsf_post_type_<?php echo $obj->name; ?>" value="<?php echo $obj->name; ?>"<?php checked( in_array($obj->name, $news_post_type), true ) . disabled( !in_array($obj->name, $allowed), true ); ?> />
		<?php echo $obj->label; ?>
	</label>
	<br/>
<?php endforeach; if ( $do_warning || 'radio' == $type ) : ?>
	<p class="description">
		<?php if ( $do_warning ) _e( 'Custom post types that do not use the post category taxonomy, cannot be included as long as any category is selected below.', 'xml-sitemap-feed' ); ?>
		<?php if ( 'radio' == $type ) printf( /* Translators: Advanced plugin name */ __( 'Including multiple post types in the same News Sitemap is provided by the %s module.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/google-news-advanced/" target="_blank">'.__('Google News Advanced','xml-sitemap-feed').'</a>' ); ?>
	</p>
<?php endif; ?>
</fieldset>
