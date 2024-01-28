<?php
/**
 * Sitemap general settings server view
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset id="xmlsf_sitemap_general_settings_disable">
	<legend class="screen-reader-text">
		<?php echo esc_html( translate( 'Deactivate' ) ); ?>
	</legend>

	<p>
		<label>
			<input type="checkbox" name="xmlsf_general_settings[disabled][]" id="xmlsf_taxonomies_disable" value="taxonomies"<?php checked( in_array( 'taxonomies', $disabled, true ) ); ?> />
			<?php esc_html_e( 'Taxonomies', 'xml-sitemap-feed' ); ?> (<?php echo count( $public_tax ); ?>)
		</label>
	</p>

	<p>
		<label>
			<input type="checkbox" name="xmlsf_general_settings[disabled][]" id="xmlsf_authors_disable" value="authors"<?php checked( in_array( 'authors', $disabled, true ) ); ?> />
			<?php esc_html_e( 'Authors', 'xml-sitemap-feed' ); ?> (<?php echo count( get_users( $users_args ) ); ?>)
		</label>
	</p>

</fieldset>
