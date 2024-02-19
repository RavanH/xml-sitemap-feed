<?php
/**
 * Additional translations
 *
 * @package XML Sitemap & Google News
 */

esc_html__( 'Advanced options', 'xml-sitemap-feed' );

sprintf( /* translators: Plugin name (linked to plugin installation modal) */ esc_html__( 'To use the Google News Advanced options, please install and activate %s.', 'xml-sitemap-feed' ), '<a href="' . esc_url( network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=xml-sitemap-feed&TB_iframe=true&width=600&height=550' ) ) . '" target="_blank" class="thickbox"><strong>' . esc_html__( 'XML Sitemap & Google News', 'xml-sitemap-feed' ) . '</strong></a>' );

sprintf( /* translators: Google News Sitemap option (linked to admin Reading page) */ esc_html__( 'To use the Google News Advanced options, please activate %s.', 'xml-sitemap-feed' ), '<a href="' . esc_url( admin_url( 'options-reading.php' ) ) . '#xmlsf_sitemaps">' . esc_html__( 'Google News Sitemap', 'xml-sitemap-feed' ) . '</a>' );

sprintf( /* Translators: Plugin name, Premium account (linked) */
	esc_html__( 'You can (de)activate your %1$s license from here or manage domains from your %2$s.', 'xml-sitemap-feed' ),
	esc_html__( 'Google News Advanced', 'xml-sitemap-feed' ),
	'<a href="' . esc_url( $account_url ) . '" target="_blank">' . esc_html__( 'Status301 Premium account', 'xml-sitemap-feed' ) . '</a>'
);

esc_html__( 'License action', 'xml-sitemap-feed' );
esc_html__( 'Deactivate license for this site', 'xml-sitemap-feed' );
esc_html__( 'Activate license for this site', 'xml-sitemap-feed' );
esc_html__( 'Check license key', 'xml-sitemap-feed' );

sprintf( /* Translators: Plugin name */
	esc_html__( 'Get updates for pre-release versions of %s.', 'xml-sitemap-feed' ),
	esc_html__( 'Google News Advanced', 'xml-sitemap-feed' )
);

sprintf( /* Translators: Premium account page (linked), Upload Plugin admin page (linked) */
	esc_html__( 'Please note: Auto-updates are disabled for beta versions. Disabling this option will not automatically revert the plugin to the latest stable release. To downgrade manually, first download the latest stable release from your %1$s and then install it via %2$s.', 'xml-sitemap-feed' ),
	'<a href="' . esc_url( $account_url ) . '" target="_blank">' . esc_html__( 'Status301 Premium account', 'xml-sitemap-feed' ) . '</a>',
	'<a href="' . esc_url( admin_url( 'plugin-install.php' ) ) . '?tab=upload">' . esc_html( translate( 'Upload Plugin' ) ) . '</a>'
);

esc_html__( 'License key', 'xml-sitemap-feed' );
esc_html__( 'Enter your license key.', 'xml-sitemap-feed' );
esc_html__( 'Your license is active for this site.', 'xml-sitemap-feed' );
sprintf( /* translators: Expiration date */ esc_html__( 'It expires on %s.', 'xml-sitemap-feed' ), esc_html( $expires ) );
printf( /* Translators: Plugin name */
	esc_html__( 'To receive updates for %s, please activate your license for this site.', 'xml-sitemap-feed' ),
	esc_html__( 'Google News Advanced', 'xml-sitemap-feed' )
);
printf( /* Translators: Plugin name, renew your license (linked) */
	esc_html__( 'To continue receiving updates for %1$s, please %2$s.', 'xml-sitemap-feed' ),
	esc_html__( 'Google News Advanced', 'xml-sitemap-feed' ),
	'<a href="' . esc_url( trailingslashit( XMLSF_NEWS_ADV_STORE_URL ) . 'checkout/?edd_license_key=' . $key . '&download_id=' . XMLSF_NEWS_ADV_ITEM_ID ) . '" target="_blank">' . esc_html__( 'renew your license', 'xml-sitemap-feed' ) . '</a>'
);
printf( /* Translators: Account (linked), Plugin name */
	esc_html__( 'Please check your %1$s for possibilities to upgrade your %2$s license.', 'xml-sitemap-feed' ),
	'<a href="' . esc_url( $account_url ) . '" target="_blank">' . esc_html__( 'Status301 Premium account', 'xml-sitemap-feed' ) . '</a>',
	esc_html__( 'Google News Advanced', 'xml-sitemap-feed' )
);

sprintf( /* Translators: Expiration date */ __( 'Your license key has expired on %s.', 'xml-sitemap-feed' ), date_i18n( get_option( 'date_format' ), strtotime( $expires, time() + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) ) );
__( 'Your license key has expired.', 'xml-sitemap-feed' );
__( 'Your license key has been disabled.', 'xml-sitemap-feed' );
__( 'This appears to be an invalid license key.', 'xml-sitemap-feed' );
__( 'Your license is not active for this site.', 'xml-sitemap-feed' );
sprintf( /* Translators: Plugin name */ __( 'This appears to be an invalid license key for %s.', 'xml-sitemap-feed' ), __( 'Google News Advanced', 'xml-sitemap-feed' ) );
sprintf( /* Translators: Plugin name */ __( 'This appears to be an invalid license key for %s.', 'xml-sitemap-feed' ), __( 'Google News Advanced', 'xml-sitemap-feed' ) );
__( 'Your license key has reached its activation limit.', 'xml-sitemap-feed' );

__( 'An error occurred, please try again.', 'xml-sitemap-feed' );
__( 'Your license was successfully activated for this site.', 'xml-sitemap-feed' );
__( 'Your license was successfully deactivated for this site.', 'xml-sitemap-feed' );
__( 'Your license is active for this site.', 'xml-sitemap-feed' );
__( 'Your license is not active for this site.', 'xml-sitemap-feed' );

esc_html__( 'An active license key grants you access to plugin updates and support. If a license key is absent, deactivated or expired, the plugin may continue to work properly but you will not receive automatic updates.', 'xml-sitemap-feed' );

sprintf( /* Translators: Plugin name, Status301 Premium account (linked) */
	esc_html__( 'You can find your %1$s license key in your %2$s.', 'xml-sitemap-feed' ),
	esc_html__( 'Google News Advanced', 'xml-sitemap-feed' ),
	'<a href="https://premium.status301.com/account/" target="_blank">' . esc_html__( 'Status301 Premium account', 'xml-sitemap-feed' ) . '</a>'
);

sprintf( /* Translators: Google News Support Forum (linked) */
	esc_html__( 'You can get Priority Support on the %1$s.', 'xml-sitemap-feed' ),
	'<a href="https://premium.status301.com/support-forums/forum/xml-sitemap-google-news/google-news/" target="_blank">' . esc_html__( 'Google News support forum', 'xml-sitemap-feed' ) . '</a>'
);

printf( /* Translators: Plugin name */ esc_html__( 'You have an invalid or expired license key for %s.', 'xml-sitemap-feed' ), esc_html__( 'Google News Advanced', 'xml-sitemap-feed' ) );
printf( /* Translators: Plugin name */ esc_html__( 'You have not yet entered your license key for %s.', 'xml-sitemap-feed' ), esc_html__( 'Google News Advanced', 'xml-sitemap-feed' ) );
printf( /* Translators: Plugin name */ esc_html__( 'Your license key for %s is not activated for this site.', 'xml-sitemap-feed' ), esc_html__( 'Google News Advanced', 'xml-sitemap-feed' ) );

printf( /* Translators: Plugin name, correct this issue (linked) */
	esc_html__( 'To receive plugin updates, please %s.', 'xml-sitemap-feed' ),
	'<a href="' . esc_url( admin_url( 'options-general.php' ) ) . '?page=xmlsf_news&tab=license">' . esc_html__( 'correct this issue', 'xml-sitemap-feed' ) . '</a>'
);

