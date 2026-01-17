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
	 * Callback function for the Bing Settings section header.
	 */
	public static function oauth_section_callback() {
		// Prepare the option if it does not already exist. Sets it as non-autoloaded option.
		\add_option( self::$option_group, '', '', false );

		// Get existing data from DB.
		$options = (array) \get_option( self::$option_group, array() );

		\settings_errors();

		// Intro.
		include XMLSF_DIR . '/views/admin/section-bwt-oauth-intro.php';

		// Fields.
		include XMLSF_DIR . '/views/admin/section-bwt-oauth-stage-1.php';
	}

	/**
	 * Render the text field for the Google Client ID.
	 */
	public static function bing_api_key_render() {
		$options = (array) \get_option( self::$option_group, array() );
		$api_key = isset( $options['bing_api_key'] ) ? \sanitize_text_field( $options['bing_api_key'] ) : '';
		?>
		<input type="text" autocomplete="off" name="<?php echo \esc_attr( self::$option_group ); ?>[bing_api_key]" id="xmlsf_notifier_bing_api_key" value="<?php echo \esc_attr( $api_key ); ?>" class="regular-text">
		<p class="description">
			<?php \esc_html_e( 'Enter your Bing Webmaster Tools API key.', 'xml-sitemap-feed' ); ?>
			<?php \esc_html_e( 'You can find this in Bing Webmaster Tools under Settings > API Access > API Key.', 'xml-sitemap-feed' ); ?>
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

		// Sanitize Google Client Secret.
		if ( isset( $input['bing_api_key'] ) && self::$pw_placeholder !== $input['bing_api_key'] ) {
			$sanitized['bing_api_key'] = ! empty( $input['bing_api_key'] ) ? Secret::encrypt( \sanitize_text_field( $input['bing_api_key'] ) ) : '';
		} else {
			$sanitized['bing_api_key'] = isset( $options['bing_api_key'] ) ? $options['bing_api_key'] : '';
		}

		return $sanitized;
	}
}
