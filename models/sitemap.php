<?php

/**
 * Filter sitemap post types
 *
 * @since 5.0
 * @param $post_types array
 * @return array
 */
function xmlsf_filter_post_types( $post_types ) {
	foreach ( array('attachment','reply') as $post_type ) {
		if ( isset( $post_types[$post_type]) )
			unset( $post_types[$post_type] );
	}

	return array_filter( $post_types );
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

		$taxonomies = get_object_taxonomies( $post_type, 'objects' );
		// check each tax public flag and term count and append name to array
		foreach ( $taxonomies as $taxonomy ) {
			if ( !empty( $taxonomy->public ) && !in_array( $taxonomy->name, xmlsf()->disabled_taxonomies() ) )
				$tax_array[$taxonomy->name] = $taxonomy->label;
		}
	}

	return $tax_array;
}
