<?php
/**
 * Status301 Premium Secret Handler
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF;

/**
 * Helper class for Secret handling.
 *
 * @since 5.7
 */
class Secret {
	/**
	 * Encrypt using OpenSSL.
	 *
	 * @param string $value The value to encrypt.
	 * @return string|false The encrypted value or false on failure.
	 */
	public static function encrypt( $value ) {
		if ( empty( $value ) ) {
			return '';
		}

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
				\__( 'The OAuth client secret could not be securely encrypted. Please set your salts in wp-config.php.', 'xml-sitemap-feed' ),
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
	 * Decrypt using OpenSSL.
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
