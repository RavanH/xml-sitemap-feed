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
	 * Defaults
	 *
	 * @var array
	 */
	private $defaults = array();

	/**
	 * News defaults
	 *
	 * @var array
	 */
	public $default_news_tags = array(
		'name'       => '',
		'post_type'  => array( 'post' ),
		'categories' => '',
	);

	/**
	 * Front pages
	 *
	 * @var null/array $frontpages
	 */
	public $frontpages = null;

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
	 * Blog pages
	 *
	 * @var null/array $blogpages
	 */
	public $blogpages = null;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ), 9 );
	}

	/**
	 * Plugin main init.
	 */
	public function init() {
		// If XML Sitemaps Manager is active, remove its init and admin_init hooks.
		if ( function_exists( 'xmlsm_init' ) ) {
			remove_action( 'init', 'xmlsm_init', 9 );
			remove_action( 'admin_init', 'xmlsm_admin_init' );
		}

		// Upgrade/install, maybe...
		$db_version = get_option( 'xmlsf_version', 0 );
		if ( ! version_compare( XMLSF_VERSION, $db_version, '=' ) ) {
			require_once XMLSF_DIR . '/upgrade.php';
		}

		if ( false === namespace\sitemaps_enabled() ) {
			return;
		}

		$sitemaps = (array) get_option( 'xmlsf_sitemaps', $this->defaults( 'sitemaps' ) );

		// Google News sitemap?
		if ( ! empty( $sitemaps['sitemap-news'] ) ) {
			require XMLSF_DIR . '/inc/functions-sitemap-news.php';

			global $xmlsf_sitemap_news;
			$xmlsf_sitemap_news = new Sitemap_News();
		}

		// XML Sitemap?
		if ( ! empty( $sitemaps['sitemap'] ) ) {
			require XMLSF_DIR . '/inc/functions-sitemap.php';

			global $xmlsf_sitemap;
			if ( namespace\uses_core_server() ) {
				// Extend core sitemap.
				$xmlsf_sitemap = new Sitemap_Core();
			} else {
				// Replace core sitemap.
				remove_action( 'init', 'wp_sitemaps_get_server' );

				$xmlsf_sitemap = new Sitemap_Plugin();
			}
		} else {
			// Disable core sitemap.
			add_filter( 'wp_sitemaps_enabled', '__return_false' );
		}
	}

	/**
	 * Default options
	 *
	 * @param bool $key Which key to get.
	 *
	 * @return array
	 */
	public function defaults( $key = false ) {
		if ( empty( $this->defaults ) ) :

			// sitemaps.
			$sitemaps = ( 1 !== (int) \get_option( 'blog_public' ) ) ? array() : array(
				'sitemap' => 'sitemap.xml',
			);

			$this->defaults = array(
				'sitemaps'           => $sitemaps,
				'server'             => \class_exists( 'SimpleXMLElement' ) && function_exists( 'get_sitemap_url' ) ? 'core' : 'plugin',
				'disabled_providers' => array(),
				'post_types'         => array(
					'post'  => array(
						'active'           => '1',
						'archive'          => 'yearly',
						'priority'         => .7,
						'dynamic_priority' => '',
						'tags'             => array(
							'image' => 'featured',
							/*'video' => ''*/
						),
					),
					'page'  => array(
						'active'           => '1',
						'priority'         => .5,
						'dynamic_priority' => '',
						'tags'             => array(
							'image' => 'attached',
							/*'video' => ''*/
						),
					),
					'limit' => 2000,
				),
				'taxonomies'         => '',
				'taxonomy_settings'  => array(
					'priority'         => .3,
					'dynamic_priority' => '',
					'include_empty'    => '',
					'limit'            => 2000,
				),
				'authors'            => '',
				'author_settings'    => array(
					'priority' => .3,
					'limit'    => 2000,
				),
				'robots'             => '',
				'urls'               => '',
				'custom_sitemaps'    => '',
				'news_tags'          => $this->default_news_tags,
			);

		endif;

		if ( $key ) {
			$return = ( isset( $this->defaults[ $key ] ) ) ? $this->defaults[ $key ] : '';
		} else {
			$return = $this->defaults;
		}

		return \apply_filters( 'xmlsf_defaults', $return, $key );
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
}
