<?php
/**
 * Rank Math compatibility
 *
 * @package XML Sitemap & Google News
 *
 * @since 5.5.4
 */

namespace XMLSF\Compat;

/**
 * Rank Math compatibility class
 */
class Rank_Math {
	/**
	 * Exclude posts marked as noindex in the plugin sitemaps.
	 *
	 * @param bool $exclude Exclude flag.
	 * @param int  $post_id Post ID.
	 *
	 * @return bool
	 */
	public static function exclude_noindex( $exclude, $post_id ) {
		$robots = (array) \get_post_meta( $post_id, 'rank_math_robots', true );
		return \in_array( 'noindex', $robots, true ) ? true : $exclude;
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
		$args['meta_query'] = \array_merge( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			(array) $args['meta_query'],
			array(
				array(
					'relation' => 'OR',
					array(
						'key'     => 'rank_math_robots',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => 'rank_math_robots',
						'value'   => 'noindex',
						'compare' => 'NOT LIKE',
					),
				),
			)
		);
		return $args;
	}
}
