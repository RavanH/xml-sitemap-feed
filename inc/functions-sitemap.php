<?php
/**
 * Sitemap Functions
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF;

/**
 * Get post attached | featured image(s)
 *
 * @param object $post  Post object.
 * @param string $which Image type.
 *
 * @return array
 */
function images_data( $post, $which ) {
	$attachments = array();

	if ( 'featured' === $which ) {
		if ( \has_post_thumbnail( $post->ID ) ) {
			$featured = \get_post( \get_post_thumbnail_id( $post->ID ) );
			if ( \is_object( $featured ) ) {
				$attachments[] = $featured;
			}
		}
	} elseif ( 'attached' === $which ) {
		$args = array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'numberposts'    => -1,
			'post_status'    => 'inherit',
			'post_parent'    => $post->ID,
		);

		$attachments = \get_posts( $args );
	}

	if ( empty( $attachments ) ) {
		return array();
	}

	// Gather all data.
	$images_data = array();

	foreach ( $attachments as $attachment ) {

		$url = \wp_get_attachment_url( $attachment->ID );

		if ( ! empty( $url ) ) {
			$url = \esc_attr( \esc_url_raw( $url ) );

			$images_data[ $url ] = array(
				'loc'     => $url,
				'title'   => $attachment->post_title,
				'caption' => $attachment->post_excerpt, // TODO consider if it is better to use get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ) here. Or make it optional?
			);
		}
	}

	return $images_data;
}

/**
 * Get root page(s) priority
 *
 * @return array
 */
function get_home_priority() {
	$settings = (array) \get_option( 'xmlsf_post_type_settings', get_default_settings( 'post_type_settings' ) );

	if ( empty( $settings['page'] ) || empty( $settings['page']['priority'] ) ) {
		return '';
	}

	$priority = \apply_filters( 'xmlsf_post_priority', '1.0', 0 ); // TODO make this optional.

	// A final check for limits and round it.
	return namespace\sanitize_number( $priority );
}

/**
 * User Priority
 *
 * @since 5.4
 *
 * @param int $user User ID.
 * @return float
 */
function get_user_priority( $user ) {

	$author_settings = (array) \get_option( 'xmlsf_author_settings', get_default_settings( 'author_settings' ) );

	if ( empty( $author_settings['priority'] ) ) {
		return '';
	}

	$priority = \is_numeric( $author_settings['priority'] ) ? \floatval( $author_settings['priority'] ) : 0.5;

	$priority = \apply_filters( 'xmlsf_user_priority', $priority, $user );

	// A final check for limits and round it.
	return namespace\sanitize_number( $priority );
}

/**
 * User Modified
 *
 * @since 5.4
 *
 * @param WP_User $user User object.
 *
 * @return string|false GMT date
 */
function get_user_modified( $user ) {

	if ( \function_exists( 'get_metadata_raw' ) ) {
		/**
		 * Use get_metadata_raw if it exists (since WP 5.5) because it will return null if the key does not exist.
		 */
		$lastmod = \get_metadata_raw( 'user', $user->ID, 'user_modified', true );
	} else {
		/**
		 * Getting ALL meta here because if checking for single key, we cannot
		 * distiguish between empty value or non-exisiting key as both return ''.
		 */
		$meta    = \get_user_meta( $user->ID );
		$lastmod = \array_key_exists( 'user_modified', $meta ) ? \get_user_meta( $user->ID, 'user_modified', true ) : null;
	}

	if ( null === $lastmod ) {
		$post_types = \get_post_types( array( 'public' => true ) );
		// We're not supporting sitemaps for author pages for attachments and pages.
		unset( $post_types['attachment'] );
		unset( $post_types['page'] );

		/**
		 * Filters the has_published_posts query argument in the author archive. Must return a boolean or an array of one or multiple post types.
		 * Allows to add or change post type when theme author archive page shows custom post types.
		 *
		 * @since 5.4
		 *
		 * @param array Array with post type slugs. Default array( 'post' ).
		 *
		 * @return mixed
		 */
		$post_types = \apply_filters( 'xmlsf_author_has_published_posts', $post_types );

		// Get lastmod from last publication date.
		$posts   = get_posts(
			array(
				'author'                 => $user->ID,
				'post_type'              => $post_types,
				'post_status'            => 'publish',
				'posts_per_page'         => 1,
				'numberposts'            => 1,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'update_cache'           => false,
			)
		);
		$lastmod = ! empty( $posts ) ? \get_post_field( 'post_date', $posts[0] ) : '';
		// Cache lastmod as user_modified meta data.
		\add_user_meta( $user->ID, 'user_modified', $lastmod );
	}

	$lastmod = \get_user_meta( $user->ID, 'user_modified', true );

	return ! empty( $lastmod ) ? \mysql2date( DATE_W3C, $lastmod, false ) : false;
}

/**
 * Get front pages
 *
 * @return array
 */
function get_frontpages() {
	if ( null === \xmlsf()->frontpages ) {
		$frontpages = array();
		if ( 'page' === \get_option( 'show_on_front' ) ) {
			$frontpage  = (int) \get_option( 'page_on_front' );
			$frontpages = (array) \apply_filters( 'xmlsf_frontpages', $frontpage );
		}
		\xmlsf()->frontpages = $frontpages;
	}

	return \xmlsf()->frontpages;
}

/**
 * Get blog_pages
 *
 * @return array
 */
function get_blogpages() {
	if ( null === \xmlsf()->blogpages ) {
		$blogpages = array();
		if ( 'page' === \get_option( 'show_on_front' ) ) {
			$blogpage  = (int) \get_option( 'page_for_posts' );
			$blogpages = (array) \apply_filters( 'xmlsf_blogpages', $blogpage );
		}
		\xmlsf()->blogpages = $blogpages;
	}

	return \xmlsf()->blogpages;
}

/**
 * Post Modified
 *
 * @param WP_Post $post Post object.
 *
 * @return string|false GMT date
 */
function get_post_modified( $post ) {

	// If blog or home page then simply look for last post date.
	if ( 'page' === $post->post_type && ( in_array( $post->ID, namespace\get_blogpages(), true ) || in_array( $post->ID, namespace\get_frontpages(), true ) ) ) {

		$lastmod = \get_lastpostdate( 'GMT', 'post' );

		// Only return if we got an actual last post date here.
		if ( $lastmod ) {
			return get_date_from_gmt( $lastmod, DATE_W3C );
		}
	}

	$lastmod = $post->post_modified_gmt;

	// make sure lastmod is not older than publication date (happens on scheduled posts).
	if ( isset( $post->post_date_gmt ) && \strtotime( $post->post_date_gmt ) > \strtotime( $lastmod ) ) {
		$lastmod = $post->post_date_gmt;
	}

	// maybe update lastmod to latest comment.
	$options = (array) \get_option( 'xmlsf_post_type_settings', get_default_settings( 'post_type_settings' ) );

	if ( ! empty( $options[ $post->post_type ]['update_lastmod_on_comments'] ) ) {
		// assuming post meta data has been primed here.
		$lastcomment = \get_post_meta( $post->ID, '_xmlsf_comment_date_gmt', true ); // only get one.

		if ( ! empty( $lastcomment ) && \strtotime( $lastcomment ) > \strtotime( $lastmod ) ) {
			$lastmod = $lastcomment;
		}
	}

	return ! empty( $lastmod ) ? $lastmod : false;
}

/**
 * Term Modified
 *
 * @param WP_Term|int $term Term object or ID.
 * @return string|false
 */
function get_term_modified( $term ) {

	if ( \is_numeric( $term ) ) {
		$term = \get_term( $term );
	}

	if ( \function_exists( 'get_metadata_raw' ) ) {
		/**
		* Use get_metadata_raw if it exists (since WP 5.5) because it will return null if the key does not exist.
		*/
		$lastmod = \get_metadata_raw( 'term', $term->term_id, 'term_modified', true );
	} else {
		/**
		* Getting ALL meta here because if checking for single key, we cannot
		* distiguish between empty value or non-exisiting key as both return ''.
		*/
		$meta    = \get_term_meta( $term->term_id );
		$lastmod = \array_key_exists( 'term_modified', $meta ) ? \get_term_meta( $term->term_id, 'term_modified', true ) : null;
	}

	if ( null === $lastmod ) {
		// Get lastmod from last publication date.
		$posts   = \get_posts(
			array(
				'post_type'              => 'any',
				'post_status'            => 'publish',
				'posts_per_page'         => 1,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'update_cache'           => false,
				'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => $term->taxonomy,
						'field'    => 'slug',
						'terms'    => $term->slug,
					),
				),
			)
		);
		$lastmod = isset( $posts[0]->post_date ) ? $posts[0]->post_date : '';
		// Cache lastmod as term_modified meta data.
		\add_term_meta( $term->term_id, 'term_modified', $lastmod );
	}

	return ! empty( $lastmod ) ? \mysql2date( DATE_W3C, $lastmod, false ) : false;
}

/**
 * Taxonomy Modified
 *
 * @param string $taxonomy Taxonomy slug.
 *
 * @return string|false
 */
function get_taxonomy_modified( $taxonomy ) {

	$obj = \get_taxonomy( $taxonomy );

	if ( false === $obj ) {
		return false;
	}

	foreach ( (array) $obj->object_type as $object_type ) {
		$lastpostdate = \get_lastpostdate( 'GMT', $object_type );
		if ( $lastpostdate ) {
			$lastmod = ! empty( $lastmod ) && $lastmod > $lastpostdate ? $lastmod : $lastpostdate; // Absolute last post date.
		}
	}

	return ! empty( $lastmod ) ? $lastmod : false;
}

/**
 * Get post priority
 *
 * @param WP_Post $post Post object.
 * @return float
 */
function get_post_priority( $post ) {
	$options = (array) \get_option( 'xmlsf_post_type_settings', get_default_settings( 'post_type_settings' ) );

	if ( empty( $options[ $post->post_type ]['priority'] ) ) {
		return '';
	}

	// Check for meta data.
	$priority_meta = \get_post_meta( $post->ID, '_xmlsf_priority', true );
	if ( $priority_meta ) {
		$priority = \floatval( \str_replace( ',', '.', $priority_meta ) );
		$priority = \apply_filters( 'xmlsf_post_priority', $priority, $post->ID );

		// A final check for limits and round it.
		return namespace\sanitize_number( $priority );
	}

	// Still here? Then get calculating...
	$priority = \is_numeric( $options[ $post->post_type ]['priority'] ) ? \floatval( $options[ $post->post_type ]['priority'] ) : 0.5;

	if ( ! empty( $options[ $post->post_type ]['dynamic_priority'] ) ) {
		$post_modified = \mysql2date( 'U', $post->post_modified );

		// Reduce by age.
		// NOTE : home/blog page gets same treatment as sticky post, i.e. no reduction by age.
		if ( \xmlsf()->timespan > 0 && ! \is_sticky( $post->ID ) && ! \in_array( $post->ID, namespace\get_blogpages(), true ) ) {
			$priority -= $priority * ( \xmlsf()->lastmodified - $post_modified ) / \xmlsf()->timespan;
		}

		// Increase by relative comment count.
		if ( $post->comment_count > 0 && $priority < 1 && \xmlsf()->comment_count > 0 ) {
			$priority += 0.1 + ( 1 - $priority ) * $post->comment_count / \xmlsf()->comment_count;
		}
	}

	$priority = \apply_filters( 'xmlsf_post_priority', $priority, $post->ID );

	// A final check for limits and round it.
	return namespace\sanitize_number( $priority );
}

/**
 * Get taxonomy priority
 *
 * @param WP_Term|int $term Term.
 *
 * @return float
 */
function get_term_priority( $term ) {
	$options = \get_option( 'xmlsf_taxonomy_settings' );

	if ( empty( $options['priority'] ) ) {
		return '';
	}

	$priority = \is_numeric( $options['priority'] ) ? \floatval( $options['priority'] ) : 0.5;

	if ( \is_numeric( $term ) ) {
		$term = \get_term( $term );
	}

	if ( ! empty( $options['dynamic_priority'] ) && $priority > 0.1 ) {
		// set first and highest term post count as maximum.
		if ( null === \xmlsf()->taxonomy_termmaxposts ) {
			\xmlsf()->taxonomy_termmaxposts = $term->count + 1;
		}

		$priority -= ( \xmlsf()->taxonomy_termmaxposts - $term->count ) * ( $priority - 0.1 ) / (int) \xmlsf()->taxonomy_termmaxposts;
	}

	$priority = \apply_filters( 'xmlsf_term_priority', $priority, $term->slug );

	// a final check for limits and round it.
	return namespace\sanitize_number( $priority );
}
