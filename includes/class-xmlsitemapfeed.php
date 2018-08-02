<?php
/* ------------------------------
 *      XMLSitemapFeed CLASS
 * ------------------------------ */

class XMLSitemapFeed {

	/**
	* Plugin base name
	* @var string
	*/
	public static $plugin_basename;

	/**
	* Plugin version
	* @var string
	*/
	public static $plugin_version;

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
	 * @var null/string $blog_language
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
	private $disabled_post_types_news = array('attachment','page');

	/**
	 * Excluded taxonomies
	 *
	 * post format taxonomy is disabled
	 * @var array
	 */
	private $disabled_taxonomies = array('post_format','product_shipping_class');

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
	 * Global values used for allowed urls and priority calculation
	 */
	private $domain;
	private $scheme;
	private $home_url;
	private $plain_permalinks;
	private $lastmodified;
	private $timespan;

	private $taxonomy_termmaxposts = null;

	private $frontpages = null;
	private $blogpages = null;

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
	 * Whether or not to use plain permalinks
	 * Used for sitemap index and admin page
	 *
	 * @return bool
	 */
	public function plain_permalinks() {
		if ( empty( $this->plain_permalinks ) ) {
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
				'image' => 'featured',
				'genres' => array(
					'default' => ''
				)
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
	}

	/**
	 * Get defaults
	 *
	 * @param bool|false $key
	 *
	 * @return array
	 */
	protected function defaults($key = false) {
		if ( empty($this->defaults) )
			$this->set_defaults();

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
	protected function disabled_post_types( $sitemap = '' ) {
		if ( 'news' == $sitemap )
			return $this->disabled_post_types_news;

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
		if ( is_object($sitepress) && method_exists($sitepress, 'get_languages') && method_exists($sitepress, 'get_object_id') ) {
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
		$output = '<?xml version="1.0" encoding="' . get_bloginfo('charset') . '"?>' . PHP_EOL;

		// which style sheet
		switch ($style) {
			case 'index':
			$output .= '<?xml-stylesheet type="text/xsl" href="' . plugins_url('xsl/sitemap-index.xsl',__FILE__) . '?ver=' . self::$plugin_version .'"?>' . PHP_EOL;
			break;

			case 'news':
			$output .= '<?xml-stylesheet type="text/xsl" href="' . plugins_url('xsl/sitemap-news.xsl',__FILE__) . '?ver=' . self::$plugin_version .'"?>' . PHP_EOL;
			break;

			default:
			$output .= '<?xml-stylesheet type="text/xsl" href="' . plugins_url('xsl/sitemap.xsl',__FILE__) . '?ver=' . self::$plugin_version .'"?>' . PHP_EOL;
		}

		$output .= '<!-- generated-on="' . date('Y-m-d\TH:i:s+00:00') . '" -->' . PHP_EOL;
		$output .= '<!-- generator="XML & Google News Sitemap Feed plugin for WordPress" -->' . PHP_EOL;
		$output .= '<!-- generator-url="https://status301.net/wordpress-plugins/xml-sitemap-feed/" -->' . PHP_EOL;
		$output .= '<!-- generator-version="' . self::$plugin_version . '" -->' . PHP_EOL;

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
		$lastmod = '';

		if ( 'post_type' == $sitemap ) :
			global $post;

			// if blog page then look for last post date
			if ( $post->post_type == 'page' && $this->is_home($post->ID) ) {
				return get_lastpostmodified('gmt'); // TODO limit to sitemap included post types...
			}

			$lastmod = get_post_modified_time( 'Y-m-d H:i:s', true, $post->ID );

			$options = $this->get_post_types();
			if ( !empty($options[$post->post_type]['update_lastmod_on_comments']) ) {
				$lastcomment = get_comments( array(
					'status' => 'approve',
					'number' => 1,
					'post_id' => $post->ID,
				) );

				if ( isset($lastcomment[0]->comment_date_gmt) )
					if ( mysql2date( 'U', $lastcomment[0]->comment_date_gmt, false ) > mysql2date( 'U', $lastmod, false ) )
						$lastmod = $lastcomment[0]->comment_date_gmt;
			}

			// make sure lastmod is not older than publication date (happens on scheduled posts)
			if ( isset($post->post_date_gmt) && strtotime($post->post_date_gmt) > strtotime($lastmod) ) {
				$lastmod = $post->post_date_gmt;
			};

		elseif ( 'taxonomy' == $sitemap ) :

			if ( is_object($term) ) {
				$lastmod = get_term_meta( $term->term_id, 'term_modified_gmt', true );

				if ( empty($lastmod) ) {
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
					$lastmod = isset($posts[0]->post_date_gmt) ? $posts[0]->post_date_gmt : '';
					// get post date here, not modified date because we're only
					// concerned about new entries on the (first) taxonomy page

					update_term_meta( $term->term_id, 'term_modified_gmt', $lastmod );
				}
			} else {

				$obj = get_taxonomy($term);

				$lastmodified = array();
				foreach ( (array)$obj->object_type as $object_type ) {
					$lastmodified[] = get_lastpostdate( 'gmt', $object_type );
					// get post date here, not modified date because we're only
					// concerned about new entries on the (first) taxonomy page
				}

				sort($lastmodified);
				$lastmodified = array_filter($lastmodified);
				$lastmod = end( $lastmodified );
			};

		endif;

		return $lastmod;
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
		return !empty($return) ? '<lastmod>'.$return.'</lastmod>' . PHP_EOL : '';
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
		$images = array();

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
						$images[] = array(
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
					$images[] =  array(
						'loc' => esc_attr( esc_url_raw( $url ) ),
						'title' => apply_filters( 'the_title_xmlsitemap', $attachment->post_title ),
						'caption' => apply_filters( 'the_title_xmlsitemap', $attachment->post_excerpt )
						// 'caption' => apply_filters( 'the_title_xmlsitemap', get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ) )
					);
				}
			}
		}

		return ( !empty($images) ) ? $images : false;
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
		$priority = 0.5;

		if ( 'post_type' == $sitemap ) :

			global $post;
			$options = $this->get_post_types();
			$defaults = $this->defaults('post_types');
			$priority_meta = get_metadata('post', $post->ID, '_xmlsf_priority' , true);

			if ( '' !== $priority_meta ) {
				$priority = floatval(str_replace(',','.',$priority_meta));
			} elseif ( !empty($options[$post->post_type]['dynamic_priority']) ) {
				$post_modified = mysql2date('U',$post->post_modified_gmt, false);

				if ( isset($options[$post->post_type]['priority']) ) {
					$priority = floatval(str_replace(',','.',$options[$post->post_type]['priority']));
				} else {
					$priority = floatval($defaults[$post->post_type]['priority']);
				}

				// reduce by age
				// NOTE : home/blog page gets same treatment as sticky post, i.e. no reduction by age
				if ( !is_sticky($post->ID) && !$this->is_home($post->ID) && $this->timespan > 0 ) {
					$priority -= $priority * ( $this->lastmodified - $post_modified ) / $this->timespan;
				}

				// increase by relative comment count
				if ( $post->comment_count > 0 && $priority <= 0.9 ) {
					$priority += 0.1 + ( 0.9 - $priority ) * $post->comment_count / wp_count_comments($post->post_type)->approved;
				}

				// make sure we're not below 0.1 after automatic calculation
				if ( $priority < .1 ) {
					$priority = .1;
				}
			} else {
				$priority = ( isset($options[$post->post_type]['priority']) && is_numeric($options[$post->post_type]['priority']) ) ? $options[$post->post_type]['priority'] : $defaults[$post->post_type]['priority'];
			}

		elseif ( 'taxonomy' == $sitemap ) :

			$options = $this->get_option('taxonomy_settings');
			$options['priority'] = empty($options['priority']) ? $this->defaults['taxonomy_settings']['priority']: floatval($options['priority']);

			$priority = $options['priority'];

			if ( $options['priority'] > 0.1 && !empty($options['dynamic_priority']) && is_object($term) ) {
				// set first term post count as maximum
				if ( null == $this->taxonomy_termmaxposts ) {
					$this->taxonomy_termmaxposts = $term->count;
				}

				$priority -= ( $this->taxonomy_termmaxposts - $term->count ) * ( $priority - 0.1 ) / $this->taxonomy_termmaxposts;
			}

		endif;

		// a final check for limits
		if ( $priority < 0.1 ) {
			$priority = 0.1;
		}
		if ( $priority > 1 ) {
			$priority = 1;
		}

		return round( $priority, 1 );
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
		} elseif ( is_object($sitepress) && method_exists($sitepress, 'get_languages') && method_exists($sitepress, 'language_url') ) {
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

		if ( $this->plain_permalinks() ) {
			$name = '?feed=sitemap-'.$sitemap;
			$name .= $type ? '-'.$type : '';
			$name .= $param ? '&m='.$param : '';
			$name .= isset($split_url[1]) && !empty($split_url[1]) ? '&' . $split_url[1] : '';
		} else {
			$name = 'sitemap-'.$sitemap;
			$name .= $type ? '-'.$type : '';
			$name .= $param ? '.'.$param : '';
			$name .= '.xml';
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
		if ( is_object($sitepress) && method_exists($sitepress, 'get_language_for_element') ) {
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
		echo '# XML Sitemap & Google News Feeds version ' . self::$plugin_version . ' - https://status301.net/wordpress-plugins/xml-sitemap-feed/' . PHP_EOL;

		if ( '1' != get_option('blog_public') ) {
			echo '# XML Sitemaps are disabled while this site is not public. Please see Site Visibility on Settings > Reading.' . PHP_EOL;
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
			$xmlsf_rules[ 'sitemap-home\.xml$' ] = $wp_rewrite->index . '?feed=sitemap-home';

			// add rules for post types (can be split by month or year)
			foreach ( $this->get_post_types() as $post_type ) {
				if ( isset($post_type['active']) && '1' == $post_type['active'] ) {
					$xmlsf_rules[ 'sitemap-posttype-' . $post_type['name'] . '\.([0-9]+)?\.xml$' ] = $wp_rewrite->index . '?feed=sitemap-posttype-' . $post_type['name'] . '&m=$matches[1]';
				}
			}

			// add rules for taxonomies
			foreach ( $this->get_taxonomies() as $taxonomy ) {
				$xmlsf_rules[ 'sitemap-taxonomy-' . $taxonomy . '\.xml$' ] = $wp_rewrite->index . '?feed=sitemap-taxonomy-' . $taxonomy;
			}

			$urls = $this->get_urls();
			if(!empty($urls)) {
				$xmlsf_rules[ 'sitemap-custom\.xml$' ] = $wp_rewrite->index . '?feed=sitemap-custom';
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
			if ( is_object($wpml_query_filter) ) {
				remove_filter( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ) );
				remove_filter( 'posts_where', array( $wpml_query_filter, 'posts_where_filter' ) );
				add_action( 'the_post', array( $this, 'wpml_language_switcher' ) );
			}

			$feed = explode( '-' , $request['feed'] );

			if ( !isset( $feed[1] ) ) return $request;

			switch( $feed[1] ) {

				case 'news':

					// prepare for news and return modified request
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

					break;

				case 'posttype':

					if ( !isset( $feed[2] ) ) break;

					$options = $this->get_post_types();

					// prepare priority calculation
					if ( !empty($options[$feed[2]]['dynamic_priority']) ) {
						// last posts or page modified date in Unix seconds
						$this->lastmodified = mysql2date('U',get_lastpostmodified('gmt',$feed[2]),false);
						// uses get_firstpostdate() function defined in xml-sitemap/inc/functions.php !
						$this->timespan = $this->lastmodified - mysql2date('U',get_firstpostdate('gmt',$feed[2]),false);
					};

					// setup filter
					add_filter( 'post_limits', array( $this, 'filter_limits' ) );

					$request['post_type'] = $feed[2];
					$request['orderby'] = 'modified';
					$request['is_date'] = false;

					break;

				case 'taxonomy':

					if ( !isset( $feed[2] ) ) break;

					// WPML compat
					global $sitepress;
					if ( is_object($sitepress) ) {
						remove_filter( 'get_terms_args', array($sitepress,'get_terms_args_filter') );
						remove_filter( 'get_term', array($sitepress,'get_term_adjust_id'), 1 );
						remove_filter( 'terms_clauses', array($sitepress,'terms_clauses') );
						$sitepress->switch_lang('all');
					}

					add_filter( 'get_terms_args', array( $this, 'set_terms_args' ) );

					// pass on taxonomy name via request
					$request['taxonomy'] = $feed[2];

					break;

				default:
				// do nothing
			}

		endif;

		return $request;
	}

	/**
	 * Terms arguments filter
	 * Does not check if we are really in a sitemap feed.
	 *
	 * @param $args
	 *
	 * @return array
	 */
	function set_terms_args( $args ) {
		// https://developer.wordpress.org/reference/classes/wp_term_query/__construct/
		$options = $this->get_option('taxonomy_settings');
		$args['number'] = isset($options['term_limit']) ? intval($options['term_limit']) : $this->defaults['taxonomy_settings']['term_limit'];
		$args['order'] = 'DESC';
		$args['orderby'] = 'count';
		$args['pad_counts'] = true;
		$args['lang'] = '';
		$args['hierachical'] = 0;
		$args['suppress_filter'] = true;

		return $args;
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

		if ( defined('WP_DEBUG') && WP_DEBUG ) {
			error_log( print_r( $response, true ) );
		}

		return wp_remote_retrieve_response_code($response);
	}

	/**
	 * Do pings
	 *
	 * @param $new_status
	 * @param $old_status
	 * @param $post
	 */
	public function do_pings($new_status, $old_status, $post) {

		// are we publishing?
		if ( $old_status != 'publish' && $new_status == 'publish' ) {

			$sitemaps = $this->get_sitemaps();
			$did_pong = $this->get_option('pong');
			$pong = $did_pong;
			$data = $this->defaults('ping');

			// first check if news sitemap is set
			if ( !empty($sitemaps['sitemap-news']) ) {
				// then check if we've got a post type that is included in our news sitemap
				$news_tags = $this->get_option('news_tags');
				if ( !empty($news_tags['post_type']) && is_array($news_tags['post_type']) && in_array($post->post_type,$news_tags['post_type']) ) {

					// TODO: check if we're posting to an included category!

					// loop through ping targets
					foreach ($this->get_ping() as $se => $settings) {
						// check active switch and if we did not ping already within the last 5 minutes
						if ( empty($settings['active'])
							 || ( !empty($did_pong)
								 && is_array($did_pong)
								 && !empty($did_pong[$se][$sitemaps['sitemap-news']]['time'])
								 && (int)$did_pong[$se][$sitemaps['sitemap-news']]['time'] + 300 > time() )
							) {
							continue;
						}
						// ping !
						if ( isset($data[$se]['req'], $data[$se]['uri'])
						 	 AND $code = $this->ping( add_query_arg( $data[$se]['req'], urlencode(trailingslashit(get_bloginfo('url')).$sitemaps['sitemap-news']), $data[$se]['uri'] ) ) ) {
							$pong[$se][$sitemaps['sitemap-news']] = array( 'time' => time(), 'code' => $code );
						}
					}
				}
			}

			// first check if regular sitemap is set
			if ( !empty($sitemaps['sitemap']) ) {
				// then check if we've got a post type that is included in our sitemap
				foreach($this->get_post_types() as $post_type) {

					if ( is_array($post_type) && isset($post_type['name']) && $post->post_type == $post_type['name'] ) {

						foreach ($this->get_ping() as $se => $settings) {

							// check active switch and if we did not ping already within the last hour
							if ( empty($settings['active'])
								 || !empty($did_pong)
								 && is_array($did_pong)
								 && !empty($did_pong[$se][$sitemaps['sitemap']]['time'])
								 && (int)$did_pong[$se][$sitemaps['sitemap']]['time'] + 3600 > time() ) {
								continue;
							}

							// ping !
							if ( isset($data[$se]['req'], $data[$se]['uri'])
								 AND $code = $this->ping( add_query_arg( $data[$se]['req'], urlencode(trailingslashit(get_bloginfo('url')).$sitemaps['sitemap']), $data[$se]['uri'] ) ) ) {
								$pong[$se][$sitemaps['sitemap']] = array( 'time' => time(), 'code' => $code );
							}
						}
					}
				}
			}

			if ( $pong != $did_pong ) update_option($this->prefix.'pong',$pong);
		}
	}

	/**
	* CLEARING & PURGING
	*/

	/**
	 * Clear settings
	 */
	public function clear_settings() {
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
		// are permalinks set?
		if ( $this->plain_permalinks() ) {
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
		$this->check_static_files();

		if ( $old_version !== 0 ) :

			if ( version_compare('4.4', $old_version, '>') ) {
				// remove robots.txt rules blocking stylesheets
			 	if ( $robot_rules = get_option( $this->prefix.'robots' ) ) {
					$robot_rules = str_replace(array('Disallow: */wp-content/','Allow: */wp-content/uploads/'),'',$robot_rules);
					delete_option( $this->prefix.'robots' );
					add_option( $this->prefix.'robots', $robot_rules, '', 'no' );
				}

				delete_option( $this->prefix.'pong' );
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

		update_option( $this->prefix.'version', self::$plugin_version );

		if ( defined('WP_DEBUG') && WP_DEBUG ) {
			error_log('XML Sitemap Feeds upgraded from '.$old_version.' to '.self::$plugin_version);
		}
	}

	/**
	 * Plugins loaded: load text domain
	 */
	public function plugins_loaded() {
		// UPGRADE
		$version = get_option( 'xmlsf_version', 0 );

		if ( version_compare(self::$plugin_basename, $version, '>') ) {
			$this->upgrade($version);
		}
	}

	/**
	 * Check for static sitemap files
	 */
	public function check_static_files() {
		$files = array();

		if ( !is_multisite() || is_main_site() || is_network_admin() ) {
			$home_path = trailingslashit( get_home_path() );
			$check_for = $this->get_sitemaps();
			$check_for['robots'] = 'robots.txt';
			foreach ( $check_for as $name => $pretty ) {
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
		// TEXT DOMAIN
		if ( is_admin() ) { // translations needed on admin only
			load_plugin_textdomain('xml-sitemap-feed', false, dirname( self::$plugin_basename ) . '/languages' );
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

	function __construct( $basename, $version ) {
		self::$plugin_basename = $basename;
		self::$plugin_version = $version;

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

		// ROBOTSTXT
		add_action( 'do_robotstxt', array($this, 'robots'), 0 );
		add_filter( 'robots_txt', array($this, 'robots_txt'), 9 );

		// PINGING
		add_action( 'transition_post_status', array($this, 'do_pings'), 10, 3 );

		// CLEAR OBJECT CACHE KEYS
		add_action( 'clean_post_cache', array($this, 'clean_post_cache'), 99, 2 );

		// NGINX HELPER PURGE URLS
		add_filter( 'rt_nginx_helper_purge_urls', array($this, 'nginx_helper_purge_urls'), 10, 2 );

		register_activation_hook( self::$plugin_basename, 'flush_rewrite_rules' );
	}
}
