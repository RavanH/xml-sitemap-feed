<?php
/**
 * Sitemap name field
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset>
	<legend class="screen-reader-text"><?php esc_html_e( 'XML Sitemap URL', 'xml-sitemap-feed' ); ?></legend>

	<?php echo esc_url( trailingslashit( get_home_url() ) ); ?><input type="text" name="xmlsf_sitemap_name" id="xmlsf_sitemap_name" placeholder="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $name ); ?>" <?php disabled( apply_filters( 'xmlsf_advanced_enabled', false ), false ); ?>>
	<p class="description" id="xmlsf-sitemap-name-description">
		<?php printf( /* Translators: default sitemap.xml file name */ esc_html__( 'Set an alternative name for the sitemap index. Leave empty to use the default: %s', 'xml-sitemap-feed' ), '<code>' . esc_html( apply_filters( 'xmlsf_sitemap_filename', $name ) ) . '</code>' ); ?>
		<?php apply_filters( 'xmlsf_advanced_enabled', false ) || printf( /* Translators: XML Sitemap Advanced */ esc_html__( 'Available in %s.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/xml-sitemap-advanced/" target="_blank">' . esc_html__( 'XML Sitemap Advanced', 'xml-sitemap-feed' ) . '</a>' ); ?>
	</p>
</fieldset>
