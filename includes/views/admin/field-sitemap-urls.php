<fieldset>
    <legend class="screen-reader-text"><?php _e('Include custom web pages','xml-sitemap-feed'); ?></legend>
    <label>
        <?php _e('Additional web pages to append in an extra XML Sitemap:','xml-sitemap-feed'); ?>
        <br>
        <textarea name="<?php echo $this->prefix; ?>urls" id="xmlsf_urls" class="large-text" cols="50" rows="4"><?php echo implode("\n",$lines); ?></textarea>
    </label>
    <p class="description">
        <?php _e('Add the full URL, including protocol (http/https) and domain.','xml-sitemap-feed'); ?> 
        <?php _e('Optionally add a priority value between 0 and 1, separated with a space after the URL.','xml-sitemap-feed'); ?> 
        <?php _e('Start each URL on a new line.','xml-sitemap-feed'); ?>
        </p>
</fieldset>