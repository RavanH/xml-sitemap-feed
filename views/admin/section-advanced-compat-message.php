<p class="<?php echo esc_attr( $class ); ?>">
	<?php esc_html_e( 'Your current version of Google News Advanced is outdated. Some advanced options may not be functional.', 'xml-sitemap-feed' ); ?>
	<?php
	printf(
		/* Translators: URL https://premium.status301.com/account/ */
		esc_html__( 'Please <a href="%1$s" target="_blank">download and install the latest version</a>.', 'xml-sitemap-feed' ),
		'https://premium.status301.com/account/'
	);
	?>
</p>
