<?php
/**
 * Status301 Premium Google Search Console Connection Manager
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF;

/**
 * Helper class with public methods to set up a Google Search Console connection.
 *
 * @since 5.6
 */
class GSC_Connect {
	/**
	 * The redirect path for the OAuth callback.
	 *
	 * @var string
	 */
	public static $query_var = 'xmlsf_notifier_google_oauth';

	/**
	 * Redirection URL.
	 *
	 * @var string
	 */
	public static $page_slug = 'gsc_connect';

	/**
	 * Handles the OAuth callback request.
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

			if ( 'error' === $data['result']['type'] ) {
				$slug         = self::$page_slug;
				$redirect_url = \add_query_arg( 'settings-updated', 'true', \admin_url( 'admin.php?page=' . $slug ) );
			} else {
				$origin = \get_transient( 'gsc_connect_origin' );
				$slug   = $origin ? $origin : ( sitemaps_enabled( 'sitemap' ) ? 'xmlsf' : ( sitemaps_enabled( 'sitemap-news' ) ? 'xmlsf_news' : false ) );

				$redirect_url = $slug ? \add_query_arg( 'page', $slug, \admin_url( 'options-general.php' ) ) : \admin_url( 'options-reading.php#xmlsf_sitemaps' );
				$redirect_url = \add_query_arg( 'settings-updated', 'true', $redirect_url );

				\delete_transient( 'gsc_connect_origin' );
			}

			\wp_safe_redirect( $redirect_url );
			exit;
		}
	}

	/**
	 * Define the query variable for the OAuth callback.
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
	 * @return string|WP_Error The valid access token or a WP_Error object on failure.
	 */
	public static function get_access_token() {
		// Try to get the access token from the transient first.
		$access_token = \get_transient( 'sitemap_notifier_google_access_token' );

		// If access token was retrieved from transient, it's valid.
		if ( false !== $access_token ) {
			return $access_token;
		}

		$new_access_token = GSC_Oauth_Handler::refresh_access_token();

		if ( \is_wp_error( $new_access_token ) ) {
			do_action( 'sitemap_notifier_refresh_access_token_error', $new_access_token, 'error' );

			return $new_access_token;
		}

		// Return the new access token.
		return $new_access_token;
	}

	/**
	 * Remote request to submit the sitemap to Google Search Console using an OAuth Access Token.
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
			$property = \XMLSF\Admin\GSC_Connect::get_property_url();

			if ( \is_wp_error( $property ) ) {
				return $property;
			}

			// Save property URL.
			$options['property_url'] = $property;
			\update_option( 'xmlsf_gsc_connect', $options, false );
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
	 * Submitter.
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
		return GSC_API_Handler::submit( $api_endpoint, $access_token );
	}

	/**
	 * Remote request to submit the sitemap to Google Search Console using an OAuth Access Token.
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

		// Get API Endpoint.
		$api_endpoint = self::get_api_endpoint( $sitemap_url );

		if ( \is_wp_error( $api_endpoint ) ) {
			return $api_endpoint;
		}

		return GSC_API_Handler::get( $api_endpoint, $access_token );
	}
}
