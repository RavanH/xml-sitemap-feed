<?php
/**
 * Status301 Premium Google Search Console Connection Manager
 *
 * @package Sitemap Notifier
 */

namespace XMLSF;

/**
 * Helper class with public methods to set up a Google Search Console connection.
 *
 * @author RavanH
 * @version 1.0
 */
class GSC_Connect {
	/**
	 * The redirect path for the OAuth callback.
	 *
	 * @var string
	 */
	public static $query_var = 'xmlsf_notifier_google_oauth';

	/**
	 * The option group name.
	 *
	 * @var string
	 */
	public static $option_group = 'xmlsf_gsc_connect';

	/**
	 * Redirection URL.
	 *
	 * @var string
	 */
	public static $page_slug = 'gsc_connect';

	/**
	 * Register settings used by the plugin.
	 */
	public static function add_options_page() {
		\add_submenu_page(
			null, // Hides settings page but breaks stuff like admin_page_title().
			__( 'Google Search Console Connection', 'xml-sitemap-feed' ), // Page title.
			__( 'Google Search Console', 'xml-sitemap-feed' ), // Menu title.
			'manage_options', // Capability required.
			self::$page_slug, // Menu slug.
			array( __NAMESPACE__ . '\GSC_Connect_Settings', 'options_page_render' ) // Function to display the page.
		);
	}

	/**
	 * Register settings used by the plugin.
	 */
	public static function register_settings() {
		$option_group = self::$option_group;
		$option_name  = $option_group; // Currently using same option name as group name.
		$page_slug    = self::$page_slug;

		\register_setting(
			$option_group, // Option group.
			$option_name, // Option name.
			array( __NAMESPACE__ . '\GSC_Connect_Settings', 'sanitize_settings' ) // Sanitize callback.
		);

		// OAuth Settings Section.
		\add_settings_section(
			'sitemap_notifier_oauth_section',
			__( 'Connect your site to Google Search Console', 'xml-sitemap-feed' ), // Title.
			array( __NAMESPACE__ . '\GSC_Connect_Settings', 'oauth_section_callback' ), // Callback for section description.
			$page_slug // Page slug.
		);

		// Add new OAuth settings fields.
		\add_settings_field(
			'google_client_id', // ID.
			__( 'Client ID', 'xml-sitemap-feed' ), // Title.
			array( __NAMESPACE__ . '\GSC_Connect_Settings', 'google_client_id_render' ), // Callback function to render the field.
			$page_slug, // Page slug where the field appears.
			'sitemap_notifier_oauth_section' // Section the field belongs to.
		);

		\add_settings_field(
			'google_client_secret', // ID.
			__( 'Client secret', 'xml-sitemap-feed' ), // Title.
			array( __NAMESPACE__ . '\GSC_Connect_Settings', 'google_client_secret_render' ), // Callback function to render the field.
			$page_slug, // Page slug where the field appears.
			'sitemap_notifier_oauth_section' // Section the field belongs to.
		);
	}

	/**
	 * Handles the OAuth callback request.
	 *
	 *  @since 5.6
	 *
	 * @param WP $wp The WP object.
	 */
	public static function parse_request( $wp ) {
		// Check if our custom query variable is set.
		if ( isset( $wp->query_vars[ self::$query_var ] ) ) {
			// Handle the OAuth callback.
			$data = GSC_Oauth_Handler::callback_handler();

			$data['result']['setting'] = 'xmlsf_gsc_connect';

			\set_transient( 'settings_errors', array( $data['result'] ), 30 ); // Store notices for the next page load.

			$redirect_url = \add_query_arg( 'settings-updated', 'true', \admin_url( 'options-general.php?page=xmlsf_news&tab=gsc' ) );

			\wp_safe_redirect( $redirect_url );
			exit;
		}
	}

	/**
	 * Define the query variable for the OAuth callback.
	 *
	 *  @since 5.6
	 *
	 * @param array $vars The query variables.
	 *
	 * @return array The query variables.
	 */
	public static function query_vars( $vars ) {
		$vars[] = self::$query_var;
		return $vars;
	}

	/**
	 * Retrieves a valid Google OAuth access token, refreshing it if necessary.
	 *
	 *  @since 5.6
	 *
	 * @return string|WP_Error The valid access token or a WP_Error object on failure.
	 */
	public static function get_access_token() {
		// Try to get the access token from the transient first.
		$access_token = \get_transient( 'sitemap_notifier_access_token' );

		// If access token was retrieved from transient, it's valid.
		if ( false !== $access_token ) {
			return $access_token;
		}

		$new_access_token = GSC_Oauth_Handler::refresh_access_token();

		if ( \is_wp_error( $new_access_token ) ) {
			return $new_access_token;
		}

		// Return the new access token.
		return $new_access_token;
	}

	/**
	 * Remote request to submit the sitemap to Google Search Console using an OAuth Access Token.
	 *
	 * @since 5.6
	 *
	 * @uses class GSC_API_Handler
	 *
	 * @param string $sitemap_url The sitemap URL.
	 *
	 * @return string|WP_Error The API endpoint or WP Error.
	 */
	public static function get_api_endpoint( $sitemap_url ) {
		// Get the property URL from settings.
		$options  = (array) \get_option( 'xmlsf_gsc_connect', array() );
		$property = ! empty( $options['property_url'] ) ? $options['property_url'] : false;

		if ( ! $property ) {
			$access_token = self::get_access_token();
			// Get our property via API.
			$property = GSC_API_Handler::get_property_url( $access_token );

			if ( \is_wp_error( $property ) ) {
				return $property;
			}

			// Save property URL.
			$options['property_url'] = $property;
			update_option( 'xmlsf_gsc_connect', $options, false );
		}

		// The API endpoint: https://www.googleapis.com/webmasters/v3/sites/siteUrl/sitemaps/feedPath
		// siteUrl needs to be URL-encoded. feedPath (sitemap_url) also needs to be URL-encoded.
		return \sprintf(
			'https://www.googleapis.com/webmasters/v3/sites/%s/sitemaps/%s',
			\rawurlencode( $property ),
			\rawurlencode( $sitemap_url ) // Submit the full sitemap URL.
		);
	}

	/**
	 * Retrieves the Settings Page URL for connecting to Google Search Console.
	 *
	 *  @since 5.6
	 *
	 * @return string The Settings Page admin URL.
	 */
	public static function get_settings_url() {
		return \admin_url( 'options-general.php?page=' . self::$page_slug );
	}

	/**
	 * Discconnect from Google Search Console.
	 *
	 *  @since 5.6
	 */
	public static function disconnect() {
		$options = (array) \get_option( self::$option_group, array() );

		// Clear the refresh token.
		unset( $options['google_refresh_token'] );
		unset( $options['property_url'] );
		\update_option( self::$option_group, $options );

		// Delete access token.
		\delete_transient( 'sitemap_notifier_access_token' );

		require_once ABSPATH . 'wp-admin/includes/template.php';
		\add_settings_error(
			'xmlsf_gsc_connect',
			'disconnected',
			__( 'Disconnected from Google Search Console successfully.', 'xml-sitemap-feed' ),
			'success'
		);
	}
}
