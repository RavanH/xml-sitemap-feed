<?php
/**
 * Sitemap general settings limit view
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset id="xmlsf_general_settings_limit">
	<legend class="screen-reader-text">
		<?php esc_html_e( 'Limit', 'xml-sitemap-feed' ); ?>
	</legend>

	<p>
		<label>
			<?php esc_html_e( 'Maximum posts per sitemap', 'xml-sitemap-feed' ); ?>
			<input type="number" step="100" min="0" max="50000" name="xmlsf_general_settings[limit]" id="xmlsf_sitemap_settings_limit" value="<?php echo esc_attr( $limit ); ?>" class="medium-text" />
		</label>
	</p>

</fieldset>
