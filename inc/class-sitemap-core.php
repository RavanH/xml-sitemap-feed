<?php
/**
 * Core Functions
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF;

/**
 * XMLSF Sitemap Core CLASS.
 */
class Sitemap_Core extends Sitemap {
	/**
	 * CONSTRUCTOR
	 *
	 * Runs on init
	 */
	public function __construct() {
		$this->slug               = \sanitize_key( (string) \apply_filters( 'xmlsf_sitemap_slug', 'wp-sitemap' ) );
		$this->server_type        = 'core';
		$this->post_type_settings = (array) \get_option( 'xmlsf_post_type_settings', array() );

		// Render sitemaps early. Prevents costly extra DB query.
		\add_action( 'parse_request', array( $this, 'render_sitemaps' ), 9 );

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

		// MAIN REQUEST filter. Calls xmlsf_sitemap_loaded action if sitemap request is detected.
		\add_filter( 'request', array( $this, 'filter_request' ) );

		// Add lastmod to index.
		\add_filter( 'wp_sitemaps_index_entry', array( $this, 'index_entry' ), 10, 4 );
		// Add lastmod & priority to authors.
		\add_filter( 'wp_sitemaps_users_entry', array( $this, 'users_entry' ), 10, 2 );
		// Add lastmod & priority to terms.
		\add_filter( 'wp_sitemaps_taxonomies_entry', array( $this, 'taxonomies_entry' ), 10, 4 );
		// Add lastmod & priority to posts.
		\add_filter( 'wp_sitemaps_posts_entry', array( $this, 'posts_entry' ), 10, 3 );
		\add_filter( 'wp_sitemaps_posts_show_on_front_entry', array( $this, 'posts_show_on_front_entry' ) );

		// Set url limit.
		\add_filter( 'wp_sitemaps_max_urls', array( $this, 'max_urls' ), 10, 2 );
		// TODO: for post types.

		// Maybe disable taxonomy or author sitemaps.
		\add_filter( 'wp_sitemaps_add_provider', array( $this, 'add_provider' ), 10, 2 );
		// Maybe disable certain post type sitemaps.
		\add_filter( 'wp_sitemaps_post_types', array( $this, 'filter_post_types' ) );
		// Maybe exclude individual posts.
		\add_filter( 'wp_sitemaps_posts_query_args', array( $this, 'posts_query_args' ) );

		// Maybe exclude taxonomies.
		\add_filter( 'wp_sitemaps_taxonomies', array( $this, 'taxonomies' ) );

		// Filter user query arguments.
		\add_filter( 'wp_sitemaps_users_query_args', array( $this, 'users_query_args' ) );

		/**
		 * Add sitemaps.
		 */
		\add_action( 'wp_sitemaps_init', array( $this, 'register_sitemap_providers' ) );

		// Stylesheet.
		\add_filter( 'wp_sitemaps_stylesheet_index_url', array( $this, 'stylesheet_index_url' ) );
		\add_filter( 'wp_sitemaps_stylesheet_url', array( $this, 'stylesheet_url' ) );

		// NGINX HELPER PURGE URLS.
		\add_filter( 'rt_nginx_helper_purge_urls', array( $this, 'nginx_helper_purge_urls' ) );

		// Compatibility hooks.
		$this->compat();    }

	/**
	 * Plugin compatibility hooks and filters.
	 * Called from constructor.
	 */
	public function compat() {
		$active_plugins = (array) \get_option( 'active_plugins', array() );

		// Polylang compatibility.
		if ( in_array( 'polylang/polylang.php', $active_plugins, true ) || in_array( 'polylang-pro/polylang.php', $active_plugins, true ) ) {
			\add_filter( 'xmlsf_blogpages', array( __NAMESPACE__ . '\Compat\Polylang', 'get_translations' ) );
			\add_filter( 'xmlsf_frontpages', array( __NAMESPACE__ . '\Compat\Polylang', 'get_translations' ) );
			\add_action( 'xmlsf_sitemap_loaded', array( __NAMESPACE__ . '\Compat\Polylang', 'request_actions' ) );
			\add_action( 'xmlsf_register_sitemap_provider', array( __NAMESPACE__ . '\Compat\Polylang', 'remove_replace_provider' ) );
			\add_action( 'xmlsf_register_sitemap_provider_after', array( __NAMESPACE__ . '\Compat\Polylang', 'add_replace_provider' ) );
			\add_filter( 'xmlsf_pre_get_lastpostmodified', array( __NAMESPACE__ . '\Compat\Polylang', 'lastpostmodified' ), 10, 3 );
			\add_filter( 'xmlsf_pre_get_taxonomy_modified', array( __NAMESPACE__ . '\Compat\Polylang', 'taxonomy_modified' ), 10, 3 );

			// Rendering the sitemap early causes issues with language home pages.
			\remove_action( 'parse_request', array( $this, 'render_sitemaps' ), 9 );
		}

		// WPML compatibility.
		if ( in_array( 'sitepress-multilingual-cms/sitepress.php', $active_plugins, true ) ) {
			// Make sure we get the correct sitemap URL in language context.
			\add_filter( 'xmlsf_sitemap_url', array( __NAMESPACE__ . '\Compat\WPML', 'convert_url' ), 10, 2 );
			// Add sitemap in Robots TXT.
			\add_filter( 'robots_txt', array( __NAMESPACE__ . '\Compat\WPML', 'robots_txt' ), 9 );
		}

		// XMLSM compatibility.
		if ( in_array( 'xml-sitemaps-manager/xml-sitemaps-manager.php', $active_plugins, true ) ) {
			\add_filter( 'plugins_loaded', array( __NAMESPACE__ . '\Compat\XMLSM', 'disable' ), 11 );
		}
	}

	/**
	 * Registers sitemap rewrite tags and routing rules.
	 *
	 * @since 5.4.5
	 */
	public function register_rewrites() {
		global $wp_rewrite;

		if ( ! $wp_rewrite->using_permalinks() || 0 === strpos( \get_option( 'permalink_structure' ), '/index.php' ) ) {
			// Nothing to do.
			return;
		}

		\add_rewrite_rule( '^' . $this->slug() . '\.xml$', 'index.php?sitemap=index', 'top' );
	}

	/**
	 * Unregisters sitemap rewrite tags and routing rules.
	 *
	 * @since 5.5
	 */
	public function unregister_rewrites() {
		global $wp_rewrite;

		if ( ! $wp_rewrite->using_permalinks() || 0 === strpos( \get_option( 'permalink_structure' ), '/index.php' ) ) {
			// Nothing to do.
			return;
		}

		// Compose key.
		$key = '^' . $this->slug() . '\.xml$';
		unset( $wp_rewrite->extra_rules_top[ $key ] );
		unset( $wp_rewrite->extra_rules_top['^wp-sitemap\.xml$'] );
		unset( $wp_rewrite->extra_rules_top['^wp-sitemap\.xsl$'] );
		unset( $wp_rewrite->extra_rules_top['^wp-sitemap-index\.xsl$'] );
		unset( $wp_rewrite->extra_rules_top['^wp-sitemap-([a-z]+?)-([a-z\d_-]+?)-(\d+?)\.xml$'] );
		unset( $wp_rewrite->extra_rules_top['^wp-sitemap-([a-z]+?)-(\d+?)\.xml$'] );
	}

	/**
	 * Get the public XML sitemap url.
	 *
	 * @since 5.5
	 *
	 * @param string $sitemap Sitemap name.
	 *
	 * @return string|false The sitemap URL or false if the sitemap doesn't exist.
	 */
	public function get_sitemap_url( $sitemap = 'index' ) {
		$slug = $this->slug();

		if ( 'index' === $sitemap && 'wp-sitemap' !== $slug && xmlsf()->using_permalinks() ) {
			$sitemap_url = \home_url( $slug . '.xml' );
		} else {
			$sitemap_url = \get_sitemap_url( $sitemap );
		}

		$sitemap_url = \apply_filters( 'xmlsf_sitemap_url', $sitemap_url, $sitemap );

		return \esc_url( $sitemap_url );
	}

	/**
	 * Filter request
	 * Hooked into wp_sitemaps_init.
	 */
	public function register_sitemap_providers() {
		\do_action( 'xmlsf_register_sitemap_provider' );

		// Additional URLs sitemap provider.
		if ( \get_option( 'xmlsf_urls' ) ) {
			\wp_register_sitemap_provider( 'custom', new Sitemaps_Provider_Custom() );
		}
		// External XML Sitemaps provider.
		if ( \get_option( 'xmlsf_custom_sitemaps' ) ) {
			\wp_register_sitemap_provider( 'external', new Sitemaps_Provider_External() );
		}

		\do_action( 'xmlsf_register_sitemap_provider_after' );
	}

	/**
	 * Loads the WordPress XML Sitemap Server
	 *
	 * @see https://core.trac.wordpress.org/ticket/51912
	 *
	 * @since 5.6
	 *
	 * @param  WP $wp             Current WordPress environment instance.
	 * @global WP_Query $wp_query WordPress Query.
	 * @return void
	 */
	public static function render_sitemaps( $wp ) {
		global $wp_query;

		if ( empty( $wp->query_vars['sitemap'] ) && empty( $wp->query_vars['sitemap-stylesheet'] ) ) {
			return;
		}

		// Prepare query variables.
		$query_vars           = $wp_query->query_vars;
		$wp_query->query_vars = $wp->query_vars;

		// Render the sitemap.
		\wp_sitemaps_get_server()->render_sitemaps();

		// Still here? Then it was an invalid sitemap request after all. Undo everything and carry on...
		$wp_query->is_sitemap            = false;
		$wp_query->is_sitemap_stylesheet = false;
		$wp_query->query_vars            = $query_vars;
	}

	/**
	 * Register sitemap providers
	 * Hooked into request filter.
	 *
	 * @param array $request Request.
	 *
	 * @return array $request filtered
	 */
	public function filter_request( $request ) {
		// Short-circuit if request was already filtered by this plugin.
		if ( \xmlsf()->request_filtered ) {
			return $request;
		} else {
			\xmlsf()->request_filtered = true;
		}

		// Short-circuit if request is not a feed, does not start with 'sitemap' or is a news sitemap.
		if ( empty( $request['sitemap'] ) ) {
			return $request;
		}

		/** IT'S A SITEMAP */
		\do_action( 'xmlsf_sitemap_loaded', 'core', $request['sitemap'] );

		/** FILTER HOOK FOR PLUGIN COMPATIBILITIES */
		/**
		 * Developers: add your actions that should run when a sitemap request is with:
		 *
		 * Use add_filter( 'xmlsf_core_request', 'your_filter_function' );
		 *
		 * Filters hooked here already:
		 * XMLSF\Compat\WPML::filter_request()
		 */
		$request = \apply_filters( 'xmlsf_core_request', $request );

		$subtype = isset( $request['sitemap-subtype'] ) ? $request['sitemap-subtype'] : '';

		switch ( $request['sitemap'] ) {

			case 'posts':
				// Try to raise memory limit, context added for filters.
				\wp_raise_memory_limit( 'wp-sitemap-posts-' . $subtype );

				// Prepare dynamic priority calculation.
				if ( $subtype && ! empty( $this->post_type_settings[ $subtype ]['priority'] ) && ! empty( $this->post_type_settings[ $subtype ]['dynamic_priority'] ) ) {
					// Last of this post type modified date in Unix seconds.
					\xmlsf()->lastmodified = \get_date_from_gmt( \get_lastpostmodified( 'GMT', $subtype ), 'U' );
					// Calculate time span, uses get_firstpostdate() function defined in xml-sitemap/inc/functions.php!
					\xmlsf()->timespan = \xmlsf()->lastmodified - \get_date_from_gmt( \get_firstpostdate( 'GMT', $subtype ), 'U' );
					// Total post type comment count.
					\xmlsf()->comment_count = \wp_count_comments()->approved;
					// TODO count comments per post type https://wordpress.stackexchange.com/questions/134338/count-all-comments-of-a-custom-post-type
					// TODO cache this more persistently than wp_cache_set does in https://developer.wordpress.org/reference/functions/wp_count_comments/.
				}
				break;

			case 'taxonomies':
				// Try to raise memory limit, context added for filters.
				\wp_raise_memory_limit( 'wp-sitemap-taxonomies-' . $subtype );
				break;

			case 'users':
				// Try to raise memory limit, context added for filters.
				\wp_raise_memory_limit( 'wp-sitemap-users' );
				break;

			default:
				// Do nothing.
		}

		return $request;
	}

	/**
	 * Filter users query arguments.
	 * Hooked into wp_sitemaps_users_query_args filter.
	 *
	 * @since 5.4
	 *
	 * @param array $args Query arguments.
	 *
	 * @return array
	 */
	public function users_query_args( $args ) {
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
		$args['has_published_posts'] = \apply_filters( 'xmlsf_author_has_published_posts', $args['has_published_posts'] );

		$include = \get_option( 'xmlsf_authors' );
		if ( ! empty( $include ) ) {
			$args['include'] = (array) $include;
		}

		return $args;
	}

	/**
	 * Get lastmod for index entries.
	 * Hooked into wp_sitemaps_index_entry filter.
	 *
	 * @since 5.5.5
	 *
	 * @param string $subtype Subtype.
	 * @param int    $page    Page number.
	 *
	 * @return mixed Lastmod in GMT or false if not found.
	 */
	public function get_lastpostmodified( $subtype, $page = 1 ) {
		if ( 1 < $page ) {
			// No paged support for past modified yet.
			return false;
		}

		/**
		 * Pre-filter the return value of get_lastpostmodified() before the query is run.
		 *
		 * @since 5.5.5
		 *
		 * @param string|false $lastpostmodified The most recent time that a post was modified,
		 *                                       in GMT format, or false. Returning anything
		 *                                       other than false will short-circuit the function.
		 * @param string       $subtype          The post type to check.
		 * @param int          $page             The page number of the sitemap.
		 */
		$lastmodified = \apply_filters( 'xmlsf_pre_get_lastpostmodified', false, $subtype, $page );

		if ( false !== $lastmodified ) {
			return $lastmodified; // Return early if already set.
		}

		$lastpostmodified = \get_lastpostmodified( 'GMT', $subtype );

		if ( 1 === $page && 'page' === $subtype && 'posts' === \get_option( 'show_on_front' ) ) {
			// Get last modified date of the home page.
			$published_front = \get_lastpostdate( 'GMT', 'post' );

			if ( $published_front > $lastpostmodified ) {
				$lastpostmodified = $published_front;
			}
		}

		return $lastpostmodified;
	}


	/**
	 * Add lastmod to index entries.
	 * Hooked into wp_sitemaps_index_entry filter.
	 *
	 * @since 5.4
	 *
	 * @param array  $entry   Entry.
	 * @param string $type    Type.
	 * @param string $subtype Subtype.
	 * @param int    $page    Page number.
	 *
	 * @return array  $entry
	 */
	public function index_entry( $entry, $type, $subtype, $page ) {
		// Skip if we're not doing a sitemap request, can happen in Nginx cache purge for example.
		if ( ! \xmlsf()->is_sitemap ) {
			return $entry;
		}

		// Add lastmod.
		switch ( $type ) {
			case 'post':
				$lastmod = $this->get_lastpostmodified( $subtype, $page );
				if ( $lastmod ) {
					$entry['lastmod'] = \get_date_from_gmt( $lastmod, DATE_W3C );
				}
				break;

			case 'term':
				$lastmod = $this->get_taxonomy_modified( $subtype, $page );
				if ( $lastmod ) {
					$entry['lastmod'] = \get_date_from_gmt( $lastmod, DATE_W3C );
				}
				break;

			// $case 'user':
				// TODO make this xmlsf_author_has_published_posts filter compatible.
			// $lastmod = \get_lastpostdate( 'GMT', 'post' ); // Absolute last post date.
			// if ( $lastmod ) {
			// $entry['lastmod'] = \get_date_from_gmt( $lastmod, DATE_W3C );
			// }
			// break;

			case 'news':
				$options    = (array) \get_option( 'xmlsf_news_tags' );
				$post_types = isset( $options['post_type'] ) && ! empty( $options['post_type'] ) ? (array) $options['post_type'] : array( 'post' );
				foreach ( $post_types as $post_type ) {
					$lastpostdate = \get_lastpostdate( 'GMT', $post_type );
					if ( $lastpostdate ) {
						$lastmod = ! empty( $lastmod ) && $lastmod > $lastpostdate ? $lastmod : $lastpostdate; // Absolute last post date.
					}
				}
				if ( $lastmod ) {
					$entry['lastmod'] = \get_date_from_gmt( $lastmod, DATE_W3C );
				}
				break;

			default:
				// Do nothing.
		}

		return $entry;
	}

	/**
	 * Add priority and lastmod to author entries.
	 * Hooked into wp_sitemaps_users_entry filter.
	 *
	 * @since 5.4
	 *
	 * @param array $entry       Entry.
	 * @param obj   $user_object User object.
	 *
	 * @return array  $entry
	 */
	public function users_entry( $entry, $user_object ) {
		// Add priority.
		$priority = $this->get_user_priority( $user_object );
		if ( $priority ) {
			$entry['priority'] = $priority;
		}
		// Add lastmod.
		$lastmod = $this->get_user_modified( $user_object );
		if ( $lastmod ) {
			$entry['lastmod'] = \get_date_from_gmt( $lastmod, DATE_W3C );
		}
		return $entry;
	}

	/**
	 * Add priority and lastmod to taxonomy entries.
	 * Hooked into wp_sitemaps_taxonomies_entry filter.
	 *
	 * @since 5.4
	 *
	 * @param array    $entry       Entry.
	 * @param int|obj  $term        Either the term ID or the WP_Term object depending on query arguments (WP 5.9).
	 * @param string   $taxonomy    Taxonomy.
	 * @param obj|null $term_object The WP_Term object, available starting WP 6.0 otherwise null.
	 *
	 * @return array     $entry
	 */
	public function taxonomies_entry( $entry, $term, $taxonomy, $term_object = null ) {
		// Make sure we have a WP_Term object.
		if ( null === $term_object ) {
			$term_object = \get_term( $term );
		}

		// Add priority.
		$priority = $this->get_term_priority( $term_object );
		if ( $priority ) {
			$entry['priority'] = $priority;
		}

		// Add lastmod.
		$lastmod = $this->get_term_modified( $term_object );
		if ( $lastmod ) {
			$entry['lastmod'] = \get_date_from_gmt( $lastmod, DATE_W3C );
		}

		return $entry;
	}

	/**
	 * Maybe exclude taxonomies.
	 * Hooked into wp_sitemaps_taxonomies filter.
	 *
	 * @since 5.4
	 *
	 * @param array $taxonomies Taxonomies.
	 *
	 * @return array
	 */
	public function taxonomies( $taxonomies ) {
		$only = \get_option( 'xmlsf_taxonomies' );

		if ( empty( $only ) || ! \is_array( $only ) ) {
			return $taxonomies;
		}

		return \array_filter(
			$taxonomies,
			function ( $tax ) use ( $only ) {
				return \in_array( $tax->name, $only, true );
			}
		);
	}

	/**
	 * Add priority and lastmod to posts entries.
	 * Hooked into wp_sitemaps_posts_entry filter.
	 *
	 * @since 5.4
	 *
	 * @param array  $entry       Entry.
	 * @param obj    $post_object Post object.
	 * @param string $post_type   Post type. Not used.
	 *
	 * @return array
	 */
	public function posts_entry( $entry, $post_object, $post_type ) {
		// Add priority.
		$priority = $this->get_post_priority( $post_object );
		if ( ! empty( $priority ) ) {
			$entry['priority'] = $priority;
		}

		// Add lastmod.
		if ( empty( $entry['lastmod'] ) ) {
			$lastmod = $this->get_post_modified( $post_object );
			if ( $lastmod ) {
				$entry['lastmod'] = \get_date_from_gmt( $lastmod, DATE_W3C );
			}
		}

		return $entry;
	}

	/**
	 * Add priority and lastmod to posts show on front entry.
	 * Hooked into wp_sitemaps_posts_show_on_front_entry filter.
	 *
	 * @since 5.4
	 *
	 * @param array $entry Entry.
	 *
	 * @return array
	 */
	public function posts_show_on_front_entry( $entry ) {
		$priority = $this->get_home_priority();
		if ( $priority ) {
			$entry['priority'] = $priority;
		}

		// Set front blog page lastmod to last published post.
		if ( empty( $entry['lastmod'] ) ) {
			$lastmod = \get_lastpostdate( 'gmt', 'post' );
			if ( $lastmod ) {
				$entry['lastmod'] = \get_date_from_gmt( $lastmod, DATE_W3C );
			}
		}

		return $entry;
	}

	/**
	 * Filter maximum urls per sitemap. Hooked into wp_sitemaps_max_urls filter.
	 *
	 * @since 5.4
	 *
	 * @param int    $max_urls    Max URLs.
	 * @param string $object_type Object type.
	 *
	 * @return int
	 */
	public function max_urls( $max_urls, $object_type ) {
		$defaults = get_default_settings();

		switch ( $object_type ) {
			case 'user':
				$settings = (array) \get_option( 'xmlsf_author_settings' );
				$limit    = ! empty( $settings['limit'] ) ? $settings['limit'] : $defaults['author_settings']['limit'];
				break;

			case 'term':
				$settings = (array) \get_option( 'xmlsf_taxonomy_settings' );
				$limit    = ! empty( $settings['limit'] ) ? $settings['limit'] : $defaults['taxonomy_settings']['limit'];
				break;

			case 'post':
			default:
				$settings = (array) \get_option( 'xmlsf_post_type_settings' );
				$limit    = ! empty( $settings['limit'] ) ? $settings['limit'] : $defaults['post_type_settings']['limit'];
		}

		$max_urls = \is_numeric( $limit ) ? \absint( $limit ) : $max_urls;

		return $max_urls;
	}

	/**
	 * Filter sitemap providers. Hooked into wp_sitemaps_add_provider filter.
	 *
	 * @since 5.4
	 *
	 * @param obj    $provider Sitemap provider.
	 * @param string $name     Sitemap name.
	 *
	 * @return false|obj Provider or false if disabled.
	 */
	public function add_provider( $provider, $name ) {
		$disabled = \get_option( 'xmlsf_disabled_providers', get_default_settings( 'disabled_providers' ) );

		// Match disabled settings.
		if ( ! empty( $disabled ) && \in_array( $name, (array) $disabled, true ) ) {
			return false;
		}

		return $provider;
	}

	/**
	 * Filter post types. Hooked into wp_sitemaps_post_types filter.
	 *
	 * @since 5.4
	 *
	 * @param array $post_types Post types array.
	 *
	 * @return array
	 */
	public function filter_post_types( $post_types ) {
		$enabled_post_types = $this->get_post_types();

		// No disabled post types.
		if ( empty( $enabled_post_types ) ) {
			return $post_types;
		}

		foreach ( $post_types as $name => $pt_obj ) {
			if ( ! in_array( $name, $enabled_post_types, true ) ) {
				unset( $post_types[ $name ] );
			}
		}

		return $post_types;
	}

	/**
	 * Filter post query arguments. Hooked into wp_sitemaps_posts_query_args filter.
	 *
	 * @since 5.4
	 *
	 * @param array $args Arguments.
	 *
	 * @return array
	 */
	public function posts_query_args( $args ) {
		$args['orderby'] = 'modified';
		$args['order']   = 'DESC';

		// Exclude posts.
		$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		array(
			'key'     => '_xmlsf_exclude',
			'compare' => 'NOT EXISTS',
		),
		);

		// Update meta cache in one query instead of many, coming from get_post_meta() in $this->get_post_priority().
		$args['update_post_meta_cache'] = true;

		return $args;
	}

	/**
	 * Get alternative stylesheet URL. Hooked into wp_sitemaps_stylesheet_url filter.
	 *
	 * @since 5.4
	 *
	 * @param string $url URL.
	 *
	 * @return string
	 */
	public function stylesheet_url( $url ) {
		// TODO make this optional: get_option( 'xmlsf_core_sitemap_stylesheet' )
		// TODO make these match sitemap type.
		$url = namespace\get_stylesheet_url( 'posttype' );

		return $url;
	}

	/**
	 * Get alternative index stylesheet URL. Hooked into wp_sitemaps_stylesheet_index_url filter.
	 *
	 * @since 5.4
	 *
	 * @param string $url URL.
	 *
	 * @return string
	 */
	public function stylesheet_index_url( $url ) {
		// TODO make this optional: get_option( 'xmlsf_core_sitemap_stylesheet' ).
		$url = namespace\get_stylesheet_url();

		return $url;
	}

	/**
	 * Nginx helper purge urls
	 * adds sitemap urls to the purge array.
	 *
	 * @since 5.4
	 *
	 * @param array $urls     URLs.
	 * @param bool  $wildcard Allow wildcard. Default false.
	 *
	 * @return array $urls
	 */
	public function nginx_helper_purge_urls( $urls = array(), $wildcard = false ) {
		$slug = $this->slug();

		if ( $wildcard ) {
			// Wildcard allowed, this makes everything simple.
			$urls[] = '/' . $slug . '*.xml';
		} else {
			// No wildcard, go through the motions.
			$urls[] = '/' . $slug . '.xml';
			$urls[] = '/' . $slug . '-custom.xml';

			// TODO use wp_get_sitemap_providers for array of provider names (where array key is provider name)
			// then use $provider->get_sitemap_type_data() for nested arrays with max number of sitemaps for each subtype
			// then use that data to build urls... /wp-sitemap-PROVIDER-SUBTYPENAME-PAGENUM++.xml.

			$sitemaps = \wp_sitemaps_get_server();
			foreach ( $sitemaps->index->get_sitemap_list() as $sitemap ) {
				// Add each element loc value.
				$urls[] = \wp_parse_url( $sitemap['loc'], PHP_URL_PATH );
			}
		}

		\do_action( 'xmlsf_nginx_helper_purge_urls', $urls );

		return $urls;
	}
}
