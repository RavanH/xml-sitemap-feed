<?php
/**
 * Status301 Premium Bing Webmaster Tools Connection Oauth Handler
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
class BWT_Oauth_Handler {
	/**
	 * The redirect path for the OAuth callback.
	 *
	 * @var string
	 */
	public static $query_var = 'xmlsf_notifier_bing_oauth';

	/**
	 * The Bing OAuth token endpoint.
	 *
	 * @var string
	 */
	private static $token_endpoint = 'https://www.bing.com/webmasters/oauth/token';

	/**
	 * Handle the OAuth callback request.
	 *
	 * @return array
	 */
	public static function callback_handler() {
		// Ensure user is an administrator.
		if ( ! \current_user_can( 'manage_options' ) ) {
			\wp_die( 401 );
		}

		// Retrieve the authorization code.
		$authorization_code = isset( $_GET['code'] ) ? \sanitize_text_field( \wp_unslash( $_GET['code'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( empty( $authorization_code ) ) {
			return array(
				'result' => array(
					'code'    => 'sitemap_notifier_oauth_code_missing',
					'message' => \__( 'OAuth authorization code missing.', 'xml-sitemap-feed' ),
					'type'    => 'error',
				),
			);
		}

		$options = (array) \get_option( 'xmlsf_bwt_connect', array() );

		// Verify our data.
		if ( empty( $options['bing_client_id'] ) || empty( $options['bing_client_secret'] ) ) {
			return array(
				'result' => array(
					'code'    => 'sitemap_notifier_oauth_credentials_missing',
					'message' => \__( 'Missing Bing Client ID or Client Secret.', 'xml-sitemap-feed' ),
					'type'    => 'error',
				),
			);
		}

		// Decrypt client secret.
		$client_secret = Secret::decrypt( $options['bing_client_secret'] );

		if ( false === $client_secret ) {
			return array(
				'result' => array(
					'code'    => 'sitemap_notifier_oauth_secret_decrypt_failed',
					'message' => \__( 'Bing Client Secret decryption failed. Please disconnect, enter a new one and reconnect your site.', 'xml-sitemap-feed' ),
					'type'    => 'error',
				),
			);
		}

		// Prepare the parameters for the POST request.
		$body = array(
			'code'          => $authorization_code,
			'client_id'     => $options['bing_client_id'],
			'client_secret' => $client_secret,
			'redirect_uri'  => \site_url( 'index.php?' . self::$query_var ),
			'grant_type'    => 'authorization_code',
		);

		// Make the POST request using wp_remote_post().
		$response = \wp_remote_post(
			self::$token_endpoint,
			array(
				'body'    => $body,
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
			)
		);

		// Check for WP_Error on the wp_remote_post response.
		if ( \is_wp_error( $response ) ) {
			return array(
				'result' => array(
					'code'    => 'sitemap_notifier_oauth_remote_error',
					'message' => $response->get_error_message(),
					'type'    => 'error',
				),
			);
		}

		// Decode the JSON response body.
		$body = \wp_remote_retrieve_body( $response );
		$data = \json_decode( $body );

		// And check for errors in the response.
		if ( isset( $data->error ) ) {
			$error_message = isset( $data->error_description ) ? $data->error_description : \__( 'Unknown OAuth error.', 'sitemap-notifier' );
			return array(
				'result' => array(
					'code'    => 'sitemap_notifier_oauth_exchange_failed',
					'message' => \sprintf( /* translators: %s error message (untranslated) */ \esc_html__( 'OAuth token exchange failed: %s', 'sitemap-notifier' ), $error_message ),
					'type'    => 'error',
				),
			);
		}

		$refresh_token = isset( $data->refresh_token ) ? $data->refresh_token : '';

		// Store the refresh token in the options table.
		if ( ! empty( $refresh_token ) ) {
			self::store_refresh_token( $refresh_token );
		}

		$access_token = isset( $data->access_token ) ? \sanitize_text_field( $data->access_token ) : '';
		$expires_in   = isset( $data->expires_in ) ? intval( $data->expires_in ) : 3600;

		if ( empty( $access_token ) ) {
			return array(
				'result' => array(
					'code'    => 'sitemap_notifier_oauth_no_access_token',
					'message' => \__( 'Failed to obtain access token from Bing.', 'xml-sitemap-feed' ),
					'type'    => 'error',
				),
			);
		}

		// Store the new access token as transient.
		self::store_access_token( $access_token, $expires_in );

		return array(
			'result' => array(
				'code'    => 'sitemap_notifier_oauth_success',
				'message' => \__( 'Successfully connected to Bing Webmaster Tools!', 'xml-sitemap-feed' ),
				'type'    => 'success',
			),
		);
	}

	/**
	 * Retrieves a valid Bing OAuth access token, refreshing it if necessary.
	 *
	 * @return string|WP_Error The valid access token or a WP_Error object on failure.
	 */
	public static function refresh_access_token() {
		$options = (array) \get_option( 'xmlsf_bwt_connect', array() );

		// Check if refresh token is available.
		if ( empty( $options['bing_refresh_token'] ) ) {
			return new WP_Error(
				'sitemap_notifier_oauth_refresh_token_missing',
				__( 'Bing refresh token is missing. Please reconnect to Bing Webmaster Tools.', 'xml-sitemap-feed' )
			);
		}

		$client_secret = Secret::decrypt( $options['bing_client_secret'] );

		if ( false === $client_secret ) {
			return new WP_Error(
				'sitemap_notifier_oauth_secret_decrypt_failed',
				__( 'Bing Client Secret decryption failed. Please disconnect, enter a new one and reconnect your site.', 'xml-sitemap-feed' )
			);
		}

		if ( empty( $options['bing_client_id'] ) || empty( $client_secret ) ) {
			return new WP_Error(
				'sitemap_notifier_oauth_credentials_missing_refresh',
				__( 'Missing Bing Client ID or Client Secret.', 'xml-sitemap-feed' )
			);
		}

		$body = array(
			'client_id'     => $options['bing_client_id'],
			'client_secret' => $client_secret,
			'refresh_token' => $options['bing_refresh_token'],
			'grant_type'    => 'refresh_token',
		);

		// Remote post new access token request.
		$response = \wp_remote_post(
			self::$token_endpoint,
			array(
				'body'    => $body,
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
				'timeout' => 15,
			)
		);

		// Check for WP_Error on the wp_remote_post response.
		if ( \is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = \wp_remote_retrieve_response_code( $response );
		$body          = \wp_remote_retrieve_body( $response );
		$data          = \json_decode( $body, true );

		// Check for errors in the refresh response.
		if ( 200 !== \wp_remote_retrieve_response_code( $response ) || ! isset( $data['access_token'] ) ) {
			$error         = isset( $data['error'] ) ? $data['error'] : __( 'Unknown refresh token error.', 'xml-sitemap-feed' );
			$error_message = isset( $data['error_description'] ) ? $data['error_description'] : $error;

			// If refresh token is invalid/expired, remove it and suggest re-connecting.
			if ( 'invalid_grant' === $error ) {
				$options['bing_refresh_token'] = ''; // Clear the refresh token.
				\update_option( 'xmlsf_bwt_connect', $options );

				return new WP_Error(
					'sitemap_notifier_oauth_refresh_invalid_grant',
					__( 'Bing refresh token is invalid or expired. Please reconnect to Bing Webmaster Tools.', 'xml-sitemap-feed' )
				);
			}

			return new WP_Error(
				'sitemap_notifier_oauth_refresh_failed',
				sprintf( /* translators: %1$s error code, %2$s error message (untranslated) */ \esc_html__( 'Failed to refresh Bing access token (HTTP %1$s): %2$s', 'xml-sitemap-feed' ), $response_code, $error_message )
			);
		}

		// Store the new refresh token if provided.
		if ( ! empty( $data['refresh_token'] ) ) {
			/**
			 * DO NOT STORE THE NEW REFRESH TOKEN. BING WEBMASTER TOOLS OAUTH IS NOT COMPLIANT WITH OAUTH2 STANDARDS IN THIS REGARD.
			 * The old refresh token remains valid until revoked, so we can continue using it.
			 * The new refresh token will cause an invalid_grant response when used.
			 */
			//self::store_refresh_token( $data['refresh_token'] );
		}

		// Successfully refreshed token. Store the new access token in the transient.
		$new_access_token = \sanitize_text_field( $data['access_token'] );
		$expires_in       = isset( $data['expires_in'] ) ? intval( $data['expires_in'] ) : 3600;

		// Store the new access token as transient.
		self::store_access_token( $new_access_token, $expires_in );

		return $new_access_token;
	}

	/**
	 * Stores the Bing OAuth access token.
	 *
	 * @param string $token The valid access token.
	 * @param int    $expires_in The expiration time of the token in seconds.
	 */
	public static function store_access_token( $token, $expires_in ) {
		if ( $expires_in > 60 ) {
			\set_transient( 'sitemap_notifier_bing_access_token', $token, $expires_in - 60 );
		}
	}

	/**
	 * Stores the Bing OAuth refresh token.
	 *
	 * @param string $token The valid access token.
	 */
	public static function store_refresh_token( $token ) {
		$options                       = (array) \get_option( 'xmlsf_bwt_connect', array() );
		$options['bing_refresh_token'] = \sanitize_text_field( $token );

		\update_option( 'xmlsf_bwt_connect', $options );
	}
}
