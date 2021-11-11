<?php

class XMLSitemapFeed {

	/**
	 * Defaults
	 * @var array
	 */
	private $defaults = array();

	/**
	 * News defaults
	 * @var array
	 */
	public $default_news_tags = array(
		'name' => '',
		'post_type' => array('post'),
		'categories' => ''
	);

	/**
	 * Front pages
	 *
	 * @var null/array $frontpages
	 */
	public $frontpages = null;

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
	public $blog_language = null;

	/**
	 * Allowed domain names
	 *
	 * @var null|array $domains
	 */
	private $domains = null;

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
	 * Excluded taxonomies
	 *
	 * post format taxonomy is disabled
	 * @var array
	 */
	private $disabled_taxonomies = array(
		'post_format',
		'product_shipping_class'
	);

	/**
	 * Maximum number of posts in any taxonomy term
	 *
	 * @var null/int $taxonomy_termmaxposts
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
	* METHODS
	*/

	/**
	 * Constructor
	 * @return void
	 */
	function __construct() {}

	/**
	 * Default options
	 *
	 * @param $key
	 * @return array
	 */
	public function defaults( $key = false )
	{
		if ( empty($this->defaults) ) :

			// sitemaps
			$sitemaps = ( '1' !== get_option('blog_public') ) ? '' : array(
				'sitemap' => 'sitemap.xml'
			);

			$this->defaults = array(
				'sitemaps' => $sitemaps,
				'post_types' => array(
					'post' => array(
						'active' => '1',
						'archive' => 'yearly',
						'priority' => '0.7',
						'dynamic_priority' => '',
						'tags' => array(
							'image' => 'featured'
							/*'video' => ''*/
						)
					),
					'page' => array(
						'active' => '1',
						'priority' => '0.5',
						'dynamic_priority' => '',
						'tags' => array(
							'image' => 'attached'
							/*'video' => ''*/
						)
					)
				),
				'taxonomies' => '',
				'taxonomy_settings' => array(
					'active' => '',
					'priority' => '0.3',
					'dynamic_priority' => '',
					'term_limit' => '3000'
				),
				'authors' => '',
				'author_settings' => array(
					'active' => '1',
					'priority' => '0.3',
					'term_limit' => '1000'
				),
				'ping' => array(
					'google',
					'bing'
				),
				'robots' => '',
				'urls' => '',
				'custom_sitemaps' => '',
				'domains' => ''
			);

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
	public function get_allowed_domains()
	{
		// allowed domain
		if ( null === $this->domains ) {

			$host = parse_url( home_url(), PHP_URL_HOST );

			$this->domains = ( !empty($host) ) ? (array) $host : array();

			$domains = get_option('xmlsf_domains');

			if ( !empty( $domains ) )
				$this->domains = array_merge( $this->domains, (array) $domains );
		}

		return $this->domains;
	}

	/**
	 * Whether or not to use plain permalinks
	 * Used for sitemap index and admin page
	 *
	 * @return bool
	 */
	public function plain_permalinks()
	{
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
	public function blog_language()
	{
		if ( $this->blog_language === null ) {
			// get site language for default language
			$this->blog_language = xmlsf_parse_language_string( get_bloginfo('language') );
		}

		return $this->blog_language;
	}

	/**
	 * Get scheme
	 * @return string
	 */
	public function scheme()
	{
		// scheme to use
		if ( empty($this->scheme) ) {
			$scheme = parse_url( home_url(), PHP_URL_SCHEME );
			$this->scheme = $scheme ? $scheme : 'http';
		}

		return $this->scheme;
	}

	/**
	 * Get disabled taxonomies
	 * @return array
	 */
	public function disabled_taxonomies()
	{
		return apply_filters( 'xmlsf_disabled_taxonomies', $this->disabled_taxonomies );
	}

}
