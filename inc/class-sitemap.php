<?php
/**
 * XMLSF Sitemap CLASS
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF;

/**
 * XMLSF Sitemap CLASS
 */
abstract class Sitemap {
	/**
	 * Sitemap slug
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * Uses core server?
	 *
	 * @var null|bool
	 */
	public $server_type;

	/**
	 * Post types included in sitemap index
	 *
	 * @var array
	 */
	protected $post_types;

	/**
	 * Post types included in sitemap index
	 *
	 * @var array
	 */
	protected $post_type_settings;

	/**
	 * Front pages
	 *
	 * @var null|array $frontpages
	 */
	public $frontpages = null;

	/**
	 * Blog pages
	 *
	 * @var null/array $blogpages
	 */
	public $blogpages = null;

	/**
	 * Get post types and their settings.
	 *
	 * @since 5.4
	 *
	 * @param string $post_type Post type.
	 * @return array
	 */
	public function post_type_settings( $post_type = '' ) {
		if ( null === $this->post_type_settings ) {
			$this->post_type_settings = (array) \get_option( 'xmlsf_post_type_settings', get_default_settings( 'post_type_settings' ) );
		}

		if ( empty( $post_type ) ) {
			return $this->post_type_settings;
		}

		return ! empty( $this->post_type_settings[ $post_type ] ) ? (array) $this->post_type_settings[ $post_type ] : array();
	}

	/**
	 * Get post types.
	 *
	 * @since 5.6
	 *
	 * @return array
	 */
	public function get_post_types() {
		if ( null === $this->post_types ) {
			$public_post_types    = \get_post_types( array( 'public' => true ) );
			$public_post_types    = \array_filter( $public_post_types, 'is_post_type_viewable' );
			$disabled_post_types  = \xmlsf()->disabled_post_types();
			$activated_post_types = \get_option( 'xmlsf_post_types' );

			// Make sure post types are allowed and publicly viewable.
			$post_types = ! empty( $activated_post_types ) ? \array_intersect( (array) $activated_post_types, $public_post_types ) : $public_post_types;
			$post_types = \array_diff( $post_types, $disabled_post_types );

			$this->post_types = $post_types;
		}

		return (array) \apply_filters( 'xmlsf_post_types', $this->post_types );
	}

	/**
	 * Get taxonomies
	 * Returns an array of taxonomy names to be included in the index
	 *
	 * @since 5.0
	 *
	 * @return array
	 */
	public function get_taxonomies() {
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
			foreach ( $this->public_taxonomies() as $name => $label ) {
				$count = \wp_count_terms( $name );
				if ( ! \is_wp_error( $count ) && $count > 0 ) {
					$tax_array[] = $name;
				}
			}
		}

		return $tax_array;
	}

	/**
	 * Get all public (and not empty) taxonomies
	 * Returns an array associated taxonomy object names and labels.
	 *
	 * @since 5.0
	 *
	 * @return array
	 */
	public function public_taxonomies() {
		$tax_array  = array();
		$disabled   = (array) \xmlsf()->disabled_taxonomies();
		$post_types = $this->get_post_types();

		foreach ( $post_types as $post_type ) {
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
	 * Is post type active?
	 *
	 * @since 5.5
	 *
	 * @param int $post_type Post type.
	 * @return bool
	 */
	public function active_post_type( $post_type ) {
		$active_post_types = $this->get_post_types();

		return empty( $active_post_types ) || \in_array( $post_type, $active_post_types, true );
	}

	/**
	 * Get sitemap slug.
	 *
	 * @since 5.5
	 */
	public function slug() {
		return $this->slug;
	}

	/**
	 * Registers sitemap rewrite tags and routing rules.
	 *
	 * @since 5.4.5
	 */
	public function register_rewrites() {}

	/**
	 * Unregisters sitemap rewrite tags and routing rules.
	 *
	 * @since 5.5
	 */
	public function unregister_rewrites() {}

	/**
	 * Are we using date archives?
	 * Returns whether the WordPress any date archives are used or not.
	 *
	 * @since 5.6
	 *
	 * @return bool
	 */
	public function uses_date_archives() {
		if ( 'core' === $this->server_type ) {
			return false;
		}

		foreach ( $this->post_type_settings() as $type => $settings ) {

			if ( $this->active_post_type( $type ) && is_array( $settings ) && ! empty( $settings['archive'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get front pages
	 *
	 * @return array
	 */
	public function get_frontpages() {
		if ( null === $this->frontpages ) {
			$frontpages = array();
			if ( 'page' === \get_option( 'show_on_front' ) ) {
				$frontpage = (int) \get_option( 'page_on_front' );

				/**
				 * Filter frontpages
				 *
				 * Hooked here already:
				 * Polylang and WPML get_translations()
				 */
				$frontpages = (array) \apply_filters( 'xmlsf_frontpages', $frontpage );
			}
			$this->frontpages = $frontpages;
		}

		return $this->frontpages;
	}

	/**
	 * Get blog_pages
	 *
	 * @return array
	 */
	public function get_blogpages() {
		if ( null === $this->blogpages ) {
			$blogpages = array();
			if ( 'page' === \get_option( 'show_on_front' ) ) {
				$blogpage = (int) \get_option( 'page_for_posts' );

				/**
				 * Filter blogpages
				 *
				 * Hooked here already:
				 * Polylang and WPML get_translations()
				 */
				$blogpages = (array) \apply_filters( 'xmlsf_blogpages', $blogpage );
			}
			$this->blogpages = $blogpages;
		}

		return $this->blogpages;
	}

	/**
	 * Get root page(s) priority
	 *
	 * @return array
	 */
	public function get_home_priority() {
		$settings = (array) \get_option( 'xmlsf_post_type_settings', get_default_settings( 'post_type_settings' ) );

		if ( empty( $settings['page'] ) || empty( $settings['page']['priority'] ) ) {
			return '';
		}

		$priority = \apply_filters( 'xmlsf_post_priority', '1.0', 0 ); // TODO make this optional.

		// A final check for limits and round it.
		return namespace\sanitize_number( $priority );
	}

	/**
	 * Get post priority
	 *
	 * @param WP_Post $post Post object.
	 * @return float
	 */
	public function get_post_priority( $post ) {
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
			if ( \xmlsf()->timespan > 0 && ! \is_sticky( $post->ID ) && ! \in_array( $post->ID, $this->get_blogpages(), true ) ) {
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
	public function get_term_priority( $term ) {
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

	/**
	 * User Priority
	 *
	 * @since 5.4
	 *
	 * @param int $user User ID.
	 * @return float
	 */
	public function get_user_priority( $user ) {

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
	public function get_user_modified( $user ) {

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
			$posts   = \get_posts(
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
	 * Post Modified
	 *
	 * @param WP_Post $post Post object.
	 *
	 * @return string|false GMT date
	 */
	public function get_post_modified( $post ) {

		// If blog or home page then simply look for last post date.
		if ( 'page' === $post->post_type && ( in_array( $post->ID, $this->get_blogpages(), true ) || in_array( $post->ID, $this->get_frontpages(), true ) ) ) {

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
	public function get_term_modified( $term ) {

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
	 * @param int    $page     Page number.
	 *
	 * @return string|false
	 */
	public function get_taxonomy_modified( $taxonomy, $page = 1 ) {
		if ( 1 < $page ) {
			// No paged support for taxonomy modified yet.
			return false;
		}

		/**
		 * Pre-filter the return value of get_taxonomy_modified() before the query is run.
		 *
		 * @since 5.5.5
		 *
		 * @param string|false $taxonomymodified The most recent time that a post was modified,
		 *                                       in GMT format, or false. Returning anything
		 *                                       other than false will short-circuit the function.
		 * @param string       $taxonomy         The taxonomy to check.
		 * @param int          $page             The page number of the sitemap.
		 */
		$taxonomymodified = \apply_filters( 'xmlsf_pre_get_taxonomy_modified', false, $taxonomy, $page );

		if ( false !== $taxonomymodified ) {
			return $taxonomymodified; // Return early if already set.
		}

		$obj = \get_taxonomy( $taxonomy );

		if ( false === $obj ) {
			return false;
		}

		foreach ( (array) $obj->object_type as $object_type ) {
			$lastpostdate = \get_lastpostdate( 'GMT', $object_type );
			if ( $lastpostdate && ( ! $taxonomymodified || $lastpostdate > $taxonomymodified ) ) {
				$taxonomymodified = $lastpostdate; // Absolute last modified date.
			}
		}

		return ! empty( $taxonomymodified ) ? $taxonomymodified : false;
	}

	/**
	 * Cache delete on clean_post_cache
	 *
	 * @param int     $post_ID Post ID. Not used anymore.
	 * @param WP_Post $post    Post object.
	 */
	public function clean_post_cache( $post_ID, $post ) {
		// are we moving the post in or out of published status?
		\wp_cache_delete( 'xmlsf_get_archives', 'general' );

		// TODO get year / month here to delete specific keys too !!!!
		$m = \get_date_from_gmt( $post->post_date_gmt, 'Ym' );
		$y = \substr( $m, 0, 4 );

		// clear possible last post modified cache keys.
		\wp_cache_delete( 'lastpostmodified:gmt', 'timeinfo' ); // should be handled by WP core?
		\wp_cache_delete( 'lastpostmodified' . $y . ':gmt', 'timeinfo' );
		\wp_cache_delete( 'lastpostmodified' . $m . ':gmt', 'timeinfo' );
		\wp_cache_delete( 'lastpostmodified' . $y . ':gmt:' . $post->post_type, 'timeinfo' );
		\wp_cache_delete( 'lastpostmodified' . $m . ':gmt:' . $post->post_type, 'timeinfo' );

		// clear possible last post date cache keys.
		\wp_cache_delete( 'lastpostdate:gmt', 'timeinfo' );
		\wp_cache_delete( 'lastpostdate:gmt:' . $post->post_type, 'timeinfo' );

		// clear possible fist post date cache keys.
		\wp_cache_delete( 'firstpostdate:gmt', 'timeinfo' );
		\wp_cache_delete( 'firstpostdate:gmt:' . $post->post_type, 'timeinfo' );
	}

	/**
	 * Update term modified meta, hooked to transition post status
	 *
	 * @param string  $new_status New status.
	 * @param string  $old_status Old status.
	 * @param WP_Post $post       Post object.
	 */
	public function update_term_modified_meta( $new_status, $old_status, $post ) {
		// Bail when...
		if (
			// no status transition or not moving in or out of 'publish' status.
			$old_status === $new_status || ( 'publish' !== $new_status && 'publish' !== $old_status ) ||
			// inactive post type.
			! $this->active_post_type( $post->post_type ) ||
			// no taxonomies activated.
			\in_array( 'taxonomies', (array) \get_option( 'xmlsf_disabled_providers', get_default_settings( 'disabled_providers' ) ), true )
		) {
			return;
		}

		$taxonomies = \get_option( 'xmlsf_taxonomies' );
		if ( empty( $taxonomies ) ) {
			$taxonomies = $this->public_taxonomies();
		}

		$term_ids = array();
		foreach ( (array) $taxonomies as $slug => $name ) {
			$terms = \wp_get_post_terms( $post->ID, $slug, array( 'fields' => 'ids' ) );
			if ( ! \is_wp_error( $terms ) ) {
				$term_ids = \array_merge( $term_ids, $terms );
			}
		}

		$time = \gmdate( 'Y-m-d H:i:s' );

		foreach ( $term_ids as $id ) {
			\update_term_meta( $id, 'term_modified', $time );
		}
	}

	/**
	 * Update user modified meta, hooked to transition post status
	 *
	 * @since 5.4
	 *
	 * @param string  $new_status New status.
	 * @param string  $old_status Old status.
	 * @param WP_Post $post       Post object.
	 */
	public function update_user_modified_meta( $new_status, $old_status, $post ) {
		// Bail when no status transition or not moving in or out of 'publish' status.
		if ( $old_status === $new_status || ( 'publish' !== $new_status && 'publish' !== $old_status ) ) {
			return;
		}

		// TODO: maybe only for activated users.

		$time    = \gmdate( 'Y-m-d H:i:s' );
		$user_id = \get_post_field( 'post_author', $post );

		\update_user_meta( $user_id, 'user_modified', $time );
	}

	/**
	 * Update post images meta, hooked to transition post status
	 *
	 * @since 5.2
	 *
	 * @param string  $new_status New status.
	 * @param string  $old_status Old status. Not used.
	 * @param WP_Post $post       Post object.
	 */
	public function update_post_images_meta( $new_status, $old_status, $post ) {
		// Bail when...
		if (
			// not publishing or updating.
			'publish' !== $new_status ||
			// inactive post type.
			! $this->active_post_type( $post->post_type ) ||
			// no image tags active.
			empty( $this->post_type_settings[ $post->post_type ] ) || empty( $this->post_type_settings[ $post->post_type ]['tags']['image'] )
		) {
			return;
		}

		$which = $this->post_type_settings[ $post->post_type ]['tags']['image'];

		// delete old image meta data.
		\delete_post_meta( $post->ID, '_xmlsf_image_' . $which );

		$this->_add_images_meta( $post, $which );
	}

	/**
	 * Update post comment meta, hooked to transition comment status
	 *
	 * @since 5.2
	 *
	 * @param string     $new_status New status.
	 * @param string     $old_status Old status.
	 * @param WP_Comment $comment    Comment object.
	 */
	public function update_post_comment_meta( $new_status, $old_status, $comment ) {
		// Bail when not publishing or unpublishing.
		if ( $old_status === $new_status || ( 'approved' !== $new_status && 'approved' !== $old_status ) ) {
			return;
		}

		$post_type = \get_post_type( $comment->comment_post_ID );

		// Bail when...
		if (
			// inactive post type.
			! $this->active_post_type( $post_type ) ||
			// comments date irrelevant.
			empty( $this->post_type_settings[ $post_type ] ) || empty( $this->post_type_settings[ $post_type ]['update_lastmod_on_comments'] )
		) {
			return;
		}

		$time = \gmdate( 'Y-m-d H:i:s' );

		\update_post_meta( $comment->comment_post_ID, '_xmlsf_comment_date_gmt', $time );
	}

	/**
	 * Update post comment meta, hooked to comment post
	 *
	 * @since 5.2
	 *
	 * @param int   $comment_id       Comment ID.
	 * @param int   $comment_approved Comment approved status.
	 * @param array $commentdata      Comment data array.
	 */
	public function update_post_comment_meta_cp( $comment_id, $comment_approved, $commentdata ) {
		// Bail when not published.
		if ( 1 !== $comment_approved ) {
			return;
		}

		$post_type = \get_post_type( $commentdata['comment_post_ID'] );

		// Bail when...
		if (
			// inactive post type.
			! $this->active_post_type( $post_type ) ||
			// comments date irrelevant.
			empty( $this->post_type_settings[ $post_type ] ) || empty( $this->post_type_settings[ $post_type ]['update_lastmod_on_comments'] )
		) {
			return;
		}

		// Update comment meta data.
		\update_post_meta( $commentdata['comment_post_ID'], '_xmlsf_comment_date_gmt', $commentdata['comment_date_gmt'] );
	}

	/**
	 * Prefetch all queried posts image and comment meta data
	 *
	 * @since 5.2
	 * @uses global $wp_query
	 */
	public function prefetch_posts_meta() {
		global $wp_query;

		$post_type = $wp_query->get( 'post_type' );

		// Bail if unexpected post type.
		if ( empty( $post_type ) || ! \is_string( $post_type ) || ! $this->active_post_type( $post_type ) ) {
			return;
		}

		$y = $wp_query->get( 'year' );
		$m = $wp_query->get( 'm' );
		if ( empty( $m ) ) {
			$m = 'all';
		}

		// If image tag active then prefetch images.
		if (
			! empty( $this->post_type_settings[ $post_type ] ) &&
			! empty( $this->post_type_settings[ $post_type ]['tags'] ) &&
			is_array( $this->post_type_settings[ $post_type ]['tags'] ) &&
			! empty( $this->post_type_settings[ $post_type ]['tags']['image'] )
		) {
			$primed = (array) \get_transient( 'xmlsf_images_meta_primed' );

			if (
				! isset( $primed[ $post_type ] ) ||
				! \is_array( $primed[ $post_type ] ) ||
				(
					! \in_array( $m, $primed[ $post_type ], true ) &&
					! \in_array( $y, $primed[ $post_type ], true ) &&
					! \in_array( 'all', $primed[ $post_type ], true )
				)
			) {
				// Prime images meta data.
				foreach ( $wp_query->posts as $post ) {
					$this->_add_images_meta( $post, $this->post_type_settings[ $post_type ]['tags']['image'] );
				}

				// Add query to primed array.
				$primed[ $post_type ][] = $m;

				// Update.
				\set_transient( 'xmlsf_images_meta_primed', $primed );
			}
		}

		// If update_lastmod_on_comments active then prefetch comments.
		if ( ! empty( $this->post_type_settings[ $post_type ]['update_lastmod_on_comments'] ) ) {
			$primed = (array) \get_transient( 'xmlsf_comments_meta_primed' );

			if (
				! isset( $primed[ $post_type ] ) ||
				! \is_array( $primed[ $post_type ] ) ||
				(
					! \in_array( $m, $primed[ $post_type ], true ) &&
					! \in_array( $y, $primed[ $post_type ], true ) &&
					! \in_array( 'all', $primed[ $post_type ], true )
				)
			) {
				// Prime comment meta data.
				foreach ( $wp_query->posts as $post ) {
					$this->_add_comment_meta( $post );
				}

				// Add query to primed array.
				$primed[ $post_type ][] = $m;

				// Update.
				\set_transient( 'xmlsf_comments_meta_primed', $primed );
			}
		}
	}

	/**
	 * Set posts images meta data
	 *
	 * @since 5.2
	 * @param WP_Post $post  Post object.
	 * @param string  $which Which.
	 */
	protected function _add_images_meta( $post, $which ) { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		if ( ! \is_object( $post ) || ! isset( $post->ID ) ) {
			return;
		}

		$stored = (array) \get_post_meta( $post->ID, '_xmlsf_image_' . $which, false );

		// Populate images and add as meta data.
		foreach ( $this->images_data( $post, $which ) as $data ) {
			if ( ! \in_array( $data, $stored, true ) ) {
				\add_post_meta( $post->ID, '_xmlsf_image_' . $which, $data );
			}
		}
	}

	/**
	 * Get post attached | featured image(s)
	 *
	 * @param object $post  Post object.
	 * @param string $which Image type.
	 *
	 * @return array
	 */
	public function images_data( $post, $which ) {
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
	 * Set post comment meta data
	 *
	 * @since 5.2
	 * @param array $post Post object.
	 */
	protected function _add_comment_meta( $post ) { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		if ( ! \is_object( $post ) || ! isset( $post->ID ) ) {
			return;
		}

		// Get latest post comment.
		$comments = \get_comments(
			array(
				'status'  => 'approve',
				'number'  => 1,
				'post_id' => $post->ID,
			)
		);

		if ( isset( $comments[0]->comment_date_gmt ) ) {
			\update_post_meta( $post->ID, '_xmlsf_comment_date_gmt', $comments[0]->comment_date_gmt );
		}
	}
}
