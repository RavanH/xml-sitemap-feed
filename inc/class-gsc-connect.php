<?php
/**
 * Status301 Premium Google Search Console Connection Manager
 *
 * @package Sitemap Notifier
 */

namespace XMLSF;

use WP_Error;

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
	public static function add_tools_page() {
		\add_submenu_page(
			'tools.php',
			__( 'Google Search Console Connection', 'xml-sitemap-feed' ),
			__( 'Google Search Console', 'xml-sitemap-feed' ),
			'manage_options',
			self::$page_slug,
			array( __NAMESPACE__ . '\GSC_Connect_Settings', 'options_page_render' )
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

			$origin = \get_transient( 'gsc_connect_origin' );
			$slug   = $origin ? $origin : ( sitemaps_enabled( 'sitemap' ) ? 'xmlsf' : ( sitemaps_enabled( 'sitemap-news' ) ? 'xmlsf_news' : false ) );

			$redirect_url = $slug ? \add_query_arg( 'page', $slug, \admin_url( 'options-general.php' ) ) : \admin_url( 'options-reading.php#xmlsf_sitemaps' );
			$redirect_url = \add_query_arg( 'settings-updated', 'true', $redirect_url );

			\delete_transient( 'gsc_connect_origin' );

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
			// Get our property via API.
			$property = self::get_property_url();

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
		return \admin_url( 'tools.php?page=' . self::$page_slug );
	}

	/**
	 * Get GSC Properties.
	 *
	 * @param string $min_level    The minimum required access level.
	 *
	 * @return string|WP_Error The siteUrl or error.
	 */
	public static function get_property_url( $min_level = 'siteFullUser' ) {
		// Get access token.
		$access_token = self::get_access_token();

		if ( \is_wp_error( $access_token ) ) {
			return $access_token;
		}

		// Parse URL.
		$parsed_url = \wp_parse_url( \get_option( 'home' ) );

		// Return WP_Error if no host found.
		if ( ! $parsed_url || empty( $parsed_url['host'] ) ) {
			return new WP_Error(
				'invalid-site-url',
				__( 'Could not determine a candidate property URL from the site URL.', 'xml-sitemap-feed' )
			);
		}

		// Get the list of properties.
		$properties = GSC_API_Handler::list_sites( $access_token );
		// Return WP_Error if no host found.

		// Return WP_Error if encountered.
		if ( \is_wp_error( $properties ) ) {
			return $properties;
		}

		// Construct candidate URL-prefix property with correct scheme (http or https).
		$site_scheme   = isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] : 'https';
		$site_host     = $parsed_url['host'];
		$host_parts    = explode( '.', $site_host );
		$site_port     = isset( $parsed_url['port'] ) ? ':' . $parsed_url['port'] : '';
		$site_url_prop = $site_scheme . '://' . $site_host . $site_port . '/'; // Site property URL for API must end with /.

		// Construct candidate Domain properties.
		$site_url_candidates = array(
			'sc-domain:' . $host_parts[ count( $host_parts ) - 2 ] . '.' . $host_parts[ count( $host_parts ) - 1 ], // e.g. sc-domain:example.com.
			'sc-domain:' . $host_parts[ count( $host_parts ) - 3 ] . '.' . $host_parts[ count( $host_parts ) - 2 ] . '.' . $host_parts[ count( $host_parts ) - 1 ], // e.g. sc-domain:example.co.uk.
			$site_url_prop, // e.g. https://www.example.com/.
		);

		$levels = array(
			'siteOwner',
		);
		if ( 'siteFullUser' === $min_level ) {
			$levels[] = 'siteFullUser';
		} elseif ( 'siteRestrictedUser' === $min_level ) {
			$levels[] = 'siteFullUser';
			$levels[] = 'siteRestrictedUser';
		}

		$properties = array_filter(
			$properties,
			function ( $property ) use ( $site_url_candidates, $levels ) {
				return in_array( $property['siteUrl'], $site_url_candidates, true ) && in_array( $property['permissionLevel'], $levels, true );
			}
		);

		if ( empty( $properties ) ) {
			return new WP_Error(
				'no-valid-site-url',
				\__( 'Could not get a valid property from Google Search Console. The connected user account may not have the correct permissions.', 'xml-sitemap-feed' )
			);
		}

		// We'll have to choose from the remaining candidates.
		foreach ( $properties as $property ) {
			$property_url = $property['siteUrl'];
			if ( strstr( $property['siteUrl'], 'sc-domain:' ) ) {
				// Let's stick with the first Domain property, why not?
				break;
			}
		}

		return $property_url;
	}

	/**
	 * Discconnect from Google Search Console.
	 *
	 * @since 5.6
	 */
	public static function disconnect() {
		$options = (array) \get_option( self::$option_group, array() );

		// Clear the refresh token.
		unset( $options['google_refresh_token'] );
		unset( $options['property_url'] );
		\update_option( self::$option_group, $options );

		// Delete access token.
		\delete_transient( 'sitemap_notifier_access_token' );
	}

	/**
	 * Submitter. Hooked on xmlsf_advanced_news_notifier event.
	 *
	 * @since 5.6
	 *
	 * @uses class GSC_API_Handler
	 *
	 * @param string $sitemap_url The sitemap URL.

	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public static function submit( $sitemap_url ) {
		// Get access token.
		$access_token = self::get_access_token();

		if ( \is_wp_error( $access_token ) ) {
			return $access_token;
		}

		// Get API Endpoint.
		$api_endpoint = self::get_api_endpoint( $sitemap_url );

		if ( \is_wp_error( $api_endpoint ) ) {
			return $api_endpoint;
		}

		// Submit sitemap URL using the OAuth access token.
		$result = GSC_API_Handler::submit( $api_endpoint, $access_token );

		if ( ! $result['success'] ) {
			return new WP_Error(
				'xmlsf_gsc_submit_error',
				$result['error'],
				$result['data']
			);
		}

		return true;
	}

	/**
	 * Remote request to submit the sitemap to Google Search Console using an OAuth Access Token.
	 *
	 * @param string $sitemap_url The sitemap URL.
	 *
	 * @return array An array containing the success status and sitemap data or error message.
	 */
	public static function get( $sitemap_url ) {
		// Get access token.
		$access_token = self::get_access_token();

		if ( \is_wp_error( $access_token ) ) {
			return $access_token;
		}

		// Get API Endpoint.
		$api_endpoint = self::get_api_endpoint( $sitemap_url );

		if ( \is_wp_error( $api_endpoint ) ) {
			return $api_endpoint;
		}

		return GSC_API_Handler::get( $api_endpoint, $access_token );
	}
}
