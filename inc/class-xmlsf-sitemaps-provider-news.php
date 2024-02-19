<?php
/**
 * Sitemaps: XMLSF_Sitemaps_Provider_News class
 *
 * Builds the sitemaps for the External Suctom Sitemaps.
 *
 * @package XML Sitemap & Google News
 * @since 5.4
 */

/**
 * Posts XML sitemap provider.
 *
 * @since 5.4
 */
class XMLSF_Sitemaps_Provider_News extends WP_Sitemaps_Provider {

	/**
	 * External Custom Sitemap URLs.
	 *
	 * @since 5.4
	 *
	 * @var array
	 */
	private $urls = array();

	/**
	 * WP_Sitemaps_Posts constructor.
	 *
	 * @since 5.4
	 */
	public function __construct() {
		$this->name        = 'news';
		$this->object_type = 'news';

		$urls       = (array) xmlsf_sitemap_url( 'news' );
		$this->urls = array_filter( $urls, 'wp_http_validate_url' );
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
		// Dud method for external sitemap. Return an emtpy array.
		return array();
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
		return count( $this->urls );
	}

	/**
	 * Lists sitemap pages exposed by this provider.
	 *
	 * The returned data is used to populate the sitemap entries of the index.
	 *
	 * @since 5.4
	 *
	 * @return array[] Array of sitemap entries.
	 */
	public function get_sitemap_entries() {
		$sitemaps = array();

		$pages = $this->get_max_num_pages();

		for ( $page = 1; $page <= $pages; $page++ ) {
			$sitemap_entry = array(
				'loc' => $this->get_sitemap_url( '', $page ),
			);

			/**
			 * Filters the sitemap entry for the sitemap index.
			 *
			 * @since 5.4
			 *
			 * @param array  $sitemap_entry  Sitemap entry for the post.
			 * @param string $object_type    Object empty name.
			 * @param string $object_subtype Object subtype name.
			 *                               Empty string if the object type does not support subtypes.
			 * @param int    $page           Page number of results.
			 */
			$sitemap_entry = apply_filters( 'wp_sitemaps_index_entry', $sitemap_entry, $this->object_type, '', $page );

			$sitemaps[] = $sitemap_entry;
		}

		return $sitemaps;
	}

	/**
	 * Gets the URL of a sitemap entry.
	 *
	 * @since 5.4
	 *
	 * @global WP_Rewrite $wp_rewrite WordPress rewrite component.
	 *
	 * @param string $name The name of the sitemap.
	 * @param int    $page The page of the sitemap.
	 * @return string The composed URL for a sitemap entry.
	 */
	public function get_sitemap_url( $name, $page ) {
		$pos = (int) $page - 1;
		return $this->urls[ $pos ];
	}
}
