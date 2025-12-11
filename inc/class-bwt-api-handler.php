<?php
/**
 * Bing Webmaster Tools API Handler
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF;

use WP_Error;

/**
 * Bing Webmaster Tools API Handler class
 *
 * @since 5.7
 */
class BWT_API_Handler {

	/**
	 * Remote request to submit the sitemap to Bing Webmaster Tools using an OAuth Access Token.
	 *
	 * @param string $api_endpoint The API endpoint to use.
	 * @param string $access_token The OAuth 2.0 access token.
	 *
	 * @return array|WP_Error An array containing the sitemap data, WP_Error on failure.
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
			return $api_response;
		}

		$api_response_code = \wp_remote_retrieve_response_code( $api_response );
		$api_response_body = \wp_remote_retrieve_body( $api_response );

		// Bing Webmaster Tools API returns 200 OK on successful submission request.
		if ( 200 === $api_response_code ) {
			return \json_decode( $api_response_body, true );
		} else {
			// Handle API errors.
			return new WP_Error(
				'bwt_api_error',
				self::handle_api_errors( $api_response_code, $api_response_body ),
				array(
					'status' => $api_response_code,
				)
			);
		}
	}

	/**
	 * Remote request to submit the sitemap to Bing Webmaster Tools using an OAuth Access Token.
	 *
	 * @param string $sitemap The sitemap URL.
	 * @param string $access_token The OAuth 2.0 access token.
	 *
	 * @return true|WP_Error True on success, WP_Error on failure.
	 */
	public static function submit( $sitemap, $access_token ) {
		$api_endpoint     = 'https://www.bing.com/webmaster/api.svc/json/SubmitFeed';
		$api_request_args = array(
			'method'  => 'POST',
			'headers' => array(
				'Authorization'  => 'Bearer ' . $access_token,
			),
			'timeout' => 15,
			'body'    => \json_encode( array(
				'siteUrl' => \home_url(),
				'feedUrl' => $sitemap,
			) ),
		);

		// Send the request.
		$api_response = \wp_remote_request( $api_endpoint, $api_request_args );

		if ( \is_wp_error( $api_response ) ) {
			return $api_response;
		}

		$api_response_code = \wp_remote_retrieve_response_code( $api_response );
		$api_response_body = \wp_remote_retrieve_body( $api_response );

		// Bing Webmaster Tools API returns 200 OK on successful submission request.
		if ( 200 === $api_response_code ) {
			return true;
		} else {
			// Handle API errors.
			return new WP_Error(
				'gsc_api_error',
				self::handle_api_errors( $api_response_code, $api_response_body ),
				array(
					'status' => $api_response_code,
				)
			);
		}
	}

	/**
	 * Handle API Errors
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
			\__( 'Bing API Error (HTTP %1$s): %2$s', 'xml-sitemap-feed' ),
			$api_response_code,
			\esc_html( $error_message )
		);

		if ( 401 === $api_response_code ) {
			$detailed_error_message .= ' ' . \__( 'Authentication failed. The access token may be invalid or expired. Please try reconnecting to Bing Webmaster Tools.', 'xml-sitemap-feed' );
		} elseif ( 403 === $api_response_code ) {
			$detailed_error_message .= ' ' . \__( 'Please ensure the connected Microsoft account has full access to the Webmaster Tools property.', 'xml-sitemap-feed' );
		} elseif ( 404 === $api_response_code ) {
			$detailed_error_message .= ' ' . \__( 'This usually means the site property URL used in the API call was not found or does not match the property verified in Webmaster Tools.', 'xml-sitemap-feed' );
		}

		return $detailed_error_message;
	}
}
