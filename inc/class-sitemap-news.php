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
	 * Sitemap index name
	 *
	 * @var string
	 */
	private $sitemap;

	/**
	 * Rewrite rules
	 *
	 * @var array
	 */
	public $rewrite_rules = array(
		'regex' => 'sitemap-news\.xml$',
		'query' => '?feed=sitemap-news',
	);

	/**
	 * CONSTRUCTOR
	 * Runs on init
	 *
	 * @param string $sitemap Sitemap slug.
	 */
	public function __construct( $sitemap = 'sitemap-news.xml' ) {
		$this->sitemap = $sitemap;

		$this->register_rewrites();

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
		// Register news sitemap provider route.
		\add_rewrite_rule(
			'^sitemap-news\.xml$',
			'index.php?feed=sitemap-news',
			'top'
		);
	}

	/**
	 * Add Google News sitemap to the plugin sitemap index
	 */
	public function news_in_plugin_index() {
		$url        = namespace\sitemap_url( 'news' );
		$options    = \get_option( 'xmlsf_news_tags' );
		$post_types = isset( $options['post_type'] ) && ! empty( $options['post_type'] ) ? (array) $options['post_type'] : array( 'post' );
		foreach ( $post_types as $post_type ) {
			$lastpostdate = \get_date_from_gmt( \get_lastpostdate( 'GMT', $post_type ), DATE_W3C );
			$lastmod      = isset( $lastmod ) && $lastmod > $lastpostdate ? $lastmod : $lastpostdate; // Absolute last post date.
		}
		echo '<sitemap><loc>' . \esc_xml( $url ) . '</loc>';
		if ( isset( $lastmod ) ) {
			echo '<lastmod>' . \esc_xml( $lastmod ) . '</lastmod>';
		}
		echo '</sitemap>' . PHP_EOL;
	}

	/**
	 * Add Google News sitemap to the core sitemap index
	 */
	public function news_in_core_index() {
		// Polylang compatibility: prevent sitemap translations.
		global $polylang;
		$pll_removed = isset( $polylang ) && \is_object( $polylang->sitemaps ) ? \remove_filter( 'wp_sitemaps_add_provider', array( $polylang->sitemaps, 'replace_provider' ) ) : false;

		\wp_register_sitemap_provider( 'news', new Sitemaps_Provider_News() );

		// Re-add Polylang filter.
		$pll_removed && \add_filter( 'wp_sitemaps_add_provider', array( $polylang->sitemaps, 'replace_provider' ) );
	}

	/**
	 * Filter request
	 *
	 * @param array $request The request.
	 *
	 * @return array $request Filtered request.
	 */
	public function filter_request( $request ) {

		global $xmlsf, $wp_rewrite;

		// Short-circuit if request was already filtered by this plugin.
		if ( $xmlsf->request_filtered_news ) {
			return $request;
		} else {
			$xmlsf->request_filtered_news = true;
		}

		// Short-circuit if request is not a feed or it does not start with 'sitemap-news'.
		if ( empty( $request['feed'] ) || \strpos( $request['feed'], 'sitemap-news' ) !== 0 ) {
			return $request;
		}

		/** IT'S A NEWS SITEMAP */

		// Set the sitemap conditional flags.
		$xmlsf->is_sitemap = true;
		$xmlsf->is_news    = true;

		// Don't go redirecting anything now..
		\remove_action( 'template_redirect', 'redirect_canonical' );

		// Save a few db queries.
		\add_filter( 'split_the_query', '__return_false' );

		// Make sure we have the proper locale setting for calculations.
		\setlocale( LC_NUMERIC, 'C' );

		// Disable caching.
		\defined( 'DONOTCACHEPAGE' ) || \define( 'DONOTCACHEPAGE', true );
		\defined( 'DONOTCACHEDB' ) || \define( 'DONOTCACHEDB', true );

		// Prepare headers.
		add_filter( 'wp_headers', __NAMESPACE__ . '\headers' );

		/** PREPARE TO LOAD TEMPLATE */
		\add_action(
			'do_feed_' . $request['feed'],
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
		 * Filters hooked here already:
		 * XMLSF\polylang_request - Polylang compatibility
		 * XMLSF\wpml_request - WPML compatibility
		 * XMLSF\bbpress_request - bbPress compatibility
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
			if ( \strtotime( \get_lastpostdate( 'gmt', $post_type ) ) > \strtotime( \gmdate( 'Y-m-d H:i:s', \strtotime( '-48 hours' ) ) ) ) {
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

		/** GENERAL MISC. PREPARATIONS */

		// Prevent public errors breaking xml.
		@\ini_set( 'display_errors', 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.PHP.IniSet.display_errors_Disallowed

		// Remove filters to prevent stuff like cdn urls for xml stylesheet and images.
		\remove_all_filters( 'plugins_url' );
		\remove_all_filters( 'wp_get_attachment_url' );
		\remove_all_filters( 'image_downsize' );

		// Remove actions that we do not need.
		\remove_all_actions( 'widgets_init' );
		\remove_all_actions( 'wp_footer' );

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
