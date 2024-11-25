<?php
/**
 * BBPress compatibility
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Compat;

/**
 * BBPress compatibility class
 */
class BBPress {
	/**
	 * BBPress compatibility hooked into xml request filter
	 *
	 * @param array $request The request.
	 *
	 * @return array
	 */
	public static function filter_request( $request ) {
		\remove_filter( 'bbp_request', 'bbp_request_feed_trap' );

		return $request;
	}
}
