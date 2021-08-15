<fieldset id="xmlsf_sitemaps">
	<legend class="screen-reader-text">
		<?php _e('Enable XML sitemaps','xml-sitemap-feed'); ?>
	</legend>
	<label>
		<input type="checkbox" name="xmlsf_sitemaps[sitemap]" id="xmlsf_sitemaps_index" value="sitemap.xml"<?php echo checked(isset($this->sitemaps['sitemap']), true, false); ?> />
		<?php _e('XML Sitemap Index','xml-sitemap-feed'); ?>
	</label>

	<?php if ( isset($this->sitemaps['sitemap']) ) {
		$sitemap_url = trailingslashit(get_bloginfo('url')) . ( xmlsf()->plain_permalinks() ? '?feed=sitemap' : $this->sitemaps['sitemap'] );
	?>
	<span class="description">
		&nbsp;&ndash;&nbsp;
		<a href="<?php echo admin_url('options-general.php'); ?>?page=xmlsf" id="xmlsf_link"><?php echo translate('Settings'); ?></a> |
		<a href="<?php echo $sitemap_url; ?>" target="_blank"><?php echo translate('View'); ?></a>
	</span>
	<?php } ?>

	<br>

	<label>
		<input type="checkbox" name="xmlsf_sitemaps[sitemap-news]" id="xmlsf_sitemaps_news" value="sitemap-news.xml"<?php echo checked(isset($this->sitemaps['sitemap-news']), true, false); ?> />
		<?php _e('Google News Sitemap','xml-sitemap-feed'); ?>
	</label>

	<?php if (isset($this->sitemaps['sitemap-news'])) {
		$news_url = trailingslashit(get_bloginfo('url')) . ( xmlsf()->plain_permalinks() ? '?feed=sitemap-news' : $this->sitemaps['sitemap-news'] );
	?>
	<span class="description">
		&nbsp;&ndash;&nbsp;
		<a href="<?php echo admin_url('options-general.php'); ?>?page=xmlsf_news" id="xmlsf_news_link"><?php echo translate('Settings'); ?></a> |
		<a href="<?php echo $news_url; ?>" target="_blank"><?php echo translate('View'); ?></a>
	</span>
	<?php } ?>

</fieldset>
<script>
jQuery( 'document' ).ready( function( $ ) {
	if ( window.location.hash === '#xmlsf_sitemaps' ) {
		$( '#xmlsf_sitemaps' ).closest( 'td' ).addClass( 'highlight' );
		$( 'html, body' ).animate( { scrollTop: $("#xmlsf_sitemaps").offset().top-40 }, 800 );
	}
} );
</script>
