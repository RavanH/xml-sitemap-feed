<?php

/**
 * Filter news post types
 *
 * @since 5.0
 * @param $post_types array
 * @return array
 */
function xmlsf_news_filter_post_types( $post_types ) {
	foreach ( array('attachment','page','reply') as $post_type ) {
		if ( isset( $post_types[$post_type]) )
			unset( $post_types[$post_type] );
	}

	return array_filter( $post_types );
}
