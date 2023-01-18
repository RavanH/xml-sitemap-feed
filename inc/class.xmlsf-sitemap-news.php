<?php
/* ------------------------------
 *    XMLSF Sitemap News CLASS
 * ------------------------------ */

class XMLSF_Sitemap_News
{
	/**
	 * Sitemap index name
	 * @var string
	 */
	private $sitemap = 'sitemap-news.xml';

	/**
	 * Rewrite rules
	 * @var array
	 */
	public $rewrite_rules = array(
		'regex' => 'sitemap-news\.xml(\.gz)?$',
		'query' => '?feed=sitemap-news$matches[1]'
	);

	/**
	 * CONSTRUCTOR
	 * Runs on init
	 */

	function __construct( $sitemap )
	{
		if ( $sitemap ) $this->sitemap = $sitemap;

		// Rewrite rules filter.
		add_filter( 'rewrite_rules_array', array( $this, 'rewrite_rules' ), 99, 1 );

		// PINGING
		add_action( 'transition_post_status', array( $this, 'do_ping' ), 999, 3 );

		// MAIN REQUEST filter.
		add_filter( 'request', array( $this, 'filter_request' ), 0 );

		// NGINX HELPER PURGE URLS
		add_filter( 'rt_nginx_helper_purge_urls', array( $this, 'nginx_helper_purge_urls' ) );
	}

	/**
	 * Filter request
	 *
	 * @param array $request
	 *
	 * @return array $request filtered
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
		$xmlsf->is_news = true;

		// Set rewrite trailingslash to false.
		$wp_rewrite->use_trailing_slashes = false;

		// Save a few db queries.
		add_filter( 'split_the_query', '__return_false' );

		// Include public functions.
		require_once XMLSF_DIR . '/inc/functions.public.php';
		require_once XMLSF_DIR . '/inc/functions.public-sitemap-news.php';

		// Make sure we have the proper locale setting for calculations.
		setlocale( LC_NUMERIC, 'C' );

		// Generator comments.
		add_action( 'xmlsf_generator', 'xmlsf_generator' );

		// REPSONSE HEADERS filtering.
		add_filter( 'wp_headers', 'xmlsf_headers' );

		// Language filters.
		add_filter( 'xmlsf_news_language', 'xmlsf_polylang_post_language_filter', 10, 2 );
		add_filter( 'xmlsf_news_language', 'xmlsf_wpml_post_language_filter', 10, 3 );
		add_filter( 'xmlsf_news_language', 'xmlsf_parse_language_string', 99 );

		// Disable caching.
		add_filter( 'nocache_headers', 'xmlsf_news_nocache_headers' );
		defined('DONOTCACHEPAGE') || define('DONOTCACHEPAGE', true);
		defined('DONOTCACHEDB') || define('DONOTCACHEDB', true);

		/** COMPRESSION */

		// check for gz request
		if ( substr($request['feed'], -3) == '.gz' ) {
			// pop that .gz
			$request['feed'] = substr($request['feed'], 0, -3);
			// verify/apply compression settings
			xmlsf_output_compression();
		}

		/** PREPARE TO LOAD TEMPLATE */

		add_action (
			'do_feed_' . $request['feed'],
			'xmlsf_load_template',
			10,
			2
		);

		/** MODIFY REQUEST PARAMETERS */

		$request['post_status'] = 'publish';
		$request['no_found_rows'] = true; // found rows calc is slow and only needed for pagination

		/** FILTER HOOK FOR PLUGIN COMPATIBILITIES */
		$request = apply_filters( 'xmlsf_news_request', $request );

		/**
		 * Developers: add your actions that should run when a news sitemap request is found with:
		 *
		 * add_filter( 'xmlsf_news_request', 'your_filter_function' );
		 *
		 * Filters hooked here already:
		 * xmlsf_polylang_request - Polylang compatibility
		 * xmlsf_wpml_request - WPML compatibility
		 * xmlsf_bbpress_request - bbPress compatibility
		 */

		// No caching.
		$request['cache_results'] = false;

		// Post type(s).
		$options = (array) get_option( 'xmlsf_news_tags' );
		$post_types = ! empty( $options['post_type'] ) ? $options['post_type'] : array('post');
		$post_types = apply_filters( 'xmlsf_news_post_types', $post_types );
		$request['post_type'] = $post_types;

		// Categories.
		if ( is_array($options) && isset($options['categories']) && is_array($options['categories']) ) {
			$request['cat'] = implode( ',', $options['categories'] );
		}

		// Set up query filters.
		$live = false;
		foreach ( $post_types as $post_type ) {
			if ( strtotime( get_lastpostdate( 'gmt', $post_type ) ) > strtotime( gmdate( 'Y-m-d H:i:s', strtotime('-48 hours') ) ) ) {
				$live = true;
				break;
			}
		}
		if ( $live ) {
			add_filter( 'post_limits', function() { return 'LIMIT 0, 1000';	} );
			add_filter( 'posts_where', 'xmlsf_news_filter_where', 10, 1 );
		} else {
			add_filter( 'post_limits', function() { return 'LIMIT 0, 1'; } );
		}

		/** GENERAL MISC. PREPARATIONS */

		// Prevent public errors breaking xml.
		@ini_set( 'display_errors', 0 );

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
	 * @param array $rewrite_rules
	 * @return array $rewrite_rules
	 */
	public function rewrite_rules( $rewrite_rules ) {
		global $wp_rewrite;

		$rewrite_rules = array_merge( array( $this->rewrite_rules['regex'] => $wp_rewrite->index . $this->rewrite_rules['query'] ), $rewrite_rules );

		return $rewrite_rules;
	}

	/**
	 * Do pings, hooked to transition post status
	 *
	 * @param $new_status
	 * @param $old_status
	 * @param $post
	 */
	public function do_ping( $new_status, $old_status, $post )
	{
		// bail out when already published or not publishing
		if ( $old_status == 'publish' || $new_status != 'publish' ) return;

		// bail out when Google ping not checked
		if ( ! in_array( 'google', (array) get_option( 'xmlsf_ping' ) ) ) return;

		// we're saving from post edit screen
		if ( ! empty( $_POST ) && ! empty( $_POST['action'] ) && 'editpost' == $_POST['action'] ) {
			// bail out when exclude field is checked
			if ( ! empty( $_POST['_xmlsf_news_exclude'] ) ) return;
		} else {
			// fall back on exclude meta data from DB which may be outdated (see bug)
			if ( get_post_meta( $post->ID, '_xmlsf_news_exclude' ) ) return;
		}

		$news_tags = (array) get_option('xmlsf_news_tags');

		// is this an active post type?
		if ( empty( $news_tags['post_type'] ) || ! in_array( $post->post_type, (array) $news_tags['post_type'] ) ) return;

		// are categories limited and is not in correct category?
		if ( ! empty( $news_tags['categories'] ) ) {
			$cats = wp_get_post_categories( $post->ID, array( 'fields' => 'ids' ) );
			$intersect = array_intersect( (array) $cats, (array) $news_tags['categories'] );
			if ( empty( $intersect ) ) return;
		}

		// PING
		//xmlsf_ping( 'google', $this->sitemap, 5 * MINUTE_IN_SECONDS );
		if ( ! wp_next_scheduled( 'xmlsf_news_ping' ) ) {
			wp_schedule_single_event( time() + 5, 'xmlsf_news_ping', array( 'google', $this->sitemap, 5 * MINUTE_IN_SECONDS ) );
		}
	}

	/**
	 * Filter Nginx helper purge urls
	 * adds news sitemap url to the purge array.
	 *
	 * @since 5.4
	 * @param $urls array
	 *
	 * @return $urls array
	 */
	function nginx_helper_purge_urls( $urls = array() ) {

		$urls[] = '/sitemap-news.xml';

		return $urls;
	}
}
