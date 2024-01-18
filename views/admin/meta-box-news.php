<?php
/**
 * Meta box: News
 *
 * @package XML Sitemap & Google News
 */

?>
<p>
	<label>
		<input type="checkbox" name="xmlsf_news_exclude" id="xmlsf_news_exclude" value="1"<?php checked( ! empty( $exclude ) ); ?><?php disabled( $disabled ); ?> />
		<?php esc_html_e( 'Exclude from Google News Sitemap', 'xml-sitemap-feed' ); ?>
	</label>
</p>
