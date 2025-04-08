<?php
/**
 * Rank Match compatibility
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Compat;

/**
 * Rank Match compatibility class
 */
class Rank_Math {
	/**
	 * Exclude posts marked as noindex.
	 *
	 * @param bool $exclude Exclude flag.
	 * @param int  $post_id Post ID.
	 *
	 * @return bool
	 */
	public static function exclude_noindex( $exclude, $post_id ) {
		$rank_math_robots = (array) \get_post_meta( $post_id, 'rank_math_robots', true );
		return \in_array( 'noindex', $rank_math_robots, true ) ? true : $exclude;
	}
}
