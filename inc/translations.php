<?php
/**
 * Additional translations
 *
 * @package XML Sitemap & Google News
 */

defined( 'WPINC' ) || die;

esc_html__( 'Advanced options', 'xml-sitemap-feed' );
__( 'XML Sitemap Advanced', 'xml-sitemap-feed' );
__( 'Google News Advanced', 'xml-sitemap-feed' );


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
__( 'Beta version', 'xml-sitemap-feed' );
esc_html__( 'This option will allow you to update the plugin to the latest beta release.', 'xml-sitemap-feed' );
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
	'<a href="' . esc_url( $url ) . 'checkout/?edd_license_key=' . $key . '&download_id=' . $_id . '" target="_blank">' . esc_html__( 'renew your license', 'xml-sitemap-feed' ) . '</a>'
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

esc_html_e( 'No log entries found.', 'xml-sitemap-feed' );
esc_html_e( 'The 20 most recent sitemap notification request and related messages are logged here.', 'xml-sitemap-feed' );
esc_html_e( 'Message', 'xml-sitemap-feed' );
__( 'Sitemap Notification Log', 'xml-sitemap-feed' );
esc_html_e( 'Warning: The sitemap notifier depends on internal WordPress events but you seem to have WP Cron disabled. Make sure that you are using a reliable alternative to WP Cron, like a server cron job, to trigger events and that this is done on fairly short interval, e.g. once every minute. If the interval is longer, automatic notifications will suffer longer delays.', 'xml-sitemap-feed' );
printf(
	/* translators: %1$s: Support ticket (linked to https://premium.status301.com/account/support/), %2$s: Contact us (linked to https://premium.status301.com/email-support/) */
	esc_html__( 'As valued pro plugin user, you are eligible to priority support. For any questions, you can open a %1$s or %2$s.', 'xml-sitemap-feed' ),
	'<a href="https://premium.status301.com/account/support/" target="_blank">' . esc_html__( 'Support ticket', 'xml-sitemap-feed' ) . '</a>',
	'<a href="https://premium.status301.com/email-support/" target="_blank">' . esc_html__( 'Contact us', 'xml-sitemap-feed' ) . '</a>'
);
printf(
	/* translators: %1$s: Sitemap notifier, %2$s: Google Search Console */
	esc_html__( '%1$s needs a connection to %2$s for automatic sitemap notifications. Please use the %3$s button and follow the instructions.', 'xml-sitemap-feed' ),
	'<strong>' . esc_html__( 'Sitemap notifier', 'xml-sitemap-feed' ) . '</strong>',
	'<strong>' . esc_html__( 'Google Search Console', 'xml-sitemap-feed' ) . '</strong>',
	'<strong>' . esc_html__( 'Connect', 'xml-sitemap-feed' ) . '</strong>'
);