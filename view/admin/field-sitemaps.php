<fieldset id="xmlsf_sitemaps">
    <legend class="screen-reader-text">
        <?php _e('XML Sitemaps','xml-sitemap-feed'); ?>
    </legend>
	<label>
        <input type="checkbox" name="xmlsf_sitemaps[sitemap]" id="xmlsf_sitemaps_index" value="sitemap.xml"<?php echo checked(isset($options['sitemap']), true, false); ?> /> 
        <?php _e('XML Sitemap Index','xml-sitemap-feed'); ?>
    </label>

    <?php if (isset($options['sitemap'])) { 
        $sitemap_url = trailingslashit(get_bloginfo('url')) . ( $this->plain_permalinks() ? '?feed=sitemap' : $options['sitemap'] );
    ?>
    <span class="description"> 
        &nbsp;&ndash;&nbsp; 
        <a href="#xmlsf" id="xmlsf_link"><?php echo translate('Settings'); ?></a> | 
        <a href="<?php echo $sitemap_url; ?>" target="_blank"><?php echo translate('View'); ?></a>
    </span>
    <?php } ?>
 
    <br>

	<label>
        <input type="checkbox" name="xmlsf_sitemaps[sitemap-news]" id="xmlsf_sitemaps_news" value="sitemap-news.xml"<?php echo checked(isset($options['sitemap-news']), true, false); ?> /> 
        <?php _e('Google News Sitemap','xml-sitemap-feed'); ?>
    </label>

    <?php if (isset($options['sitemap-news'])) { 
        $news_url = trailingslashit(get_bloginfo('url')) . ( $this->plain_permalinks() ? '?feed=sitemap-news' : $options['sitemap-news'] );
    ?>
	<span class="description"> 
        &nbsp;&ndash;&nbsp; 
        <a href="#xmlnf" id="xmlnf_link"><?php echo translate('Settings'); ?></a> | 
        <a href="<?php echo $news_url; ?>" target="_blank"><?php echo translate('View'); ?></a>
    </span>
    <?php } ?>

    <script type="text/javascript">
    jQuery( document ).ready( function() {
        jQuery( "#xmlsf_link" ).click( function(event) {
                event.preventDefault();
                jQuery("html, body").animate({
                scrollTop: jQuery("a[name=\'xmlsf\']").offset().top - 30
            }, 1000);
        });
        jQuery( "#xmlnf_link" ).click( function(event) {
                event.preventDefault();
                jQuery("html, body").animate({
            scrollTop: jQuery("a[name=\'xmlnf\']").offset().top - 30
            }, 1000);
        });
    });
    </script>
</fieldset>