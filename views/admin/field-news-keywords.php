<?php
/**
 * Hierarchical post types field
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset>
	<legend class="screen-reader-text">
		<?php esc_html_e( 'Keywords', 'xml-sitemap-feed' ); ?>
	</legend>
	<label><?php esc_html_e( 'Use keywords from', 'xml-sitemap-feed' ); ?>
		<select name="" disabled="disabled">
			<option value=""><?php esc_html_e( 'None' ); ?></option>
		</select>
	</label>
	<p class="description">
		<?php printf( /* Translators: Sitemap tag name, Advanced plugin name */ esc_html__( '%1$s are provided by the %2$s module.', 'xml-sitemap-feed' ), esc_html__( 'Keywords', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/google-news-advanced/" target="_blank">' . esc_html__( 'Google News Advanced', 'xml-sitemap-feed' ) . '</a>' ); ?>
	</p>
</fieldset>
