<?php
/**
 * XMLSitemapFeed CLASS
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF;

/**
 * XMLSitemapFeed CLASS
 */
class XMLSitemapFeed {

	/**
	 * Sitemap object
	 *
	 * @var null|obj
	 */
	public $sitemap = null;

	/**
	 * News sitemap object
	 *
	 * @var null|obj
	 */
	public $sitemap_news = null;

	/**
	 * Defaults
	 *
	 * @var array
	 */
	private $defaults = array();

	/**
	 * News defaults
	 * Keep for backward compatibility with XMLSF Advanced News 1.3.5 and earlier.
	 *
	 * @var array
	 */
	public $default_news_tags = array(
		'name'       => '',
		'post_type'  => array( 'post' ),
		'categories' => '',
	);

	/**
	 * Signifies whether the request has been filtered.
	 *
	 * @var bool
	 */
	public $request_filtered = false;

	/**
	 * Signifies whether the current query is for a sitemap feed.
	 *
	 * @var bool
	 */
	public $is_sitemap = false;

	/**
	 * Signifies whether the request has been filtered for news.
	 *
	 * @var bool
	 */
	public $request_filtered_news = false;

	/**
	 * Signifies whether the current query is for a news feed.
	 *
	 * @var bool
	 */
	public $is_news = false;

	/**
	 * Site public scheme
	 *
	 * @var string $domain
	 */
	private $scheme;

	/**
	 * Excluded post types
	 *
	 * @var array
	 */
	private $disabled_post_types = array(
		'attachment',
		'reply', // bbPress.
	);

	/**
	 * Excluded taxonomies
	 *
	 * @var array
	 */
	private $disabled_taxonomies = array(
		'product_shipping_class',
		// 'post_format',
	);

	/**
	 * Maximum number of posts in any taxonomy term
	 *
	 * @var null|int $taxonomy_termmaxposts
	 */
	public $taxonomy_termmaxposts = null;

	/**
	 * Unix last modified date
	 *
	 * @var int $lastmodified
	 */
	public $lastmodified;

	/**
	 * Unix time spanning first post date and last modified date
	 *
	 * @var int $timespan
	 */
	public $timespan = 0;

	/**
	 * Post type total approved comment count
	 *
	 * @var int $comment_count
	 */
	public $comment_count = 0;

	/**
	 * Using permalinks?
	 *
	 * @var null|bool
	 */
	protected $using_permalinks = null;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		// Upgrade/install, maybe...
		$db_version = \get_option( 'xmlsf_version', 0 );
		if ( ! \version_compare( XMLSF_VERSION, $db_version, '=' ) ) {
			require_once \XMLSF_DIR . '/upgrade.php';
		}

		\add_action( 'plugins_loaded', __NAMESPACE__ . '\plugin_compat' );
		\add_filter( 'robots_txt', __NAMESPACE__ . '\robots_txt', 11 );

		// Load sitemap servers.
		$sitemaps = (array) \get_option( 'xmlsf_sitemaps', $this->defaults( 'sitemaps' ) );

		// XML Sitemap?
		if ( ! empty( $sitemaps['sitemap'] ) ) {
				$this->get_server( 'sitemap' );
		} else {
			// Disable core sitemap.
			\add_filter( 'wp_sitemaps_enabled', '__return_false' );
		}

		// Google News sitemap?
		if ( ! empty( $sitemaps['sitemap-news'] ) ) {
			$this->get_server( 'sitemap-news' );
		}

		// Resiter rewrites.
		\add_action( 'init', array( $this, 'register_rewrites' ) );

		// Sitemap hooks.
		\add_action( 'xmlsf_sitemap_loaded', __NAMESPACE__ . '\sitemap_loaded' );
		\add_action( 'xmlsf_news_sitemap_loaded', __NAMESPACE__ . '\sitemap_loaded' );
		\add_action( 'xmlsf_generator', array( $this, 'generator' ) );
	}

	/**
	 * Load sitemap servers
	 *
	 * @param string $sitemap Sitemap name.
	 */
	public function get_server( $sitemap = '' ) {
		if ( empty( $sitemap ) || 'sitemap-news' === $sitemap ) {
			$this->sitemap_news = new Sitemap_News();
		}

		if ( empty( $sitemap ) || 'sitemap' === $sitemap ) {
			if ( \function_exists( 'get_sitemap_url' ) && 'core' === \get_option( 'xmlsf_server', $this->defaults( 'server' ) ) ) {
				$this->sitemap = new Sitemap_Core();
			} else {
				// Replace core sitemap.
				\remove_action( 'init', 'wp_sitemaps_get_server' );

				$this->sitemap = new Sitemap_Plugin();
			}
		}
	}

	/**
	 * Default options
	 *
	 * @return bool
	 */
	public function using_permalinks() {
		if ( null === $this->using_permalinks ) {
			global $wp_rewrite;

			$index_php              = 0 === strpos( get_option( 'permalink_structure' ), '/index.php' ) ? 'index.php' : '';
			$this->using_permalinks = $wp_rewrite->using_permalinks() && ! $index_php;
		}

		return $this->using_permalinks;
	}

	/**
	 * Default options
	 *
	 * @return array
	 */
	public function defaults() {
		if ( empty( $this->defaults ) ) :

			// sitemaps.
			$sitemaps = ( 1 !== (int) \get_option( 'blog_public' ) ) ? array() : array(
				'sitemap' => \class_exists( 'SimpleXMLElement' ) && \function_exists( 'get_sitemap_url' ) ? 'wp-sitemap.xml' : 'sitemap.xml',
			);

			$this->defaults = array(
				'sitemaps'           => $sitemaps,
				'server'             => \class_exists( 'SimpleXMLElement' ) && \function_exists( 'get_sitemap_url' ) ? 'core' : 'plugin',
				'disabled_providers' => array(),
				'post_types'         => array(),
				'post_type_settings' => array(
					'post'  => array(
						'archive'          => 'yearly',
						'priority'         => '',
						'dynamic_priority' => '',
						'tags'             => array(
							'image' => 'attached',
						),
					),
					'page'  => array(
						'priority'         => '',
						'dynamic_priority' => '',
						'tags'             => array(
							'image' => 'attached',
						),
					),
					'limit' => '',
				),
				'taxonomies'         => '',
				'taxonomy_settings'  => array(
					'priority'         => '',
					'dynamic_priority' => '',
					'include_empty'    => '',
					'limit'            => '',
				),
				'authors'            => '',
				'author_settings'    => array(
					'priority' => '',
					'limit'    => '',
				),
				'robots'             => '',
				'urls'               => '',
				'custom_sitemaps'    => '',
				'news_tags'          => $this->default_news_tags,
			);

		endif;

		return $this->defaults;
	}

	/**
	 * Get scheme
	 *
	 * @return string
	 */
	public function scheme() {
		// Scheme to use.
		if ( empty( $this->scheme ) ) {
			$scheme       = \wp_parse_url( home_url(), PHP_URL_SCHEME );
			$this->scheme = $scheme ? $scheme : 'http';
		}

		return $this->scheme;
	}

	/**
	 * Get disabled taxonomies
	 *
	 * @return array
	 */
	public function disabled_taxonomies() {
		return \apply_filters( 'xmlsf_disabled_taxonomies', $this->disabled_taxonomies );
	}

	/**
	 * Get disabled post types
	 *
	 * @return array
	 */
	public function disabled_post_types() {
		return (array) \apply_filters( 'xmlsf_disabled_post_types', $this->disabled_post_types );
	}

	/**
	 * Register rewrites per sitemap
	 *
	 * @param string $sitemap Sitemap.
	 */
	public function register_rewrites( $sitemap = '' ) {
		switch ( $sitemap ) {
			case 'sitemap':
				null !== $this->sitemap && $this->sitemap->register_rewrites();
				break;

			case 'sitemap-news':
				null !== $this->sitemap_news && $this->sitemap_news->register_rewrites();
				break;

			default:
				null !== $this->sitemap_news && $this->sitemap_news->register_rewrites();
				null !== $this->sitemap && $this->sitemap->register_rewrites();
		}
	}

	/**
	 * Unregister rewrites per sitemap
	 *
	 * @param string $sitemap Sitemap.
	 */
	public function unregister_rewrites( $sitemap = '' ) {
		switch ( $sitemap ) {
			case 'sitemap':
				null !== $this->sitemap && $this->sitemap->unregister_rewrites();
				break;

			case 'sitemap-news':
				null !== $this->sitemap_news && $this->sitemap_news->unregister_rewrites();
				break;

			default:
				null !== $this->sitemap_news && $this->sitemap_news->unregister_rewrites();
				null !== $this->sitemap && $this->sitemap->unregister_rewrites();
		}
	}

	/**
	 * Generator info
	 */
	public function generator() {
		echo '<!-- generated-on="' . \esc_xml( \gmdate( 'c' ) ) . '" -->' . PHP_EOL;
		echo '<!-- generator="XML Sitemap & Google News for WordPress" -->' . PHP_EOL;
		echo '<!-- generator-url="https://status301.net/wordpress-plugins/xml-sitemap-feed/" -->' . PHP_EOL;
		echo '<!-- generator-version="' . \esc_xml( XMLSF_VERSION ) . '" -->' . PHP_EOL;
	}
}
