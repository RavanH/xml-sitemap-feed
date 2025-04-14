<?php
/**
 * Sitemap slug field
 *
 * @package XML Sitemap & Google News
 */

$using_permalinks = xmlsf()->using_permalinks();
?>
<fieldset>
	<legend class="screen-reader-text"><?php esc_html_e( 'XML Sitemap URL', 'xml-sitemap-feed' ); ?></legend>

	<?php echo esc_url( trailingslashit( get_home_url() ) ); ?><input type="text" name="xmlsf_sitemap_name" id="xmlsf_sitemap_name" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="<?php echo esc_attr( $slug ); ?>" <?php disabled( ! apply_filters( 'xmlsf_advanced_enabled', false ) || ! $using_permalinks, true ); ?>>.xml
	<p class="description" id="xmlsf-sitemap-name-description">
		<?php esc_html_e( 'Set an alternative name for the sitemap index.', 'xml-sitemap-feed' ); ?>
		<?php if ( ! $using_permalinks ) { ?>
			<em>
			<?php printf( /* Translators: Permalinks */ esc_html__( 'Not available because of this site\'s %s settings.', 'xml-sitemap-feed' ), '<a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '">' . esc_html( translate( 'Permalinks' ) ) . '</a>' ); ?>
			</em>
		<?php } else { ?>
			<?php apply_filters( 'xmlsf_advanced_enabled', false ) || printf( /* Translators: XML Sitemap Advanced */ esc_html__( 'Available in %s.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/xml-sitemap-advanced/" target="_blank">' . esc_html__( 'XML Sitemap Advanced', 'xml-sitemap-feed' ) . '</a>' ); ?>
		<?php } ?>
	</p>
</fieldset>
