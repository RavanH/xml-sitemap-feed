<?php
/**
 * Sitemap external sitemaps settings view
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset>
	<legend class="screen-reader-text"><?php esc_html_e( 'External XML Sitemaps', 'xml-sitemap-feed' ); ?></legend>

	<label for="xmlsf_custom_sitemaps"><?php esc_html_e( 'Additional XML Sitemaps to append to the main XML Sitemap Index:', 'xml-sitemap-feed' ); ?></label>
	<br/>
	<textarea name="xmlsf_custom_sitemaps" id="xmlsf_custom_sitemaps" class="large-text" cols="50" rows="4"><?php echo esc_textarea( $lines ); ?></textarea>
</fieldset>
