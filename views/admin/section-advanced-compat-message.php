<p<?php echo ! empty( $class ) ? ' class="'.$class.'' : '' ?>">
	<?php _e ( 'Your current version of Google News Advanced is outdated. Some advanced options may not be functional.', 'xml-sitemap-feed' ); ?>
	<?php printf (
		__( 'Please <a href="%1$s" target="_blank">download and install the latest version</a>.', 'xml-sitemap-feed' ),
		'https://premium.status301.com/account/'
	); ?>
</p>
