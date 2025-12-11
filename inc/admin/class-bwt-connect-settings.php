<?php
/**
 * Status301 Premium Google Search Console Connection Onboarding
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Admin;

use XMLSF\Secret;

/**
 * Helper class with public methods to set up a Google Search Console connection.
 *
 * @author RavanH
 * @version 5.7
 */
class BWT_Connect_Settings extends BWT_Connect {

	/**
	 * Placeholder for the saved password.
	 * This is used to avoid showing the actual password in the settings page.
	 * If this value is present, it means the password is saved and should not be changed.
	 *
	 * @var string
	 */
	public static $pw_placeholder = 'DONT_BOTHER_CLIENT_SECRET_ENCRYPTED';

	/**
	 * Display the plugin options page HTML.
	 */
	public static function options_page_render() {
		// Check user capabilities.
		if ( ! \current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo \esc_html( get_admin_page_title() ); ?></h1>

			<form action="options.php" method="post">
				<?php
				// Output security fields for the registered setting section.
				\settings_fields( self::$option_group );

				// Output setting sections and fields.
				\do_settings_sections( self::$page_slug );

				// Output save settings button.
				\submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Callback function for the Google Search Console OAuth Settings section header.
	 */
	public static function oauth_section_callback() {
		// Set referrer transient to redirect to after connection.
		isset( $_GET['ref'] ) && in_array( $_GET['ref'], array( 'xmlsf', 'xmlsf_news' ), true ) && \set_transient( 'gsc_connect_origin', \sanitize_key( $_GET['ref'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Prepare the option if it does not already exist. Sets it as non-autoloaded option.
		\add_option( self::$option_group, '', '', false );

		// Get existing data from DB.
		$options = (array) \get_option( self::$option_group, array() );

		\settings_errors();

		// Intro.
		include XMLSF_DIR . '/views/admin/section-bwt-oauth-intro.php';

		// Check if the Google Client ID and Secret are set.
		if ( empty( $options['bing_client_id'] ) || empty( $options['bing_client_secret'] ) ) {
			// Stage 1.
			include XMLSF_DIR . '/views/admin/section-bwt-oauth-stage-1.php';
		} else {
			$redirect_uri = \site_url( 'index.php?' . self::$query_var );
			$oauth_url    = \add_query_arg(
				array(
					'client_id'     => $options['bing_client_id'],
					'redirect_uri'  => \rawurlencode( $redirect_uri ),
					'scope'         => 'Webmaster.manage',
					'response_type' => 'code',
					//'access_type'   => 'offline', // Request a refresh token. DO WE NEED THIS FOR BING???
					//'prompt'        => 'consent', // Ensure consent screen is shown. DO WE NEED THIS FOR BING???
				),
				'https://www.bing.com/webmasters/OAuth/authorize'
			);

			// Stage 2.
			include XMLSF_DIR . '/views/admin/section-bwt-oauth-stage-2.php';
		}
	}

	/**
	 * Render the text field for the Google Client ID.
	 */
	public static function client_id_render() {
		$options   = (array) \get_option( self::$option_group, array() );
		$client_id = isset( $options['bing_client_id'] ) ? \sanitize_text_field( $options['bing_client_id'] ) : '';
		?>
		<input type="text" autocomplete="off" name="<?php echo \esc_attr( self::$option_group ); ?>[bing_client_id]" id="xmlsf_notifier_bing_client_id" value="<?php echo \esc_attr( $client_id ); ?>" class="regular-text">
		<p class="description">
			<?php \esc_html_e( 'Enter your Google Cloud Project Client ID. You can find this in the Google Cloud Console under APIs & Services > Credentials.', 'xml-sitemap-feed' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the text field for the Google Client Secret.
	 */
	public static function client_secret_render() {
		$options       = (array) \get_option( self::$option_group, array() );
		$client_secret = ! empty( $options['bing_client_secret'] ) ? self::$pw_placeholder : '';
		?>
		<input type="password" autocomplete="new-password" name="<?php echo \esc_attr( self::$option_group ); ?>[bing_client_secret]" id="xmlsf_notifier_bing_client_secret" value="<?php echo \esc_attr( $client_secret ); ?>" class="regular-text">
		<p class="description">
			<?php \esc_html_e( 'Enter your Google Cloud Project Client Secret. Keep this secret confidential. If you loose it, you will need to create a new one in the Google Cloud Console under APIs & Services > Credentials.', 'xml-sitemap-feed' ); ?>
		</p>
		<?php
	}

	/**
	 * Sanitize the plugin options before saving.
	 *
	 * @param array $input The options array submitted by the form.
	 *
	 * @return array The sanitized options array.
	 */
	public static function sanitize_settings( $input ) {
		$sanitized = array();
		$options   = (array) \get_option( self::$option_group, array() ); // Not strictly needed if only sanitizing submitted input.

		// Sanitize Google Client ID.
		if ( isset( $input['bing_client_id'] ) ) {
			$sanitized['bing_client_id'] = \sanitize_text_field( $input['bing_client_id'] );
		} else {
			$sanitized['bing_client_id'] = isset( $options['bing_client_id'] ) ? $options['bing_client_id'] : '';
		}

		// Sanitize Google Client Secret.
		if ( isset( $input['bing_client_secret'] ) && self::$pw_placeholder !== $input['bing_client_secret'] ) {
			$sanitized['bing_client_secret'] = ! empty( $input['bing_client_secret'] ) ? Secret::encrypt( \sanitize_text_field( $input['bing_client_secret'] ) ) : '';
		} else {
			$sanitized['bing_client_secret'] = isset( $options['bing_client_secret'] ) ? $options['bing_client_secret'] : '';
		}

		// Make sure to not loose existing refresh token, but only if client id is set and was not changed.
		if ( ! empty( $options['bing_refresh_token'] ) && $sanitized['bing_client_id'] === $options['bing_client_id'] ) {
			$sanitized['bing_refresh_token'] = $options['bing_refresh_token'];
		}

		return $sanitized;
	}
}
