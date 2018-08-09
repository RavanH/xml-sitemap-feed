<?php

class XMLSitemapFeed {

	/**
	 * Defaults
	 * @var array
	 */
	private $defaults = array();

	/**
	 * Which options should be autoloaded
	 * @var array
	 */
	public $autoload = array('sitemaps');

	/**
	* Signifies whether the request has been filtered.
	* @var bool
	*/
	public $request_filtered = false;

	/**
	* Signifies whether the current query is for a sitemap feed.
	* @var bool
	*/
	public $is_sitemap = false;

	/**
	* Signifies whether the current query is for a news feed.
	* @var bool
	*/
	public $is_news = false;

	/**
	 * Default language
	 * @var null/string $blog_language
	 */
	private $blog_language = null;

	/**
	 * Excluded post types
	 *
	 * attachment post type is disabled
	 * images are included via tags in the post and page sitemaps
	 * @var array
	 */
	private $disabled_post_types = array('attachment');
	private $disabled_post_types_news = array('attachment','page');

	/**
	 * Excluded taxonomies
	 *
	 * post format taxonomy is disabled
	 * @var array
	 */
	private $disabled_taxonomies = array('post_format','product_shipping_class');

	/**
	 * Site public domain name
	 *
	 * @var string $domain
	 */
	private $domain;

	/**
	 * Site public scheme
	 *
	 * @var string $domain
	 */
	private $scheme;

	/**
	 * Are we using plain permalinks
	 *
	 * @var bool $plain_permalinks
	 */
	private $plain_permalinks = null;

	/**
	* METHODS
	*/

	/**
	 * Constructor
	 * @return void
	 */
	 function __construct() {
		add_filter( 'robots_txt', array($this, 'robots_txt'), 9 );
	}

	/**
	 * Get sitemap feed conditional
	 * @return bool
	 */
	public function is_sitemap() {
		return (bool) $this->is_sitemap;
	}

	/**
	 * Get news feed conditional
	 * @return bool
	 */
	public function is_news() {
		return (bool) $this->is_news;
	}

	/**
	 * Default options
	 *
	 * @param $key
	 * @return array
	 */
	public function defaults( $key = false ) {

		if ( empty($this->defaults) ) :

			// sitemaps
			$sitemaps = ( '1' !== get_option('blog_public') ) ? array() : array(
				'sitemap' => 'sitemap.xml'
			);

			$this->defaults = array(
				'version' => '',
				'sitemaps' => $sitemaps,
				'post_types' => array(
					'post' => array(
						'name' => 'post',
						'active' => '1',
						'archive' => 'yearly',
						'priority' => '0.7',
						'dynamic_priority' => '1',
						'tags' => array(
							'image' => 'attached'
							/*'video' => ''*/
						)
					),
					'page' => array(
						'name' => 'page',
						'active' => '1',
						'priority' => '0.3',
						'dynamic_priority' => '',
						'tags' => array(
							'image' => 'attached'
							/*'video' => ''*/
						)
					)
				),
				'taxonomies' => array(),
				'taxonomy_settings' => array(
					'priority' => '0.3',
					'dynamic_priority' => '1',
					'term_limit' => '1000'
				),
				'ping' => array(
					'google' => array(
						'active' => '1',
						'uri' => 'http://www.google.com/ping',
						'req' => 'sitemap'
					),
					'bing' => array(
						'active' => '1',
						'uri' => 'http://www.bing.com/ping',
						'req' => 'sitemap'
					)
				),
				'pong' => array(),
				'robots' => '',
				'urls' => array(),
				'custom_sitemaps' => array(),
				'domains' => array(),
				//'news_sitemap' => array(),
				'news_tags' => array(
					'name' => '',
					'post_type' => array('post'),
					'categories' => '',
					'image' => 'featured'
				)
			);

			// append public post_types defaults
			foreach ( get_post_types(array('public'=>true),'names') as $name ) {
				// skip unallowed post types
				$skip = array_merge( array('post','page'), $this->disabled_post_types() );
				if ( in_array($name,$skip) ) {
					continue;
				}

				$this->defaults['post_types'][$name] = array(
					'name' => $name,
					'active' => '',
					'archive' => '',
					'priority' => '0.5',
					'dynamic_priority' => '',
					'tags' => array( 'image' => 'attached' /*,'video' => ''*/)
				);
			}

		endif;

		if ( $key ) {
			$return = ( isset($this->defaults[$key]) ) ? $this->defaults[$key] : '';
		} else {
			$return = $this->defaults;
		}

		return apply_filters( 'xmlsf_defaults', $return, $key );
	}

	/**
	 * Get domain
	 * @return string
	 */
	public function domain() {
		// allowed domain
		if ( empty($this->domain) ) {
			$host = parse_url( get_home_url(), PHP_URL_HOST );
			$this->domain = str_replace( 'www.', '', $host );
		}

		return $this->domain;
	}

	/**
	 * Whether or not to use plain permalinks
	 * Used for sitemap index and admin page
	 *
	 * @return bool
	 */
	public function plain_permalinks() {
		if ( null === $this->plain_permalinks ) {
			$permalink_structure = get_option('permalink_structure');
			$this->plain_permalinks = ('' == $permalink_structure || 0 === strpos($permalink_structure,'/index.php') ) ? true : false;
		}
		return $this->plain_permalinks;
	}

	/**
	 * Get scheme
	 * @return string
	 */
	public function scheme() {
		// scheme to use
		if ( empty($this->scheme) ) {
			$scheme = parse_url( get_home_url(), PHP_URL_SCHEME );
			$this->scheme = $scheme ? $scheme : 'http';
		}

		return $this->scheme;
	}

	/**
	 * Get post types
	 * @return array
	 */
	public function get_post_types() {
		$return = get_option('xmlsf_post_types');

		// make sure it's an array we are returning
		return !empty($return) ? (array)$return : array();
	}

	/**
	 * Get disabled post types
	 * @return array
	 */
	public function disabled_post_types( $sitemap = '' ) {
		if ( 'news' == $sitemap )
			return $this->disabled_post_types_news;

		return $this->disabled_post_types;
	}

	/**
	 * Get disabled taxonomies
	 * @return array
	 */
	public function disabled_taxonomies() {
		return $this->disabled_taxonomies;
	}

	/**
	 * Get urls
	 * @return array
	 */
	public function get_urls() {
		$urls = get_option('xmlsf_urls');
		// make sure it's an array we are returning
		if ( !empty($urls) ) {
			$return = ( !is_array($urls) ) ? explode( PHP_EOL, $urls ) : $urls;
		} else {
			$return = array();
		}
		return apply_filters( 'xmlsf_custom_urls', $return );
	}

	/**
	 * Get custom sitemaps
	 * @return array
	 */
	public function get_custom_sitemaps() {
		$urls = get_option('xmlsf_custom_sitemaps');
		// make sure it's an array we are returning
		if (!empty($urls)) {
			$return = ( !is_array($urls) ) ? explode( PHP_EOL, $urls ) : $urls;
		} else {
			$return = array();
		}
		return apply_filters( 'xmlsf_custom_sitemaps', $return );
	}

	/**
	 * Filter robots.txt rules
	 *
	 * @param $output
	 * @return string
	 */
	public function robots_txt( $output ) {
		$url = trailingslashit(get_bloginfo('url'));

		$sitemaps = get_option( 'xmlsf_sitemaps', array() );

		$pre = '# XML Sitemap & Google News Feeds version ' . XMLSF_VERSION . ' - https://status301.net/wordpress-plugins/xml-sitemap-feed/' . PHP_EOL;
		if ( '1' != get_option('blog_public') )
			$pre .= '# XML Sitemaps are disabled because of this site\'s privacy settings.' . PHP_EOL;
		elseif( empty( $sitemaps ) )
			$pre .= '# No XML Sitemaps are enabled on this site.' . PHP_EOL;
		else
			foreach ( $sitemaps as $pretty )
				$pre .= 'Sitemap: ' . $url . $pretty . PHP_EOL;
		$pre .= PHP_EOL;

		$post = $this->get_robots_txt();

		return $pre . $output . $post;
	}
}
