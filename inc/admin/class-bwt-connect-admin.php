<?php
/**
 * Status301 Premium Bing Webmaster Tools Connection Manager
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Admin;

use WP_Error;

/**
 * Helper class with public methods to set up a Bing Webmaster Tools connection.
 *
 * @author RavanH
 * @version 5.7
 */
class BWT_Connect_Admin {
	/**
	 * The redirect path for the OAuth callback.
	 *
	 * @var string
	 */
	public static $query_var = 'xmlsf_notifier_bing_oauth';

	/**
	 * The option group name.
	 *
	 * @var string
	 */
	public static $option_group = 'xmlsf_bwt_connect';

	/**
	 * Redirection URL.
	 *
	 * @var string
	 */
	public static $page_slug = 'bwt_connect';

	/**
	 * Register settings used by the plugin.
	 */
	public static function add_settings_page() {
		\add_submenu_page(
			'settings_page_xmlsf',
			__( 'Bing Webmaster Tools', 'xml-sitemap-feed' ),
			__( 'Bing Webmaster Tools', 'xml-sitemap-feed' ),
			'manage_options',
			self::$page_slug,
			array( __NAMESPACE__ . '\BWT_Connect_Settings', 'options_page_render' )
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
			array( __NAMESPACE__ . '\BWT_Connect_Settings', 'sanitize_settings' ) // Sanitize callback.
		);

		// OAuth Settings Section.
		\add_settings_section(
			'sitemap_notifier_oauth_section',
			__( 'Connect your site to Bing Webmaster Tools', 'xml-sitemap-feed' ), // Title.
			array( __NAMESPACE__ . '\BWT_Connect_Settings', 'oauth_section_callback' ), // Callback for section description.
			$page_slug // Page slug.
		);

		// Add new OAuth settings fields.
		\add_settings_field(
			'bing_api_key', // ID.
			__( 'API Key', 'xml-sitemap-feed' ), // Title.
			array( __NAMESPACE__ . '\BWT_Connect_Settings', 'bing_api_key_render' ), // Callback function to render the field.
			$page_slug, // Page slug where the field appears.
			'sitemap_notifier_oauth_section' // Section the field belongs to.
		);
	}

	/**
	 * Retrieves the Settings Page URL for connecting to Bing Webmaster Tools.
	 *
	 * @return string The Settings Page admin URL.
	 */
	public static function get_settings_url() {
		return \admin_url( 'admin.php?page=' . self::$page_slug );
	}

	/**
	 * Discconnect from Bing Webmaster Tools.
	 */
	public static function disconnect() {
		$options = (array) \get_option( self::$option_group, array() );

		// Clear the refresh token.
		unset( $options['bing_api_key'] );
		\update_option( self::$option_group, $options );
	}
}
