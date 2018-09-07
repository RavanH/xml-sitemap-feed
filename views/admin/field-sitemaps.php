<fieldset id="xmlsf_sitemaps">
    <legend class="screen-reader-text">
        <?php _e('Enable XML sitemaps','xml-sitemap-feed'); ?>
    </legend>
	<label>
        <input type="checkbox" name="xmlsf_sitemaps[sitemap]" id="xmlsf_sitemaps_index" value="sitemap.xml"<?php echo checked(isset($options['sitemap']), true, false); ?> />
        <?php _e('XML Sitemap Index','xml-sitemap-feed'); ?>
    </label>

    <?php if ( isset($options['sitemap']) ) {
        $sitemap_url = trailingslashit(get_bloginfo('url')) . ( xmlsf()->plain_permalinks() ? '?feed=sitemap' : $options['sitemap'] );
    ?>
    <span class="description">
        &nbsp;&ndash;&nbsp;
        <a href="<?php echo admin_url('options-general.php'); ?>?page=xmlsf" id="xmlsf_link"><?php echo translate('Settings'); ?></a> |
        <a href="<?php echo $sitemap_url; ?>" target="_blank"><?php echo translate('View'); ?></a>
    </span>
    <?php } ?>

    <br>

	<label>
        <input type="checkbox" name="xmlsf_sitemaps[sitemap-news]" id="xmlsf_sitemaps_news" value="sitemap-news.xml"<?php echo checked(isset($options['sitemap-news']), true, false); ?> />
        <?php _e('Google News Sitemap','xml-sitemap-feed'); ?>
    </label>

    <?php if (isset($options['sitemap-news'])) {
        $news_url = trailingslashit(get_bloginfo('url')) . ( xmlsf()->plain_permalinks() ? '?feed=sitemap-news' : $options['sitemap-news'] );
    ?>
	<span class="description">
        &nbsp;&ndash;&nbsp;
        <a href="<?php echo admin_url('options-general.php'); ?>?page=xmlsf-news" id="xmlsf_news_link"><?php echo translate('Settings'); ?></a> |
        <a href="<?php echo $news_url; ?>" target="_blank"><?php echo translate('View'); ?></a>
    </span>
    <?php } ?>

</fieldset>
