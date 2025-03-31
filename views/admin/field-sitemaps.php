<?php
/**
 * Sitemaps settings view
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset id="xmlsf_sitemaps">
	<legend class="screen-reader-text">
		<?php esc_html_e( 'Enable XML sitemaps', 'xml-sitemap-feed' ); ?>
	</legend>
	<label>
		<input type="checkbox" name="xmlsf_sitemaps[sitemap]" id="xmlsf_sitemaps_index" value="1"<?php checked( XMLSF\sitemaps_enabled( 'sitemap' ) ); ?><?php disabled( ! apply_filters( 'xmlsf_sitemaps_enabled', true, 'sitemap' ) ); ?> />
		<?php esc_html_e( 'XML Sitemap Index', 'xml-sitemap-feed' ); ?>
	</label>

	<?php if ( XMLSF\sitemaps_enabled( 'sitemap' ) ) { ?>
	<span class="description">
		&nbsp;&ndash;&nbsp;
		<a href="<?php echo esc_attr( admin_url( 'options-general.php' ) ); ?>?page=xmlsf" id="xmlsf_link"><?php echo esc_html( translate( 'Settings' ) ); ?></a> |
		<a href="<?php echo esc_attr( xmlsf()->sitemap->get_sitemap_url() ); ?>" target="_blank"><?php echo esc_html( translate( 'View' ) ); ?></a>
	</span>
	<?php } ?>

	<br>

	<label>
		<input type="checkbox" name="xmlsf_sitemaps[sitemap-news]" id="xmlsf_sitemaps_news" value="1"<?php checked( XMLSF\sitemaps_enabled( 'news' ) ); ?><?php disabled( ! apply_filters( 'xmlsf_sitemaps_enabled', true, 'news' ) ); ?> />
		<?php esc_html_e( 'Google News Sitemap', 'xml-sitemap-feed' ); ?>
	</label>

	<?php
	if ( XMLSF\sitemaps_enabled( 'news' ) ) {
		?>
	<span class="description">
		&nbsp;&ndash;&nbsp;
		<a href="<?php echo esc_url( admin_url( 'options-general.php' ) ); ?>?page=xmlsf_news" id="xmlsf_news_link"><?php echo esc_html( translate( 'Settings' ) ); ?></a> |
		<a href="<?php echo esc_url( xmlsf()->sitemap_news->get_sitemap_url() ); ?>" target="_blank"><?php echo esc_html( translate( 'View' ) ); ?></a>
	</span>
	<?php } ?>

</fieldset>
<script>
if ( window.location.hash === '#xmlsf_sitemaps' ) {
	jQuery( 'html, body' ).animate( { scrollTop: jQuery("#xmlsf_sitemaps").offset().top-40 }, 400, 'swing', function(){ jQuery( '#xmlsf_sitemaps' ).closest( 'td' ).addClass( 'highlight' ); } );
}
</script>
