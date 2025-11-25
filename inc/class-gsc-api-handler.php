<?php
/**
 * Google Search Console API Handler
 *
 * @package Sitemap Notifier
 **/

namespace XMLSF;

use WP_Error;

/**
 * Google Search Console API Handler class
 *
 * @since 5.6
 */
class GSC_API_Handler {

	/**
	 * Remote request to submit the sitemap to Google Search Console using an OAuth Access Token.
	 *
	 * @param string $api_endpoint The API endpoint to use.
	 * @param string $access_token The OAuth 2.0 access token.
	 *
	 * @return array An array containing the success status sitemap data.
	 */
	public static function get( $api_endpoint, $access_token ) {
		$api_request_args = array(
			'method'  => 'GET', // Request method uses GET.
			'headers' => array(
				'Authorization'  => 'Bearer ' . $access_token,
				'Content-Length' => '0', // GET request with no body.
			),
			'timeout' => 15, // Seconds.
		);

		$api_response = \wp_remote_request( $api_endpoint, $api_request_args );

		if ( \is_wp_error( $api_response ) ) {
			$error_message = $api_response->get_error_message();
			return array(
				'success' => false,
				'message' => $error_message,
			);
		}

		$api_response_code = \wp_remote_retrieve_response_code( $api_response );
		$api_response_body = \wp_remote_retrieve_body( $api_response );

		// Google Search Console API returns 204 OK on successful submission request.
		if ( 200 === $api_response_code ) {
			return array(
				'success' => true,
				'data'    => \json_decode( $api_response_body, true ),
			);
		} else {
			// Handle API errors.
			return array(
				'success' => false,
				'message' => self::handle_api_errors( $api_response_code, $api_response_body ),
			);
		}
	}

	/**
	 * Remote request to submit the sitemap to Google Search Console using an OAuth Access Token.
	 *
	 * @param string $api_endpoint The API endpoint to use.
	 * @param string $access_token The OAuth 2.0 access token.
	 *
	 * @return array An array containing the success status and a message.
	 */
	public static function submit( $api_endpoint, $access_token ) {
		// The API endpoint: https://www.googleapis.com/webmasters/v3/sites/siteUrl/sitemaps/feedPath.

		$api_request_args = array(
			'method'  => 'PUT', // Submit method uses PUT.
			'headers' => array(
				'Authorization'  => 'Bearer ' . $access_token,
				'Content-Length' => '0', // PUT request with no body.
			),
			'timeout' => 15, // Seconds.
		);

		$api_response = \wp_remote_request( $api_endpoint, $api_request_args );

		if ( \is_wp_error( $api_response ) ) {
			$error_message = $api_response->get_error_message();
			return array(
				'success' => false,
				'message' => \sprintf( /* translators: %s API error message (untranslated) */ \esc_html__( 'Error submitting sitemap: %s', 'xml-sitemap-feed' ), \esc_html( $error_message ) ),
			);
		}

		$api_response_code = \wp_remote_retrieve_response_code( $api_response );
		$api_response_body = \wp_remote_retrieve_body( $api_response );

		// Google Search Console API returns 204 OK on successful submission request.
		if ( 204 === $api_response_code ) {
			return array(
				'success' => true,
				'message' => $api_response_body, // Empty.
			);
		} else {
			// Handle API Errors.
			return array(
				'success' => false,
				'message' => self::handle_api_errors( $api_response_code, $api_response_body ),
			);
		}
	}

	/**
	 * Handle API Errors
	 *
	 *  @since 5.6
	 *
	 * @param int    $api_response_code The HTTP response code.
	 * @param string $api_response_body The response body.
	 * @return array An array containing the success status and a message.
	 */
	public static function handle_api_errors( $api_response_code, $api_response_body ) {
		// Handle API errors.
		$error_message = \__( 'Unknown API error.', 'sitemap-notifier' );
		$response_data = \json_decode( $api_response_body, true );

		if ( isset( $response_data['error']['message'] ) ) {
			$error_message = $response_data['error']['message'];
		} elseif ( ! empty( $api_response_body ) ) {
			$error_message = \wp_strip_all_tags( $api_response_body );
		}

		// Provide more specific guidance for common errors relevant to OAuth.
		$detailed_error_message = \sprintf(
			/* translators: %1$s: HTTP status code, %2$s: Error message from Google API */
			\__( 'Google API Error (HTTP %1$s): %2$s', 'xml-sitemap-feed' ),
			$api_response_code,
			\esc_html( $error_message )
		);

		if ( 401 === $api_response_code ) {
			$detailed_error_message .= ' ' . \__( 'Authentication failed. The access token may be invalid or expired. Please try reconnecting to Google Search Console.', 'xml-sitemap-feed' );
		} elseif ( 403 === $api_response_code ) {
			$detailed_error_message .= ' ' . \__( 'Please ensure the connected Google account has full access to the Search Console property.', 'xml-sitemap-feed' );
		} elseif ( 404 === $api_response_code ) {
			$detailed_error_message .= ' ' . \__( 'This usually means the site property URL used in the API call was not found or does not match the property verified in Search Console. Ensure the property exists and the URL matches exactly (including www/non-www and http/https).', 'xml-sitemap-feed' );
		}

		return $detailed_error_message;
	}

	/**
	 * Get GSC Properties.
	 *
	 * @param string $access_token The OAuth 2.0 access token.
	 *
	 * @return array|WP_Error Array containing available properties and their rights levels or WP_Error on failure.
	 */
	public static function list_sites( $access_token ) {
		// Get the list of properties.
		$api_endpoint = 'https://www.googleapis.com/webmasters/v3/sites';

		$api_request_args = array(
			'method'  => 'GET', // Request method uses GET.
			'headers' => array(
				'Authorization'  => 'Bearer ' . $access_token,
				'Content-Length' => '0', // GET request with no body.
			),
			'timeout' => 15, // Seconds.
		);

		$api_response = \wp_remote_request( $api_endpoint, $api_request_args );

		if ( \is_wp_error( $api_response ) ) {
			return $api_response;
		}

		$api_response_code = \wp_remote_retrieve_response_code( $api_response );
		$api_response_body = \wp_remote_retrieve_body( $api_response );

		// Google Search Console API returns 204 OK on successful submission request.
		if ( 200 === $api_response_code ) {
			$data = \json_decode( $api_response_body, true );

			return $data['siteEntry'];
		} else {
			// Handle API errors.
			return new WP_Error(
				'gsc-api-error',
				self::handle_api_errors( $api_response_code, $api_response_body )
			);
		}
	}
}
