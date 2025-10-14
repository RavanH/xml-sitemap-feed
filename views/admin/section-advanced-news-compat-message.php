<?php
/**
 * Advanced compatibility message
 *
 * @package XML Sitemap & Google News
 */

?>
<p>
	<?php esc_html_e( 'Your current version of Google News Advanced is outdated. Some advanced options may not be functional.', 'xml-sitemap-feed' ); ?>
	<?php
	if ( current_user_can( 'update_plugins' ) ) {
		?>
	<a href="https://premium.status301.com/account/downloads/" target="_blank">
		<?php esc_html_e( 'Please download and install the latest version.', 'xml-sitemap-feed' ); ?>
	</a>
		<?php
	} else {
		?>
	<?php esc_html_e( 'Please contact your site administrator to install the update.', 'xml-sitemap-feed' ); ?>
		<?php
	}
	?>
</p>
