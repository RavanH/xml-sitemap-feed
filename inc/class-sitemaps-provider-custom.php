<?php
/**
 * Sitemaps: Sitemaps_Provider_Custom class
 *
 * Builds the sitemaps for the Custom URLs.
 *
 * @package XML Sitemap & Google News
 * @since 5.4
 */

namespace XMLSF;

/**
 * Posts XML sitemap provider.
 *
 * @since 5.4
 */
class Sitemaps_Provider_Custom extends \WP_Sitemaps_Provider {

	/**
	 * External Custom Sitemap URLs.
	 *
	 * @since 5.4
	 *
	 * @var array
	 */
	private $urls = array();

	/**
	 * External Custom Sitemap URLs.
	 *
	 * @since 5.4
	 *
	 * @var int
	 */
	private $max_urls = 50000;

	/**
	 * WP_Sitemaps_Posts constructor.
	 *
	 * @since 5.4
	 */
	public function __construct() {
		$this->name        = 'custom';
		$this->object_type = 'url';

		$urls       = (array) \apply_filters( 'xmlsf_custom_urls', (array) \get_option( 'xmlsf_urls', array() ) );
		$this->urls = \array_filter( $urls );
	}

	/**
	 * Gets a URL list for a post type sitemap.
	 *
	 * @since 5.4
	 *
	 * @param int    $page_num       Page of results.
	 * @param string $object_subtype Optional. Not applicable for URLs but
	 *                               required for compatibility with the parent
	 *                               provider class. Default empty.
	 * @return array[] Array of URL information for a sitemap.
	 */
	public function get_url_list( $page_num, $object_subtype = '' ) {
		$length = $this->max_urls; // Or better use wp_sitemaps_get_max_urls( 'custom' )?
		$offset = (int) $page_num > 1 ? ( (int) $page_num - 1 ) * $length : 0;

		$urls = \array_slice(
			$this->urls,
			$offset,
			$length
		);

		$url_list = array();

		add_filter( 'http_request_host_is_external', '__return_true' ); // Allow external domains while validating URLs.

		foreach ( $urls as $url ) {
			if ( ! \wp_http_validate_url( $url[0] ) ) {
				continue;
			}

			$sitemap_entry = array(
				'loc' => $url[0],
			);

			if ( isset( $url[1] ) && \is_numeric( $url[1] ) ) {
				$sitemap_entry['priority'] = namespace\sanitize_number( $url[1] );
			}

			/**
			 * Filters the sitemap entry for an individual post.
			 *
			 * @since 5.4
			 *
			 * @param array   $sitemap_entry Sitemap entry for the post.
			 * @param array   $url           URL and priority array.
			 */
			$sitemap_entry = \apply_filters( 'wp_sitemaps_urls_entry', $sitemap_entry, $url );
			$url_list[]    = $sitemap_entry;
		}

		remove_filter( 'http_request_host_is_external', '__return_true' );

		return $url_list;
	}

	/**
	 * Gets the max number of pages available for the object type.
	 *
	 * @since 5.4
	 *
	 * @param string $object_subtype Optional. Post type name. Default empty.
	 * @return int Total number of pages.
	 */
	public function get_max_num_pages( $object_subtype = '' ) {

		$max_num_pages = \is_numeric( $this->max_urls ) && (int) $this->max_urls > 0 ? \ceil( \count( $this->urls ) / $this->max_urls ) : 0;

		return $max_num_pages;
	}
}
