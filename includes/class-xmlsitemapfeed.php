<?php
/* ------------------------------
 *      XMLSitemapFeed CLASS
 * ------------------------------ */

class XMLSitemapFeed {

	/**
	* Plugin variables
	*/

	// Pretty permalinks base name
	public $base_name = 'sitemap';

	// Pretty permalinks extension
	public $extension = 'xml';

	// Database options prefix
	private $prefix = 'xmlsf_';

	// Timezone and default language
	private $timezone = null;
	private $blog_language = null;

	// Flushed flag
	private $yes_mother = false;

	private $defaults = array();
	private $disabled_post_types = array('attachment'); /* attachment post type is disabled... images are included via tags in the post and page sitemaps */
	private $disabled_taxonomies = array('post_format'); /* post format taxonomy is brute force disabled for now; might come back... */
	private $gn_genres = array(
				'PressRelease',
				'Satire',
				'Blog',
				'OpEd',
				'Opinion',
				'UserGenerated'
				);

	// Global values used for priority and changefreq calculation
	private $domain;
	private $firstdate;
	private $lastmodified; // unused at the moment
	private $postmodified = array();
	private $termmodified = array();
	private $blogpage;
	private $images = array();

	// make some private parts public ;)

	public function prefix()
	{
		return $this->prefix;
	}

	public function gn_genres()
	{
		return $this->gn_genres;
	}

	public function domain()
	{
		// allowed domain
		if (empty($this->domain)) {
			$url_parsed = parse_url(home_url()); // second parameter PHP_URL_HOST for only PHP5 + ...
			$this->domain = str_replace("www.","",$url_parsed['host']);
		}

		return $this->domain;
	}

	// default options
	private function set_defaults()
	{
		// sitemaps
		if ( '1' == get_option('blog_public') )
			$this->defaults['sitemaps'] = array(
					'sitemap' => XMLSF_NAME
					);
		else
			$this->defaults['sitemaps'] = array();

		// post_types
		$this->defaults['post_types'] = array();
		foreach ( get_post_types(array('public'=>true),'names') as $name ) { // want 'publicly_queryable' but that excludes pages for some weird reason
			// skip unallowed post types
			if (in_array($name,$this->disabled_post_types))
				continue;

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

		foreach ( $active_arr as $name )
			if ( isset($this->defaults['post_types'][$name]) )
				$this->defaults['post_types'][$name]['active'] = '1';

		if ( isset($this->defaults['post_types']['post']) ) {
			if (wp_count_posts('post')->publish > 500)
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
						'uri' => 'http://www.google.com/webmasters/tools/ping?sitemap=',
						'type' => 'GET',
						'news' => '1'
						),
					'bing' => array (
						'active' => '1',
						'uri' => 'http://www.bing.com/ping?sitemap=',
						'type' => 'GET',
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
		$this->defaults['robots'] = "";

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
							'default' => array('Blog')
							),
						'keywords' => array(
							'from' => 'category',
							'default' => ''
							)
						);
	}

	/**
	* QUERY FUNCTIONS
	*/

	protected function timezone()
	{
		$gmt = date_default_timezone_set('UTC');
		if ( $this->timezone === null ) {
			$this->timezone = $gmt ? 'gmt' : 'blog';
		}
		return $this->timezone;
	}

	protected function defaults($key = false)
	{
		if (empty($this->defaults))
			$this->set_defaults();

		if ($key) {
			$return = ( isset($this->defaults[$key]) ) ? $this->defaults[$key] : '';
		} else {
			$return = $this->defaults;
		}

		return apply_filters( 'xmlsf_defaults', $return, $key );
	}

	public function get_option($option)
	{
		return get_option($this->prefix.$option, $this->defaults($option));
	}

	public function get_sitemaps()
	{
		$return = $this->get_option('sitemaps');

		// make sure it's an array we are returning
		return (!empty($return)) ? (array)$return : array();
	}

	public function get_ping()
	{
		$return = $this->get_option('ping');

		// make sure it's an array we are returning
		return (!empty($return)) ? (array)$return : array();
	}

	protected function disabled_post_types()
	{
		return $this->disabled_post_types;
	}

	protected function disabled_taxonomies()
	{
		return $this->disabled_taxonomies;
	}

	public function get_post_types()
	{
		$return = $this->get_option('post_types');

		// make sure it's an array we are returning
		return (!empty($return)) ? (array)$return : array();
	}

	public function have_post_types()
	{
		$return = array();

		foreach ( $this->get_post_types() as $type => $values ) {
			if(!empty($values['active'])) {
				$count = wp_count_posts( $values['name'] );
				if ($count->publish > 0) {
					$values['count'] = $count->publish;
					$return[$type] = $values;
				}
			}
		}

		// make sure it's an array we are returning
		return (!empty($return)) ? (array)$return : array();
	}

	public function get_taxonomies()
	{
		$return = $this->get_option('taxonomies');

		// make sure it's an array we are returning
		return (!empty($return)) ? (array)$return : array();
	}

	public function get_custom_sitemaps()
	{
		$urls = $this->get_option('custom_sitemaps');
		// make sure it's an array we are returning
		if(!empty($urls)) {
			$return = ( !is_array($urls) ) ? explode( "\n", $urls ) : $urls;
		} else {
			$return = array();
		}
		return apply_filters( 'xmlsf_custom_sitemaps', $return );
	}

	public function get_urls()
	{
		$urls = $this->get_option('urls');
		// make sure it's an array we are returning
		if(!empty($urls)) {
			$return = ( !is_array($urls) ) ? explode( "\n", $urls ) : $urls;
		} else {
			$return = array();
		}
		return apply_filters( 'xmlsf_custom_urls', $return );
	}

	public function get_domains()
	{
		$domains = $this->get_option('domains');
		if (!empty($domains) && is_array($domains))
			return array_merge( array( $this->domain() ), $domains );
		else
			return array( $this->domain() );
	}

	public function get_archives($post_type = 'post', $type = '')
	{
		global $wpdb;
		$return = array();
		if ( 'monthly' == $type ) {
			$query = "SELECT YEAR(post_date) AS `year`, LPAD(MONTH(post_date),2,'0') AS `month`, count(ID) as posts FROM $wpdb->posts WHERE post_type = '$post_type' AND post_status = 'publish' GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date DESC";
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
			}
		} elseif ('yearly' == $type) {
			$query = "SELECT YEAR(post_date) AS `year`, count(ID) as posts FROM $wpdb->posts WHERE post_type = '$post_type' AND post_status = 'publish' GROUP BY YEAR(post_date) ORDER BY post_date DESC";
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
			}
		} else {
			$return[0] = $this->get_index_url('posttype', $post_type); // $sitemap = 'home', $type = false, $param = false
		}
		return $return;
	}

	public function get_robots()
	{
		return ( $robots = $this->get_option('robots') ) ? $robots : '';
	}

	public function do_tags( $type = 'post' )
	{
		$return = $this->get_post_types();

		// make sure it's an array we are returning
		return (
				is_string($type) &&
				isset($return[$type]) &&
				!empty($return[$type]['tags'])
				) ? (array)$return[$type]['tags'] : array();
	}

	public function is_home($id)
	{
			if ( empty($this->blogpage) ) {
				$blogpage = get_option('page_for_posts');

				if ( !empty($blogpage) ) {
					global $polylang,$sitepress; // Polylang and WPML compat
					if ( isset($polylang) && is_object($polylang) && isset($polylang->model) && is_object($polylang->model) && method_exists($polylang->model, 'get_translations') )
						$this->blogpage = $polylang->model->get_translations('post', $blogpage);
					if ( isset($sitepress) && is_object($sitepress) && method_exists($sitepress, 'get_languages') && method_exists($sitepress, 'get_object_id') )
						foreach ( array_keys ( $sitepress->get_languages(false,true) ) as $term )
							$this->blogpage[] = $sitepress->get_object_id($id,'page',false,$term);
					else
						$this->blogpage = array($blogpage);
				} else {
					$this->blogpage = array('-1');
				}
			}

			return in_array($id,$this->blogpage);
	}

	/**
	* TEMPLATE FUNCTIONS
	*/

	public function modified($sitemap = 'post_type', $term = '')
	{
		if ('post_type' == $sitemap) :

			global $post;

			// if blog page then look for last post date
			if ( $post->post_type == 'page' && $this->is_home($post->ID) )
				return get_lastmodified('GMT','post');

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
					$posts = get_posts ( array(
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
				return get_lastdate( 'gmt', $obj->object_type );
				// uses get_lastdate() function defined in xml-sitemap/hacks.php !
				// which is a shortcut: returns last post date, not last modified date...
				// TODO find the long way home: take tax type, get all terms,
				// do tax_query with all terms for one post and get its lastmod date
				// ... or can 'terms' in tax_query be empty?
			}

		else :

			return '';

		endif;
	}

	public function get_images($sitemap = '')
	{
		global $post;
		if ( empty($this->images[$post->ID]) ) {
			if ('news' == $sitemap) {
				$options = $this->get_option('news_tags');
				$which = isset($options['image']) ? $options['image'] : '';
			} else {
				$options = $this->get_post_types();
				$which = isset($options[$post->post_type]['tags']['image']) ? $options[$post->post_type]['tags']['image'] : '';
			}
			if('attached' == $which) {
				$args = array( 'post_type' => 'attachment', 'post_mime_type' => 'image', 'numberposts' => -1, 'post_status' =>'inherit', 'post_parent' => $post->ID );
				$attachments = get_posts($args);
				if ($attachments) {
					foreach ( $attachments as $attachment ) {
						$url = wp_get_attachment_image_src( $attachment->ID, 'full' );
						$this->images[$post->ID][] = array(
										'loc' => esc_attr( esc_url_raw( $url[0] ) ), // use esc_attr() to entity escape & here ?? esc_url() creates &#038; which is not what we want...
										'title' => apply_filters( 'the_title_xmlsitemap', $attachment->post_title ),
										'caption' => apply_filters( 'the_title_xmlsitemap', $attachment->post_excerpt )
										);
					}
				}
			} elseif ('featured' == $which) {
				if (has_post_thumbnail( $post->ID ) ) {
					$attachment = get_post(get_post_thumbnail_id( $post->ID ));
					$url = wp_get_attachment_image_src( $attachment->ID, 'full' );
					$this->images[$post->ID][] =  array(
										'loc' => esc_attr( esc_url_raw( $url[0] ) ),
										'title' => apply_filters( 'the_title_xmlsitemap', $attachment->post_title ),
										'caption' => apply_filters( 'the_title_xmlsitemap', $attachment->post_excerpt )
										);
				}
			}
		}
		return ( isset($this->images[$post->ID]) ) ? $this->images[$post->ID] : false;
	}

	public function get_lastmod($sitemap = 'post_type', $term = '')
	{
		$return = trim(mysql2date('Y-m-d\TH:i:s+00:00', $this->modified($sitemap,$term), false));
		return !empty($return) ? "\t<lastmod>".$return."</lastmod>\r\n\t" : '';
	}

	public function get_changefreq($sitemap = 'post_type', $term = '')
	{
		$modified = trim($this->modified($sitemap,$term));

		if (empty($modified))
			return 'weekly';

		$lastactivityage = ( gmdate('U') - mysql2date( 'U', $modified, false ) ); // post age

	 	if ( ($lastactivityage/86400) < 1 ) { // last activity less than 1 day old
	 		$changefreq = 'hourly';
	 	} elseif ( ($lastactivityage/86400) < 7 ) { // last activity less than 1 week old
	 		$changefreq = 'daily';
	 	} elseif ( ($lastactivityage/86400) < 30 ) { // last activity less than one month old
	 		$changefreq = 'weekly';
	 	} elseif ( ($lastactivityage/86400) < 365 ) { // last activity less than 1 year old
	 		$changefreq = 'monthly';
	 	} else {
	 		$changefreq = 'yearly'; // over a year old...
	 	}

	 	return $changefreq;
	}

	public function get_priority($sitemap = 'post_type', $term = '')
	{
		if ( 'post_type' == $sitemap ) :
			global $post;
			$options = $this->get_post_types();
			$defaults = $this->defaults('post_types');
			$priority_meta = get_metadata('post', $post->ID, '_xmlsf_priority' , true);

			if ( !empty($priority_meta) || $priority_meta == '0' ) {

				$priority = floatval(str_replace(",",".",$priority_meta));

			} elseif ( !empty($options[$post->post_type]['dynamic_priority']) ) {

				$post_modified = mysql2date('U',$post->post_modified_gmt, false);

				if ( empty($this->lastmodified) )
					$this->lastmodified = mysql2date('U',get_lastmodified('GMT',$post->post_type),false);
					// last posts or page modified date in Unix seconds
					// uses get_lastmodified() function defined in xml-sitemap/hacks.php !

				if ( empty($this->firstdate) )
					$this->firstdate = mysql2date('U',get_firstdate('GMT',$post->post_type),false);
					// uses get_firstdate() function defined in xml-sitemap/hacks.php !

				if ( isset($options[$post->post_type]['priority']) )
					$priority_value = floatval(str_replace(",",".",$options[$post->post_type]['priority']));
				else
					$priority_value = floatval($defaults[$post->post_type]['priority']);

				// reduce by age
				// NOTE : home/blog page gets same treatment as sticky post
				if ( is_sticky($post->ID) || $this->is_home($post->ID) )
					$priority = $priority_value;
				else
					$priority = ( $this->lastmodified > $this->firstdate ) ? $priority_value - $priority_value * ( $this->lastmodified - $post_modified ) / ( $this->lastmodified - $this->firstdate ) : $priority_value;

				if ( $post->comment_count > 0 )
					$priority = $priority + 0.1 + ( 0.9 - $priority ) * $post->comment_count / wp_count_comments($post->post_type)->approved;

			} else {

				$priority = ( isset($options[$post->post_type]['priority']) && is_numeric($options[$post->post_type]['priority']) ) ? $options[$post->post_type]['priority'] : $defaults[$post->post_type]['priority'];

			}

		elseif ( ! empty($term) ) :

			$max_priority = 0.4;
			$min_priority = 0.0;
			// TODO make these values optional

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

		// make sure we're not below zero
		if ($priority < 0)
			$priority = 0;

		// and a final trim for cases where we ended up above 1 (sticky posts with many comments)
		if ($priority > 1)
			$priority = 1;

		return number_format($priority,1);
	}

	public function get_home_urls()
	{
		$urls = array();

		global $polylang,$sitepress; // Polylang and WPML compat

		if ( isset($polylang) && is_object($polylang) && method_exists($polylang, 'get_languages') && method_exists($polylang, 'get_home_url') )
			foreach ($polylang->get_languages_list() as $term)
		    $urls[] = $polylang->get_home_url($term);
		elseif ( isset($sitepress) && is_object($sitepress) && method_exists($sitepress, 'get_languages') && method_exists($sitepress, 'language_url') )
			foreach ( array_keys ( $sitepress->get_languages(false,true) ) as $term )
				$urls[] = $sitepress->language_url($term);
		else
			$urls[] = home_url();

		return $urls;
	}

	public function get_excluded($post_type)
	{
		$exclude = array();

		if ( $post_type == 'page' and $id = get_option('page_on_front') ) { // use 'and' here for precedence of the assignement operator, thanks @kitchin
			global $polylang,$sitepress; // Polylang and WPML compat
			if ( isset($polylang) && is_object($polylang) && isset($polylang->model) && is_object($polylang->model) && method_exists($polylang->model, 'get_translations') )
				$exclude += $polylang->model->get_translations('post', $id);
			if ( isset($sitepress) && is_object($sitepress) && method_exists($sitepress, 'get_languages') && method_exists($sitepress, 'get_object_id') )
				foreach ( array_keys ( $sitepress->get_languages(false,true) ) as $term )
					$exclude[] = $sitepress->get_object_id($id,'page',false,$term);
			else
				$exclude[] = $id;
		}

		return $exclude;
	}

	public function is_allowed_domain($url)
	{
		$domains = $this->get_domains();
		$return = false;
		$parsed_url = parse_url($url);

		if (isset($parsed_url['host'])) {
			foreach( $domains as $domain ) {
				if( $parsed_url['host'] == $domain || strpos($parsed_url['host'],".".$domain) !== false ) {
					$return = true;
					break;
				}
			}
		}

		return apply_filters( 'xmlsf_allowed_domain', $return );
	}

	public function get_index_url( $sitemap = 'home', $type = false, $param = false )
	{
		$split_url = explode('?', home_url());

		$name = $this->base_name.'-'.$sitemap;

		if ( $type )
			$name .= '-'.$type;

		if ( '' == get_option('permalink_structure') || '1' != get_option('blog_public')) {
			$name = '?feed='.$name;
			$name .= $param ? '&m='.$param : '';
			$name .= isset($split_url[1]) && !empty($split_url[1]) ? '&' . $split_url[1] : '';
		} else {
			$name .= $param ? '.'.$param : '';
			$name .= '.'.$this->extension;
			$name .= isset($split_url[1]) && !empty($split_url[1]) ? '?' . $split_url[1] : '';
		}

		return esc_url( trailingslashit($split_url[0]) . $name );
	}

	public function get_language( $id )
	{
		$language = null;

		if ( empty($this->blog_language) ) {
			// get site language for default language
			$blog_language = convert_chars(strip_tags(get_bloginfo('language')));
			$allowed = array('zh-cn','zh-tw');
			if ( !in_array($blog_language,$allowed) ) {
				// bloginfo_rss('language') returns improper format so
				// we explode on hyphen and use only first part.
				$expl = explode('-', $blog_language);
				$blog_language = $expl[0];
			}

			$this->blog_language = !empty($blog_language) ? $blog_language : 'en';
		}

		// WPML compat
		global $sitepress;
		if ( isset($sitepress) && is_object($sitepress) && method_exists($sitepress, 'get_language_for_element') ) {
			$post_type = get_query_var( 'post_type', 'post' );
			$language = $sitepress->get_language_for_element( $id, 'post_'.$post_type[0] );
			//apply_filters( 'wpml_element_language_code', null, array( 'element_id' => $id, 'element_type' => $post_type ) );
		}

		// Polylang
		if ( taxonomy_exists('language') ) {
			$lang = get_the_terms($id,'language');
			if ( is_array($lang) ) {
				$lang = reset($lang);
				$language = is_object($lang) ? $lang->slug : $language;
			}
		}

		return !empty($language) ? $language : $this->blog_language;
	}


	/**
	* ROBOTSTXT
	*/

	// add sitemap location in robots.txt generated by WP
	public function robots($output)
	{
		echo "\n# XML Sitemap & Google News Feeds version ".XMLSF_VERSION." - http://status301.net/wordpress-plugins/xml-sitemap-feed/";

		if ( '1' != get_option('blog_public') ) {
			echo "\n# XML Sitemaps are disabled. Please see Site Visibility on Settings > Reading.";
		} else {
			foreach ( $this->get_sitemaps() as $pretty )
				echo "\nSitemap: " . trailingslashit(get_bloginfo('url')) . $pretty;

			if ( empty($pretty) )
				echo "\n# No XML Sitemaps are enabled. Please see XML Sitemaps on Settings > Reading.";
		}
		echo "\n\n";
	}

	// add robots.txt rules
	public function robots_txt($output)
	{
		return $output . $this->get_option('robots') . "\n\n";
	}

	/**
	* REWRITES
	*/

	/**
	 * Remove the trailing slash from permalinks that have an extension,
	 * such as /sitemap.xml (thanks to Permalink Editor plugin for WordPress)
	 *
	 * @param string $request
	 */
	public function trailingslash($request)
	{
		if (pathinfo($request, PATHINFO_EXTENSION)) {
			return untrailingslashit($request);
		}
		return $request; // trailingslashit($request);
	}

	/**
	 * Add sitemap rewrite rules
	 *
	 * @param string $wp_rewrite
	 */
	public function rewrite_rules($wp_rewrite)
	{
		$xmlsf_rules = array();
		$sitemaps = $this->get_sitemaps();

		foreach ( $sitemaps as $name => $pretty )
			$xmlsf_rules[ preg_quote($pretty) . '$' ] = $wp_rewrite->index . '?feed=' . $name;

		if (!empty($sitemaps['sitemap'])) {
			// home urls
			$xmlsf_rules[ $this->base_name . '-home\.' . $this->extension . '$' ] = $wp_rewrite->index . '?feed=sitemap-home';

			// add rules for post types (can be split by month or year)
			foreach ( $this->get_post_types() as $post_type ) {
				if ( isset($post_type['active']) && '1' == $post_type['active'] )
					$xmlsf_rules[ $this->base_name . '-posttype-' . $post_type['name'] . '\.([0-9]+)?\.?' . $this->extension . '$' ] = $wp_rewrite->index . '?feed=sitemap-posttype-' . $post_type['name'] . '&m=$matches[1]';
			}

			// add rules for taxonomies
			foreach ( $this->get_taxonomies() as $taxonomy ) {
				$xmlsf_rules[ $this->base_name . '-taxonomy-' . $taxonomy . '\.' . $this->extension . '$' ] = $wp_rewrite->index . '?feed=sitemap-taxonomy-' . $taxonomy;
			}

			$urls = $this->get_urls();
			if(!empty($urls))
				$xmlsf_rules[ $this->base_name . '-custom\.' . $this->extension . '$' ] = $wp_rewrite->index . '?feed=sitemap-custom';

		}

		$wp_rewrite->rules = $xmlsf_rules + $wp_rewrite->rules;
	}

	/**
	* REQUEST FILTER
	*/

	public function filter_request( $request )
	{
		if ( isset($request['feed']) && strpos($request['feed'],'sitemap') === 0 ) :
			// modify request parameters
			$request['post_status'] = 'publish';
			$request['no_found_rows'] = true;
			$request['cache_results'] = false;
			$request['update_post_term_cache'] = false;
			$request['update_post_meta_cache'] = false;
			$request['lang'] = ''; // Polylang

			if ( $request['feed'] == 'sitemap-news' ) {
				$defaults = $this->defaults('news_tags');
				$options = $this->get_option('news_tags');
				$news_post_type = isset($options['post_type']) && !empty($options['post_type']) ? $options['post_type'] : $defaults['post_type'];
				if (empty($news_post_type)) $news_post_type = 'post';

				// disable caching
				define('DONOTCACHEPAGE', true);
				define('DONOTCACHEDB', true);

				// set up query filters
				$zone = $this->timezone();
				if ( get_lastdate($zone, $news_post_type) > date('Y-m-d H:i:s', strtotime('-48 hours')) ) {
					add_filter('post_limits', array($this, 'filter_news_limits'));
					add_filter('posts_where', array($this, 'filter_news_where'), 10, 1);
				} else {
					add_filter('post_limits', array($this, 'filter_no_news_limits'));
				}

				global $wpml_query_filter; // WPML compat
				if ( isset($wpml_query_filter) && is_object($wpml_query_filter) ) {
					remove_filter( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ) );
					remove_filter( 'posts_where', array( $wpml_query_filter, 'posts_where_filter' ) );
				}

				// post type
				$request['post_type'] = $news_post_type;

				// categories
				if ( isset($options['categories']) && is_array($options['categories']) )
					$request['cat'] = implode(',',$options['categories']);

				return $request;
			}

			if ( strpos($request['feed'],'sitemap-posttype') === 0 ) {
				foreach ( $this->get_post_types() as $post_type ) {
					if ( $request['feed'] == 'sitemap-posttype-'.$post_type['name'] ) {
						// setup filter
						add_filter( 'post_limits', array($this, 'filter_limits') );

						$request['post_type'] = $post_type['name'];
						$request['orderby'] = 'modified';

						global $wpml_query_filter; // WPML compat
						if ( isset($wpml_query_filter) && is_object($wpml_query_filter) ) {
							remove_filter('posts_join', array($wpml_query_filter, 'posts_join_filter'));
							remove_filter('posts_where', array($wpml_query_filter, 'posts_where_filter'));
						}

						return $request;
					}
				}
			}

			if ( strpos($request['feed'],'sitemap-taxonomy') === 0 ) {
				foreach ( $this->get_taxonomies() as $taxonomy ) {
					if ( $request['feed'] == 'sitemap-taxonomy-'.$taxonomy ) {

						$request['taxonomy'] = $taxonomy;

						// WPML compat
						global $sitepress;
						if ( isset($sitepress) && is_object($sitepress) ) {
							remove_filter('get_terms_args', array($sitepress, 'get_terms_args_filter'));
							remove_filter('get_term', array($sitepress,'get_term_adjust_id'));
							remove_filter('terms_clauses', array($sitepress,'terms_clauses'));
						}

						return $request;
					}
				}
			}
		endif;

		return $request;
	}

	/**
	* FEED TEMPLATES
	*/

	// set up the sitemap index template
	public function load_template_index()
	{
		load_template( dirname( __FILE__ ) . '/feed-sitemap.php' );
	}

	// set up the sitemap home page(s) template
	public function load_template_base()
	{
		load_template( dirname( __FILE__ ) . '/feed-sitemap-home.php' );
	}

	// set up the post types sitemap template
	public function load_template()
	{
		load_template( dirname( __FILE__ ) . '/feed-sitemap-post_type.php' );
	}

	// set up the taxonomy sitemap template
	public function load_template_taxonomy()
	{
		load_template( dirname( __FILE__ ) . '/feed-sitemap-taxonomy.php' );
	}

	// set up the news sitemap template
	public function load_template_news()
	{
		load_template( dirname( __FILE__ ) . '/feed-sitemap-news.php' );
	}

	// set up the news sitemap template
	public function load_template_custom()
	{
		load_template( dirname( __FILE__ ) . '/feed-sitemap-custom.php' );
	}

	/**
	* LIMITS
	*/

	// override default feed limit
	public function filter_limits( $limit )
	{
		return 'LIMIT 0, 50000';
	}

	// only posts from the last 48 hours
	public function filter_news_where( $where = '' )
	{
		$_gmt = ( 'gmt' === $this->timezone() ) ? '_gmt' : '';
		return $where . " AND post_date" . $_gmt . " > '" . date('Y-m-d H:i:s', strtotime('-48 hours')) . "'";
	}

	// override default feed limit for GN
	public function filter_news_limits( $limits )
	{
		return 'LIMIT 0, 1000';
	}

	// in case there is no news, just take the latest post
	public function filter_no_news_limits( $limits )
	{
		return 'LIMIT 0, 1';
	}

	/**
	* PINGING
	*/

	public function ping($uri, $timeout = 3)
	{
		$options = array();
		$options['timeout'] = $timeout;

		$response = wp_remote_request( $uri, $options );

		if ( '200' == wp_remote_retrieve_response_code($response) )
			$succes = true;
		else
			$succes = false;

		return $succes;
	}

	public function do_pings($new_status, $old_status, $post)
	{
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
						if( empty($data['active']) || empty($data['news']) )
							continue;
						// and if we did not ping already within the last 5 minutes
						if( !empty($data['pong']) && is_array($data['pong']) && !empty($data['pong'][$sitemaps['sitemap-news']]) && (int)$data['pong'][$sitemaps['sitemap-news']] + 300 > time() )
								 continue;
						// ping !
						if ( $this->ping( $data['uri'].urlencode(trailingslashit(get_bloginfo('url')) . $sitemaps['sitemap-news']) ) ) {
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
							if( empty($data['active']) || empty($data['type']) || $data['type']!='GET' )
								continue;
							// and if we did not ping already within the last hour
							if( !empty($data['pong']) && is_array($data['pong']) && !empty($data['pong'][$sitemaps['sitemap']]) && (int)$data['pong'][$sitemaps['sitemap']] + 3600 > time() )
									 continue;
							// ping !
							if ( $this->ping( $data['uri'].urlencode(trailingslashit(get_bloginfo('url')) . $sitemaps['sitemap']) ) ) {
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

	public function clear_settings()
	{
		delete_option('xmlsf_version');
		foreach ( $this->defaults() as $option => $settings ) {
			delete_option('xmlsf_'.$option);
		}

		if ( defined('WP_DEBUG') && WP_DEBUG ) {
			error_log('XML Sitemap Feeds settings cleared');
		}
	}

	function cache_flush($new_status, $old_status)
	{
		// are we moving the post in or out of published status?
		if ( $new_status == 'publish' || $old_status == 'publish' ) {
			// Use cache_delete to remove single key instead of complete cache_flush. Thanks Jeremy Clarke!
			wp_cache_delete('xmlsf_get_archives', 'general');
		}
	}

	public function nginx_helper_purge_urls( $urls = array(), $redis = false )
	{
		// are permalinks set, blog public and $urls an array?
		if ( '' == get_option('permalink_structure') || '1' != get_option('blog_public') || ! is_array( $urls ) )
			return $urls;

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
						foreach ( $this->get_archives($post_type['name'],$archive) as $url )
							 $urls[] = parse_url( $url, PHP_URL_PATH);
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
						if ( !empty($url) && $this->is_allowed_domain($url) )
							$urls[] = parse_url( esc_url($url), PHP_URL_PATH);
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

	public function upgrade($old_version)
	{
		// rewrite rules not available on plugins_loaded
		// and don't flush rules from init as Polylang chokes on that
		// just remove the db option and let WP regenerate them when ready...
		delete_option('rewrite_rules');
		// ... but make sure rules are regenerated when admin is visited.
		set_transient('xmlsf_flush_rewrite_rules','');

		// remove robots.txt rule blocking stylesheets, but only one time!
		if ( version_compare('4.4', $old_version, '>') && $robot_rules = get_option($this->prefix.'robots')) {
			$robot_rules = str_replace(array("Disallow: */wp-content/","Allow: */wp-content/uploads/"),"",$robot_rules);
			delete_option($this->prefix.'robots');
			add_option($this->prefix.'robots', $robot_rules, '', 'no');
		}

		if ( version_compare('4.4.1', $old_version, '>') ) {
			// register location taxonomies then delete all terms
			register_taxonomy( 'gn-location-3', null );
			$terms = get_terms('gn-location-3',array('hide_empty' => false));
			foreach ( $terms as $term )
				wp_delete_term(	$term->term_id, 'gn-location-3' );

			register_taxonomy( 'gn-location-2', null );
			$terms = get_terms('gn-location-2',array('hide_empty' => false));
			foreach ( $terms as $term )
				wp_delete_term(	$term->term_id, 'gn-location-2' );

			register_taxonomy( 'gn-location-1', null );
			$terms = get_terms('gn-location-1',array('hide_empty' => false));
			foreach ( $terms as $term )
				wp_delete_term(	$term->term_id, 'gn-location-1' );
		}

		if ( version_compare('4.5', $old_version, '>') ) {
			// purge genres taxonomy terms
			$this->register_gn_taxonomies();
			$terms = get_terms('gn-genre',array('hide_empty' => false));
			foreach ( $terms as $term )
				wp_delete_term(	$term->term_id, 'gn-genre' );
			set_transient('xmlsf_create_genres','', 10); // flag recreation
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

		delete_option('xmlsf_version');
		add_option($this->prefix.'version', XMLSF_VERSION, '', 'no');

		if ( defined('WP_DEBUG') && WP_DEBUG ) {
			error_log('XML Sitemap Feeds upgraded from '.$old_version.' to '.XMLSF_VERSION);
		}
	}

	public function plugins_loaded()
	{
		// TEXT DOMAIN
		if ( is_admin() ) { // text domain needed on admin only
			load_plugin_textdomain('xml-sitemap-feed', false, dirname( dirname( __FILE__ ) ) . '/languages' );
		}
	}

	public function activate()
	{
		// flush permalink structure
		$this->flush_rules();

		// try to remove static sitemap files, but only if
		// this is not a multisite or we're on the main site or network activating
		if ( !is_multisite() || is_main_site() || is_network_admin() ) {
			// CHECK FOR STATIC SITEMAP FILES, DELETE IF EXIST
			$home_path = trailingslashit( get_home_path() );
			$sitemaps = $this->get_sitemaps();
			foreach ( $sitemaps as $name => $pretty ) {
				if ( file_exists( $home_path . $pretty ) )
					unlink( $home_path . $pretty );
			}
		}
	}

	public function init()
	{
		// UPGRADE
		$version = get_option('xmlsf_version', 0);

		if ( version_compare(XMLSF_VERSION, $version, '>') ) {
			$this->upgrade($version);
		}

		$sitemaps = $this->get_sitemaps();

		if (isset($sitemaps['sitemap'])) {
			// load feed templates
			add_action('do_feed_sitemap', array($this, 'load_template_index'), 10, 1);
			add_action('do_feed_sitemap-home', array($this, 'load_template_base'), 10, 1);
			add_action('do_feed_sitemap-custom', array($this, 'load_template_custom'), 10, 1);
			foreach ( $this->get_post_types() as $post_type ) {
				add_action('do_feed_sitemap-posttype-'.$post_type['name'], array($this, 'load_template'), 10, 1);
			}
			foreach ( $this->get_taxonomies() as $taxonomy ) {
				add_action('do_feed_sitemap-taxonomy-'.$taxonomy, array($this, 'load_template_taxonomy'), 10, 1);
			}
		}

		if (isset($sitemaps['sitemap-news'])) {
			// load feed template
			add_action('do_feed_sitemap-news', array($this, 'load_template_news'), 10, 1);

			// register the taxonomies
			$this->register_gn_taxonomies();

			// create terms
			if ( delete_transient('xmlsf_create_genres') ) {
				foreach ($this->gn_genres as $name) {
					wp_insert_term(	$name, 'gn-genre' );
				}
			}
		}
	}

	public function admin_init()
	{
		// CATCH TRANSIENT for reset
		if (delete_transient('xmlsf_clear_settings'))
			$this->clear_settings();

		// CATCH TRANSIENT for flushing rewrite rules after the sitemaps setting has changed
		if (delete_transient('xmlsf_flush_rewrite_rules'))
			$this->flush_rules();

		// Include the admin class file
		include_once( dirname( __FILE__ ) . '/class-xmlsitemapfeed-admin.php' );
	}

	public function flush_rules($hard = false)
	{
		// did you flush already?
		if ($this->yes_mother)
			return; // yes, mother!

		global $wp_rewrite;
		// don't need hard flush by default
		$wp_rewrite->flush_rules($hard);

		if ( defined('WP_DEBUG') && WP_DEBUG )
			error_log('XML Sitemap Feeds rewrite rules flushed');

		$this->yes_mother = true;
	}

	public function register_gn_taxonomies()
	{
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

	// for debugging
	public function _e_usage()
	{
		if (defined('WP_DEBUG') && WP_DEBUG == true) {
			echo '<!-- Queries executed '.get_num_queries();
			if(function_exists('memory_get_peak_usage'))
				echo ' | Peak memory usage '.round(memory_get_peak_usage()/1024/1024,2).'M';
			echo ' -->';
		}
	}

	/**
	* CONSTRUCTOR
	*/

	function __construct()
	{
		// sitemap element filters
		add_filter('the_title_xmlsitemap', 'strip_tags');
		add_filter('the_title_xmlsitemap', 'ent2ncr', 8);
		add_filter('the_title_xmlsitemap', 'esc_html');
		add_filter('bloginfo_xmlsitemap', 'ent2ncr', 8);

		// REQUEST main filtering function
		add_filter('request', array($this, 'filter_request'), 1 );

		// TEXT DOMAIN, UPGRADE PROCESS ...
		add_action('plugins_loaded', array($this,'plugins_loaded'), 11 );

		// REWRITES
		add_action('generate_rewrite_rules', array($this, 'rewrite_rules') );
		add_filter('user_trailingslashit', array($this, 'trailingslash') );

		// TAXONOMY
		add_action('init', array($this,'init'), 0 );

		// REGISTER SETTINGS, SETTINGS FIELDS...
		add_action('admin_init', array($this,'admin_init'), 0);

		// ROBOTSTXT
		add_action('do_robotstxt', array($this, 'robots'), 0 );
		add_filter('robots_txt', array($this, 'robots_txt'), 0 );

		// PINGING
		add_action('transition_post_status', array($this, 'do_pings'), 10, 3);

		// CLEAR OBJECT CACHE
		add_action('transition_post_status', array($this, 'cache_flush'), 99, 2);

		// NGINX HELPER PURGE URLS
		add_filter('rt_nginx_helper_purge_urls', array($this, 'nginx_helper_purge_urls'), 10, 2);

		// ACTIVATION
		register_activation_hook( XMLSF_PLUGIN_BASENAME, array($this, 'activate') );
	}
}
