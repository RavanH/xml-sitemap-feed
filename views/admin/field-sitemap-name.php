<?php
/**
 * Sitemap name field
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset>
	<legend class="screen-reader-text"><?php esc_html_e( 'XML Sitemap URL', 'xml-sitemap-feed' ); ?></legend>

	<?php echo esc_url( trailingslashit( get_home_url() ) ); ?><input type="text" name="xmlsf_sitemap_name" id="xmlsf_sitemap_name" placeholder="sitemap" value="<?php echo esc_attr( $slug ); ?>" <?php disabled( apply_filters( 'xmlsf_advanced_enabled', false ), false ); ?>>.xml
	<p class="description" id="xmlsf-sitemap-name-description">
		<?php esc_html__( 'Set an alternative name for the sitemap index.', 'xml-sitemap-feed' ); ?>
		<?php apply_filters( 'xmlsf_advanced_enabled', false ) || printf( /* Translators: XML Sitemap Advanced */ esc_html__( 'Available in %s.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/xml-sitemap-advanced/" target="_blank">' . esc_html__( 'XML Sitemap Advanced', 'xml-sitemap-feed' ) . '</a>' ); ?>
	</p>
</fieldset>
