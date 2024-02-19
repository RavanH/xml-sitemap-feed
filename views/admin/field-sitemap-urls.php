<?php
/**
 * URLs field
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset>
	<legend class="screen-reader-text"><?php esc_html_e( 'External web pages', 'xml-sitemap-feed' ); ?></legend>

	<label for="xmlsf_urls"><?php esc_html_e( 'Additional web pages to append in an extra XML Sitemap:', 'xml-sitemap-feed' ); ?></label>
	<br/>
	<textarea name="xmlsf_urls" id="xmlsf_urls" class="large-text" cols="50" rows="4"><?php echo esc_textarea( implode( "\n", $lines ) ); ?></textarea>
</fieldset>
