<?php

/**
 * Filter news post types
 *
 * @since 5.0
 * @param $post_types array
 * @return array
 */
function xmlsf_news_filter_post_types( $post_types ) {
	$post_types = (array) $post_types;

	unset( $post_types['attachment'], $post_types['reply'] );

	return array_filter( $post_types );
}
