<fieldset>
    <legend class="screen-reader-text"><?php _e('External web pages','xml-sitemap-feed'); ?></legend>

    <label for="xmlsf_urls"><?php _e('Additional web pages to append in an extra XML Sitemap:','xml-sitemap-feed'); ?></label>
	<br/>
    <textarea name="xmlsf_urls" id="xmlsf_urls" class="large-text" cols="50" rows="4"><?php echo implode("\n",$lines); ?></textarea>
</fieldset>
