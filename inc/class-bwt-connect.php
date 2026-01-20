<?php
/**
 * Status301 Premium Bing Webmaster Tools Connection Manager
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF;

use WP_Error;

/**
 * Helper class with public methods to set up a Bing Webmaster Tools connection.
 *
 * @since 5.6
 */
class BWT_Connect {

	/**
	 * Retrieves the Bing API key.
	 *
	 * @return string|WP_Error The valid API key or a WP_Error object on failure.
	 */
	public static function get_access_token() {
		// Get the api key from DB.
		$options = (array) \get_option( 'xmlsf_bwt_connect', array() );

		// If no api key found, return an error object.
		if ( empty( $options['bing_api_key'] ) ) {
			$error = \esc_html__( 'Bing API key is missing. Please reconnect to Bing Webmaster Tools.', 'xml-sitemap-feed' );

			\do_action( 'sitemap_notifier_refresh_access_token_error', $error, 'error' );

			return new WP_Error(
				'bwt_api_error',
				$error
			);
		}

		$api_key = Secret::decrypt( $options['bing_api_key'] );

		// If api key decoding failed, return an error object.
		if ( ! $api_key ) {
			$error = \esc_html__( 'Bing API key decryption failed. Please reconnect to Bing Webmaster Tools.', 'xml-sitemap-feed' );

			\do_action( 'sitemap_notifier_refresh_access_token_error', $error, 'error' );

			return new WP_Error(
				'bwt_api_error',
				$error
			);
		}

		// Return the api key.
		return $api_key;
	}

	/**
	 * Submitter.
	 *
	 * @uses class BWT_API_Handler
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

		// Submit sitemap URL using the OAuth access token.
		return BWT_API_Handler::submit( $sitemap_url, $access_token );
	}

	/**
	 * Remote request to submit the sitemap to Bing Webmaster Tools using an OAuth Access Token.
	 *
	 * @param string $sitemap_url The sitemap URL.
	 *
	 * @return array|WP_Error An array containing the success status and sitemap data, WP_Error on failure.
	 */
	public static function get( $sitemap_url ) {
		// Get access token.
		$access_token = self::get_access_token();

		if ( \is_wp_error( $access_token ) ) {
			return $access_token;
		}

		return BWT_API_Handler::get( $sitemap_url, $access_token );
	}
}
