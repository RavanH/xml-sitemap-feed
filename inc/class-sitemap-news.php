<?php
/**
 * XMLSF Sitemap News CLASS
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF;

/**
 * XMLSF Sitemap News CLASS
 */
class Sitemap_News {
	/**
	 * Sitemap slug
	 *
	 * @var string
	 */
	private $slug = 'sitemap-news';

	/**
	 * CONSTRUCTOR
	 *
	 * Runs on init
	 */
	public function __construct() {
		\add_action( 'init', array( $this, 'register_rewrites' ) );

		// MAIN REQUEST filter.
		\add_filter( 'request', array( $this, 'filter_request' ), 0 );

		// NGINX HELPER PURGE URLS.
		\add_filter( 'rt_nginx_helper_purge_urls', array( $this, 'nginx_helper_purge_urls' ) );

		// Add news sitemap to the index.
		\add_filter( 'xmlsf_sitemap_index_after', array( $this, 'news_in_plugin_index' ) );
		\add_action( 'wp_sitemaps_init', array( $this, 'news_in_core_index' ), 11 );
	}

	/**
	 * Registers sitemap rewrite tags and routing rules.
	 *
	 * @since 5.4.5
	 */
	public function register_rewrites() {
		global $wp_rewrite;

		if ( ! $wp_rewrite->using_permalinks() ) {
			return;
		}

		// Register news sitemap provider route.
		\add_rewrite_rule(
			'^' . $this->slug() . '\.xml$',
			'index.php?feed=sitemap-news',
			'top'
		);
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

		// Unregister news sitemap provider route.
		\remove_rewrite_rule(
			'^' . $this->slug() . '\.xml$',
			'index.php?feed=sitemap-news',
			'top'
		);
	}

	/**
	 * Get sitemap slug.
	 *
	 * @since 5.5
	 */
	public function slug() {
		$slug = (string) \apply_filters( 'xmlsf_sitemap_news_slug', $this->slug );

		// Clean filename if altered.
		if ( $this->slug !== $slug ) {
			$slug = \sanitize_key( $slug );
		}

		return ! empty( $slug ) ? $slug : $this->slug;
	}

	/**
	 * Add Google News sitemap to the plugin sitemap index
	 */
	public function news_in_plugin_index() {
		$url        = $this->get_sitemap_url();
		$options    = \get_option( 'xmlsf_news_tags' );
		$post_types = isset( $options['post_type'] ) && ! empty( $options['post_type'] ) ? (array) $options['post_type'] : array( 'post' );
		foreach ( $post_types as $post_type ) {
			$lastpostdate = \get_lastpostdate( 'GMT', $post_type );
			if ( $lastpostdate ) {
				$lastpostdate = \get_date_from_gmt( $lastpostdate, DATE_W3C );
				$lastmod      = isset( $lastmod ) && $lastmod > $lastpostdate ? $lastmod : $lastpostdate; // Absolute last post date.
			}
		}
		echo '<sitemap><loc>' . \esc_xml( $url ) . '</loc>';
		if ( isset( $lastmod ) ) {
			echo '<lastmod>' . \esc_xml( $lastmod ) . '</lastmod>';
		}
		echo '</sitemap>' . PHP_EOL;
	}

	/**
	 * Get the public XML sitemap url.
	 *
	 * @since 5.5
	 *
	 * @return string The sitemap URL.
	 */
	public function get_sitemap_url() {
		global $wp_rewrite;

		$slug      = $this->slug();
		$index_php = 0 === strpos( get_option( 'permalink_structure' ), '/index.php' ) ? 'index.php' : '';

		if ( $wp_rewrite->using_permalinks() && ! $index_php ) {
			$basename = '/' . $slug . '.xml';
		} else {
			$basename = '/' . $index_php . '?feed=' . $slug;
		}

		return \esc_url( \home_url( $basename ) );
	}

	/**
	 * Add Google News sitemap to the core sitemap index
	 */
	public function news_in_core_index() {
		\do_action( 'xmlsf_register_sitemap_provider', 'news' );

		\wp_register_sitemap_provider( 'news', new Sitemaps_Provider_News() );

		\do_action( 'xmlsf_register_sitemap_provider_after', 'news' );
	}

	/**
	 * Filter request
	 *
	 * @param array $request The request.
	 *
	 * @return array $request Filtered request.
	 */
	public function filter_request( $request ) {
		global $wp_rewrite;

		// Short-circuit if request was already filtered by this plugin.
		if ( \xmlsf()->request_filtered_news ) {
			return $request;
		} else {
			\xmlsf()->request_filtered_news = true;
		}

		// Short-circuit if request is not a feed or it does not start with 'sitemap-news'.
		if ( empty( $request['feed'] ) || 'sitemap-news' !== $request['feed'] ) {
			return $request;
		}

		/** IT'S A NEWS SITEMAP */

		\do_action( 'xmlsf_news_sitemap_loaded' );

		// Set the sitemap conditional flags.
		\xmlsf()->is_news = true;

		// Disable caching.
		\defined( 'DONOTCACHEPAGE' ) || \define( 'DONOTCACHEPAGE', true );
		\defined( 'DONOTCACHEDB' ) || \define( 'DONOTCACHEDB', true );

		/** PREPARE TO LOAD TEMPLATE */
		\add_action(
			'do_feed_sitemap-news',
			'XMLSF\load_template',
			10,
			2
		);

		/** MODIFY REQUEST PARAMETERS */
		$request['post_status']   = 'publish';
		$request['no_found_rows'] = true; // found rows calc is slow and only needed for pagination.

		/** FILTER HOOK FOR PLUGIN COMPATIBILITIES */

		/**
		 * Developers
		 *
		 * Add your actions that should run when a news sitemap request is found with: add_filter( 'xmlsf_news_request', 'your_filter_function' );
		 *
		 * Possible filters hooked here:
		 * XMLSF\Compat/Polylang->filter_request - Polylang compatibility
		 * XMLSF\Compat\WPML->filter_request - WPML compatibility
		 * XMLSF\Compat/BBPress->filter_request - bbPress compatibility
		 */
		$request = \apply_filters( 'xmlsf_news_request', $request );

		// No caching.
		$request['cache_results'] = false;

		// Post type(s).
		$options              = (array) \get_option( 'xmlsf_news_tags' );
		$post_types           = ! empty( $options['post_type'] ) ? $options['post_type'] : array( 'post' );
		$post_types           = \apply_filters( 'xmlsf_news_post_types', $post_types );
		$request['post_type'] = $post_types;

		// Categories.
		if ( \is_array( $options ) && isset( $options['categories'] ) && \is_array( $options['categories'] ) ) {
			$request['cat'] = \implode( ',', $options['categories'] );
		}

		// Set up query filters.
		$live = false;
		foreach ( $post_types as $post_type ) {
			$lastpostdate = \get_lastpostdate( 'gmt', $post_type );
			if ( $lastpostdate && \strtotime( $lastpostdate ) > \strtotime( \gmdate( 'Y-m-d H:i:s', \strtotime( '-48 hours' ) ) ) ) {
				$live = true;
				break;
			}
		}
		if ( $live ) {
			\add_filter(
				'post_limits',
				function () {
					return 'LIMIT 0, 1000';
				}
			);
			\add_filter( 'posts_where', 'XMLSF\news_filter_where', 10, 1 );
		} else {
			\add_filter(
				'post_limits',
				function () {
					return 'LIMIT 0, 1';
				}
			);
		}

		return $request;
	}

	/**
	 * Filter Nginx helper purge urls
	 * adds news sitemap url to the purge array.
	 *
	 * @since 5.4
	 *
	 * @param array $urls URLs array.
	 *
	 * @return array
	 */
	public function nginx_helper_purge_urls( $urls = array() ) {
		$urls[] = '/sitemap-news.xml';
		return $urls;
	}
}
