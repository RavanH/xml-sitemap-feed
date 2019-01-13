<fieldset>
    <legend class="screen-reader-text"><?php _e( 'Publication name', 'xml-sitemap-feed' ); ?></legend>
    <input type="text" name="xmlsf_news_tags[name]" id="xmlsf_news_name" value="<?php echo $name; ?>" class="regular-text">
	<p class="description">
		<?php printf( /* translators: Site Title linked to Options > General */ __( 'By default, the general %s setting will be used.', 'xml-sitemap-feed' ), '<a href="options-general.php">'.translate('Site Title').'</a>' ); ?>
	</p>
</fieldset>
