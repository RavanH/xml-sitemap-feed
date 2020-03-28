<fieldset id="xmlsf_author_settings">
	<legend class="screen-reader-text">
		<?php echo translate( 'General' ); ?>
	</legend>
	<p>
		<label>
			<input type="checkbox" name="xmlsf_author_settings[active]" id="xmlsf_author_active" value="1"<?php checked( !empty( $author_settings['active'] ), true); ?> />
			<?php _e( 'Include authors', 'xml-sitemap-feed' ); ?> (<?php echo count( get_users( array( 'fields' => 'ID', 'who' => 'authors', 'has_published_posts' => true, ) ) ); ?>)
		</label>
	</p>
	<p>
		<label>
			<?php _e( 'Priority', 'xml-sitemap-feed' ); ?>
			<input type="number" step="0.1" min="0.1" max="0.9" name="xmlsf_author_settings[priority]" id="xmlsf_author_priority" value="<?php echo ( isset($author_settings['priority']) ? $author_settings['priority'] : '' ); ?>" class="small-text" />
		</label>
	</p>
	<p>
		<label>
			<?php _e( 'Maximum authors per sitemap', 'xml-sitemap-feed' ); ?>
			<input type="number" step="100" min="0" max="50000" name="xmlsf_author_settings[term_limit]" id="xmlsf_author_term_limit" value="<?php echo ( isset($author_settings['term_limit']) ? $author_settings['term_limit'] : '' ); ?>" class="medium-text" />
		</label>
	</p>
	<p class="description">
		<?php echo apply_filters (
			'xmlsf_author_settings_description',
			sprintf (
				/* Translators: XML Sitemap Advanced */
				__( 'More options available in %s.', 'xml-sitemap-feed' ),
				'<a href="https://premium.status301.com/downloads/xml-sitemap-advanced/" target="_blank">'.__('XML Sitemap Advanced','xml-sitemap-feed').'</a>'
				)
			);
		?>
	</p>
</fieldset>
