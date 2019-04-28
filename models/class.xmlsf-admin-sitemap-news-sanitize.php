<?php

class XMLSF_Admin_Sitemap_News_Sanitize
{
	public static function news_tags_settings( $new )
	{
		$sanitized = is_array( $new ) ? $new : array();

		// at least one, default post type
		if ( empty( $sanitized['post_type'] ) || !is_array( $sanitized['post_type'] ) ) {
			$sanitized['post_type'] = array('post');
		}

		// if there are categories selected, then test
		// if we have post types selected that do not use the post category taxonomy
		if ( !empty( $sanitized['categories'] ) ) {
			global $wp_taxonomies;
			$post_types = ( isset( $wp_taxonomies['category'] ) ) ? $wp_taxonomies['category']->object_type : array();

			$disabled = false;
			foreach ( $sanitized['post_type'] as $post_type ) {
				if ( !in_array( $post_type, $post_types ) ) {
					$disabled = true;
					break;
				}
			}
			// suppress category selection
			if ( $disabled )
				unset( $sanitized['categories'] );
		}

		return $sanitized;
	}
}
