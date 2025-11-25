<?php
/**
 * Status301 Premium Google Search Console Connection Oauth Handler
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
class GSC_Oauth_Handler {
	/**
	 * The redirect path for the OAuth callback.
	 *
	 * @var string
	 */
	public static $query_var = 'xmlsf_notifier_google_oauth';

	/**
	 * The Google OAuth token endpoint.
	 *
	 * @var string
	 */
	private static $token_endpoint = 'https://oauth2.googleapis.com/token';

	/**
	 * Handle the OAuth callback request.
	 *
	 *  @since 5.6
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

		$options = (array) \get_option( 'xmlsf_gsc_connect', array() );

		// Verify our data.
		if ( empty( $options['google_client_id'] ) || empty( $options['google_client_secret'] ) ) {
			return array(
				'result' => array(
					'code'    => 'sitemap_notifier_oauth_credentials_missing',
					'message' => \__( 'Missing Google Client ID or Client Secret.', 'xml-sitemap-feed' ),
					'type'    => 'error',
				),
			);
		}

		// Decrypt client secret.
		$client_secret = self::decrypt( $options['google_client_secret'] );

		if ( false === $client_secret ) {
			return array(
				'result' => array(
					'code'    => 'sitemap_notifier_oauth_secret_decrypt_failed',
					'message' => \__( 'Google Client Secret decryption failed. Please disconnect, enter a new one and reconnect your site.', 'xml-sitemap-feed' ),
					'type'    => 'error',
				),
			);
		}

		// Prepare the parameters for the POST request.
		$body = array(
			'code'          => $authorization_code,
			'client_id'     => $options['google_client_id'],
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
		$expires_in   = isset( $data->expires_in ) ? intval( $data->expires_in ) : 86400;

		if ( empty( $access_token ) ) {
			return array(
				'result' => array(
					'code'    => 'sitemap_notifier_oauth_no_access_token',
					'message' => 'Failed to obtain access token from Google.',
					'type'    => 'error',
				),
			);
		}

		// Store the new access token as transient.
		self::store_access_token( $access_token, $expires_in );

		return array(
			'result' => array(
				'code'    => 'sitemap_notifier_oauth_success',
				'message' => \__( 'Successfully connected to Google Search Console!', 'xml-sitemap-feed' ),
				'type'    => 'success',
			),
		);
	}

	/**
	 * Retrieves a valid Google OAuth access token, refreshing it if necessary.
	 *
	 * @return string|WP_Error The valid access token or a WP_Error object on failure.
	 */
	public static function refresh_access_token() {
		$options = (array) \get_option( 'xmlsf_gsc_connect', array() );

		// Check if refresh token is available.
		if ( empty( $options['google_refresh_token'] ) ) {
			return new WP_Error(
				'sitemap_notifier_oauth_refresh_token_missing',
				__( 'Google refresh token is missing. Please reconnect to Google Search Console.', 'xml-sitemap-feed' )
			);
		}

		$client_secret = self::decrypt( $options['google_client_secret'] );

		if ( false === $client_secret ) {
			return new WP_Error(
				'sitemap_notifier_oauth_secret_decrypt_failed',
				__( 'Google Client Secret decryption failed. Please disconnect, enter a new one and reconnect your site.', 'xml-sitemap-feed' )
			);
		}

		if ( empty( $options['google_client_id'] ) || empty( $client_secret ) ) {
			return new WP_Error(
				'sitemap_notifier_oauth_credentials_missing_refresh',
				__( 'Missing Google Client ID or Client Secret.', 'xml-sitemap-feed' )
			);
		}

		$body = array(
			'grant_type'    => 'refresh_token',
			'refresh_token' => $options['google_refresh_token'],
			'client_id'     => $options['google_client_id'],
			'client_secret' => $client_secret,
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
			WP_DEBUG && WP_DEBUG_LOG && \error_log( '[Sitemap Notifier] Error refreshing access token (remote error): ' . $response->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return new WP_Error(
				'sitemap_notifier_oauth_refresh_remote_error',
				$response->get_error_message()
			);
		}

		$response_code = \wp_remote_retrieve_response_code( $response );
		$body          = \wp_remote_retrieve_body( $response );
		$data          = \json_decode( $body, true );

		// Check for errors in the refresh response.
		if ( 200 !== \wp_remote_retrieve_response_code( $response ) || ! isset( $data['access_token'] ) ) {
			$error         = isset( $data['error'] ) ? $data['error'] : 'Unknown refresh token error.';
			$error_message = isset( $data['error_description'] ) ? $data['error_description'] : $error;
			WP_DEBUG && WP_DEBUG_LOG && \error_log( '[Sitemap Notifier] Error refreshing access token (API error). Code: ' . $response_code . '. Message: ' . $error_message . '. Body: ' . $body ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

			// If refresh token is invalid/expired, remove it and suggest re-connecting.
			if ( 'invalid_grant' === $error ) {
				$options['google_refresh_token'] = ''; // Clear the refresh token.
				unset( $options['google_client_secret'] ); // Prevent double encryption.
				\update_option( 'xmlsf_gsc_connect', $options );

				return new WP_Error(
					'sitemap_notifier_oauth_refresh_invalid_grant',
					__( 'Google refresh token is invalid or expired. Please reconnect to Google Search Console.', 'xml-sitemap-feed' )
				);
			}

			return new WP_Error(
				'sitemap_notifier_oauth_refresh_failed',
				sprintf( /* translators: %1$s error code, %2$s error message (untranslated) */ \esc_html__( 'Failed to refresh Google access token (HTTP %1$s): %2$s', 'xml-sitemap-feed' ), $response_code, $error_message )
			);
		}

		// Successfully refreshed token. Store the new access token in the transient.
		$new_access_token = \sanitize_text_field( $data['access_token'] );
		$expires_in       = isset( $data['expires_in'] ) ? intval( $data['expires_in'] ) : 86400;

		// Store the new access token as transient.
		self::store_access_token( $new_access_token, $expires_in );

		// Store the new refresh token if provided.
		if ( ! empty( $data['refresh_token'] ) ) {
			self::store_refresh_token( $data['refresh_token'] );
		}

		return $new_access_token;
	}

	/**
	 * Stores the Google OAuth access token.
	 *
	 *  @since 5.6
	 *
	 * @param string $token The valid access token.
	 * @param int    $expires_in The expiration time of the token in seconds.
	 */
	public static function store_access_token( $token, $expires_in ) {
		if ( $expires_in > 60 ) {
			\set_transient( 'sitemap_notifier_access_token', $token, $expires_in - 60 );
		}
	}

	/**
	 * Stores the Google OAuth refresh token.
	 *
	 *  @since 5.6
	 *
	 * @param string $token The valid access token.
	 */
	public static function store_refresh_token( $token ) {
		$options                         = (array) \get_option( 'xmlsf_gsc_connect', array() );
		$options['google_refresh_token'] = \sanitize_text_field( $token );

		\update_option( 'xmlsf_gsc_connect', $options );
	}

	/**
	 * Encrypt the Google Client Secret using OpenSSL.
	 *
	 * @param string $value The value to encrypt.
	 * @return string|false The encrypted value or false on failure.
	 */
	public static function encrypt( $value ) {
		if ( ! \extension_loaded( 'openssl' ) ) {
			\add_settings_error(
				'sitemap_notifier_oauth_section',
				'openssl_not_loaded',
				__( 'The openssl extension appears to be missing. Your OAuth client secret was stored in the database without proper encryption. It is recommended to upgrade your server and resave the data.', 'xml-sitemap-feed' ),
				'warning'
			);
			return $value;
		}

		$key  = \defined( 'LOGGED_IN_KEY' ) && '' !== LOGGED_IN_KEY ? LOGGED_IN_KEY : 'this-is-not-a-secret-key';
		$salt = \defined( 'LOGGED_IN_SALT' ) && '' !== LOGGED_IN_SALT ? LOGGED_IN_SALT : 'this-is-not-a-secret-salt';

		if ( 'this-is-not-a-secret-key' === $key || 'this-is-not-a-secret-salt' === $salt ) {
			\add_settings_error(
				'sitemap_notifier_oauth_section',
				'no_salts_found',
				sprintf( /* translators: %s: The respective field name (Google Client Secret) */ \__( 'The %s could not be securely encrypted. Please set your salts in wp-config.php.', 'xml-sitemap-feed' ), \__( 'Google Client Secret', 'xml-sitemap-feed' ) ),
				'error'
			);
		}

		$method = 'aes-256-ctr';
		$ivlen  = \openssl_cipher_iv_length( $method );
		$iv     = \openssl_random_pseudo_bytes( $ivlen );

		$raw_value = \openssl_encrypt( $value . $salt, $method, $key, 0, $iv );
		if ( ! $raw_value ) {
			return false;
		}

		return \base64_encode( $iv . $raw_value ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Decrypt the Google Client Secret using OpenSSL.
	 *
	 * @param string $raw_value The value to encrypt.
	 *
	 * @return string|false The encrypted value or false on failure.
	 */
	public static function decrypt( $raw_value ) {
		if ( ! \extension_loaded( 'openssl' ) ) {
			return $raw_value;
		}

		$raw_value = \base64_decode( $raw_value, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

		$key    = \defined( 'LOGGED_IN_KEY' ) && '' !== LOGGED_IN_KEY ? LOGGED_IN_KEY : 'this-is-not-a-secret-key';
		$salt   = \defined( 'LOGGED_IN_SALT' ) && '' !== LOGGED_IN_SALT ? LOGGED_IN_SALT : 'this-is-not-a-secret-salt';
		$method = 'aes-256-ctr';
		$ivlen  = \openssl_cipher_iv_length( $method );
		$iv     = substr( $raw_value, 0, $ivlen );

		$raw_value = substr( $raw_value, $ivlen );

		$value = \openssl_decrypt( $raw_value, $method, $key, 0, $iv );
		if ( ! $value || substr( $value, - strlen( $salt ) ) !== $salt ) {
			return false;
		}

		return substr( $value, 0, - strlen( $salt ) );
	}
}
