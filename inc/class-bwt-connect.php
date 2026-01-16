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
	 * The redirect path for the OAuth callback.
	 *
	 * @var string
	 */
	public static $query_var = 'xmlsf_notifier_bing_oauth';

	/**
	 * Redirection URL.
	 *
	 * @var string
	 */
	public static $page_slug = 'bwt_connect';

	/**
	 * Handles the OAuth callback request.
	 *
	 * @param WP $wp The WP object.
	 */
	public static function parse_request( $wp ) {
		// Check if our custom query variable is set.
		if ( isset( $wp->query_vars[ self::$query_var ] ) ) {
			// Handle the OAuth callback.
			$data = BWT_Oauth_Handler::callback_handler();

			$data['result']['setting'] = 'xmlsf_bwt_connect';

			\set_transient( 'settings_errors', array( $data['result'] ), 30 ); // Store notices for the next page load.

			if ( 'error' === $data['result']['type'] ) {
				$slug         = self::$page_slug;
				$redirect_url = \add_query_arg( 'settings-updated', 'true', \admin_url( 'admin.php?page=' . $slug ) );
			} else {
				$slug         = sitemaps_enabled( 'sitemap' ) ? 'xmlsf' : false;
				$redirect_url = $slug ? \add_query_arg( 'page', $slug, \admin_url( 'options-general.php' ) ) : \admin_url( 'options-reading.php#xmlsf_sitemaps' );
				$redirect_url = \add_query_arg( 'settings-updated', 'true', $redirect_url );
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
		$access_token = \get_transient( 'sitemap_notifier_bing_access_token' );

		// If access token was retrieved from transient, it's valid.
		if ( false !== $access_token ) {
			return $access_token;
		}

		$new_access_token = BWT_Oauth_Handler::refresh_access_token();

		if ( \is_wp_error( $new_access_token ) ) {
			do_action( 'sitemap_notifier_refresh_access_token', $new_access_token, 'error' );

			return $new_access_token;
		}

		// Return the new access token.
		return $new_access_token;
	}

	/**
	 * Submitter. Hooked on xmlsf_advanced_news_notifier event.
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
