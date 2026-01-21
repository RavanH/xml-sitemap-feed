<?php
/**
 * Sitemap author setting view
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset id="xmlsf_author_settings">
	<legend class="screen-reader-text">
		<?php echo esc_html( translate( 'General' ) );  // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction ?>
	</legend>
	<p>
		<label>
			<input type="checkbox" name="xmlsf_author_settings[include_empty]" id="xmlsf_author_include_empty" value="1"<?php checked( ! empty( $author_settings['include_empty'] ), true ); ?><?php disabled( apply_filters( 'xmlsf_advanced_enabled', false ), false ); ?> />
			<?php esc_html_e( 'Include empty author archives.', 'xml-sitemap-feed' ); ?>
			<?php apply_filters( 'xmlsf_advanced_enabled', false ) || printf( /* Translators: %s: XML Sitemap Advanced (with link) */ esc_html__( 'Available in %s.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/xml-sitemap-advanced/" target="_blank">' . esc_html__( 'XML Sitemap Advanced', 'xml-sitemap-feed' ) . '</a>' ); ?>
		</label>
	</p>
	<p>
		<label>
			<?php esc_html_e( 'Maximum authors per sitemap', 'xml-sitemap-feed' ); ?>
			<input type="number" step="100" min="0" max="50000" placeholder="2000" name="xmlsf_author_settings[limit]" id="xmlsf_author_limit" value="<?php echo ( isset( $author_settings['limit'] ) ? esc_attr( $author_settings['limit'] ) : '' ); ?>" class="medium-text" />
		</label>
	</p>
</fieldset>
