<?php
/**
 * Sitemap domains settings view
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset>
	<legend class="screen-reader-text"><?php esc_html_e( 'Allowed domains', 'xml-sitemap-feed' ); ?></legend>

	<label for="xmlsf_domains"><?php esc_html_e( 'Additional domains to allow in the XML Sitemaps:', 'xml-sitemap-feed' ); ?></label>
	<br/>
	<textarea name="xmlsf_domains" id="xmlsf_domains" class="large-text" cols="50" rows="4"><?php echo esc_textarea( implode( "\n", $domains ) ); ?></textarea>
</fieldset>
