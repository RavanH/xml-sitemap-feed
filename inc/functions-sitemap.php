<?php
/**
 * Sitemap Functions
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF;

/**
 * Get post types and their settings.
 *
 * TODO make static wariable for faster processing.
 *
 * @since 5.4
 *
 * @return array
 */
function get_post_types_settings() {
	$post_types = (array) \apply_filters( 'xmlsf_post_types', \get_post_types( array( 'public' => true ) ) );
	// Make sure post types are allowed and publicly viewable.
	$post_types = \array_diff( $post_types, \xmlsf()->disabled_post_types() );
	$post_types = \array_filter( $post_types, 'is_post_type_viewable' );

	$settings = (array) \get_option( 'xmlsf_post_type_settings', get_default_settings( 'post_type_settings' ) );

	// Get active post types.
	$post_types_settings = array();
	foreach ( $post_types as $post_type ) {
		if ( ! \xmlsf()->sitemap->active_post_type( $post_type ) ) {
			continue;
		}

		if ( ! empty( $settings[ $post_type ] ) ) {
			$post_types_settings[ $post_type ] = $settings[ $post_type ];
		}
	}

	return $post_types_settings;
}

/**
 * Get taxonomies
 * Returns an array of taxonomy names to be included in the index
 *
 * @since 5.0
 *
 * @return array
 */
function get_taxonomies() {
	$disabled = \get_option( 'xmlsf_disabled_providers', get_default_settings( 'disabled_providers' ) );

	if ( ! empty( $disabled ) && \in_array( 'taxonomies', (array) $disabled, true ) ) {
		return array();
	}

	$tax_array  = array();
	$taxonomies = \get_option( 'xmlsf_taxonomies', get_default_settings( 'taxonomies' ) );

	if ( \is_array( $taxonomies ) ) {
		foreach ( $taxonomies as $taxonomy ) {
			$count = \wp_count_terms( $taxonomy );
			if ( ! \is_wp_error( $count ) && $count > 0 ) {
				$tax_array[] = $taxonomy;
			}
		}
	} else {
		foreach ( namespace\public_taxonomies() as $name => $label ) {
			$count = \wp_count_terms( $name );
			if ( ! \is_wp_error( $count ) && $count > 0 ) {
				$tax_array[] = $name;
			}
		}
	}

	return $tax_array;
}

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
 * Get all public (and not empty) taxonomies
 * Returns an array associated taxonomy object names and labels.
 *
 * @since 5.0
 *
 * @return array
 */
function public_taxonomies() {
	$tax_array  = array();
	$disabled   = (array) \xmlsf()->disabled_taxonomies();
	$post_types = get_post_types_settings();

	foreach ( $post_types as $post_type => $settings ) {
		// Check each tax public flag and term count and append name to array.
		foreach ( \get_object_taxonomies( $post_type, 'objects' ) as $taxonomy ) {
			if ( ! empty( $taxonomy->public ) && ! in_array( $taxonomy->name, $disabled, true ) ) {
				$tax_array[ $taxonomy->name ] = $taxonomy->label;
			}
		}
	}

	return $tax_array;
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
 * Do image tag
 *
 * @param string  $type Type.
 * @param WP_Post $post Post object.
 *
 * @return void
 */
function image_tag( $type, $post = null ) {
	if ( 'post_type' !== $type || null === $post ) {
		return;
	}

	$post_types = get_post_types_settings();

	if (
		isset( $post_types[ $post->post_type ] ) &&
		\is_array( $post_types[ $post->post_type ] ) &&
		isset( $post_types[ $post->post_type ]['tags'] ) &&
		\is_array( $post_types[ $post->post_type ]['tags'] ) &&
		! empty( $post_types[ $post->post_type ]['tags']['image'] ) &&
		\is_string( $post_types[ $post->post_type ]['tags']['image'] )
	) {
		$images = \get_post_meta( $post->ID, '_xmlsf_image_' . $post_types[ $post->post_type ]['tags']['image'] );
		foreach ( $images as $img ) {
			if ( empty( $img['loc'] ) ) {
				continue;
			}

			echo '<image:image><image:loc>' . \esc_xml( \utf8_uri_encode( $img['loc'] ) ) . '</image:loc>';
			if ( ! empty( $img['title'] ) ) {
				echo '<image:title><![CDATA[' . \esc_xml( $img['title'] ) . ']]></image:title>';
			}
			if ( ! empty( $img['caption'] ) ) {
				echo '<image:caption><![CDATA[' . \esc_xml( $img['caption'] ) . ']]></image:caption>';
			}
			\do_action( 'xmlsf_image_tags_inner', 'post_type' );
			echo '</image:image>';
		}
	}
}

\add_action( 'xmlsf_tags_after', __NAMESPACE__ . '\image_tag', 10, 2 );

/**
 * Image schema
 *
 * @param string $type Type.
 * @uses WP_Post $post
 * @return void
 */
function image_schema( $type ) {
	global $wp_query;

	if ( 'post_type' !== $type || empty( $wp_query->query_vars['post_type'] ) ) {
		return;
	}

	$post_types = get_post_types_settings();

	if (
		isset( $post_types[ $wp_query->query_vars['post_type'] ] ) &&
		\is_array( $post_types[ $wp_query->query_vars['post_type'] ] ) &&
		isset( $post_types[ $wp_query->query_vars['post_type'] ]['tags'] ) &&
		\is_array( $post_types[ $wp_query->query_vars['post_type'] ]['tags'] ) &&
		! empty( $post_types[ $wp_query->query_vars['post_type'] ]['tags']['image'] )
	) {
		echo 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
	}
}

\add_action( 'xmlsf_urlset', __NAMESPACE__ . '\image_schema' );

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
