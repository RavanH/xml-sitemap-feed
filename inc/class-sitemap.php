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
	 * Post types included in sitemap index
	 *
	 * @var array
	 */
	protected $post_types = array();

	/**
	 * Post types included in sitemap index
	 *
	 * @var array
	 */
	protected $post_type_settings = array();

	/**
	 * Post types included in sitemap index
	 *
	 * @var array
	 */
	protected $rewrite_rules = array();

	/**
	 * Uses core server?
	 *
	 * @var null|bool
	 */
	protected $uses_core_server;

	/**
	 * Is post type active?
	 *
	 * @since 5.5
	 *
	 * @param int $post_type Post type.
	 * @return bool
	 */
	public function active_post_type( $post_type ) {
		if ( empty( $this->post_types ) || in_array( $post_type, $this->post_types, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get sitemap slug.
	 *
	 * @since 5.5
	 */
	public function slug() {
		$slug = (string) \apply_filters( 'xmlsf_sitemap_slug', $this->slug );

		// Clean filename if altered.
		if ( $this->slug !== $slug ) {
			$slug = \sanitize_key( $slug );
		}

		return ! empty( $slug ) ? $slug : $this->slug;
	}

	/**
	 * Registers sitemap rewrite tags and routing rules.
	 *
	 * @since 5.4.5
	 */
	public function register_rewrites() {
		global $wp_rewrite;

		if ( empty( $this->rewrite_rules ) || ! $wp_rewrite->using_permalinks() || 0 === strpos( get_option( 'permalink_structure' ), '/index.php' ) ) {
			// Nothing to do.
			return;
		}

		foreach ( (array) $this->rewrite_rules as $regex => $query ) {
			\add_rewrite_rule( $regex, $query, 'top' );
		}
	}

	/**
	 * Unregisters sitemap rewrite tags and routing rules.
	 *
	 * @since 5.5
	 */
	public function unregister_rewrites() {
		global $wp_rewrite;

		if ( empty( $this->rewrite_rules ) || ! $wp_rewrite->using_permalinks() || 0 === strpos( get_option( 'permalink_structure' ), '/index.php' ) ) {
			// Nothing to do.
			return;
		}

		foreach ( $this->rewrite_rules as $regex => $query ) {
			\remove_rewrite_rule( $regex, $query, 'top' );
		}
	}

	/**
	 * Are we using the WP core server?
	 * Returns whether the WordPress core sitemap server is used or not.
	 *
	 * @since 5.4
	 *
	 * @return bool
	 */
	public function uses_core_server() {
		if ( null === $this->uses_core_server ) {
			// Sitemap disabled, core server unavailable or user selected Plugin server.
			if ( ! namespace\sitemaps_enabled( 'sitemap' ) || ! \function_exists( 'get_sitemap_url' ) || 'core' !== \get_option( 'xmlsf_server', get_default_settings( 'server' ) ) ) {
				$this->uses_core_server = false;
			} else {
				$this->uses_core_server = true;
			}
		}

		return $this->uses_core_server;
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
			$taxonomies = namespace\public_taxonomies();
		}

		$term_ids = array();
		foreach ( (array) $taxonomies as $slug => $name ) {
			$terms = \wp_get_post_terms( $post->ID, $slug, array( 'fields' => 'ids' ) );
			if ( ! is_wp_error( $terms ) ) {
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

		$stored = (array) \get_post_meta( $post->ID, '_xmlsf_image_' . $which );

		// Populate images and add as meta data.
		foreach ( namespace\images_data( $post, $which ) as $data ) {
			if ( ! \in_array( $data, $stored, true ) ) {
				\add_post_meta( $post->ID, '_xmlsf_image_' . $which, $data );
			}
		}
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
