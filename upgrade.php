<?php
/*
 * XML Sitemap Feed upgrade routines
 *
 * @since 5.1
 */
class XMLSitemapFeed_Upgrade {

	/*
	 * constructor: manages upgrade
	 *
	 * @since 5.1
	 */
	function __construct( $db_version = null )
	{
		// make sure rules are regenerated when admin is visited.
		set_transient( 'xmlsf_flush_rewrite_rules', '' );
		// static files checking
		set_transient( 'xmlsf_check_static_files', '' );

		if ( $db_version )
			$this->upgrade( $db_version );
		else
			$this->install();

		update_option( 'xmlsf_version', XMLSF_VERSION );
	}

	/*
	 * set up default plugin data
	 *
	 * @since 5.1
	 */
	private function install()
	{
		$defaults = xmlsf()->defaults();

		foreach ( $defaults as $option => $default ) {
			delete_option( 'xmlsf_'.$option );
			if ( in_array( $option, array( 'ping', 'robots' ) ) )
				add_option( 'xmlsf_'.$option, $default, null, false );
			else
				add_option( 'xmlsf_'.$option, $default );
		}

		// Kilroy was here
		if ( defined('WP_DEBUG') && WP_DEBUG ) {
			error_log('XML Sitemap Feeds version '.XMLSF_VERSION.' installed.');
		}
	}

	/*
	 * upgrade plugin data
	 *
	 * @since 5.1
	 */
	private function upgrade( $db_version )
	{
		global $wpdb;

		if ( version_compare( '4.4', $db_version, '>' ) ) {
			// remove robots.txt rules blocking stylesheets
			if ( $robot_rules = get_option( 'xmlsf_robots' ) ) {
				$robot_rules = str_replace( array('Disallow: */wp-content/','Allow: */wp-content/uploads/'), '', $robot_rules );
				delete_option( 'xmlsf_robots' );
				add_option( 'xmlsf_robots', $robot_rules, null, false );
			}

			// make sure custom sitemaps is an array
			$urls = get_option('xmlsf_custom_sitemaps');
			if ( !is_array($urls) ) {
				$urls = explode( PHP_EOL, $urls );
				update_option('xmlsf_custom_sitemaps',$urls);
			}

			// register location taxonomies then delete all terms
			register_taxonomy( 'gn-location-3', null );
			$terms = get_terms( 'gn-location-3', array('hide_empty' => false) );
			foreach ( $terms as $term ) {
				wp_delete_term(	$term->term_id, 'gn-location-3' );
			}

			register_taxonomy( 'gn-location-2', null );
			$terms = get_terms( 'gn-location-2',array( 'hide_empty' => false ) );
			foreach ( $terms as $term ) {
				wp_delete_term(	$term->term_id, 'gn-location-2' );
			}

			register_taxonomy( 'gn-location-1', null );
			$terms = get_terms( 'gn-location-1',array( 'hide_empty' => false ) );
			foreach ( $terms as $term ) {
				wp_delete_term(	$term->term_id, 'gn-location-1' );
			}
		}

		if ( version_compare( '5.0.1', $db_version, '>' ) ) {
			// delete all taxonomy terms
			register_taxonomy( 'gn-genre', null );

			$terms = get_terms( 'gn-genre', array( 'hide_empty' => false ) );

			if ( is_array( $terms ) )
				foreach ( $terms as $term )
					wp_delete_term(	$term->term_id, 'gn-genre' );

			// new taxonomy settings
			$taxonomies = get_option( 'xmlsf_taxonomies' );
			if ( empty($taxonomies) ) {
				$active = '';
			} else {
				$available = 0;
				$checked = count($taxonomies);
				foreach ( (array) get_option( 'xmlsf_post_types' ) as $post_type => $settings ) {
					if ( empty($settings['active']) ) continue;
					$taxonomies = get_object_taxonomies( $post_type, 'objects' );
					// check each tax public flag and term count and append name to array
					foreach ( $taxonomies as $taxonomy ) {
						if ( !empty( $taxonomy->public ) && !in_array( $taxonomy->name, xmlsf()->disabled_taxonomies() ) )
							$available++;
					}
				}
				if ( $checked == $available )
					update_option( 'xmlsf_taxonomies', '' );
				$active = '1';
			}
			$taxonomy_settings = array(
				'active' => $active,
				'priority' => '0.3',
				'dynamic_priority' => '1',
				'term_limit' => '5000'
			);
			add_option( 'xmlsf_taxonomy_settings', $taxonomy_settings );

			// update ping option
			$ping = get_option( 'xmlsf_ping' );
			$new = array( 'google', 'bing' );
			if ( is_array($ping) ) {
				foreach ( $ping as $key => $value ) {
					if ( is_array($value) && empty( $value['active'] ) && isset( $new[$key] ) ) {
						unset( $new[$key] );
					}
				}
			}
			update_option( 'xmlsf_ping', $new, false );

			// make sure no pong option remains
			delete_option( 'xmlsf_pong');

			// update or create robots option
			$robots = get_option( 'xmlsf_robots', '' );
			delete_option( 'xmlsf_robots');
			add_option( 'xmlsf_robots', $robots, null, false );
		}

		if ( version_compare( '5.1', $db_version, '>' ) ) {
			delete_transient('xmlsf_ping_google_sitemap_news');
			delete_transient('xmlsf_ping_google_sitemap');
			delete_transient('xmlsf_ping_bing_sitemap');
		}

		if ( version_compare( '5.2', $db_version, '>' ) ) {
			// remove term meta term_modified_gmt
			$wpdb->delete( $wpdb->prefix.'termmeta', array( 'meta_key' => 'term_modified_gmt' ) );
		}

		if ( version_compare( '5.3', $db_version, '>' ) ) {
			// clear comments meta
			$wpdb->delete( $wpdb->prefix.'postmeta', array( 'meta_key' => '_xmlsf_comment_date' ) );
			update_option( 'xmlsf_comments_meta_primed', array() );
		}

		$this->update_from_defaults();

		if ( defined('WP_DEBUG') && WP_DEBUG ) {
			error_log('XML Sitemap Feeds upgraded from '.$db_version.' to '.XMLSF_VERSION);
		}
	}

	private function update_from_defaults() {

		foreach ( xmlsf()->defaults() as $option => $default ) {
			if ( get_option( 'xmlsf_'.$option ) ) continue;
			if ( in_array( $option, array('ping','robots') ) )
				add_option( 'xmlsf_'.$option, $default, null, false );
			else
				add_option( 'xmlsf_'.$option, $default );
		}

	}

}
