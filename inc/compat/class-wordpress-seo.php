<?php
/**
 * Yoast SEO compatibility
 *
 * @package XML Sitemap & Google News
 *
 * @since 5.5.4
 */

namespace XMLSF\Compat;

/**
 * Rank Match compatibility class
 */
class WordPress_SEO {
	/**
	 * Exclude posts marked as noindex in the plugin sitemaps.
	 *
	 * @param bool $exclude Exclude flag.
	 * @param int  $post_id Post ID.
	 *
	 * @return bool
	 */
	public static function exclude_noindex( $exclude, $post_id ) {
		$robots = \get_post_meta( $post_id, '_yoast_wpseo_meta-robots-noindex', true );
		return '1' === $robots ? true : $exclude;
	}

	/**
	 * Filter post query arguments. Hooked into wp_sitemaps_posts_query_args filter.
	 *
	 * @param array $args Arguments.
	 *
	 * @return array
	 */
	public static function posts_query_args( $args ) {
		// Exclude posts.
        // TODO construct query for post types with default exclude?
		$args['meta_query'] = \array_merge( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			(array) $args['meta_query'],
			array(
				array(
					'relation' => 'OR',
					array(
						'key'     => '_yoast_wpseo_meta-robots-noindex',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => '_yoast_wpseo_meta-robots-noindex',
						'value'   => '1',
						'compare' => '!=',
					),
				),
			)
		);
		return $args;
	}
}
