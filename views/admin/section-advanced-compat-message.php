<?php
/**
 * Advanced compatibility message
 *
 * @package XML Sitemap & Google News
 */

?>
<p>
	<?php esc_html_e( 'Your current version of XML Sitemap Advanced is outdated. Some advanced options may not be functional.', 'xml-sitemap-feed' ); ?>
	<?php
	if ( current_user_can( 'update_plugins' ) ) {
		?>
	<a href="https://premium.status301.com/downloads/xml-sitemap-advanced/" target="_blank">
		<?php printf( /* Translators: Advanced plugin version number */ __( 'Please download and install version %s or later.', 'xml-sitemap-feed' ), XMLSF_ADV_MIN_VERSION ); ?>
	</a>
		<?php
	} else {
		?>
	<?php esc_html_e( 'Please contact your site administrator to install the update.', 'xml-sitemap-feed' ); ?>
		<?php
	}
	?>
</p>
