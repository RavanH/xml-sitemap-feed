<?php

/**
 * Filter sitemap post types
 *
 * @since 5.0
 * @param $post_types array
 * @return array
 */
function xmlsf_filter_post_types( $post_types ) {
	$post_types = (array) $post_types;

	// Always exclude attachment and reply post types (bbpress)
	unset( $post_types['attachment'], $post_types['reply'] );

	return array_filter( $post_types );
}

/**
 * Get index url
 *
 * @param string $sitemap
 * @param array $args arguments:
 *                    $type - post_type or taxonomy, default false
 *                    $m    - YYYY, YYYYMM, YYYYMMDD
 *                    $w    - week of the year ($m must be YYYY format)
 *                    $gz   - bool for GZ extension (triggers compression verification)
 *
 * @return string
 */
function xmlsf_get_index_url( $sitemap = 'root', $args = array() ) {

	// get our arguments
	$args = apply_filters( 'xmlsf_index_url_args', wp_parse_args( $args, array( 'type' => false, 'm' => false, 'w' => false, 'gz' => false) ) );
	extract( $args );

	if ( xmlsf()->plain_permalinks() ) {
		$name = '?feed=sitemap-'.$sitemap;
		$name .= $gz ? '.gz' : '';
		$name .= $type ? '-'.$type : '';
		$name .= $m ? '&m='.$m : '';
		$name .= $w ? '&w='.$w : '';
	} else {
		$name = 'sitemap-'.$sitemap;
		$name .= $type ? '-'.$type : '';
		$name .= $m ? '.'.$m : '';
		$name .= $w ? '.'.$w : '';
		$name .= '.xml';
		$name .= $gz ? '.gz' : '';
	}

	return esc_url( trailingslashit( home_url() ) . $name );

}

/**
 * Get taxonomies
 * Returns an array of taxonomy names to be included in the index
 *
 * @since 5.0
 * @param void
 * @return array
 */
function xmlsf_get_taxonomies() {

	$taxonomy_settings = get_option('xmlsf_taxonomy_settings');

	$tax_array = array();

	if ( !empty( $taxonomy_settings['active'] ) ) {

		$taxonomies = get_option('xmlsf_taxonomies');

		if ( is_array($taxonomies) ) {
			foreach ( $taxonomies as $taxonomy ) {
				$count = wp_count_terms( $taxonomy, array('hide_empty'=>true) );
				if ( !is_wp_error($count) && $count > 0 )
					$tax_array[] = $taxonomy;
			}
		} else {
			foreach ( xmlsf_public_taxonomies() as $name => $label )
				if ( 0 < wp_count_terms( $name, array('hide_empty'=>true) ) )
					$tax_array[] = $name;
		}

	}

	return $tax_array;

}

/**
 * Get all public (and not empty) taxonomies
 * Returns an array associated taxonomy object names and labels.
 *
 * @since 5.0
 * @param void
 * @return array
 */
function xmlsf_public_taxonomies() {

	$tax_array = array();

	foreach ( (array) get_option( 'xmlsf_post_types' ) as $post_type => $settings ) {

		if ( empty($settings['active']) ) continue;

		// check each tax public flag and term count and append name to array
		foreach ( get_object_taxonomies( $post_type, 'objects' ) as $taxonomy ) {

			if ( !empty( $taxonomy->public ) && !in_array( $taxonomy->name, xmlsf()->disabled_taxonomies() ) )
				$tax_array[$taxonomy->name] = $taxonomy->label;

		}

	}

	return $tax_array;
}

/**
 * Santize priority value
 * Expects proper locale setting for calculations: setlocale( LC_NUMERIC, 'C' );
 *
 * Returns a float within the set limits.
 *
 * @since 5.2
 * @param float $priority
 * @param float $min
 * @param float $max
 * @return float
 */
function xmlsf_sanitize_priority( $priority, $min = .1, $max = 1 ) {

	$priority = (float) $priority;
	$min = (float) $min;
	$max = (float) $max;

	if ( $priority <= $min ) {
		return number_format( $min, 1 );
	} elseif ( $priority >= $max ) {
		return number_format( $max, 1 );
	} else {
		return number_format( $priority, 1 );
	}
}

/**
 * Get post attached | featured image(s)
 *
 * @param object $post
 * @param string $which
 *
 * @return array
 */
function xmlsf_images_data( $post, $which ) {
	$attachments = array();

	if ( 'featured' == $which ) {

		if ( has_post_thumbnail( $post->ID ) ) {
			$featured = get_post( get_post_thumbnail_id( $post->ID ) );
			if ( is_object($featured) ) {
				$attachments[] = $featured;
			}
		}

	} elseif ( 'attached' == $which ) {

		$args = array(
			'post_type' => 'attachment',
			'post_mime_type' => 'image',
			'numberposts' => -1,
			'post_status' =>'inherit',
			'post_parent' => $post->ID
		);

		$attachments = get_posts( $args );

	}

	if ( empty( $attachments ) ) return array();

	// gather all data
	$images_data = array();

	foreach ( $attachments as $attachment ) {

		$url = wp_get_attachment_url( $attachment->ID );

		if ( !empty($url) ) {

			$url = esc_attr( esc_url_raw( $url ) );

			$images_data[$url] = array(
				'loc' => $url,
				'title' => apply_filters( 'the_title_xmlsitemap', $attachment->post_title ),
				'caption' => apply_filters( 'the_title_xmlsitemap', $attachment->post_excerpt )
				// 'caption' => apply_filters( 'the_title_xmlsitemap', get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ) )
			);

		}

	}

	return $images_data;
}
