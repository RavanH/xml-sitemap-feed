<?php
/* ------------------------------
 *      XMLSF Admin CLASS
 * ------------------------------ */

class XMLSitemapFeed_Admin extends XMLSitemapFeed {

	/**
	 * Static files conflicting with this plugin
	 * @var array
	 */
	private $static_files = array();

	/**
	* SETTINGS
	*/

	/* SITEMAPS */

	public function sitemaps_settings_field() {
		$options = parent::get_sitemaps();

		// TODO refer to support forum + invite plugin rating !

		// The actual fields for data entry
		include dirname( __FILE__ ) . '/views/admin/field-sitemaps.php';
	}

	public function sanitize_sitemaps_settings($new) {
		$old = parent::get_sitemaps();

		if ( isset($new['reset']) && $new['reset'] == '1' ) {
			// if reset is checked, set transient to clear all settings
			set_transient('xmlsf_clear_settings','');
		} elseif ( $old != $new ) {
			// when sitemaps are added or removed, set transient to flush rewrite rules
			set_transient('xmlsf_flush_rewrite_rules','');

			if ( empty($old['sitemap-news']) && !empty($new['sitemap-news']) )
				set_transient('xmlsf_create_genres','');
		}

		return $new;
	}

	/* PINGS */

	public function ping_settings_field() {
		$options = parent::get_ping();
		$defaults = parent::defaults('ping');
		$pong = $this->get_option('pong', array());
		$ping_data = array( 'google' => '', 'bing' => '' );

		if ( $tzstring = get_option('timezone_string') ) {
			// use same timezoneformat as translatable examples in options-general.php
			$timezone_format = translate_with_gettext_context('Y-m-d G:i:s', 'timezone date format');
			date_default_timezone_set($tzstring);
		} else {
			$timezone_format = 'Y-m-d G:i:s T';
		}

		foreach ( $defaults as $key => $values ) {
			if ( !empty($pong[$key]) ) {
				foreach ((array)$pong[$key] as $pretty => $data) {
					if ( !empty($data['time']) ) {
						if ( '200' == $data['code'] )
							$ping_data[$key] .= ' &nbsp;&ndash;&nbsp; ' . sprintf(__('Successfully sent %1$s on %2$s.','xml-sitemap-feed'),$pretty, date($timezone_format,$data['time']));
						else
							$ping_data[$key] .= ' &nbsp;&ndash;&nbsp; ' .sprintf(__('Failed to send %1$s on %2$s.','xml-sitemap-feed'),$pretty, date($timezone_format,$data['time']));
					}
				}
			}
		}

		date_default_timezone_set('UTC');

		// The actual fields for data entry
		include dirname( __FILE__ ) . '/views/admin/field-ping.php';
	}

	public function sanitize_ping_settings($new) {
		$defaults = parent::defaults('ping');
		$old = parent::get_option('ping');
		$sanitized = array();

		foreach ($defaults as $key => $values) {
			if(!isset($new[$key]))
				continue;

			if ( is_array($new[$key])  ) {
				$sanitized += array( $key => $new[$key] );
			}
		}

		return $sanitized;
	}

	/* ROBOTS */

	public function robots_settings_field() {
		// The actual fields for data entry
		include dirname( __FILE__ ) . '/views/admin/field-robots.php';
	}

	public function sanitize_robots_settings($new) {
		// clean up input
		if(is_array($new)) {
		  $new = array_filter($new);
		  $new = reset($new);
		}
		return trim(strip_tags($new));
	}

	/* RESET */

	public function reset_settings_field() {
		// The actual fields for data entry
		include dirname( __FILE__ ) . '/views/admin/field-sitemap-reset.php';
	}

	/**
	* XML SITEMAP SECTION
	*/

	public function xml_sitemap_settings() {
		// The actual settings section text
		include dirname( __FILE__ ) . '/views/admin/section-sitemap.php';
	}

	public function post_types_settings_field() {
		$post_types = get_post_types(array('public'=>true),'objects');
		if ( !is_array($post_types) || is_wp_error($post_types) )
			return;

		$options = parent::get_post_types();
		$defaults = parent::defaults('post_types');
	
		// The actual fields for data entry
		include dirname( __FILE__ ) . '/views/admin/field-sitemap-post-types.php';
	}

	public function taxonomies_settings_field() {
		$taxonomies = parent::get_taxonomies();
		$taxonomy_settings = parent::get_option('taxonomy_settings');
		$active = parent::get_option('post_types');
		$tax_list = array();

		foreach ( get_taxonomies(array('public'=>true),'objects') as $taxonomy ) {
			// skip unallowed post types
			if (in_array($taxonomy->name,parent::disabled_taxonomies()))
				continue;

			$skip = true;
			foreach ( $taxonomy->object_type as $post_type)
				if (!empty($active[$post_type]['active']) && $active[$post_type]['active'] == '1')
					$skip = false;
			if ($skip) continue; // skip if none of the associated post types are active

			$count = wp_count_terms( $taxonomy->name );
			$tax_list[] = '<label><input type="checkbox" name="'.$this->prefix.'taxonomies['.
				$taxonomy->name.']" id="xmlsf_taxonomies_'.$taxonomy->name.'" value="'.$taxonomy->name.'"'.
				checked(in_array($taxonomy->name,$taxonomies), true, false).' /> '.$taxonomy->label.' ('.$count.')</label>';

//			if ( in_array($taxonomy->name,$options) && empty($taxonomy->show_tagcloud) )
//				echo '<span class="description error" style="color: red">'.__('This taxonomy type might not be suitable for public use. Please check the urls in the taxonomy sitemap.','xml-sitemap-feed').'</span>';
		}

		if ( empty($tax_list) ) {
			echo '<p class="description warning">'.__('No taxonomies available for the currently included post types.','xml-sitemap-feed').'</p>';
			return;
		}

		// The actual fields for data entry
		include dirname( __FILE__ ) . '/views/admin/field-sitemap-taxonomies.php';
	}

	public function custom_sitemaps_settings_field() {
		$lines = parent::get_custom_sitemaps();

		// The actual fields for data entry
		include dirname( __FILE__ ) . '/views/admin/field-sitemap-custom.php';
	}

	public function urls_settings_field() {
		$urls = parent::get_urls();
		$lines = array();

		if(!empty($urls)) {
			foreach($urls as $arr) {
				if(is_array($arr))
					$lines[] = implode(" ",$arr);
			}
		}

		// The actual fields for data entry
		include dirname( __FILE__ ) . '/views/admin/field-sitemap-urls.php';
	}

	/**
	 * Domain settings field and sanitization
	 */

	 public function domains_settings_field() {
		$default = parent::domain();
		$domains = (array) parent::get_option('domains');

		// The actual fields for data entry
		include dirname( __FILE__ ) . '/views/admin/field-sitemap-domains.php';
	}

	public function sanitize_domains_settings($new) {
		$default = parent::domain();

		// clean up input
		if(is_array($new)) {
		  $new = array_filter($new);
		  $new = reset($new);
		}
		$input = $new ? explode("\n",trim(strip_tags($new))) : array();

		// build sanitized output
		$sanitized = array();
		foreach ($input as $line) {
			$line = trim($line);
			$parsed_url = parse_url(trim(filter_var($line,FILTER_SANITIZE_URL)));
			// Before PHP version 5.4.7, parse_url will return the domain as path when scheme is omitted so we do:
			if ( !empty($parsed_url['host']) ) {
				$domain = trim( $parsed_url['host'] );
			} else {
				$domain_arr = explode('/', $parsed_url['path']);
				$domain_arr = array_filter($domain_arr);
				$domain = array_shift( $domain_arr );
				$domain = trim( $domain );
			}

			// filter out empties and default domain
			if(!empty($domain) && $domain !== $default && strpos($domain,".".$default) === false)
				$sanitized[] = $domain;
		}

		return (!empty($sanitized)) ? $sanitized : '';
	}

	/**
	* GOOGLE NEWS SITEMAP SECTION
	*/

	public function news_sitemap_settings() {
		// The actual section text 
		include dirname( __FILE__ ) . '/views/admin/section-news.php';
	}

	//TODO: publication name allow tag %category% ... post_types (+ exclusion per post or none + allow inclusion per post), limit to category ...
	public function news_name_field() {
		$options = parent::get_option('news_tags');

		$name = !empty($options['name']) ? $options['name'] : '';

		// The actual fields for data entry
		include dirname( __FILE__ ) . '/views/admin/field-news-name.php';
	}

	public function news_post_type_field() {
		$defaults = parent::defaults('news_tags');
		$options = parent::get_option('news_tags');

		$news_post_type = isset($options['post_type']) && !empty($options['post_type']) ? $options['post_type'] : $defaults['post_type'];

		$post_types = get_post_types(array('public' =>true),'objects');

		// check for valid post types
		if ( !is_array($post_types) || empty($post_types) || is_wp_error($post_types) ) {
			echo '
			<p style="color: red" class="error">'.__('Error: There where no valid post types found. Without at least one public post type, a Google News Sitemap cannot be created by this plugin. Please deselect the option Google News Sitemap at <a href="#xmlsf_sitemaps">Enable XML sitemaps</a> and choose another method.','xml-sitemap-feed').'</p>';
		} else {

			$options = array();
			foreach ( $post_types as $post_type ) {
				// skip unallowed post types
				if ( !is_object($post_type) || in_array($post_type->name,$this->disabled_post_types('news')) )
					continue;

				$checked = in_array($post_type->name,$news_post_type) ? true : false;
				$disabled = false;
				if ( isset($options['categories']) && is_array($options['categories']) ) {
					// need to disable all post types that do not have the category taxonomy
					$taxonomies = get_object_taxonomies($post_type->name,'names');
					if ( !in_array('category',(array)$taxonomies) ) {
						$disabled = true;
						$checked = false;
					}
				}

				$options[] = '<label><input type="checkbox" name="'.
					$this->prefix.'news_tags[post_type][]" id="xmlsf_post_type_'.
					$post_type->name.'" value="'.$post_type->name.'" '.
					checked( $checked, true, false ).' '.
					disabled( $disabled, true, false ).' /> '.
					$post_type->label.'</label>';
			}

			// The actual fields for data entry
			include dirname( __FILE__ ) . '/views/admin/field-news-post-types.php';
		}

	}

	public function news_categories_field() {
		$options = parent::get_option('news_tags');

		if ( !empty($options['post_type']) && array( 'post' ) !== (array)$options['post_type'] )	{
			echo '
			<p class="description">' . sprintf(__('Selection based on categories will be available when <strong>only</strong> the post type %s is included above.','xml-sitemap-feed'),translate('Posts')) . '</p>';
			return;
		}

		$selected_categories = isset($options['categories']) && is_array($options['categories']) ? $options['categories'] : array();

		$cat_list = str_replace('name="post_category[]"','name="'.$this->prefix.'news_tags[categories][]"', wp_terms_checklist( null, array( 'taxonomy' => 'category', 'selected_cats' => $selected_categories, 'echo' => false ) ) );

		// The actual fields for data entry
		include dirname( __FILE__ ) . '/views/admin/field-news-categories.php';

	}

	public function news_image_field() {
		$options = parent::get_option('news_tags');

		$image = !empty($options['image']) ? $options['image'] : '';

		// The actual fields for data entry
		include dirname( __FILE__ ) . '/views/admin/field-news-image.php';
	}

	public function news_labels_field() {

		$options = parent::get_option('news_tags');

		// genres tag
		$gn_translations = array(
			'PressRelease' => __('PressRelease','xml-sitemap-feed'),
			'Satire' => __('Satire','xml-sitemap-feed'),
			'Blog' => __('Blog','xml-sitemap-feed'),
			'OpEd' => __('OpEd','xml-sitemap-feed'),
			'Opinion' => __('Opinion','xml-sitemap-feed'),
			'UserGenerated' => __('UserGenerated','xml-sitemap-feed'),
			'FactCheck' => __('FactCheck','xml-sitemap-feed')
		);
		$genres = !empty($options['genres']) ? $options['genres'] : array();
		$genres_default = !empty($genres['default']) ? (array)$genres['default'] : array();
		$disabled = array('FactCheck');

		foreach ( parent::$gn_genres as $name ) {
			$genre_list[] = '<label><input type="checkbox" name="' .
				$this->prefix . 'news_tags[genres][default][]" id="xmlsf_news_tags_genres_default_' .
				$name . '" value="' . $name . '"' . checked( in_array($name,$genres_default), true, false ) . disabled(in_array($name,$disabled), true, false ) . ' />' .
				( isset($gn_translations[$name]) && !empty($gn_translations[$name]) ? $gn_translations[$name] : $name ) . '</label>';
		}

		// The actual fields for data entry
		include dirname( __FILE__ ) . '/views/admin/field-news-labels.php';
	}

	//sanitize callback functions

	public function sanitize_post_types_settings( $new = array() ) {
		$old = parent::get_post_types();
		$defaults = parent::defaults('post_types');
		$sanitized = $new;

		foreach ($new as $post_type => $settings) {
			// when post types are (de)activated, set transient to flush rewrite rules
			if ( ( !empty($old[$post_type]['active']) && empty($settings['active']) ) || ( empty($old[$post_type]['active']) && !empty($settings['active']) ) )
				set_transient('xmlsf_flush_rewrite_rules','');

			if ( isset($settings['priority']) && is_numeric($settings['priority']) )
				$sanitized[$post_type]['priority'] = $this->sanitize_priority($settings['priority'],0.1,0.9);
			else
				$sanitized[$post_type]['priority'] = $defaults[$post_type]['priority'];
		}

		return $sanitized;
	}

	private function sanitize_priority($priority, $min = 0.0, $max = 1.0) {
			$priority = floatval(str_replace(",",".",$priority));
			if ($priority <= (float)$min)
				return number_format($min,1);
			elseif ($priority >= (float)$max)
				return number_format($max,1);
			else
				return number_format($priority,1);
	}

	public function sanitize_taxonomies_settings($new) {
		$old = parent::get_taxonomies();

		if ($old != $new) // when taxonomy types are added or removed, set transient to flush rewrite rules
			set_transient('xmlsf_flush_rewrite_rules','');

		return $new;
	}

	public function sanitize_taxonomy_settings_settings($new) {
		$defaults = parent::defaults('taxonomy_settings');
		$sanitized = array();

		$sanitized['term_limit'] = isset($new['term_limit']) ? intval($new['term_limit']) : $defaults['term_limit'];
		$sanitized['priority'] = isset($new['priority']) && is_numeric($new['priority']) ? $this->sanitize_priority($new['priority'], 0.1, 0.9) : $defaults['priority'];
		$sanitized['dynamic_priority'] = !empty($new['dynamic_priority']) ? '1' : '';

		return $sanitized;
	}

	public function sanitize_custom_sitemaps_settings($new) {
		$old = parent::get_custom_sitemaps();

		// clean up input
		if(is_array($new)) {
			$new = array_filter($new);
			$new = reset($new);
		}
		$input = $new ? explode("\n",trim(strip_tags($new))) : array();

		// build sanitized output
		$sanitized = array();
		foreach ($input as $line) {
			$line = filter_var(esc_url(trim($line)),FILTER_VALIDATE_URL,FILTER_FLAG_PATH_REQUIRED);
			if(!empty($line))
				$sanitized[] = $line;
		}

		return $sanitized;
	}

	public function sanitize_urls_settings($new) {
		$old = parent::get_urls();

		// clean up input
		if(is_array($new)) {
		  $new = array_filter($new);
		  $new = reset($new);
		}
		$input = $new ? explode("\n",trim(strip_tags($new))) : array();

		// build sanitized output
		$sanitized = array();

		foreach ($input as $line) {
			if(empty($line))
				continue;

			$arr = array_values(array_filter(explode(" ",trim($line)),array($this,'sanitize_urls_array_filter')));

			if(isset($arr[0])) {
				if(isset($arr[1]))
					$arr[1] = $this->sanitize_priority($arr[1]);
				else
					$arr[1] = '0.5';

				$sanitized[] = array( esc_url($arr[0]) , $arr[1] );
			}
		}

		if (empty($old)) {
			if (!empty($sanitized))
				set_transient('xmlsf_flush_rewrite_rules','');
		} else if (empty($sanitized)) {
			set_transient('xmlsf_flush_rewrite_rules','');
		}

		return (!empty($sanitized)) ? $sanitized : '';
	}

	public function sanitize_urls_array_filter( $url ) {
		return filter_var( $url, FILTER_VALIDATE_URL ) || is_numeric( $url );
	}

	public function sanitize_news_tags_settings($new) {
		// TODO default post type : to 'post' when none are selected
		return $new;
	}

	/**
	* META BOXES
	*/

	/* Adds a XML Sitemap box to the side column */
	public function add_meta_box () {
		foreach ( parent::get_post_types() as $post_type ) {
			// Only include metaboxes on post types that are included
			if (isset($post_type["active"]))
				add_meta_box(
					'xmlsf_section',
					__( 'XML Sitemap', 'xml-sitemap-feed' ),
					array($this,'meta_box'),
					$post_type['name'],
					'side',
					'low'
				);
		}
	}

	public function meta_box($post) {
		// Use nonce for verification
		wp_nonce_field( parent::$plugin_basename, 'xmlsf_sitemap_nonce' );

		// Use get_post_meta to retrieve an existing value from the database and use the value for the form
		$exclude = get_post_meta( $post->ID, '_xmlsf_exclude', true );
		$priority = get_post_meta( $post->ID, '_xmlsf_priority', true );
		$disabled = false;

		// disable options and (visibly) set excluded to true for private posts
		if ( 'private' == $post->post_status ) {
			$disabled = true;
			$exclude = true;
		}

		// disable options and (visibly) set priority to 1 for front page
		if ( $post->ID == get_option('page_on_front') ) {
			$disabled = true;
			$exclude = false;
			$priority = '1'; // force priority to 1 for front page
		}

		$description = sprintf(
			__('Leave empty for automatic Priority as configured on %1$s > %2$s.','xml-sitemap-feed'), 
			translate('Settings'), 
			'<a href="' . admin_url('options-reading.php') . '#xmlsf">' . translate('Reading') . '</a>'
		);

		// The actual fields for data entry
		include dirname( __FILE__ ) . '/views/admin/meta-box.php';
	}

	/* Adds a News Sitemap box to the side column */
	public function add_meta_box_news () {
		$news_tags = parent::get_option('news_tags');
		$defaults = parent::defaults('news_tags');
		$news_post_type = isset($news_tags['post_type']) && !empty($news_tags['post_type']) ? $news_tags['post_type'] : $defaults['post_type'];

		foreach ( (array)$news_post_type as $post_type ) {
      // Only include metabox on post types that are included
			add_meta_box(
				'xmlsf_news_section',
				__( 'Google News', 'xml-sitemap-feed' ),
				array($this,'meta_box_news'),
				$post_type,
				'side'
			);
		}
	}

	public function meta_box_news($post) {
		// Use nonce for verification
		wp_nonce_field( parent::$plugin_basename, 'xmlsf_sitemap_nonce' );

		// Use get_post_meta to retrieve an existing value from the database and use the value for the form
		$exclude = 'private' == $post->post_status || get_post_meta( $post->ID, '_xmlsf_news_exclude', true );
		$disabled = 'private' == $post->post_status;

		// The actual fields for data entry
		include dirname( __FILE__ ) . '/views/admin/meta-box-news.php';
	}

	/* When the post is saved, save our meta data */
	function save_metadata( $post_id ) {
		if ( !isset($post_id) )
			$post_id = (int)$_REQUEST['post_ID'];

		if ( !current_user_can( 'edit_post', $post_id ) || !isset($_POST['xmlsf_sitemap_nonce']) || !wp_verify_nonce($_POST['xmlsf_sitemap_nonce'], parent::$plugin_basename) )
			return;

		// _xmlsf_priority
		if ( isset($_POST['xmlsf_priority']) && is_numeric($_POST['xmlsf_priority']) ) {
			update_post_meta($post_id, '_xmlsf_priority', $this->sanitize_priority($_POST['xmlsf_priority']) );
		} else {
			delete_post_meta($post_id, '_xmlsf_priority');
		}

		// _xmlsf_exclude
		if ( isset($_POST['xmlsf_exclude']) && $_POST['xmlsf_exclude'] != '' ) {
			update_post_meta($post_id, '_xmlsf_exclude', $_POST['xmlsf_exclude']);
		} else {
			delete_post_meta($post_id, '_xmlsf_exclude');
		}

		// _xmlsf_news_exclude
		if ( isset($_POST['xmlsf_news_exclude']) && $_POST['xmlsf_news_exclude'] != '' ) {
			update_post_meta($post_id, '_xmlsf_news_exclude', $_POST['xmlsf_news_exclude']);
		} else {
			delete_post_meta($post_id, '_xmlsf_news_exclude');
		}
	}

	/**
	 * Catch transient flags
	 */

	function handle_flags() {
		// CATCH TRANSIENT for reset
		if ( delete_transient('xmlsf_clear_settings') ) {
			$this->clear_settings();
		}

		// CATCH TRANSIENT for flushing rewrite rules after the sitemaps setting has changed
		if ( delete_transient('xmlsf_flush_rewrite_rules') ) {
			flush_rewrite_rules();
		}

		// CATCH TRANSIENT for recreating terms
		if ( delete_transient('xmlsf_create_genres') && taxonomy_exists('gn-genre') ) {

			// check and update existing or delete not allowed terms
			$terms = get_terms( 'gn-genre', array('hide_empty' => false) );
			if ( is_array($terms) && !empty($terms) ) {
				foreach ( $terms as $term ) {
					if ( in_array($term->name,parent::$gn_genres) ) {
						$slug = strtolower($term->name);
						if ( $term->slug !== $slug )
							wp_update_term( $term->term_id, 'gn-genre', array(
								'slug' => $slug
							) );
					} else {
						wp_delete_term( $term->term_id, 'gn-genre' );
					}
				}
			}

			// add any new ones
			foreach (parent::$gn_genres as $name) {
				wp_insert_term(	$name, 'gn-genre' );
				if ( defined('WP_DEBUG') && WP_DEBUG ) {
					error_log( 'Created GN Genre taxonomy term ' . $name );
				}
			}
		}
	}

	/**
	 * Register settings and add settings fields
	 */

	function register_settings() {
		$sitemaps = parent::get_sitemaps();

		// sitemaps
		register_setting('reading', $this->prefix.'sitemaps', array($this,'sanitize_sitemaps_settings') );
		add_settings_field($this->prefix.'sitemaps', __('Enable XML sitemaps','xml-sitemap-feed'), array($this,'sitemaps_settings_field'), 'reading');

		// robots rules only when permalinks are set
		$rules = get_option( 'rewrite_rules' );
		if( ! $this->plain_permalinks() && isset( $rules['robots\.txt$'] ) ) {
			register_setting('reading', $this->prefix.'robots', array($this,'sanitize_robots_settings') );
			add_settings_field($this->prefix.'robots', __('Additional robots.txt rules','xml-sitemap-feed'), array($this,'robots_settings_field'), 'reading');
		}

		add_settings_field($this->prefix.'reset', __('Reset XML sitemaps','xml-sitemap-feed'), array($this,'reset_settings_field'), 'reading');

		if ( isset($sitemaps['sitemap-news']) ) {
			// XML SITEMAP SETTINGS
			add_settings_section('news_sitemap_section', '<a name="xmlnf"></a>'.__('Google News Sitemap','xml-sitemap-feed'), array($this,'news_sitemap_settings'), 'reading');
			// tags
			register_setting('reading', $this->prefix.'news_tags', array($this,'sanitize_news_tags_settings') );
			add_settings_field($this->prefix.'news_name', '<label for="xmlsf_news_name">'.__('Publication name','xml-sitemap-feed').'</label>', array($this,'news_name_field'), 'reading', 'news_sitemap_section');
			add_settings_field($this->prefix.'news_post_type', __('Include post types','xml-sitemap-feed'), array($this,'news_post_type_field'), 'reading', 'news_sitemap_section');
			add_settings_field($this->prefix.'news_categories', translate('Categories'), array($this,'news_categories_field'), 'reading', 'news_sitemap_section');
			add_settings_field($this->prefix.'news_image', translate('Images'), array($this,'news_image_field'), 'reading', 'news_sitemap_section');
			add_settings_field($this->prefix.'news_labels', __('Source labels','xml-sitemap-feed'), array($this,'news_labels_field'), 'reading', 'news_sitemap_section');
      		// post meta box
      		add_action( 'add_meta_boxes', array($this,'add_meta_box_news') );
		}

		if ( isset($sitemaps['sitemap']) ) {
			// XML SITEMAP SETTINGS
			add_settings_section('xml_sitemap_section', '<a name="xmlsf"></a>'.__('XML Sitemap','xml-sitemap-feed'), array($this,'xml_sitemap_settings'), 'reading');
			// post_types
			register_setting('reading', $this->prefix.'post_types', array($this,'sanitize_post_types_settings') );
			add_settings_field($this->prefix.'post_types', __('Include post types','xml-sitemap-feed'), array($this,'post_types_settings_field'), 'reading', 'xml_sitemap_section');
			// taxonomies
			register_setting('reading', $this->prefix.'taxonomies', array($this,'sanitize_taxonomies_settings') );
			register_setting('reading', $this->prefix.'taxonomy_settings', array($this,'sanitize_taxonomy_settings_settings') );
			add_settings_field($this->prefix.'taxonomies', __('Include taxonomies','xml-sitemap-feed'), array($this,'taxonomies_settings_field'), 'reading', 'xml_sitemap_section');
			// custom domains
			register_setting('reading', $this->prefix.'domains', array($this,'sanitize_domains_settings') );
			add_settings_field($this->prefix.'domains', __('Allowed domains','xml-sitemap-feed'), array($this,'domains_settings_field'), 'reading', 'xml_sitemap_section');
			// custom urls
			register_setting('reading', $this->prefix.'urls', array($this,'sanitize_urls_settings') );
			add_settings_field($this->prefix.'urls', __('Include custom web pages','xml-sitemap-feed'), array($this,'urls_settings_field'), 'reading', 'xml_sitemap_section');
			// custom sitemaps
			register_setting('reading', $this->prefix.'custom_sitemaps', array($this,'sanitize_custom_sitemaps_settings') );
			add_settings_field($this->prefix.'custom_sitemaps', __('Include custom XML Sitemaps','xml-sitemap-feed'), array($this,'custom_sitemaps_settings_field'), 'reading', 'xml_sitemap_section');
			// post meta box
			add_action( 'add_meta_boxes', array($this,'add_meta_box') );
		}

		if ( isset($sitemaps['sitemap']) || isset($sitemaps['sitemap-news']) ) {
			register_setting('writing', $this->prefix.'ping', array($this,'sanitize_ping_settings') );
			add_settings_field($this->prefix.'ping', __('Ping Services','xml-sitemap-feed'), array($this,'ping_settings_field'), 'writing');

	        // save post meta box settings
	        add_action( 'save_post', array($this,'save_metadata') );
		}
	}

	/**
	 * Check for static sitemap files
	 */
	public function check_static_files() {

		$home_path = trailingslashit( get_home_path() );
		$check_for = $this->get_sitemaps();
		if ( !empty($this->get_option('robots')) )
			$check_for['robots'] = 'robots.txt';

		foreach ( $check_for as $name => $pretty ) {
			if ( file_exists( $home_path . $pretty ) ) {
				$this->static_files[$pretty] = $home_path . $pretty;
			}
		}
	}

	public function static_files_admin_notice_deleted() {
		include dirname( __FILE__ ) . '/views/admin/notice-deleted.php';
	}

	public function static_files_admin_notice_failed() {
		include dirname( __FILE__ ) . '/views/admin/notice-failed.php';
	}

	public function static_files_admin_notice_nonce() {
		include dirname( __FILE__ ) . '/views/admin/notice-nonce-error.php';
	}

	public function static_files_admin_notice() {
		//$screen = get_current_screen();
		if ( !get_user_meta( get_current_user_id(), 'xmlsf_static_files_warning_dismissed' ) /*$screen->id === 'options-reading' */) {
			$nonce = wp_create_nonce( 'xmlsf-static-warning-nonce' );
			$number = count($this->static_files);

			include dirname( __FILE__ ) . '/views/admin/notice.php';
		}
	}

	// plugin action links

	public function add_action_link( $links ) {
		$settings_link = '<a href="' . admin_url('options-reading.php') . '#blog_public">' . translate('Settings') . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * INIT
	 */

	function init() {

		$this->handle_flags();

		$this->register_settings();

		// CHECK STATIC FILES
		if ( !current_user_can( 'manage_options' ) || ( is_multisite() && !is_super_admin() ) ) return;

		$this->check_static_files();

		if ( isset( $_GET['xmlsf-static-dismiss'] ) ) {
			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'xmlsf-static-warning-nonce' ) ) {
				add_user_meta( get_current_user_id(), 'xmlsf_static_files_warning_dismissed', 'true', true );
			}
		}

		if ( isset( $_GET['xmlsf-delete'] ) ) {
			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'xmlsf-static-warning-nonce' ) ) {
				foreach ( $this->static_files as $name => $file ) {
					if ( !in_array($name,$_GET['xmlsf-delete']) )
						continue;
					if ( unlink($file) )
						$this->deleted[] = $file;
					else
						$this->failed[] = $file;
					unset($this->static_files[$name]);
				}
				if ( !empty($this->deleted) ) {
					add_action( 'admin_notices', array($this,'static_files_admin_notice_deleted') );
				}
				if ( !empty($this->failed) ) {
					add_action( 'admin_notices', array($this,'static_files_admin_notice_failed') );
				}
			} else {
				add_action( 'admin_notices', array($this,'static_files_admin_notice_nonce') );
			}
		}

		if ( !empty($this->static_files) ) {
			add_action( 'admin_notices', array($this,'static_files_admin_notice') );
		}
	}

	/**
	 * CONSTRUCTOR
	 */

	function __construct() {

		// ACTION LINK
		add_filter( 'plugin_action_links_' . parent::$plugin_basename, array($this, 'add_action_link') );

		// REGISTER SETTINGS, SETTINGS FIELDS...
		add_action( 'admin_init', array($this,'init'), 0 );

	}

}
