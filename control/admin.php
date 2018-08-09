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

		if ( 1 == get_option('blog_public') ) :

			$options = get_option( 'xmlsf_sitemaps', array() );

			// TODO refer to support forum + invite plugin rating !

			// The actual fields for data entry
			include XMLSF_DIR . '/view/admin/field-sitemaps.php';

		else :

				_e( 'XML Sitemaps are not available because of your site&#8217;s visibility settings (above).', 'xml-sitemap-feed' );

		endif;
	}

	public function sanitize_sitemaps_settings($new) {
		$old = get_option( 'xmlsf_sitemaps', array() );

		if ( isset($new['reset']) && $new['reset'] == '1' ) {
			// if reset is checked, set transients
			set_transient('xmlsf_clear_settings','');
			set_transient('xmlsf_check_static_files','');

		} elseif ( $old != $new ) {
			// when sitemaps are added or removed, set transients
			set_transient('xmlsf_flush_rewrite_rules','');

			if ( empty($old['sitemap']) || empty($old['sitemap-news']) ) {
				set_transient('xmlsf_check_static_files','');
			}
		}

		return $new;
	}

	/* PINGS */

	public function ping_settings_help() {
		get_current_screen()->add_help_tab( array(
			'id'      => 'ping-services',
			'title'   => __( 'Ping Services', 'xml-sitemap-feed' ),
			'content' => '<p>' . __( 'If desired, search engines will automatically be alerted of your updated XML Sitemap and News Sitemap.' ) . '</p>',
			'priority' => 11
		) );
	}

	public function ping_settings_field() {

		if ( 1 == get_option('blog_public') ) :

			$sitemaps = get_option( 'xmlsf_sitemaps', array() );
			if ( empty($sitemaps) ) {
				printf(
					/* translators: Reading Settings URL */
					__( 'Search engines will not be pinged because there are no <a href="%s">sitemaps enabled</a>.', 'xml-sitemap-feed' ),
					'options-reading.php'
				);
				return;
			}

			$options = (array) get_option( 'xmlsf_ping', array() );
			$pong = (array) get_option( 'xmlsf_pong', array() );

			if ( $tzstring = get_option( 'timezone_string' ) ) {
				// use same timezoneformat as translatable examples in options-general.php
				$timezone_format = translate_with_gettext_context('Y-m-d G:i:s', 'timezone date format');
				date_default_timezone_set($tzstring);
			} else {
				$timezone_format = 'Y-m-d G:i:s T';
			}

			// The actual fields for data entry
			include XMLSF_DIR . '/view/admin/field-ping.php';

			date_default_timezone_set('UTC');

		else :

			printf(
				/* translators: Reading Settings URL */
				__( 'Search engines will not be pinged because of your site&#8217;s <a href="%s">visibility settings</a>.', 'xml-sitemap-feed' ),
				'options-reading.php'
			);

		endif;
	}

	public function sanitize_ping_settings($new) {
		$defaults = $this->defaults('ping');
		$old = get_option('xmlsf_ping');
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
		include XMLSF_DIR . '/view/admin/field-robots.php';
	}

	public function sanitize_robots_settings($new) {
		$old = get_option('xmlsf_robots');

		// clean up input
		if(is_array($new)) {
		  $new = array_filter($new);
		  $new = reset($new);
		}

		if ( empty($old) && !empty($new) )
			set_transient('xmlsf_check_static_files','');

		return trim(strip_tags($new));
	}

	/* RESET */

	public function reset_settings_field() {
		// The actual fields for data entry
		include XMLSF_DIR . '/view/admin/field-sitemap-reset.php';
	}

	/**
	* XML SITEMAP SECTION
	*/

	public function xml_sitemap_settings() {
		// The actual settings section text
		include XMLSF_DIR . '/view/admin/section-sitemap.php';
	}

	public function post_types_settings_field() {
		$post_types = get_post_types(array('public'=>true),'objects');
		if ( !is_array($post_types) || is_wp_error($post_types) )
			return;

		$options = $this->get_post_types();
		$defaults = $this->defaults('post_types');

		// The actual fields for data entry
		include XMLSF_DIR . '/view/admin/field-sitemap-post-types.php';
	}

	public function taxonomies_settings_field() {

		$taxonomies = (array) get_option('xmlsf_taxonomies');
		$disabled = xmlsf()->disabled_taxonomies();
		$taxonomy_settings = get_option('xmlsf_taxonomy_settings');
		$active = get_option('xmlsf_post_types');
		$tax_list = array();

		foreach ( get_taxonomies(array('public'=>true),'objects') as $taxonomy ) {
			// skip unallowed post types
			if ( in_array( $taxonomy->name, $disabled ) )
				continue;

			$skip = true;
			foreach ( $taxonomy->object_type as $post_type)
				if (!empty($active[$post_type]['active']) && $active[$post_type]['active'] == '1')
					$skip = false;
			if ($skip) continue; // skip if none of the associated post types are active

			$count = wp_count_terms( $taxonomy->name );
			$tax_list[] = '<label><input type="checkbox" name="'.'xmlsf_taxonomies['.
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
		include XMLSF_DIR . '/view/admin/field-sitemap-taxonomies.php';
	}

	public function custom_sitemaps_settings_field() {
		$lines = $this->get_custom_sitemaps();

		// The actual fields for data entry
		include XMLSF_DIR . '/view/admin/field-sitemap-custom.php';
	}

	public function urls_settings_field() {
		$urls = $this->get_urls();
		$lines = array();

		if(!empty($urls)) {
			foreach($urls as $arr) {
				if(is_array($arr))
					$lines[] = implode(" ",$arr);
			}
		}

		// The actual fields for data entry
		include XMLSF_DIR . '/view/admin/field-sitemap-urls.php';
	}

	/**
	 * Domain settings field and sanitization
	 */

	 public function domains_settings_field() {
		$default = $this->domain();
		$domains = (array) get_option('xmlsf_domains');

		// The actual fields for data entry
		include XMLSF_DIR . '/view/admin/field-sitemap-domains.php';
	}

	public function sanitize_domains_settings($new) {
		$default = $this->domain();

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
		include XMLSF_DIR . '/view/admin/section-news.php';
	}

	//TODO: publication name allow tag %category% ... post_types (+ exclusion per post or none + allow inclusion per post), limit to category ...
	public function news_name_field() {
		$options = get_option('xmlsf_news_tags');

		$name = !empty($options['name']) ? $options['name'] : '';

		// The actual fields for data entry
		include XMLSF_DIR . '/view/admin/field-news-name.php';
	}

	public function news_post_type_field() {
		$defaults = $this->defaults('news_tags');
		$options = get_option('xmlsf_news_tags');

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
					'xmlsf_news_tags[post_type][]" id="xmlsf_post_type_'.
					$post_type->name.'" value="'.$post_type->name.'" '.
					checked( $checked, true, false ).' '.
					disabled( $disabled, true, false ).' /> '.
					$post_type->label.'</label>';
			}

			// The actual fields for data entry
			include XMLSF_DIR . '/view/admin/field-news-post-types.php';
		}

	}

	public function news_categories_field() {
		$options = get_option('xmlsf_news_tags');

		if ( !empty($options['post_type']) && array( 'post' ) !== (array)$options['post_type'] )	{
			echo '
			<p class="description">' . sprintf(__('Selection based on categories will be available when <strong>only</strong> the post type %s is included above.','xml-sitemap-feed'),translate('Posts')) . '</p>';
			return;
		}

		$selected_categories = isset($options['categories']) && is_array($options['categories']) ? $options['categories'] : array();

		$cat_list = str_replace('name="post_category[]"','name="'.'xmlsf_news_tags[categories][]"', wp_terms_checklist( null, array( 'taxonomy' => 'category', 'selected_cats' => $selected_categories, 'echo' => false ) ) );

		// The actual fields for data entry
		include XMLSF_DIR . '/view/admin/field-news-categories.php';

	}

	public function news_image_field() {
		$options = get_option('xmlsf_news_tags');

		$image = !empty($options['image']) ? $options['image'] : '';

		// The actual fields for data entry
		include XMLSF_DIR . '/view/admin/field-news-image.php';
	}

	public function news_labels_field() {
		// The actual fields for data entry
		include XMLSF_DIR . '/view/admin/field-news-labels.php';
	}

	//sanitize callback functions

	public function sanitize_post_types_settings( $new = array() ) {
		$old = $this->get_post_types();
		$defaults = $this->defaults('post_types');
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
		$old = get_option('xmlsf_taxonomies', array());

		if ($old != $new) // when taxonomy types are added or removed, set transient to flush rewrite rules
			set_transient('xmlsf_flush_rewrite_rules','');

		return $new;
	}

	public function sanitize_taxonomy_settings_settings($new) {
		$defaults = $this->defaults('taxonomy_settings');
		$sanitized = array();

		$sanitized['term_limit'] = isset($new['term_limit']) ? intval($new['term_limit']) : $defaults['term_limit'];
		$sanitized['priority'] = isset($new['priority']) && is_numeric($new['priority']) ? $this->sanitize_priority($new['priority'], 0.1, 0.9) : $defaults['priority'];
		$sanitized['dynamic_priority'] = !empty($new['dynamic_priority']) ? '1' : '';

		return $sanitized;
	}

	public function sanitize_custom_sitemaps_settings($new) {
		$old = $this->get_custom_sitemaps();

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
		$old = $this->get_urls();

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
		foreach ( $this->get_post_types() as $post_type ) {
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
		wp_nonce_field( XMLSF_BASENAME, 'xmlsf_sitemap_nonce' );

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
		include XMLSF_DIR . '/view/admin/meta-box.php';
	}

	/* Adds a News Sitemap box to the side column */
	public function add_meta_box_news () {
		$news_tags = get_option('xmlsf_news_tags');
		$defaults = $this->defaults('news_tags');
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
		wp_nonce_field( XMLSF_BASENAME, 'xmlsf_sitemap_nonce' );

		// Use get_post_meta to retrieve an existing value from the database and use the value for the form
		$exclude = 'private' == $post->post_status || get_post_meta( $post->ID, '_xmlsf_news_exclude', true );
		$disabled = 'private' == $post->post_status;

		// The actual fields for data entry
		include XMLSF_DIR . '/view/admin/meta-box-news.php';
	}

	/* When the post is saved, save our meta data */
	function save_metadata( $post_id ) {
		if ( !isset($post_id) )
			$post_id = (int)$_REQUEST['post_ID'];

		if ( !current_user_can( 'edit_post', $post_id ) || !isset($_POST['xmlsf_sitemap_nonce']) || !wp_verify_nonce($_POST['xmlsf_sitemap_nonce'], XMLSF_BASENAME) )
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
	 * Clear settings
	 */
	public function clear_settings() {
		foreach ( xmlsf()->defaults() as $option => $settings ) {
			delete_option( 'xmlsf_' . $option );
		}

		if ( defined('WP_DEBUG') && WP_DEBUG ) {
			error_log( 'XML Sitemap Feeds settings cleared' );
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

		// CATCH TRANSIENT for static file check
		if ( delete_transient('xmlsf_check_static_files') ) {
			$this->check_static_files();
		}
	}

	/**
	 * Register settings and add settings fields
	 */

	function register_settings() {
		$sitemaps = get_option( 'xmlsf_sitemaps', array() );

		// sitemaps
		register_setting( 'reading', 'xmlsf_sitemaps', array($this,'sanitize_sitemaps_settings') );
		add_settings_field( 'xmlsf_sitemaps', __('Enable XML sitemaps','xml-sitemap-feed'), array($this,'sitemaps_settings_field'), 'reading' );

		// robots rules only when permalinks are set
		$rules = get_option( 'rewrite_rules' );
		if( ! $this->plain_permalinks() && isset( $rules['robots\.txt$'] ) ) {
			register_setting( 'reading', 'xmlsf_robots', array($this,'sanitize_robots_settings') );
			add_settings_field( 'xmlsf_robots', __('Additional robots.txt rules','xml-sitemap-feed'), array($this,'robots_settings_field'), 'reading' );
		}

		add_settings_field( 'xmlsf_reset', __('Reset XML sitemaps','xml-sitemap-feed'), array($this,'reset_settings_field'), 'reading' );

		register_setting('writing', 'xmlsf_ping', array($this,'sanitize_ping_settings') );
		add_settings_field('xmlsf_ping', __('Ping Services','xml-sitemap-feed'), array($this,'ping_settings_field'), 'writing');
		add_action('load-options-writing.php', array($this,'ping_settings_help'));

		if ( isset($sitemaps['sitemap-news']) ) {
			// XML SITEMAP SETTINGS
			add_settings_section( 'news_sitemap_section', '<a name="xmlnf"></a>'.__('Google News Sitemap','xml-sitemap-feed'), array($this,'news_sitemap_settings'), 'reading' );
			// tags
			register_setting( 'reading', 'xmlsf_news_tags', array($this,'sanitize_news_tags_settings') );
			add_settings_field( 'xmlsf_news_name', '<label for="xmlsf_news_name">'.__('Publication name','xml-sitemap-feed').'</label>', array($this,'news_name_field'), 'reading', 'news_sitemap_section' );
			add_settings_field( 'xmlsf_news_post_type', __('Include post types','xml-sitemap-feed'), array($this,'news_post_type_field'), 'reading', 'news_sitemap_section' );
			add_settings_field( 'xmlsf_news_categories', translate('Categories'), array($this,'news_categories_field'), 'reading', 'news_sitemap_section' );
			add_settings_field( 'xmlsf_news_image', translate('Images'), array($this,'news_image_field'), 'reading', 'news_sitemap_section');
			add_settings_field( 'xmlsf_news_labels', __('Source labels','xml-sitemap-feed'), array($this,'news_labels_field'), 'reading', 'news_sitemap_section' );
      		// post meta box
      		add_action( 'add_meta_boxes', array($this,'add_meta_box_news') );
		}

		if ( isset($sitemaps['sitemap']) ) {
			// XML SITEMAP SETTINGS
			add_settings_section('xml_sitemap_section', '<a name="xmlsf"></a>'.__('XML Sitemap','xml-sitemap-feed'), array($this,'xml_sitemap_settings'), 'reading' );
			// post_types
			register_setting( 'reading', 'xmlsf_post_types', array($this,'sanitize_post_types_settings') );
			add_settings_field( 'xmlsf_post_types', __('Include post types','xml-sitemap-feed'), array($this,'post_types_settings_field'), 'reading', 'xml_sitemap_section' );
			// taxonomies
			register_setting('reading', 'xmlsf_taxonomies', array($this,'sanitize_taxonomies_settings') );
			register_setting('reading', 'xmlsf_taxonomy_settings', array($this,'sanitize_taxonomy_settings_settings') );
			add_settings_field('xmlsf_taxonomies', __('Include taxonomies','xml-sitemap-feed'), array($this,'taxonomies_settings_field'), 'reading', 'xml_sitemap_section' );
			// custom domains
			register_setting('reading', 'xmlsf_domains', array($this,'sanitize_domains_settings') );
			add_settings_field('xmlsf_domains', __('Allowed domains','xml-sitemap-feed'), array($this,'domains_settings_field'), 'reading', 'xml_sitemap_section');
			// custom urls
			register_setting('reading', 'xmlsf_urls', array($this,'sanitize_urls_settings') );
			add_settings_field('xmlsf_urls', __('Include custom web pages','xml-sitemap-feed'), array($this,'urls_settings_field'), 'reading', 'xml_sitemap_section');
			// custom sitemaps
			register_setting('reading', 'xmlsf_custom_sitemaps', array($this,'sanitize_custom_sitemaps_settings') );
			add_settings_field('xmlsf_custom_sitemaps', __('Include custom XML Sitemaps','xml-sitemap-feed'), array($this,'custom_sitemaps_settings_field'), 'reading', 'xml_sitemap_section');
			// post meta box
			add_action( 'add_meta_boxes', array($this,'add_meta_box') );
		}

		if ( isset($sitemaps['sitemap']) || isset($sitemaps['sitemap-news']) ) {
	        // save post meta box settings
	        add_action( 'save_post', array($this,'save_metadata') );
		}
	}

	/**
	 * Delete static sitemap files
	 */
	public function delete_static_files() {

		if ( !isset( $_GET['_wpnonce'] ) || !wp_verify_nonce( $_GET['_wpnonce'], 'xmlsf-static-warning-nonce' ) ) {
			add_action( 'admin_notices', array($this,'static_files_admin_notice_nonce') );
			return;
		}

		$allowed_files = array('sitemap.xml','sitemap-news.xml','robots.txt');

		$this->check_static_files( false );

		foreach ( $_GET['xmlsf-delete'] as $name ) {
			if ( !in_array($name,$allowed_files) ) {
				add_action( 'admin_notices', array($this,'static_files_admin_notice_not_allowed') );
				continue;
			}
			if ( !isset($this->static_files[$name]) ) {
				//add_action( 'admin_notices', array($this,'static_files_admin_notice_not_found') );
				// do nothing and be quiet about it...
				continue;
			}
			if ( unlink($this->static_files[$name]) ) {
				unset($this->static_files[$name]);
				add_action( 'admin_notices', array($this,'static_files_admin_notice_deleted') );
			} else {
				add_action( 'admin_notices', array($this,'static_files_admin_notice_failed') );
			}
		}

		$this->check_static_files();
	}

	/**
	 * Check for static sitemap files
	 */
	public function check_static_files( $update = true ) {

		$home_path = trailingslashit( get_home_path() );
		$check_for = get_option( 'xmlsf_sitemaps', array() );
		if ( !empty( get_option('xmlsf_robots') ) ) {
			$check_for['robots'] = 'robots.txt';
		}

		foreach ( $check_for as $name => $pretty ) {
			if ( file_exists( $home_path . $pretty ) ) {
				$this->static_files[$pretty] = $home_path . $pretty;
			}
		}

		if ( $update ) {
			update_option( 'xmlsf_static_files', $this->static_files );
		}
	}

	public function static_files_admin_notice_dismiss() {
		if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'xmlsf-static-warning-nonce' ) ) {
			add_user_meta( get_current_user_id(), 'xmlsf_static_files_warning_dismissed', 'true', true );
		}
	}

	public function static_files_admin_notice_deleted() {
		include XMLSF_DIR . '/view/admin/notice-deleted.php';
	}

/*	public function static_files_admin_notice_not_found() {
		include XMLSF_DIR . '/view/admin/notice-not-found.php';
	}*/

	public function static_files_admin_notice_not_allowed() {
		include XMLSF_DIR . '/view/admin/notice-not-allowed.php';
	}

	public function static_files_admin_notice_failed() {
		$stati = array(
			'failed' => __('Failed to delete the file, possibly due to insufficient rights. Please try it via FTP.','xml-sitemap-feed'),
			//'not-found' => __('File not found. It may have already been deleted.','xml-sitemap-feed'),
			'not-allowed' => __('File not in the list of allowed files!','xml-sitemap-feed')
		);
		include XMLSF_DIR . '/view/admin/notice-failed.php';
	}

	public function static_files_admin_notice_nonce() {
		include XMLSF_DIR . '/view/admin/notice-nonce-error.php';
	}

	public function static_files_admin_notice() {
		//$screen = get_current_screen();
		if ( !get_user_meta( get_current_user_id(), 'xmlsf_static_files_warning_dismissed' ) /*$screen->id === 'options-reading' */) {
			$nonce = wp_create_nonce( 'xmlsf-static-warning-nonce' );

			if ( $number = count($this->static_files) )
				include XMLSF_DIR . '/view/admin/notice.php';
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

		if ( isset( $_GET['xmlsf-static-dismiss'] ) ) {
			$this->static_files_admin_notice_dismiss();
		}

		if ( isset( $_GET['xmlsf-delete'] ) ) {
			$this->delete_static_files();
		}

		// CHECK STATIC FILES
		if ( current_user_can( 'manage_options' ) || ( is_multisite() && is_super_admin() ) ) {
			$static_files = get_option( 'xmlsf_static_files', false );

			if ( false === $static_files ) {
				$this->check_static_files();
			} else {
				$this->static_files = $static_files;
			}

			if ( !empty($this->static_files) ) {
				add_action( 'admin_notices', array($this,'static_files_admin_notice') );
			}
		}
	}

	/**
	 * CONSTRUCTOR
	 */

	function __construct() {

		// ACTION LINK
		add_filter( 'plugin_action_links_' . XMLSF_BASENAME, array($this, 'add_action_link') );

		// REGISTER SETTINGS, SETTINGS FIELDS...
		add_action( 'admin_init', array($this,'init'), 0 );

	}

}
