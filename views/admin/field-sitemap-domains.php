<fieldset>
    <legend class="screen-reader-text"><?php _e('Allowed domains','xml-sitemap-feed'); ?></legend>

    <label for="xmlsf_domains"><?php _e('Additional domains to allow in the XML Sitemaps:','xml-sitemap-feed'); ?></label>
	<br/>
    <textarea name="xmlsf_domains" id="xmlsf_domains" class="large-text" cols="50" rows="4"><?php echo implode("\n",$domains); ?></textarea>
</fieldset>
