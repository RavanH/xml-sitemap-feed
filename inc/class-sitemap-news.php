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
	private $slug;

	/**
	 * CONSTRUCTOR
	 *
	 * Runs on init
	 */
	public function __construct() {
		$this->slug = \sanitize_key( (string) \apply_filters( 'xmlsf_sitemap_news_slug', 'sitemap-news' ) );

		// MAIN REQUEST filter.
		\add_filter( 'request', array( $this, 'filter_request' ), 0 );

		// NGINX HELPER PURGE URLS.
		\add_filter( 'rt_nginx_helper_purge_urls', array( $this, 'nginx_helper_purge_urls' ) );

		// Add news sitemap to the index.
		\add_filter( 'xmlsf_sitemap_index_after', array( $this, 'news_in_plugin_index' ) );
		\add_action( 'wp_sitemaps_init', array( $this, 'news_in_core_index' ), 11 );

		\add_filter( 'nocache_headers', array( $this, 'news_nocache_headers' ) );

		\add_filter( 'xmlsf_news_language', array( $this, 'parse_language_string' ), 99 );

		// Add sitemap in Robots TXT.
		add_filter( 'robots_txt', array( $this, 'robots_txt' ), 9 );
	}

	/**
	 * Registers sitemap rewrite tags and routing rules.
	 *
	 * @since 5.4.5
	 */
	public function register_rewrites() {
		global $wp_rewrite;

		if ( ! $wp_rewrite->using_permalinks() || 0 === strpos( get_option( 'permalink_structure' ), '/index.php' ) ) {
			// Nothing to do.
			return;
		}

		\add_rewrite_rule( '^' . $this->slug() . '\.xml$', 'index.php?feed=sitemap-news', 'top' );
	}

	/**
	 * Unregisters sitemap rewrite tags and routing rules.
	 *
	 * @since 5.5
	 */
	public function unregister_rewrites() {
		global $wp_rewrite;

		if ( ! $wp_rewrite->using_permalinks() || 0 === strpos( get_option( 'permalink_structure' ), '/index.php' ) ) {
			// Nothing to do.
			return;
		}

		unset( $wp_rewrite->extra_rules_top[ '^' . $this->slug() . '\.xml$' ] );
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
		$slug = $this->slug();

		if ( xmlsf()->using_permalinks() ) {
			$basename = $slug . '.xml';
		} else {
			$basename = '?feed=' . $slug;
		}

		$sitemap_url = \apply_filters( 'xmlsf_sitemap_news_url', \home_url( $basename ) );

		return \esc_url( $sitemap_url );
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

		\add_filter( 'post_limits', array( $this, 'post_limits' ) );

		\add_filter( 'posts_where', array( $this, 'posts_where' ), 10, 1 );

		return $request;
	}

	/**
	 * Response headers filter
	 * Does not check if we are really in a sitemap feed.
	 *
	 * @param array $headers The headers array.
	 *
	 * @return array
	 */
	public function news_nocache_headers( $headers ) {
		// Prevent proxy caches serving a cached news sitemap.
		$headers['Cache-Control'] .= ', no-store';

		return $headers;
	}

	/**
	 * Filter post LIMIT
	 *
	 * Max 1000 posts
	 */
	public function post_limits() {
		return 'LIMIT 0, 1000';
	}

	/**
	 * Filter news WHERE
	 * only posts from the last 48 hours
	 *
	 * @param string $where DB Query where clause.
	 *
	 * @return string
	 */
	public function posts_where( $where = '' ) {
		$hours  = (int) \apply_filters( 'xmlsf_news_hours_old', 48 );
		$hours  = \XMLSF\sanitize_number( $hours, 1, 168, 0 );
		$where .= ' AND post_date_gmt > \'' . \gmdate( 'Y-m-d H:i:s', \strtotime( '-' . $hours . ' hours' ) ) . '\'';

		return $where;
	}

	/**
	 * Parse language string into two or three letter ISO 639 code.
	 *
	 * @param string $lang Unformatted language string.
	 *
	 * @return string
	 */
	public function parse_language_string( $lang ) {
		// Lower case, no tags.
		$lang = \convert_chars( \strtolower( \wp_strip_all_tags( $lang ) ) );

		// Convert underscores.
		$lang = \str_replace( '_', '-', $lang );

		// No hyphens except...
		if ( \strpos( $lang, '-' ) ) :
			if ( 0 === \strpos( $lang, 'zh' ) ) {
				$lang = \strpos( $lang, 'hk' ) || \strpos( $lang, 'tw' ) || \strpos( $lang, 'hant' ) ? 'zh-tw' : 'zh-cn';
			} else {
				// Explode on hyphen and use only first part.
				$expl = \explode( '-', $lang );
				$lang = $expl[0];
			}
		endif;

		// Make sure it's max 3 letters.
		$lang = \substr( $lang, 0, 2 );

		return $lang;
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
		$slug = $this->slug();

		$urls[] = '/' . $slug . '.xml';

		return $urls;
	}

	/**
	 * Filter robots.txt rules
	 *
	 * @since 5.5
	 *
	 * @param string $output Output.
	 * @return string
	 */
	public function robots_txt( $output ) {
		return $output . PHP_EOL . 'Sitemap: ' . $this->get_sitemap_url() . PHP_EOL;
	}
}
