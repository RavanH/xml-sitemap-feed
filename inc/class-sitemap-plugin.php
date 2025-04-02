<?php
/**
 * Plugin Sitemap
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF;

/**
 * XMLSF Sitemap Plugin CLASS.
 */
class Sitemap_Plugin extends Sitemap {
	/**
	 * CONSTRUCTOR
	 *
	 * Runs on init
	 */
	public function __construct() {
		$this->slug = 'sitemap';
		$post_types = \get_option( 'xmlsf_post_types', array() );
		if ( is_array( $post_types ) ) {
			$this->post_types = $post_types;
		}
		$this->post_type_settings = (array) \get_option( 'xmlsf_post_type_settings', array() );
		$this->rewrite_rules      = array(
			'^' . $this->slug . '\.xml$' => 'index.php?feed=sitemap',
			'^' . $this->slug . '-([a-z]+?)?(-[a-z0-9\-_]+?)?(?:\.([0-9]{4,8}))?(?:\.([0-9]{1,2}))?\.xml$' => 'index.php?feed=sitemap-$matches[1]$matches[2]&m=$matches[3]&w=$matches[4]',
		);

		\add_action( 'init', array( $this, 'register_rewrites' ) );

		// Redirect wp-sitemap.xml requests.
		\add_action( 'template_redirect', array( $this, 'redirect' ), 0 );

		// Cache clearance.
		\add_action( 'clean_post_cache', array( $this, 'clean_post_cache' ), 99, 2 );

		// Update term meta lastmod date.
		\add_action( 'transition_post_status', array( $this, 'update_term_modified_meta' ), 10, 3 );

		// Update user meta lastmod date.
		\add_action( 'transition_post_status', array( $this, 'update_user_modified_meta' ), 10, 3 );

		// Update images post meta.
		\add_action( 'transition_post_status', array( $this, 'update_post_images_meta' ), 10, 3 );

		// Update last comment date post meta.
		\add_action( 'transition_comment_status', array( $this, 'update_post_comment_meta' ), 10, 3 );
		\add_action( 'comment_post', array( $this, 'update_post_comment_meta_cp' ), 10, 3 ); // When comment is not held for moderation.

		// MAIN REQUEST filter.
		\add_filter( 'request', array( $this, 'filter_request' ), 1 );

		// Add index archive data filter.
		\add_filter( 'xmlsf_index_archive_data', array( $this, 'index_archive_data' ), 10, 3 );

		// Add RT Camp Nginx Helper NGINX HELPER PURGE URLS filter.
		\add_filter( 'rt_nginx_helper_purge_urls', array( $this, 'nginx_helper_purge_urls' ) );
	}

	/**
	 * Get the public XML sitemap url.
	 *
	 * @since 5.5
	 *
	 * @param string $sitemap Sitemap name.
	 * @param array  $args    Arguments array:
	 *                        $type - post_type or taxonomy, default false
	 *                        $m    - YYYY, YYYYMM, YYYYMMDD
	 *                        $w    - week of the year ($m must be YYYY format)
	 *                        $gz   - bool for GZ extension (triggers compression verification).
	 *
	 * @return string|false The sitemap URL or false if the sitemap doesn't exist.
	 */
	public function get_sitemap_url( $sitemap = 'index', $args = array() ) {
		global $wp_rewrite;

		// Get our arguments.
		$args = \apply_filters(
			'xmlsf_index_url_args',
			\wp_parse_args(
				$args,
				array(
					'type' => false,
					'm'    => false,
					'w'    => false,
				)
			)
		);

		$index_php = 0 === strpos( get_option( 'permalink_structure' ), '/index.php' ) ? 'index.php' : '';

		if ( $wp_rewrite->using_permalinks() && ! $index_php ) {
			// Construct file name.
			$name = $this->slug();
			if ( 'index' !== $sitemap ) {
				$name .= '-' . $sitemap;
				$name .= $args['type'] ? '-' . $args['type'] : '';
				$name .= $args['m'] ? '.' . $args['m'] : '';
				$name .= $args['w'] ? '.' . $args['w'] : '';
			}
			$name .= '.xml';
		} else {
			// Construct request string.
			$name = $index_php . '?feed=sitemap';
			if ( 'index' !== $sitemap ) {
				$name .= '-' . $sitemap;
				$name .= $args['type'] ? '-' . $args['type'] : '';
				$name .= $args['m'] ? '&m=' . $args['m'] : '';
				$name .= $args['w'] ? '&w=' . $args['w'] : '';
			}
		}

		return \esc_url( \trailingslashit( \home_url() ) . $name );
	}

	/**
	 * Filter request
	 *
	 * @param array $request Original request.
	 *
	 * @return array $request Filtered request.
	 */
	public function filter_request( $request ) {
		global $wp_rewrite;

		// Short-circuit if request was already filtered by this plugin.
		if ( \xmlsf()->request_filtered ) {
			return $request;
		} else {
			\xmlsf()->request_filtered = true;
		}

		// Short-circuit if request is not a feed, does not start with 'sitemap' or is a news sitemap.
		if ( empty( $request['feed'] ) || \strpos( $request['feed'], 'sitemap' ) !== 0 || 'sitemap-news' === $request['feed'] ) {
			return $request;
		}

		/** IT'S A SITEMAP */

		$feed = \explode( '-', $request['feed'], 3 );
		$type = isset( $feed[1] ) ? $feed[1] : '';

		\do_action( 'xmlsf_sitemap_loaded', 'plugin', $type );

		/** MODIFY REQUEST PARAMETERS */

		switch ( $type ) {

			case 'posttype':
				if ( ! isset( $feed[2] ) || ! $this->active_post_type( $feed[2] ) ) {
					return $request;
				}

				// Try to raise memory limit, context added for filters.
				\wp_raise_memory_limit( 'sitemap-posttype-' . $feed[2] );

				// Prepare priority calculation.
				if ( ! empty( $this->post_type_settings[ $feed[2] ] ) && ! empty( $this->post_type_settings[ $feed[2] ]['priority'] ) && ! empty( $this->post_type_settings[ $feed[2] ]['dynamic_priority'] ) ) {
					// Last of this post type modified date in Unix seconds.
					\xmlsf()->lastmodified = \get_date_from_gmt( \get_lastpostmodified( 'GMT', $feed[2] ), 'U' );
					// Calculate time span, uses get_firstpostdate() function defined in xml-sitemap/inc/functions.php!
					\xmlsf()->timespan = \xmlsf()->lastmodified - \get_date_from_gmt( get_firstpostdate( 'GMT', $feed[2] ), 'U' );
					// Total post type comment count.
					\xmlsf()->comment_count = \wp_count_comments()->approved;
					// TODO count comments per post type https://wordpress.stackexchange.com/questions/134338/count-all-comments-of-a-custom-post-type
					// TODO cache this more persistently than wp_cache_set does in https://developer.wordpress.org/reference/functions/wp_count_comments/.
				}

				// Setup filters.
				\add_filter(
					'post_limits',
					function () {
						return 'LIMIT 0, 50000';
					}
				);

				// Modify request.
				$request['post_type'] = $feed[2];

				// Prevent term cache update query unless needed for permalinks.
				if ( \strpos( \get_option( 'permalink_structure' ), '%category%' ) === false ) {
					$request['update_post_term_cache'] = false;
				}

				// Make sure to update meta cache for:
				// 1. excluded posts.
				// 2. image data (if activated).
				// 3. lasmod on comments (if activated).
				$request['update_post_meta_cache'] = true;
				break;

			case 'taxonomy':
				$disabled = \get_option( 'xmlsf_disabled_providers', get_default_settings( 'disabled_providers' ) );
				if ( ! isset( $feed[2] ) || ( ! empty( $disabled ) && in_array( 'taxonomies', (array) $disabled, true ) ) ) {
					return $request;
				}

				$taxonomies = \get_option( 'xmlsf_taxonomies' );
				if ( ! empty( $taxonomies ) && ! \in_array( $feed[2], (array) $taxonomies, true ) ) {
					return $request;
				}

				// Try to raise memory limit, context added for filters.
				\wp_raise_memory_limit( 'sitemap-taxonomy-' . $feed[2] );
				// Pass on taxonomy name via request.
				$request['taxonomy'] = $feed[2];
				// Set terms args.
				\add_filter( 'get_terms_args', array( $this, 'set_terms_args' ), 9 );
				break;

			case 'author':
				$disabled = \get_option( 'xmlsf_disabled_providers', get_default_settings( 'disabled_providers' ) );
				if ( ! empty( $disabled ) && \in_array( 'users', (array) $disabled, true ) ) {
					return $request;
				}

				// Set users args.
				\add_filter( 'xmlsf_get_author_args', array( $this, 'set_authors_args' ), 9 );
				// Set user filter for multisite.
				\add_filter( 'xmlsf_skip_user', array( $this, 'skip_deleted_or_spam_authors' ), 10, 2 );
				break;

			default:
				// We're on the index. Do nothing.
		}

		$request['post_status']   = 'publish';
		$request['no_found_rows'] = true; // Found rows calc is slow and only needed for pagination.

		// SPECIFIC REQUEST FILTERING AND PREPARATIONS.

		/** FILTER HOOK FOR PLUGIN COMPATIBILITIES */

		/**
		 * Filters the request.
		 *
		 * Use add_filter( 'xmlsf_request', 'your_filter_function' );
		 *
		 * Possible filters hooked here:
		 * XMLSF\Compat/Polylang->filter_request - Polylang compatibility
		 * XMLSF\Compat\WPML->filter_request - WPML compatibility
		 * XMLSF\Compat/BBPress->filter_request - bbPress compatibility
		 */
		$request = \apply_filters( 'xmlsf_request', $request );

		/** PREPARE TO LOAD TEMPLATE */
		\add_action(
			'do_feed_' . $request['feed'],
			'XMLSF\load_template',
			10,
			2
		);

		return $request;
	}

	/**
	 * Terms arguments filter
	 * Does not check if we are really in a sitemap feed.
	 *
	 * @param array $args Term arguments.
	 *
	 * @return array
	 */
	public function set_terms_args( $args ) {
		// Read more on https://developer.wordpress.org/reference/classes/wp_term_query/__construct/.
		$options = \get_option( 'xmlsf_taxonomy_settings', get_default_settings( 'taxonomy_settings' ) );

		$args['number'] = isset( $options['limit'] ) && \is_numeric( $options['limit'] ) && $options['limit'] > 0 ? \intval( $options['limit'] ) : 2000;

		if ( $args['number'] > 50000 ) {
			$args['number'] = 50000;
		}

		$args['order']                  = 'DESC';
		$args['orderby']                = 'count';
		$args['pad_counts']             = true;
		$args['lang']                   = '';
		$args['hierarchical']           = false;
		$args['suppress_filter']        = true;
		$args['hide_empty']             = true;
		$args['update_term_meta_cache'] = false;

		return $args;
	}

	/**
	 * Authors arguments filter
	 * Does not check if we are really in a sitemap feed.
	 *
	 * @param array $args Author arguments.
	 *
	 * @return array
	 */
	public function set_authors_args( $args ) {
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
		 * @param array Array with post type slugs. Default array('post').
		 *
		 * @return mixed
		 */
		$post_types = \apply_filters( 'xmlsf_author_has_published_posts', $post_types );

		$options = \get_option( 'xmlsf_author_settings', get_default_settings( 'author_settings' ) );

		$args['has_published_posts'] = $post_types;
		$args['number']              = ! empty( $options['limit'] ) && \is_numeric( $options['limit'] ) && $options['limit'] > 0 ? \intval( $options['limit'] ) : 2000;

		if ( $args['number'] > 50000 ) {
			$args['number'] = 50000;
		}

		$include = \get_option( 'xmlsf_authors' );
		if ( ! empty( $include ) ) {
			$args['include'] = (array) $include;
		}

		return $args;
	}

	/**
	 * Exclude spammed or deleted Authors in a multisite environment.
	 * Does not check if we are really in a sitemap feed.
	 *
	 * @param bool $skip Skip or not skip.
	 * @param obj  $user User object.
	 *
	 * @uses is_multisite()
	 *
	 * @return bool
	 */
	public function skip_deleted_or_spam_authors( $skip, $user ) {
		if ( ! \is_multisite() ) {
			return $skip;
		}

		if ( \property_exists( $user, 'deleted' ) && $user->deleted ) {
			return true;
		}

		if ( \property_exists( $user, 'spam' ) && $user->spam ) {
			return true;
		}

		return $skip;
	}

	/**
	 * Post archives data
	 *
	 * @param array  $data         Data.
	 * @param string $post_type    Post type.
	 * @param string $archive_type Archive type.
	 *
	 * @return array $data
	 */
	public function index_archive_data( $data, $post_type = 'post', $archive_type = '' ) {
		return \array_merge( $data, $this->get_index_archive_data( $post_type, $archive_type ) );
	}

	/**
	 * Get post archives data
	 *
	 * @param string $post_type    Post type.
	 * @param string $archive_type Archive type.
	 *
	 * @return array
	 */
	public function get_index_archive_data( $post_type, $archive_type ) {
		global $wpdb;

		$return = array();

		if ( 'weekly' === $archive_type ) :

			$week       = \_wp_mysql_week( '`post_date`' );
			$query      = $wpdb->prepare( "SELECT DISTINCT LPAD(%d,2,'0') AS `week`, YEAR(`post_date`) AS `year`, COUNT(`ID`) AS `posts` FROM %s WHERE `post_type` = %s AND `post_status` = 'publish' GROUP BY YEAR(`post_date`), LPAD(%d,2,'0') ORDER BY `year` DESC, `week` DESC", array( $week, $wpdb->posts, $post_type, $week ) );
			$arcresults = $this->cache_get_archives( $query );

			foreach ( (array) $arcresults as $arcresult ) {
				$url            = $this->get_sitemap_url(
					'posttype',
					array(
						'type' => $post_type,
						'm'    => $arcresult->year,
						'w'    => $arcresult->week,
					)
				);
				$return[ $url ] = \get_date_from_gmt( \get_lastmodified( 'GMT', $post_type, $arcresult->year, $arcresult->week ), DATE_W3C );
			}

		elseif ( 'monthly' === $archive_type ) :

			$query      = $wpdb->prepare( "SELECT YEAR(`post_date`) AS `year`, LPAD(MONTH(`post_date`),2,'0') AS `month`, COUNT(`ID`) AS `posts` FROM $wpdb->posts WHERE `post_type` = %s AND `post_status` = 'publish' GROUP BY YEAR(`post_date`), LPAD(MONTH(`post_date`),2,'0') ORDER BY `year` DESC, `month` DESC", $post_type );
			$arcresults = $this->cache_get_archives( $query );

			foreach ( (array) $arcresults as $arcresult ) {
				$url            = $this->get_sitemap_url(
					'posttype',
					array(
						'type' => $post_type,
						'm'    => $arcresult->year . $arcresult->month,
					)
				);
				$return[ $url ] = \get_date_from_gmt( \get_lastmodified( 'GMT', $post_type, $arcresult->year . $arcresult->month ), DATE_W3C );
			}

		elseif ( 'yearly' === $archive_type ) :

			$query      = $wpdb->prepare( "SELECT YEAR(`post_date`) AS `year`, COUNT(`ID`) AS `posts` FROM $wpdb->posts WHERE `post_type` = %s AND `post_status` = 'publish' GROUP BY YEAR(`post_date`) ORDER BY `year` DESC", $post_type );
			$arcresults = $this->cache_get_archives( $query );

			foreach ( (array) $arcresults as $arcresult ) {
				$url            = $this->get_sitemap_url(
					'posttype',
					array(
						'type' => $post_type,
						'm'    => $arcresult->year,
					)
				);
				$return[ $url ] = \get_date_from_gmt( \get_lastmodified( 'GMT', $post_type, $arcresult->year ), DATE_W3C );
			}

		else :

			$query      = $wpdb->prepare( "SELECT COUNT(ID) AS `posts` FROM $wpdb->posts WHERE `post_type` = %s AND `post_status` = 'publish' ORDER BY `post_date` DESC", $post_type );
			$arcresults = $this->cache_get_archives( $query );

			if ( is_object( $arcresults[0] ) && $arcresults[0]->posts > 0 ) {
				$url            = $this->get_sitemap_url( 'posttype', array( 'type' => $post_type ) );
				$return[ $url ] = \get_date_from_gmt( \get_lastmodified( 'GMT', $post_type ), DATE_W3C );
			}

		endif;

		return $return;
	}

	/**
	 * Get archives from wp_cache
	 *
	 * @param string $sql The prepared query.
	 *
	 * @return array
	 */
	private function cache_get_archives( $sql ) {
		global $wpdb;

		$key    = \md5( $sql );
		$_cache = \wp_cache_get( 'xmlsf_get_archives', 'general' );
		$cache  = false === $_cache ? array() : $_cache;

		if ( ! isset( $cache[ $key ] ) ) {
			$cache[ $key ] = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery
			\wp_cache_set( 'xmlsf_get_archives', $cache, 'general' );
		}

		return $cache[ $key ];
	}

	/**
	 * Nginx helper purge urls
	 * adds sitemap urls to the purge array.
	 *
	 * @param array $urls     URLs array.
	 * @param bool  $wildcard Use wildcard or not.
	 *
	 * @return $urls array
	 */
	public function nginx_helper_purge_urls( $urls = array(), $wildcard = false ) {
		if ( $wildcard ) {
			// Wildcard makes everything simple.
			$urls[] = '/sitemap*.xml';
		} else {
			// No wildcard, go through the motions.
			$urls[] = '/sitemap.xml';
			$urls[] = '/sitemap-author.xml';
			$urls[] = '/sitemap-custom.xml';

			// Add public post types sitemaps.
			$post_types = namespace\get_post_types_settings();
			foreach ( $post_types as $post_type => $settings ) :
				$archive      = isset( $settings['archive'] ) ? $settings['archive'] : '';
				$archive_data = \apply_filters( 'xmlsf_index_archive_data', array(), $post_type, $archive );

				foreach ( $archive_data as $url => $lastmod ) {
					$path = \wp_parse_url( $url, PHP_URL_PATH );
					if ( $path ) {
						$urls[] = $path;
					}
				}
			endforeach;

			// Add public post taxonomies sitemaps.
			$taxonomies = namespace\get_taxonomies();
			foreach ( $taxonomies as $taxonomy ) {
				$path = \wp_parse_url( $this->get_sitemap_url( 'taxonomy', array( 'type' => $taxonomy ) ), PHP_URL_PATH );
				if ( $path ) {
					$urls[] = $path;
				}
			}
		}

		\do_action( 'xmlsf_nginx_helper_purge_urls', $urls );

		return $urls;
	}

	/**
	 * Do WP core sitemap index redirect
	 *
	 * @uses wp_redirect()
	 */
	public function redirect() {
		// Sadly, we cannot get this info from $wp->request.
		if ( ! isset( $_SERVER['REQUEST_URI'] ) || ( 0 !== \strpos( \wp_unslash( $_SERVER['REQUEST_URI'] ), '/wp-sitemap.xml' ) && 0 !== \strpos( \wp_unslash( $_SERVER['REQUEST_URI'] ), '/?sitemap=' ) ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return;
		}

		\wp_safe_redirect( $this->get_sitemap_url(), 301, 'XML Sitemap & Google News for WordPress' );

		exit();
	}
}
