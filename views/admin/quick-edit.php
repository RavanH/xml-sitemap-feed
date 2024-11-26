<?php
/**
 * Quick Edit: Sitemap
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset class="inline-edit-col-left">
	<div class="inline-edit-col column-<?php echo esc_attr( $column_name ); ?>">
		<label class="inline-edit-group">
			<input type="checkbox" name="xmlsf_exclude" value="1" />
			<span class="checkbox-title"><?php esc_html_e( 'Exclude from XML Sitemap', 'xml-sitemap-feed' ); ?></span>
		</label>
	</div>
</fieldset>
