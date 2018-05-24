<?php
/* ------------------------------
 *      XMLSitemapFeed CLASS
 * ------------------------------ */

class XMLSitemapFeed {

	/**
	* Plugin base name
	* @var string
	*/
	public $plugin_basename;

	/**
	* Pretty permalinks base name
	* @var string
	*/
	public $base_name = 'sitemap';

	/**
	* Pretty permalinks extension
	* @var string
	*/
	public $extension = 'xml';

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
	* Database options prefix
	* @var string
	*/
	protected $prefix = 'xmlsf_';

	/**
	 * Default language
	 * @var null $blog_language
	 */
	private $blog_language = null;

	/**
	 * Flushed flag
	 * @var bool
	 */
	private $yes_mother = false;

	/**
	 * Defaults
	 * @var array
	 */
	private $defaults = array();

	/**
	 * Excluded post types
	 *
	 * attachment post type is disabled
	 * images are included via tags in the post and page sitemaps
	 * @var array
	 */
	private $disabled_post_types = array('attachment');

	/**
	 * Excluded taxonomies
	 *
	 * post format taxonomy is disabled
	 * @var array
	 */
	private $disabled_taxonomies = array('post_format');

	/**
	 * Google News genres
	 * @var array
	 */
	protected $gn_genres = array(
		'PressRelease',
		'Satire',
		'Blog',
		'OpEd',
		'Opinion',
		'UserGenerated'
		//'FactCheck'
	);

	/**
	 * Global values used for allowed urls, priority and changefreq calculation
	 */
	private $domain;
	private $scheme;
	private $home_url;
	private $firstdate;
	private $lastmodified; // unused at the moment
	private $postmodified = array();
	private $termmodified = array();
	private $frontpages = null;
	private $blogpages = null;
	private $images = array();

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
	 * Get gn_genres
	 * @return array
	 */
	public function gn_genres() {
		return $this->gn_genres;
	}

	/**
	 * Get domain
	 * @return string
	 */
	public function domain() {
		// allowed domain
		if ( empty($this->domain) ) {
			$host = parse_url( $this->home_url(), PHP_URL_HOST );
			$this->domain = str_replace( 'www.', '', $host );
		}

		return $this->domain;
	}

	/**
	 * Get scheme
	 * @return string
	 */
	public function scheme() {
		// scheme to use
		if ( empty($this->scheme) ) {
			$scheme = parse_url( $this->home_url(), PHP_URL_SCHEME );
			$this->scheme = $scheme ? $scheme : 'http';
		}

		return $this->scheme;
	}

	/**
	 * Get home URL
	 * @return string
	 */
	public function home_url() {
		return empty($this->home_url) ? home_url() : $this->home_url;
	}

	/**
	 * Set default options
	 *
	 * @return void
	 */
	private function set_defaults() {
		// sitemaps
		if ( '1' == get_option('blog_public') ) {
			$this->defaults['sitemaps'] = array(
				'sitemap' => XMLSF_NAME
			);
		} else {
			$this->defaults['sitemaps'] = array();
		}

		// post_types
		$this->defaults['post_types'] = array();

		foreach ( get_post_types(array('public'=>true),'names') as $name ) { // want 'publicly_queryable' but that excludes pages for some weird reason
			// skip unallowed post types
			if (in_array($name,$this->disabled_post_types)) {
				continue;
			}

			$this->defaults['post_types'][$name] = array(
				'name' => $name,
				'active' => '',
				'archive' => '',
				'priority' => '0.5',
				'dynamic_priority' => '',
				'tags' => array('image' => 'attached'/*,'video' => ''*/)
			);
		}

		$active_arr = array('post','page');

		foreach ( $active_arr as $name ) {
			if ( isset($this->defaults['post_types'][$name]) ) {
				$this->defaults['post_types'][$name]['active'] = '1';
			}
		}

		if ( isset($this->defaults['post_types']['post']) ) {
			$this->defaults['post_types']['post']['archive'] = 'yearly';
			$this->defaults['post_types']['post']['priority'] = '0.7';
			$this->defaults['post_types']['post']['dynamic_priority'] = '1';
		}

		if ( isset($this->defaults['post_types']['page']) ) {
			unset($this->defaults['post_types']['page']['archive']);
			$this->defaults['post_types']['page']['priority'] = '0.3';
			$this->defaults['post_types']['page']['dynamic_priority'] = '1';
		}

		// taxonomies
		$this->defaults['taxonomies'] = array(); // by default do not include any taxonomies

		// news sitemap settings
		$this->defaults['news_sitemap'] = array();

		// search engines to ping
		$this->defaults['ping'] = array(
			'google' => array (
				'active' => '1',
				'uri' => 'http://www.google.com/ping',
				'type' => 'GET',
				'req' => 'sitemap',
				'news' => '1'
			),
			'bing' => array (
				'active' => '1',
				'uri' => 'http://www.bing.com/ping',
				'type' => 'GET',
				'req' => 'sitemap',
				'news' => '1'
			),
			'yandex' => array (
				'active' => '',
				'uri' => 'http://ping.blogs.yandex.ru/RPC2',
				'type' => 'RPC',
			),
			'baidu' => array (
				'active' => '',
				'uri' => 'http://ping.baidu.com/ping/RPC2',
				'type' => 'RPC',
			),
			'others' => array (
				'active' => '1',
				'uri' => 'http://rpc.pingomatic.com/',
				'type' => 'RPC',
			),
		);

		// robots
		$this->defaults['robots'] = '';

		// additional urls
		$this->defaults['urls'] = array();

		// additional custom_sitemaps
		$this->defaults['custom_sitemaps'] = array();

		// additional allowed domains
		$this->defaults['domains'] = array();

		// news sitemap tags settings
		$this->defaults['news_tags'] = array(
			'name' => '',
			'post_type' => array('post'),
			'categories' => '',
			'image' => 'featured',
			'access' => array(
				'default' => '',
				//'private' => 'Registration', // private posts do not show up in feeds when not logged in. no point in setting access level then...
				'password' => 'Subscription'
			),
			'genres' => array(
				'default' => ''
			),
			'keywords' => array(
				'from' => 'category',
				'default' => ''
			)
		);
	}

	/**
	 * Get defaults
	 *
	 * @param bool|false $key
	 *
	 * @return array
	 */
	protected function defaults($key = false) {
		if ( empty($this->defaults) ) {
			$this->set_defaults();
		}

		if ( $key ) {
			$return = ( isset($this->defaults[$key]) ) ? $this->defaults[$key] : '';
		} else {
			$return = $this->defaults;
		}

		return apply_filters( 'xmlsf_defaults', $return, $key );
	}

	/**
	 * Get option
	 *
	 * @param $option
	 *
	 * @return array
	 */
	public function get_option($option) {
		return get_option( $this->prefix.$option, $this->defaults($option) );
	}

	/**
	 * Get sitemaps
	 * @return array
	 */
	public function get_sitemaps() {
		$return = $this->get_option('sitemaps');

		// make very sure it's an array we are returning
		return !empty($return) ? (array)$return : array();
	}

	/**
	 * Get ping
	 * @return array
	 */
	public function get_ping() {
		$return = $this->get_option('ping');

		// make very sure it's an array we are returning
		return !empty($return) ? (array)$return : array();
	}

	/**
	 * Get disabled post types
	 * @return array
	 */
	protected function disabled_post_types() {
		return $this->disabled_post_types;
	}

	/**
	 * Get disabled taxonomies
	 * @return array
	 */
	protected function disabled_taxonomies() {
		return $this->disabled_taxonomies;
	}

	/**
	* QUERY FUNCTIONS
	*/

	/**
	 * Get post types
	 * @return array
	 */
	public function get_post_types() {
		$return = $this->get_option('post_types');

		// make sure it's an array we are returning
		return !empty($return) ? (array)$return : array();
	}

	/**
	 * Have post types
	 * @return array
	 */
	public function have_post_types() {
		$return = array();

		foreach ( $this->get_post_types() as $type => $values ) {
			if ( !empty($values['active']) ) {
				$count = wp_count_posts( $values['name'] );
				if ( $count->publish > 0 ) {
					$values['count'] = $count->publish;
					$return[$type] = $values;
				}
			}
		}

		return $return;
	}

	/**
	 * Get taxonomies
	 * @return array
	 */
	public function get_taxonomies() {
		$return = $this->get_option('taxonomies');

		// make sure it's an array we are returning
		return !empty($return) ? (array)$return : array();
	}

	/**
	 * Get custom sitemaps
	 * @return array
	 */
	public function get_custom_sitemaps() {
		$urls = $this->get_option('custom_sitemaps');
		// make sure it's an array we are returning
		if (!empty($urls)) {
			$return = ( !is_array($urls) ) ? explode( PHP_EOL, $urls ) : $urls;
		} else {
			$return = array();
		}
		return apply_filters( 'xmlsf_custom_sitemaps', $return );
	}

	/**
	 * Get urls
	 * @return array
	 */
	public function get_urls() {
		$urls = $this->get_option('urls');
		// make sure it's an array we are returning
		if ( !empty($urls) ) {
			$return = ( !is_array($urls) ) ? explode( PHP_EOL, $urls ) : $urls;
		} else {
			$return = array();
		}
		return apply_filters( 'xmlsf_custom_urls', $return );
	}

	/**
	 * Get domains
	 * @return array
	 */
	public function get_domains() {
		$domains = $this->get_option('domains');
		if ( !empty($domains) && is_array($domains) ) {
			return array_merge( array( $this->domain() ), $domains );
		} else {
			return array( $this->domain() );
		}
	}

	/**
	 * Get archives
	 *
	 * @param string $post_type
	 * @param string $type
	 *
	 * @return array
	 */
	public function get_archives( $post_type = 'post', $type = '' ) {
		global $wpdb;
		$return = array();

		if ( 'monthly' == $type ) :

			$query = "SELECT YEAR(post_date) AS `year`, LPAD(MONTH(post_date),2,'0') AS `month`, count(ID) as posts FROM {$wpdb->posts} WHERE post_type = '{$post_type}' AND post_status = 'publish' GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date DESC";
			$key = md5($query);
			$cache = wp_cache_get( 'xmlsf_get_archives' , 'general');

			if ( !isset( $cache[ $key ] ) ) {
				$arcresults = $wpdb->get_results($query);
				$cache[ $key ] = $arcresults;
				wp_cache_set( 'xmlsf_get_archives', $cache, 'general' );
			} else {
				$arcresults = $cache[ $key ];
			}

			if ( $arcresults ) {
				foreach ( (array) $arcresults as $arcresult ) {
					$return[$arcresult->year.$arcresult->month] = $this->get_index_url( 'posttype', $post_type, $arcresult->year . $arcresult->month );
				}
			};

		elseif ( 'yearly' == $type ) :

			$query = "SELECT YEAR(post_date) AS `year`, count(ID) as posts FROM {$wpdb->posts} WHERE post_type = '{$post_type}' AND post_status = 'publish' GROUP BY YEAR(post_date) ORDER BY post_date DESC";
			$key = md5($query);
			$cache = wp_cache_get( 'xmlsf_get_archives' , 'general');

			if ( !isset( $cache[ $key ] ) ) {
				$arcresults = $wpdb->get_results($query);
				$cache[ $key ] = $arcresults;
				wp_cache_set( 'xmlsf_get_archives', $cache, 'general' );
			} else {
				$arcresults = $cache[ $key ];
			}

			if ($arcresults) {
				foreach ( (array) $arcresults as $arcresult) {
					$return[$arcresult->year] = $this->get_index_url( 'posttype', $post_type, $arcresult->year );
				}
			};

		else :

			$return[0] = $this->get_index_url('posttype', $post_type); // $sitemap = 'home', $type = false, $param = false

		endif;

		return $return;
	}

	/**
	 * Get robots
	 * @return string
	 */
	public function get_robots() {
		return ( $robots = $this->get_option('robots') ) ? $robots : '';
	}

	/**
	 * Do tags
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public function do_tags( $type = 'post' ) {
		$return = $this->get_post_types();

		// make sure it's an array we are returning
		return (
			is_string($type) &&
			isset($return[$type]) &&
			!empty($return[$type]['tags'])
		) ? (array) $return[$type]['tags'] : array();
	}

	/**
	 * Get translations
	 *
	 * @param $post_id
	 *
	 * @return array
	 */
	private function get_translations( $post_id ) {
		$translation_ids = array();
		// Polylang compat
		if ( function_exists('pll_get_post_translations') ) {
			$translations = pll_get_post_translations($post_id);
			foreach ( $translations as $slug => $id ) {
				if ( $post_id != $id ) $translation_ids[] = $id;
			}
		}
		// WPML compat
		global $sitepress;
		if ( isset($sitepress) && is_object($sitepress) && method_exists($sitepress, 'get_languages') && method_exists($sitepress, 'get_object_id') ) {
			foreach ( array_keys ( $sitepress->get_languages(false,true) ) as $term ) {
				$id = $sitepress->get_object_id($post_id,'page',false,$term);
				if ( $post_id != $id ) $translation_ids[] = $id;
			}
		}

		return $translation_ids;
	}

	/**
	 * Get blog_pages
	 * @return array
	 */
	private function get_blogpages() {
		if ( null === $this->blogpages ) :
			$blogpages = array();
			if ( 'page' == get_option('show_on_front') ) {
				$blogpage = (int)get_option('page_for_posts');
				if ( !empty($blogpage) ) {
					$blogpages = array_merge( (array)$blogpage, $this->get_translations($blogpage) );
				}
			}
			$this->blogpages = $blogpages;
		endif;

		return $this->blogpages;
	}

	/**
	 * Get front pages
	 * @return array
	 */
	private function get_frontpages() {
		if ( null === $this->frontpages ) :
			$frontpages = array();
			if ( 'page' == get_option('show_on_front') ) {
				$frontpage = (int)get_option('page_on_front');
				$frontpages = array_merge( (array)$frontpage, $this->get_translations($frontpage) );
			}
			$this->frontpages = $frontpages;
		endif;

		return $this->frontpages;
	}

	/**
	 * Is home?
	 *
	 * @param $post_id
	 *
	 * @return bool
	 */
	private function is_home( $post_id ) {
		return in_array( $post_id, $this->get_blogpages() );
	}

	/**
	* TEMPLATE FUNCTIONS
	*/

	/**
	 * Template headers
	 *
	 * @return string
	 */
	public function head( $style = '' ) {
		$output = '';

		// check if headers are already sent (bad) and set up a warning in admin (how?)
		if ( headers_sent($filename, $linenum) )
			$output = "<!-- WARNING: Headers already sent by $filename on line $linenum. Please fix! -->\n";

		// which style sheet
		switch ($style) {
			case 'index':
			$style_sheet = plugins_url('xsl/sitemap-index.xsl',__FILE__);
			break;

			case 'news':
			$style_sheet = plugins_url('xsl/sitemap-news.xsl',__FILE__);
			break;

			default:
			$style_sheet = plugins_url('xsl/sitemap.xsl',__FILE__);
		}

		$output .= '<?xml version="1.0" encoding="' . get_bloginfo('charset') . '"?>' . PHP_EOL;
		$output .= '<?xml-stylesheet type="text/xsl" href="' . $style_sheet . '?ver=' . XMLSF_VERSION .'"?>' . PHP_EOL;
		$output .= '<!-- generated-on="' . date('Y-m-d\TH:i:s+00:00') . '" -->' . PHP_EOL;
		$output .= '<!-- generator="XML & Google News Sitemap Feed plugin for WordPress" -->' . PHP_EOL;
		$output .= '<!-- generator-url="https://status301.net/wordpress-plugins/xml-sitemap-feed/" -->' . PHP_EOL;
		$output .= '<!-- generator-version="' . XMLSF_VERSION . '" -->' . PHP_EOL;

		// return output
		return $output;
	}

	/**
	 * Modified
	 *
	 * @param string $sitemap
	 * @param string $term
	 *
	 * @return string
	 */
	public function modified( $sitemap = 'post_type', $term = '' ) {
		global $post;

		if ( 'post_type' == $sitemap ) :

			// if blog page then look for last post date
			if ( $post->post_type == 'page' && $this->is_home($post->ID) )
				return get_lastpostmodified('gmt'); // TODO limit to sitemap included post types...

			if ( empty($this->postmodified[$post->ID]) ) {
				$postmodified = get_post_modified_time( 'Y-m-d H:i:s', true, $post->ID );
				$options = $this->get_post_types();

				if( !empty($options[$post->post_type]['update_lastmod_on_comments']) )
					$lastcomment = get_comments( array(
						'status' => 'approve',
						'number' => 1,
						'post_id' => $post->ID,
					) );

				if ( isset($lastcomment[0]->comment_date_gmt) )
					if ( mysql2date( 'U', $lastcomment[0]->comment_date_gmt, false ) > mysql2date( 'U', $postmodified, false ) )
						$postmodified = $lastcomment[0]->comment_date_gmt;

				// make sure lastmod is not older than publication date (happens on scheduled posts)
				if ( isset($post->post_date_gmt) && strtotime($post->post_date_gmt) > strtotime($postmodified) )
					$postmodified = $post->post_date_gmt;

				$this->postmodified[$post->ID] = $postmodified;
			}

			return $this->postmodified[$post->ID];

		elseif ( !empty($term) ) :

			if ( is_object($term) ) {
				if ( !isset($this->termmodified[$term->term_id]) ) {
				// get the latest post in this taxonomy item, to use its post_date as lastmod
					$posts = get_posts (
						array(
							'post_type' => 'any',
					 		'numberposts' => 1,
							'no_found_rows' => true,
							'update_post_meta_cache' => false,
							'update_post_term_cache' => false,
							'update_cache' => false,
							'tax_query' => array(
								array(
									'taxonomy' => $term->taxonomy,
									'field' => 'slug',
									'terms' => $term->slug
								)
							)
						)
					);
					$this->termmodified[$term->term_id] = isset($posts[0]->post_date_gmt) ? $posts[0]->post_date_gmt : '';
				}
				return $this->termmodified[$term->term_id];
			} else {
				$obj = get_taxonomy($term);

				$lastmodified = array();
				foreach ( (array)$obj->object_type as $object_type ) {
					$lastmodified[] = get_lastpostdate( 'gmt', $object_type );
					// returns last post date, not last modified date... (TODO consider making this an opion)
				}

				sort($lastmodified);
				$lastmodified = array_filter($lastmodified);

				return end($lastmodified);
			}

		endif;

		return '';
	}

	/**
	 * Get absolute URL
	 * Converts path or protocol relative URLs to absolute ones.
	 *
	 * @param string $url
	 *
	 * @return string|bool
	 */
	public function get_absolute_url( $url = false ) {
		// have a string or return false
		if ( empty( $url ) || ! is_string( $url ) ) {
			return false;
		}

		// check for scheme
		if ( strpos( $url, 'http' ) !== 0 ) {
			// check for relative url path
			if ( strpos( $url, '//' ) !== 0 ) {
				return $this->home_url() . $url;
			}
			return $this->scheme() . ':' . $url;
		}

		return $url;
	}

	/**
	 * Get images
	 *
	 * @param string $sitemap
	 *
	 * @return array|bool
	 */
	public function get_images( $sitemap = '' ) {
		global $post;

		if ( empty($this->images[$post->ID]) ) :

			if ( 'news' == $sitemap ) {
				$options = $this->get_option('news_tags');
				$which = isset($options['image']) ? $options['image'] : '';
			} else {
				$options = $this->get_post_types();
				$which = isset($options[$post->post_type]['tags']['image']) ? $options[$post->post_type]['tags']['image'] : '';
			}

			if ( 'attached' == $which ) {
				$args = array( 'post_type' => 'attachment', 'post_mime_type' => 'image', 'numberposts' => -1, 'post_status' =>'inherit', 'post_parent' => $post->ID );
				$attachments = get_posts($args);
				if ( $attachments ) {
					foreach ( $attachments as $attachment ) {
						$url = wp_get_attachment_image_url( $attachment->ID, 'full' );
						$url = $this->get_absolute_url( $url );
						if ( !empty($url) ) {
							$this->images[$post->ID][] = array(
								'loc' => esc_attr( esc_url_raw( $url ) ),
								'title' => apply_filters( 'the_title_xmlsitemap', $attachment->post_title ),
								'caption' => apply_filters( 'the_title_xmlsitemap', $attachment->post_excerpt )
								// 'caption' => apply_filters( 'the_title_xmlsitemap', get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ) )
							);
						}
					}
				}
			} elseif ( 'featured' == $which ) {
				if ( has_post_thumbnail( $post->ID ) ) {
					$attachment = get_post( get_post_thumbnail_id( $post->ID ) );
					$url = wp_get_attachment_image_url( get_post_thumbnail_id( $post->ID ), 'full' );
					$url = $this->get_absolute_url( $url );
					if ( !empty($url) ) {
						$this->images[$post->ID][] =  array(
							'loc' => esc_attr( esc_url_raw( $url ) ),
							'title' => apply_filters( 'the_title_xmlsitemap', $attachment->post_title ),
							'caption' => apply_filters( 'the_title_xmlsitemap', $attachment->post_excerpt )
							// 'caption' => apply_filters( 'the_title_xmlsitemap', get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ) )
						);
					}
				}
			}

		endif;

		return ( isset($this->images[$post->ID]) ) ? $this->images[$post->ID] : false;
	}

	/**
	 * Get last modified
	 *
	 * @param string $sitemap
	 * @param string $term
	 *
	 * @return string
	 */
	public function get_lastmod( $sitemap = 'post_type', $term = '' ) {
		$return = trim( mysql2date( 'Y-m-d\TH:i:s+00:00', $this->modified( $sitemap, $term ), false ) );
		return !empty($return) ? '	<lastmod>'.$return.'</lastmod>
	' : '';
	}

	/**
	 * Get priority
	 *
	 * @param string $sitemap
	 * @param WP_Term|string $term
	 *
	 * @return string
	 */
	public function get_priority( $sitemap = 'post_type', $term = '' ) {

		if ( 'post_type' == $sitemap ) :

			global $post;
			$options = $this->get_post_types();
			$defaults = $this->defaults('post_types');
			$priority_meta = get_metadata('post', $post->ID, '_xmlsf_priority' , true);

			if ( !empty($priority_meta) || $priority_meta == '0' ) {
				$priority = floatval(str_replace(',','.',$priority_meta));
			} elseif ( !empty($options[$post->post_type]['dynamic_priority']) ) {
				$post_modified = mysql2date('U',$post->post_modified_gmt, false);

				if ( empty($this->lastmodified) ) {
					$this->lastmodified = mysql2date('U',get_lastpostmodified('gmt',$post->post_type),false);
					// last posts or page modified date in Unix seconds
				}

				if ( empty($this->firstdate) ) {
					$this->firstdate = mysql2date('U',get_firstpostdate('gmt',$post->post_type),false);
					// uses get_firstpostdate() function defined in xml-sitemap/hacks.php !
				}

				if ( isset($options[$post->post_type]['priority']) ) {
					$priority_value = floatval(str_replace(',','.',$options[$post->post_type]['priority']));
				} else {
					$priority_value = floatval($defaults[$post->post_type]['priority']);
				}

				// reduce by age
				// NOTE : home/blog page gets same treatment as sticky post, i.e. no reduction by age
				if ( is_sticky($post->ID) || $this->is_home($post->ID) ) {
					$priority = $priority_value;
				} else {
					$priority = ( $this->lastmodified > $this->firstdate ) ? $priority_value - $priority_value * ( $this->lastmodified - $post_modified ) / ( $this->lastmodified - $this->firstdate ) : $priority_value;
				}

				if ( $post->comment_count > 0 ) {
					$priority = $priority + 0.1 + ( 0.9 - $priority ) * $post->comment_count / wp_count_comments($post->post_type)->approved;
				}
			} else {
				$priority = ( isset($options[$post->post_type]['priority']) && is_numeric($options[$post->post_type]['priority']) ) ? $options[$post->post_type]['priority'] : $defaults[$post->post_type]['priority'];
			}

		elseif ( ! empty($term) ) :

			$max_priority = 0.4;
			$min_priority = 0.0;
			// TODO make these values optional?

			$tax_obj = get_taxonomy($term->taxonomy);
			$postcount = 0;
			foreach ($tax_obj->object_type as $post_type) {
				$_post_count = wp_count_posts($post_type);
				$postcount += $_post_count->publish;
			}

			$priority = ( $postcount > 0 ) ? $min_priority + ( $max_priority * $term->count / $postcount ) : $min_priority;

		else :

			$priority = 0.5;

		endif;

		// make sure we're not below zero or cases where we ended up above 1 (sticky posts with many comments)
		$priority = filter_var( $priority, FILTER_VALIDATE_INT, array(
				'options' => array(
					'default' => .5,
					'min_range' => 0,
					'max_range' => 1
				)
			)
		);

		return number_format( $priority, 1 );
	}

	/**
	 * Get home urls
	 * @return array
	 */
	public function get_home_urls() {
		$urls = array();

		global $sitepress; // Polylang and WPML compat
		if ( function_exists('pll_the_languages') ) {
			$languages = pll_the_languages( array( 'raw' => 1 ) );
			if ( is_array($languages) ) {
				foreach ( $languages as $language ) {
					$urls[] = pll_home_url( $language['slug'] );
				}
			} else {
				$urls[] = $this->home_url();
			}
		} elseif ( isset($sitepress) && is_object($sitepress) && method_exists($sitepress, 'get_languages') && method_exists($sitepress, 'language_url') ) {
			foreach ( array_keys ( $sitepress->get_languages(false,true) ) as $term ) {
				$urls[] = $sitepress->language_url($term);
			}
		} else {
			$urls[] = $this->home_url();
		}

		return $urls;
	}

	/**
	 * Is excluded
	 *
	 * @param null $post_id
	 *
	 * @return bool
	 */
	public function is_excluded( $post_id = null ) {
		// no ID, try and get it from global post object
		if ( null == $post_id ) {
			global $post;
			if ( is_object($post) && isset($post->ID)) {
				$post_id = $post->ID;
			} else {
				return false;
			}
		}

		$excluded = get_post_meta($post_id,'_xmlsf_exclude',true) || in_array($post_id,$this->get_frontpages()) ? true : false;

		return apply_filters( 'xmlsf_excluded', $excluded, $post_id );
	}

	/**
	 * Is allowed domain
	 *
	 * @param $url
	 *
	 * @return mixed|void
	 */
	public function is_allowed_domain( $url ) {
		$domains = $this->get_domains();
		$return = false;
		$parsed_url = parse_url($url);

		if (isset($parsed_url['host'])) {
			foreach( $domains as $domain ) {
				if( $parsed_url['host'] == $domain || strpos($parsed_url['host'],'.'.$domain) !== false ) {
					$return = true;
					break;
				}
			}
		}

		return apply_filters( 'xmlsf_allowed_domain', $return, $url );
	}

	/**
	 * Get index url
	 *
	 * @param string $sitemap
	 * @param bool|false $type
	 * @param bool|false $param
	 *
	 * @return string
	 */
	public function get_index_url( $sitemap = 'home', $type = false, $param = false ) {
		$split_url = explode('?', $this->home_url());

		if ( '' == get_option('permalink_structure') || '1' != get_option('blog_public')) {
			$name = '?feed='.$name;
			$name .= $param ? '&m='.$param : '';
			$name .= isset($split_url[1]) && !empty($split_url[1]) ? '&' . $split_url[1] : '';
		} else {
			$name = $this->base_name.'-'.$sitemap;
			$name .= $type ? '-'.$type : '';
			$name .= $param ? '.'.$param : '';
			$name .= '.'.$this->extension;
			$name .= isset($split_url[1]) && !empty($split_url[1]) ? '?' . $split_url[1] : '';
		}

		return esc_url( trailingslashit($split_url[0]) . $name );
	}

	/**
	 * Get site language
	 *
	 * @return string
	 */
	public function get_blog_language() {
		if ( empty($this->blog_language) ) {
			// get site language for default language
			$blog_language = $this->parse_language_string( get_bloginfo('language') );

			$this->blog_language = !empty($blog_language) ? $blog_language : 'en';
		}

		return $this->blog_language;
	}

	/**
	 * Get site language
	 *
	 * @param string $lang unformatted language string
	 *
	 * @return string
	 */
	public function parse_language_string( $lang ) {
		$lang = convert_chars( strtolower( strip_tags( $lang ) ) );

		// no underscores
		if ( strpos( $lang, '_' ) ) {
			$expl = explode('_', $lang);
			$lang = $expl[0];
		}

		// no hyphens except...
		if ( strpos( $lang, '-' ) && !in_array( $lang, array('zh-cn','zh-tw') ) ) {
			// explode on hyphen and use only first part
			$expl = explode('-', $lang);
			$lang = $expl[0];
		}

		return $lang;
	}

	/**
	 * Get language
	 *
	 * @param $post_id
	 *
	 * @return null|string
	 */
	public function get_language( $post_id ) {
		$language = $this->get_blog_language();

		// Polylang
		if ( function_exists('pll_get_post_language') ) {
			$lang = pll_get_post_language( $post_id, 'slug' );
			if ( !empty($lang) )
				$language = $this->parse_language_string( $lang );
		}

		// WPML compat
		global $sitepress;
		if ( isset($sitepress) && is_object($sitepress) && method_exists($sitepress, 'get_language_for_element') ) {
			$post_type = (array) get_query_var( 'post_type', 'post' );
			$lang = $sitepress->get_language_for_element( $post_id, 'post_'.$post_type[0] );
			//apply_filters( 'wpml_element_language_code', null, array( 'element_id' => $post_id, 'element_type' => $post_type ) );
			if ( !empty($lang) )
				$language = $this->parse_language_string( $lang );
		}

		return apply_filters( 'xmlsf_post_language', $language, $post_id );
	}


	/**
	* ROBOTSTXT
	*/

	// add sitemap location in robots.txt generated by WP
	public function robots($output) {
		echo '# XML Sitemap & Google News Feeds version ' . XMLSF_VERSION . ' - http://status301.net/wordpress-plugins/xml-sitemap-feed/' . PHP_EOL;

		if ( '1' != get_option('blog_public') ) {
			echo '# XML Sitemaps are disabled. Please see Site Visibility on Settings > Reading.';
		} else {
			foreach ( $this->get_sitemaps() as $pretty )
				echo 'Sitemap: ' . trailingslashit(get_bloginfo('url')) . $pretty . PHP_EOL;

			if ( empty($pretty) )
				echo '# No XML Sitemaps are enabled. Please see XML Sitemaps on Settings > Reading.' . PHP_EOL;
		}

		echo PHP_EOL;
	}

	/**
	 * add robots.txt rules
	 *
	 * @param $output
	 *
	 * @return string
	 */
	public function robots_txt($output) {
		return $output . $this->get_option('robots');
	}

	/**
	* REWRITES
	*/

	/**
	 * Remove the trailing slash from permalinks that have an extension,
	 * such as /sitemap.xml (thanks to Permalink Editor plugin for WordPress)
	 *
	 * @param string $request
	 *
	 * @return mixed
	 */
	public function trailingslash($request) {
		return pathinfo($request, PATHINFO_EXTENSION) ? untrailingslashit($request) : $request;
	}

	/**
	 * Add sitemap rewrite rules
	 *
	 * @param string $wp_rewrite
	 *
	 * @return void
	 */
	public function rewrite_rules($wp_rewrite) {
		$xmlsf_rules = array();
		$sitemaps = $this->get_sitemaps();

		foreach ( $sitemaps as $name => $pretty ) {
			$xmlsf_rules[ preg_quote($pretty) . '$' ] = $wp_rewrite->index . '?feed=' . $name;
		}

		if (!empty($sitemaps['sitemap'])) {
			// home urls
			$xmlsf_rules[ $this->base_name . '-home\.' . $this->extension . '$' ] = $wp_rewrite->index . '?feed=sitemap-home';

			// add rules for post types (can be split by month or year)
			foreach ( $this->get_post_types() as $post_type ) {
				if ( isset($post_type['active']) && '1' == $post_type['active'] ) {
					$xmlsf_rules[ $this->base_name . '-posttype-' . $post_type['name'] . '\.([0-9]+)?\.?' . $this->extension . '$' ] = $wp_rewrite->index . '?feed=sitemap-posttype-' . $post_type['name'] . '&m=$matches[1]';
				}
			}

			// add rules for taxonomies
			foreach ( $this->get_taxonomies() as $taxonomy ) {
				$xmlsf_rules[ $this->base_name . '-taxonomy-' . $taxonomy . '\.' . $this->extension . '$' ] = $wp_rewrite->index . '?feed=sitemap-taxonomy-' . $taxonomy;
			}

			$urls = $this->get_urls();
			if(!empty($urls)) {
				$xmlsf_rules[ $this->base_name . '-custom\.' . $this->extension . '$' ] = $wp_rewrite->index . '?feed=sitemap-custom';
			}

		}

		$wp_rewrite->rules = $xmlsf_rules + $wp_rewrite->rules;
	}

	/**
	 * WPML: switch language
	 * @see https://wpml.org/wpml-hook/wpml_post_language_details/
	 */
	public function wpml_language_switcher() {
		global $sitepress,$post;
		if( isset( $sitepress ) ) {
			$post_language = apply_filters( 'wpml_post_language_details', NULL, $post->ID );
			$sitepress->switch_lang($post_language['language_code']);
		}
	}

	/**
	 * Filter request
	 *
	 * @param $request
	 *
	 * @return mixed
	 */
	public function filter_request( $request ) {
		$this->request_filtered = true;

		if ( isset($request['feed']) && strpos($request['feed'],'sitemap') === 0 ) :

			// set the normal sitemap conditional tag
			$this->is_sitemap = true;

			// REPSONSE HEADERS filtering
			add_filter( 'wp_headers', array($this, 'headers') );

			// modify request parameters
			$request['post_status'] = 'publish';
			$request['no_found_rows'] = true;
			$request['cache_results'] = false;
			$request['update_post_term_cache'] = false;
			$request['update_post_meta_cache'] = false;

			// Polylang compat
			$request['lang'] = '';
			// WPML compat
			global $wpml_query_filter;
			if ( isset($wpml_query_filter) && is_object($wpml_query_filter) ) {
				remove_filter( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ) );
				remove_filter( 'posts_where', array( $wpml_query_filter, 'posts_where_filter' ) );
				add_action( 'the_post', array( $this, 'wpml_language_switcher' ) );
			}

			// prepare for news and return modified request
			if ( $request['feed'] == 'sitemap-news' ) {
				$defaults = $this->defaults('news_tags');
				$options = $this->get_option('news_tags');
				$news_post_types = isset($options['post_type']) && !empty($options['post_type']) ? (array)$options['post_type'] : $defaults['post_type'];

				// disable caching
				define('DONOTCACHEPAGE', true);
				define('DONOTCACHEDB', true);

				// set up query filters
				$live = false;
				foreach ($news_post_types as $news_post_type) {
					if ( get_lastpostdate('gmt', $news_post_type) > date('Y-m-d H:i:s', strtotime('-48 hours')) ) {
						$live = true;
						break;
					}
				}
				if ( $live ) {
					add_filter('post_limits', array($this, 'filter_news_limits'));
					add_filter('posts_where', array($this, 'filter_news_where'), 10, 1);
				} else {
					add_filter('post_limits', array($this, 'filter_no_news_limits'));
				}

				// post type
				$request['post_type'] = $news_post_types;

				// categories
				if ( isset($options['categories']) && is_array($options['categories']) ) {
					$request['cat'] = implode(',',$options['categories']);
				}

				// set the news sitemap conditional tag
				$this->is_news = true;

				return $request;
			}

			$options = $this->get_post_types();

			foreach ( $options as $post_type ) {
				if( !empty($post_type['update_lastmod_on_comments']) ) {
					$request['withcomments'] = true;
					break;
				}
			}

			// prepare for post types and return modified request
			if ( strpos($request['feed'],'sitemap-posttype') === 0 ) {
				foreach ( $options as $post_type ) {
					if ( $request['feed'] == 'sitemap-posttype-'.$post_type['name'] ) {
						// setup filter
						add_filter( 'post_limits', array($this, 'filter_limits') );

						$request['post_type'] = $post_type['name'];
						$request['orderby'] = 'modified';
						$request['is_date'] = false;

						return $request;
					}
				}
			}

			// for index and custom sitemap, nothing (else) to do (yet)

			// prepare for taxonomies and return modified request
			if ( strpos($request['feed'],'sitemap-taxonomy') === 0 ) {
				foreach ( $this->get_taxonomies() as $taxonomy ) {
					if ( $request['feed'] == 'sitemap-taxonomy-'.$taxonomy ) {

						$request['taxonomy'] = $taxonomy;

						// WPML compat
						global $sitepress;
						if ( isset($sitepress) && is_object($sitepress) ) {
							remove_filter( 'get_terms_args', array($sitepress, 'get_terms_args_filter') );
							remove_filter( 'get_term', array($sitepress,'get_term_adjust_id'), 1 );
							remove_filter( 'terms_clauses', array($sitepress,'terms_clauses') );
							$sitepress->switch_lang('all');
						}

						return $request;
					}
				}
			}

		endif;

		return $request;
	}

	/**
	 * Response headers filter
	 * Does not check if we are really in a sitemap feed.
	 *
	 * @param $headers
	 *
	 * @return array
	 */
	function headers( $headers ) {
		// set noindex
		$headers['X-Robots-Tag'] = 'noindex, follow';
		$headers['Content-Type'] = 'text/xml; charset=' . get_bloginfo('charset');
	    return $headers;
	}

	/**
	* FEED TEMPLATES
	*/

	/**
	 * Set up the sitemap index template
	 */
	public function load_template_index() {
		load_template( dirname( __FILE__ ) . '/feed-sitemap.php' );
	}

	/**
	 * set up the sitemap home page(s) template
	 */
	public function load_template_base() {
		load_template( dirname( __FILE__ ) . '/feed-sitemap-home.php' );
	}

	/**
	 * set up the post types sitemap template
	 */
	public function load_template() {
		load_template( dirname( __FILE__ ) . '/feed-sitemap-post_type.php' );
	}

	/**
	 * set up the taxonomy sitemap template
	 */
	public function load_template_taxonomy() {
		load_template( dirname( __FILE__ ) . '/feed-sitemap-taxonomy.php' );
	}

	/**
	 * set up the news sitemap template
	 */
	public function load_template_news() {
		load_template( dirname( __FILE__ ) . '/feed-sitemap-news.php' );
	}

	/**
	 * set up the custom sitemap template
	 */
	public function load_template_custom() {
		load_template( dirname( __FILE__ ) . '/feed-sitemap-custom.php' );
	}

	/**
	* LIMITS
	*/

	/**
	 * Filter limits
	 * override default feed limit
	 * @return string
	 */
	public function filter_limits( $limit ) {
		return 'LIMIT 0, 50000';
	}

	/**
	 * Filter news WHERE
	 * only posts from the last 48 hours
	 *
	 * @param string $where
	 *
	 * @return string
	 */
	public function filter_news_where( $where = '' ) {
		return $where . ' AND post_date_gmt > \'' . date('Y-m-d H:i:s', strtotime('-48 hours')) . '\'';
	}

	/**
	 * Filter news limits
	 * override default feed limit for GN
	 * @return string
	 */
	public function filter_news_limits( $limits ) {
		return 'LIMIT 0, 1000';
	}

	/**
	 * Filter no news limits
	 * in case there is no news, just take the latest post
	 * @return string
	 */
	public function filter_no_news_limits( $limits ) {
		return 'LIMIT 0, 1';
	}

	/**
	* PINGING
	*/

	/**
	 * Ping
	 *
	 * @param $uri
	 * @param int $timeout
	 *
	 * @return bool
	 */
	public function ping($uri, $timeout = 3) {
		$response = wp_remote_request( $uri, array('timeout'=>$timeout) );

		return ( '200' == wp_remote_retrieve_response_code($response) ) ? true : false;
	}

	/**
	 * Do pings
	 *
	 * @param $new_status
	 * @param $old_status
	 * @param $post
	 */
	public function do_pings($new_status, $old_status, $post) {
		$sitemaps = $this->get_sitemaps();
		$to_ping = $this->get_ping();
		$update = false;

		// first check if news sitemap is set
		if ( !empty($sitemaps['sitemap-news']) ) {
			// then check if we've got a post type that is included in our news sitemap
			$news_tags = $this->get_option('news_tags');
			if ( !empty($news_tags['post_type']) && is_array($news_tags['post_type']) && in_array($post->post_type,$news_tags['post_type']) ) {

				// TODO: check if we're posting to an included category!

				// are we publishing?
				if ( $old_status != 'publish' && $new_status == 'publish' ) {
					// loop through ping targets
					foreach ($to_ping as $se => $data) {
						// check active switch
						if( empty($data['active']) || empty($data['news']) ) {
							continue;
						}
						// and if we did not ping already within the last 5 minutes
						if( !empty($data['pong']) && is_array($data['pong']) && !empty($data['pong'][$sitemaps['sitemap-news']]) && (int)$data['pong'][$sitemaps['sitemap-news']] + 300 > time() ) {
							continue;
						}
						// ping !
						if ( $this->ping( add_query_arg( $data['req'], urlencode(trailingslashit(get_bloginfo('url')).$sitemaps['sitemap-news']), $data['uri'] ) ) ) {
							$to_ping[$se]['pong'][$sitemaps['sitemap-news']] = time();
							$update = true;
						}
					}
				}
			}
		}

		// first check if regular sitemap is set
		if ( !empty($sitemaps['sitemap']) ) {
			// then check if we've got a post type that is included in our sitemap
			foreach($this->get_post_types() as $post_type) {
				if ( !empty($post_type) && is_array($post_type) && in_array($post->post_type,$post_type) ) {
					// are we publishing?
					if ( $old_status != 'publish' && $new_status == 'publish' ) {
						foreach ($to_ping as $se => $data) {
							// check active switch
							if ( empty($data['active']) || empty($data['type']) || $data['type']!='GET' ) {
								continue;
							}
							// and if we did not ping already within the last hour
							if ( !empty($data['pong']) && is_array($data['pong']) && !empty($data['pong'][$sitemaps['sitemap']]) && (int)$data['pong'][$sitemaps['sitemap']] + 3600 > time() ) {
								continue;
							}
							// ping !
							if ( $this->ping( add_query_arg( $data['req'], urlencode(trailingslashit(get_bloginfo('url')).$sitemaps['sitemap']), $data['uri'] ) ) ) {
								$to_ping[$se]['pong'][$sitemaps['sitemap']] = time();
								$update = true;
							}
						}
					}
				}
			}
		}

		if ( $update ) update_option($this->prefix.'ping',$to_ping);
	}

	/**
	* CLEARING & PURGING
	*/

	/**
	 * Clear settings
	 */
	public function clear_settings() {
		delete_option( 'xmlsf_version' );
		foreach ( $this->defaults() as $option => $settings ) {
			delete_option( 'xmlsf_' . $option );
		}

		if ( defined('WP_DEBUG') && WP_DEBUG ) {
			error_log( 'XML Sitemap Feeds settings cleared' );
		}
	}

	/**
	 * Cache delete on clean_post_cache
	 *
	 * @param $post_ID
	 * @param $post
	 */
	public function clean_post_cache( $post_ID, $post ) {
		// are we moving the post in or out of published status?
		wp_cache_delete('xmlsf_get_archives', 'general');

		// TODO get year / month here to delete specific keys too !!!!
		$m = mysql2date('Ym',$post->post_date_gmt, false);
		$y = substr($m, 0, 4);

		// clear possible last post modified cache keys
		wp_cache_delete( 'lastpostmodified:gmt', 'timeinfo' ); // should be handled by WP core?
		wp_cache_delete( 'lastpostmodified'.$y.':gmt', 'timeinfo' );
		wp_cache_delete( 'lastpostmodified'.$m.':gmt', 'timeinfo' );
		wp_cache_delete( 'lastpostmodified'.$y.':gmt:'.$post->post_type, 'timeinfo' );
		wp_cache_delete( 'lastpostmodified'.$m.':gmt:'.$post->post_type, 'timeinfo' );

		// clear possible last post date cache keys
		wp_cache_delete( 'lastpostdate:gmt', 'timeinfo' );
		wp_cache_delete( 'lastpostdate:gmt:'.$post->post_type, 'timeinfo' );

		// clear possible fist post date cache keys
		wp_cache_delete( 'firstpostdate:gmt', 'timeinfo' );
		wp_cache_delete( 'firstpostdate:gmt:'.$post->post_type, 'timeinfo' );
	}

	/**
	 * Nginx helper purge urls
	 * adds sitemap urls to the purge array.
	 *
	 * @param $urls array
	 * @param $redis bool|false
	 *
	 * @return $urls array
	 */
	public function nginx_helper_purge_urls( $urls = array(), $redis = false ) {
		// are permalinks set, blog public and $urls an array?
		if ( '' == get_option('permalink_structure') || '1' != get_option('blog_public') || ! is_array( $urls ) ) {
			return $urls;
		}

		if ( $redis ) {
			// wildcard allowed, this makes everything simple
			$urls[] = '/sitemap*.xml';
		} else {
			// no wildcard, go through the motions
			foreach ( $this->get_sitemaps() as $pretty ) {

				if ( 'sitemap.xml' == $pretty ) {

					// add home sitemap
					$urls[] = parse_url( $this->get_index_url('home'), PHP_URL_PATH);

					// add public post types sitemaps
					foreach ( $this->have_post_types() as $post_type ) {
						$archive = !empty($post_type['archive']) ? $post_type['archive'] : '';
						foreach ( $this->get_archives($post_type['name'],$archive) as $url ) {
							 $urls[] = parse_url( $url, PHP_URL_PATH);
						}
					}

					// add public post taxonomies sitemaps
					foreach ( $this->get_taxonomies() as $taxonomy ) {
						$urls[] = parse_url( $this->get_index_url('taxonomy',$taxonomy), PHP_URL_PATH);
					}

					// add custom URLs sitemap
					$custom_urls = $this->get_urls();
					if ( !empty( $custom_urls ) ) {
						$urls[] = parse_url( $this->get_index_url('custom'), PHP_URL_PATH);
					}

					// custom sitemaps
					foreach ($this->get_custom_sitemaps() as $url) {
						if ( !empty($url) && $this->is_allowed_domain($url) ) {
							$urls[] = parse_url( esc_url($url), PHP_URL_PATH);
						}
					}
				}
			}

			$urls[] = '/' . $pretty;
		}

		return $urls;
	}

	/**
	* INITIALISATION
	*/

	/**
	 * Upgrade
	 */
	public function upgrade( $old_version ) {
		// rewrite rules not available on plugins_loaded
		// and don't flush rules from init as Polylang chokes on that
		// just remove the db option and let WP regenerate them when ready...
		delete_option( 'rewrite_rules' );
		// ... but make sure rules are regenerated when admin is visited.
		set_transient( 'xmlsf_flush_rewrite_rules', '' );

		// set this up with a transient too !! get_home_path function not available on init !?!
		//$this->check_static_files();

		if ( $old_version !== 0 ) :

			if ( version_compare('4.4', $old_version, '>') ) {
				// remove robots.txt rules blocking stylesheets
			 	if ( $robot_rules = get_option($this->prefix.'robots') ) {
					$robot_rules = str_replace(array('Disallow: */wp-content/','Allow: */wp-content/uploads/'),'',$robot_rules);
					delete_option( $this->prefix.'robots' );
					add_option( $this->prefix.'robots', $robot_rules, '', 'no' );
				}

				// upgrade pings
				if ( $pong = get_option( $this->prefix.'pong' ) and is_array($pong) ) { // use 'and' here for precedence of the assignement operator, thanks @kitchin
					$ping = $this->get_ping();
					foreach ( $pong as $se => $arr) {
						if ( is_array( $arr ) ) {
							// convert formatted time to unix time
							foreach ( $arr as $pretty => $date ) {
								$time = strtotime($date);
								$arr[$pretty] = (int)$time < time() ? $time : '';
							}
							// and set array
							$ping[$se]['pong'] = $arr;
						}
					}
					delete_option( $this->prefix.'pong' );
					delete_option( $this->prefix.'ping' );
					add_option( $this->prefix.'ping', array_merge( $this->defaults('ping'), $ping ), '', 'no' );
				}
			}

			if ( version_compare('4.4.1', $old_version, '>') ) {
				// register location taxonomies then delete all terms
				register_taxonomy( 'gn-location-3', null );
				$terms = get_terms( 'gn-location-3', array('hide_empty' => false) );
				foreach ( $terms as $term ) {
					wp_delete_term(	$term->term_id, 'gn-location-3' );
				}

				register_taxonomy( 'gn-location-2', null );
				$terms = get_terms( 'gn-location-2',array('hide_empty' => false) );
				foreach ( $terms as $term ) {
					wp_delete_term(	$term->term_id, 'gn-location-2' );
				}

				register_taxonomy( 'gn-location-1', null );
				$terms = get_terms( 'gn-location-1',array('hide_empty' => false) );
				foreach ( $terms as $term ) {
					wp_delete_term(	$term->term_id, 'gn-location-1' );
				}
			}

			if ( version_compare('4.9', $old_version) || version_compare('4.9.1', $old_version) ) {
				// flag to rebuild taxonomy terms
				set_transient('xmlsf_create_genres','');
			};

		endif;

		update_option( $this->prefix.'version', XMLSF_VERSION );

		if ( defined('WP_DEBUG') && WP_DEBUG ) {
			error_log('XML Sitemap Feeds upgraded from '.$old_version.' to '.XMLSF_VERSION);
		}
	}

	/**
	 * Plugins loaded: load text domain
	 */
	public function plugins_loaded() {
		// TEXT DOMAIN
		if ( is_admin() ) { // text domain needed on admin only
			load_plugin_textdomain('xml-sitemap-feed', false, dirname( $this->plugin_basename ) . '/languages' );
		}
	}

	/**
	 * Check for static sitemap files
	 */
	public function check_static_files() {
		$files = array();

		if ( !is_multisite() || is_main_site() || is_network_admin() ) {
			$home_path = trailingslashit( get_home_path() );
			$sitemaps = $this->get_sitemaps();
			foreach ( $sitemaps as $name => $pretty ) {
				if ( file_exists( $home_path . $pretty ) ) {
					$files[] = $home_path . $pretty;
				}
			}
		}

		if ( !empty($files) ) {
			set_transient('xmlsf_static_files_found', $files);
		}
	}


	/**
	 * Init
	 */
	public function init() {
		// UPGRADE
		$version = get_option( 'xmlsf_version', 0 );

		if ( version_compare(XMLSF_VERSION, $version, '>') ) {
			$this->upgrade($version);
		}

		$sitemaps = $this->get_sitemaps();

		if (isset($sitemaps['sitemap'])) {
			// setup feed templates
			add_action( 'do_feed_sitemap', array($this, 'load_template_index'), 10, 1 );
			add_action( 'do_feed_sitemap-home', array($this, 'load_template_base'), 10, 1 );
			add_action( 'do_feed_sitemap-custom', array($this, 'load_template_custom'), 10, 1 );
			foreach ( $this->get_post_types() as $post_type ) {
				add_action( 'do_feed_sitemap-posttype-'.$post_type['name'], array($this, 'load_template'), 10, 1 );
			}
			foreach ( $this->get_taxonomies() as $taxonomy ) {
				add_action( 'do_feed_sitemap-taxonomy-'.$taxonomy, array($this, 'load_template_taxonomy'), 10, 1 );
			}
		}

		if (isset($sitemaps['sitemap-news'])) {
			// setup feed template
			add_action('do_feed_sitemap-news', array($this, 'load_template_news'), 10, 1);

			// register the taxonomies
			$this->register_gn_taxonomies();
		}
	}

	/**
	 * Admin init
	 */
	public function admin_init() {
		// Include the admin class file
		include_once( dirname( __FILE__ ) . '/class-xmlsitemapfeed-admin.php' );

		new XMLSitemapFeed_Admin( $this->plugin_basename );
	}

	/**
	 * Flush rules
	 *
	 * @param bool|false $hard
	 */
	public function flush_rules( $hard = false ) {
		// did you flush already?
		if ( $this->yes_mother ) {
			return; // yes, mother!
		}

		flush_rewrite_rules($hard);

		if ( defined('WP_DEBUG') && WP_DEBUG ) {
			error_log('XML Sitemap Feeds rewrite rules flushed');
		}

		$this->yes_mother = true;
	}

	/**
	 * register google news taxonomies
	 */
	public function register_gn_taxonomies() {
		$defaults = $this->defaults('news_tags');
		$options = $this->get_option('news_tags');

		$post_types = !empty($options['post_type']) ? $options['post_type'] : $defaults['post_type'];

		register_taxonomy( 'gn-genre', $post_types, array(
			'hierarchical' => true,
			'labels' => array(
				'name' => __('Google News Genres','xml-sitemap-feed'),
				'singular_name' => __('Google News Genre','xml-sitemap-feed'),
				'all_items' => translate('All') // __('All Genres','xml-sitemap-feed')
				//'menu_name' => __('GN Genres','xml-sitemap-feed'),
			),
			'public' => false,
			'show_ui' => true,
			'show_tagcloud' => false,
			'query_var' => false,
			'capabilities' => array( // prevent creation / deletion
				'manage_terms' => 'nobody',
				'edit_terms' => 'nobody',
				'delete_terms' => 'nobody',
				'assign_terms' => 'edit_posts'
			)
		));
	}

	/**
	 * Echo usage info
	 * for debugging
	 */
	public function _e_usage() {
		if ( defined('WP_DEBUG') && WP_DEBUG == true ) {
			echo '<!-- Queries executed '.get_num_queries();
			if(function_exists('memory_get_peak_usage')) {
				echo ' | Peak memory usage '.round(memory_get_peak_usage()/1024/1024,2).'M';
			}
			echo ' -->';
		}
	}

	/**
	* CONSTRUCTOR
	*/

	function __construct( $basename = 'xml-sitemap-feed/xml-sitemap.php' ) {
		$this->plugin_basename = $basename;

		// sitemap element filters
		add_filter( 'the_title_xmlsitemap', 'strip_tags' );
		add_filter( 'the_title_xmlsitemap', 'ent2ncr', 8 );
		add_filter( 'the_title_xmlsitemap', 'esc_html' );
		add_filter( 'bloginfo_xmlsitemap', 'ent2ncr', 8 );

		// main REQUEST filtering function
		add_filter( 'request', array($this, 'filter_request'), 1 );

		// TEXT DOMAIN...
		add_action( 'plugins_loaded', array($this,'plugins_loaded'), 11 );

		// REWRITES
		add_action( 'generate_rewrite_rules', array($this, 'rewrite_rules') );
		add_filter( 'user_trailingslashit', array($this, 'trailingslash') );

		// TAXONOMIES, ACTIONS, UPGRADE...
		add_action( 'init', array($this,'init'), 0 );

		// REGISTER SETTINGS, SETTINGS FIELDS...
		add_action( 'admin_init', array($this,'admin_init'), 0 );

		// ROBOTSTXT
		add_action( 'do_robotstxt', array($this, 'robots'), 0 );
		add_filter( 'robots_txt', array($this, 'robots_txt'), 9 );

		// PINGING
		add_action( 'transition_post_status', array($this, 'do_pings'), 10, 3 );

		// CLEAR OBJECT CACHE KEYS
		add_action( 'clean_post_cache', array($this, 'clean_post_cache'), 99, 2 );

		// NGINX HELPER PURGE URLS
		add_filter( 'rt_nginx_helper_purge_urls', array($this, 'nginx_helper_purge_urls'), 10, 2 );
	}

}
