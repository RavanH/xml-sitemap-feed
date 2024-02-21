<?php
/**
 * XMLSF Sitemap News CLASS
 *
 * @package XML Sitemap & Google News
 */

/**
 * XMLSF Sitemap News CLASS
 */
class XMLSF_Sitemap_News {
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
		'regex' => 'sitemap-news\.xml(\.gz)?$',
		'query' => '?feed=sitemap-news$matches[1]',
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
		add_filter( 'request', array( $this, 'filter_request' ), 0 );

		// NGINX HELPER PURGE URLS.
		add_filter( 'rt_nginx_helper_purge_urls', array( $this, 'nginx_helper_purge_urls' ) );

		// Add nnes sitemap to the index.
		add_filter( 'xmlsf_sitemap_index_after', array( $this, 'news_in_index' ) );
	}

	/**
	 * Registers sitemap rewrite tags and routing rules.
	 *
	 * @since 5.4.5
	 */
	public function register_rewrites() {
		// Register news sitemap provider route.
		add_rewrite_rule(
			'^sitemap-news\.xml(\.gz)?$',
			'index.php?feed=sitemap-news$matches[1]',
			'top'
		);
	}

	/**
	 * Add Google News sitemap to the sitemap index
	 */
	public function news_in_index() {
		$url        = xmlsf_sitemap_url( 'news' );
		$options    = get_option( 'xmlsf_news_tags' );
		$post_types = isset( $options['post_type'] ) && ! empty( $options['post_type'] ) ? (array) $options['post_type'] : array( 'post' );
		foreach ( $post_types as $post_type ) {
			$lastpostdate = get_date_from_gmt( get_lastpostdate( 'GMT', $post_type ), DATE_W3C );
			$lastmod      = isset( $lastmod ) && $lastmod > $lastpostdate ? $lastmod : $lastpostdate; // Absolute last post date.
		}
		echo '<sitemap><loc>' . esc_xml( $url ) . '</loc>';
		if ( isset( $lastmod ) ) {
			echo '<lastmod>' . esc_xml( $lastmod ) . '</lastmod>';
		}
		echo '</sitemap>' . PHP_EOL;
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
		if ( empty( $request['feed'] ) || strpos( $request['feed'], 'sitemap-news' ) !== 0 ) {
			return $request;
		}

		/** IT'S A NEWS SITEMAP */

		// Set the sitemap conditional flags.
		$xmlsf->is_sitemap = true;
		$xmlsf->is_news    = true;

		// Don't go redirecting anything now..
		remove_action( 'template_redirect', 'redirect_canonical' );

		// Save a few db queries.
		add_filter( 'split_the_query', '__return_false' );

		// Include public functions.
		require_once XMLSF_DIR . '/inc/functions-public.php';
		require_once XMLSF_DIR . '/inc/functions-public-sitemap-news.php';

		// Make sure we have the proper locale setting for calculations.
		setlocale( LC_NUMERIC, 'C' );

		// Disable caching.
		defined( 'DONOTCACHEPAGE' ) || define( 'DONOTCACHEPAGE', true );
		defined( 'DONOTCACHEDB' ) || define( 'DONOTCACHEDB', true );

		/** COMPRESSION */

		// Check for gz request.
		if ( substr( $request['feed'], -3 ) === '.gz' ) {
			// Pop that .gz.
			$request['feed'] = substr( $request['feed'], 0, -3 );
			// Verify/apply compression settings.
			xmlsf_output_compression();
		}

		/** PREPARE TO LOAD TEMPLATE */
		add_action(
			'do_feed_' . $request['feed'],
			'xmlsf_load_template',
			10,
			2
		);

		/** MODIFY REQUEST PARAMETERS */
		$request['post_status']   = 'publish';
		$request['no_found_rows'] = true; // found rows calc is slow and only needed for pagination.

		/** FILTER HOOK FOR PLUGIN COMPATIBILITIES */
		$request = apply_filters( 'xmlsf_news_request', $request );

		/**
		 * Developers
		 *
		 * Add your actions that should run when a news sitemap request is found with: add_filter( 'xmlsf_news_request', 'your_filter_function' );
		 *
		 * Filters hooked here already:
		 * xmlsf_polylang_request - Polylang compatibility
		 * xmlsf_wpml_request - WPML compatibility
		 * xmlsf_bbpress_request - bbPress compatibility
		 */

		// No caching.
		$request['cache_results'] = false;

		// Post type(s).
		$options              = (array) get_option( 'xmlsf_news_tags' );
		$post_types           = ! empty( $options['post_type'] ) ? $options['post_type'] : array( 'post' );
		$post_types           = apply_filters( 'xmlsf_news_post_types', $post_types );
		$request['post_type'] = $post_types;

		// Categories.
		if ( is_array( $options ) && isset( $options['categories'] ) && is_array( $options['categories'] ) ) {
			$request['cat'] = implode( ',', $options['categories'] );
		}

		// Set up query filters.
		$live = false;
		foreach ( $post_types as $post_type ) {
			if ( strtotime( get_lastpostdate( 'gmt', $post_type ) ) > strtotime( gmdate( 'Y-m-d H:i:s', strtotime( '-48 hours' ) ) ) ) {
				$live = true;
				break;
			}
		}
		if ( $live ) {
			add_filter(
				'post_limits',
				function () {
					return 'LIMIT 0, 1000';
				}
			);
			add_filter( 'posts_where', 'xmlsf_news_filter_where', 10, 1 );
		} else {
			add_filter(
				'post_limits',
				function () {
					return 'LIMIT 0, 1';
				}
			);
		}

		/** GENERAL MISC. PREPARATIONS */

		// Prevent public errors breaking xml.
		@ini_set( 'display_errors', 0 ); // phpcs:ignore WordPress.PHP.IniSet.display_errors_Disallowed

		// Remove filters to prevent stuff like cdn urls for xml stylesheet and images.
		remove_all_filters( 'plugins_url' );
		remove_all_filters( 'wp_get_attachment_url' );
		remove_all_filters( 'image_downsize' );

		// Remove actions that we do not need.
		remove_all_actions( 'widgets_init' );
		remove_all_actions( 'wp_footer' );

		return $request;
	}

	/**
	 * Add sitemap rewrite rules
	 *
	 * Hooked into rewrite_rules_array filter
	 *
	 * @param array $rewrite_rules Rewrite rules.
	 *
	 * @return array
	 */
	public function rewrite_rules( $rewrite_rules ) {
		global $wp_rewrite;

		$rewrite_rules = array_merge( array( $this->rewrite_rules['regex'] => $wp_rewrite->index . $this->rewrite_rules['query'] ), $rewrite_rules );

		return $rewrite_rules;
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
