<?php
/**
 * Settings Sanitization
 *
 * @package XML Sitemap & Google News
 */

/**
 * Sanitization Class
 */
class XMLSF_Admin_Sitemap_News_Sanitize {

	/**
	 * Sanitize news tag settings
	 *
	 * @param array $save Settings array.
	 *
	 * @return array
	 */
	public static function news_tags_settings( $save ) {
		$sanitized = is_array( $save ) ? $save : array();

		// At least one, default post type.
		if ( empty( $sanitized['post_type'] ) || ! is_array( $sanitized['post_type'] ) ) {
			$sanitized['post_type'] = array( 'post' );
		}

		// If there are categories selected, then test.
		// If we have post types selected that do not use the post category taxonomy.
		if ( ! empty( $sanitized['categories'] ) ) {
			global $wp_taxonomies;
			$post_types = ( isset( $wp_taxonomies['category'] ) ) ? $wp_taxonomies['category']->object_type : array();

			$disabled = false;
			foreach ( $sanitized['post_type'] as $post_type ) {
				if ( ! in_array( $post_type, $post_types ) ) {
					$disabled = true;
					break;
				}
			}
			// Suppress category selection.
			if ( $disabled ) {
				unset( $sanitized['categories'] );
			}
		}

		return $sanitized;
	}
}
