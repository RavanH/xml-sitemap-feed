<fieldset>
    <legend class="screen-reader-text"><?php _e('Include custom XML Sitemaps','xml-sitemap-feed'); ?></legend>
    <label>
        <?php _e('Additional XML Sitemaps to append to the main XML Sitemap Index:','xml-sitemap-feed'); ?>
        <br>
        <textarea name="<?php echo $this->prefix; ?>custom_sitemaps" id="xmlsf_custom_sitemaps" class="large-text" cols="50" rows="4"><?php echo implode("\n",$lines); ?></textarea>
    </label>
    <p class="description">
        <?php _e('Add the full URL, including protocol (http/https) and domain.','xml-sitemap-feed'); ?> 
        <?php _e('Start each URL on a new line.','xml-sitemap-feed'); ?>
        <br>
        <span style="color: red" class="warning">
            <?php _e('Only valid sitemaps are allowed in the Sitemap Index. Use your Google/Bing Webmaster Tools to verify!','xml-sitemap-feed'); ?>
        </span>
    </p>
</fieldset>