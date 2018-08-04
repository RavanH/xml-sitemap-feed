<fieldset>
    <legend class="screen-reader-text"><?php _e('Allowed domains','xml-sitemap-feed'); ?></legend>
    <label>
        <?php _e('Additional domains to allow in the XML Sitemaps:','xml-sitemap-feed'); ?>
        <br>
        <textarea name="<?php echo $this->prefix; ?>domains" id="xmlsf_domains" class="large-text" cols="50" rows="4"><?php echo implode("\n",$domains); ?></textarea>
    </label>
    <p class="description">
        <?php printf(__('By default, only the domain %s as used in your WordPress site address is allowed.','xml-sitemap-feed'),'<strong>'.$default.'</strong>'); ?> 
        <a href="#xmlsf_domains_note_1_more" id="xmlsf_domains_note_1_link">
            <?php echo translate('Read more...'); ?>
        </a>
    </p>
    <p id="xmlsf_domains_note_1_more" class="description">
        <?php _e('This means that all URLs that use another domain (custom URLs or using a plugin like Page Links To) are filtered from the XML Sitemap. However, if you are the verified owner of other domains in your Google/Bing Webmaster Tools account, you can include these in the same sitemap. Add these domains, without protocol (http/https) each on a new line. Note that if you enter a domain with www, all URLs without it or with other subdomains will be filtered.','xml-sitemap-feed'); ?>
    </p>
    <script type="text/javascript">
    jQuery( document ).ready( function() {
        jQuery("#xmlsf_domains_note_1_more").hide();
        jQuery("#xmlsf_domains_note_1_link").click( function(event) {
        event.preventDefault();
        jQuery("#xmlsf_domains_note_1_link").hide();
        jQuery("#xmlsf_domains_note_1_more").show("fast");
        });
    });
    </script>
</fieldset>