<?php
/**
 * Status301 Premium Google Search Console Connection Manager
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Admin;

use WP_Error;

/**
 * Helper class with public methods to set up a Google Search Console connection.
 *
 * @author RavanH
 * @version 5.6
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
	public static function add_settings_page() {
		\add_submenu_page(
			'options-reading.php',
			__( 'Google Search Console', 'xml-sitemap-feed' ),
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
	 * Retrieves the Settings Page URL for connecting to Google Search Console.
	 *
	 *  @since 5.6
	 *
	 * @return string The Settings Page admin URL.
	 */
	public static function get_settings_url() {
		return \admin_url( 'admin.php?page=' . self::$page_slug );
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
		$access_token = \XMLSF\GSC_Connect::get_access_token();

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
		$properties = \XMLSF\GSC_API_Handler::list_sites( $access_token );
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
		\delete_transient( 'sitemap_notifier_google_access_token' );
	}
}
