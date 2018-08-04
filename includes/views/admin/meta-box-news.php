<p>
    <label>
        <input type="checkbox" name="xmlsf_news_exclude" id="xmlsf_news_exclude" value="1"<?php echo checked( !empty($exclude ), true, false) . disabled( $disabled, true, false); ?> />
		<?php _e('Exclude from Google News Sitemap','xml-sitemap-feed'); ?>
    </label>
</p>

