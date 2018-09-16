<?php
/*
Plugin Name: XML Sitemap & Google News
Plugin URI: http://status301.net/wordpress-plugins/xml-sitemap-feed/
Description: Feed the  hungry spiders in compliance with the XML Sitemap and Google News protocols. Happy with the results? Please leave me a <strong><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=XML%20Sitemap%20Feed">tip</a></strong> for continued development and support. Thanks :)
Text Domain: xml-sitemap-feed
Version: 5.0.6
Requires PHP: 5.4
Author: RavanH
Author URI: http://status301.net/
*/

define( 'XMLSF_VERSION', '5.0.6' );

/*  Copyright 2018 RavanH
    http://status301.net/
    mailto: ravanhagen@gmail.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 3 as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/

/* --------------------
 *  AVAILABLE HOOKS
 * --------------------
 *
 *  FILTERS
 *  xmlsf_defaults        -> Filters the default array values for different option groups.
 * 	xmlsf_allowed_domain  -> Filters the response when checking the url against allowed domains.
 *                           Passes variable $url; must return true or false.
 *  xmlsf_excluded        -> Filters the response when checking the post for exclusion flags in
 *							 XML Sitemap context. Passes variable $post_id; must return true or false.
 *  xmlsf_news_excluded   -> Filters the response when checking the post for exclusion flags in
 *							 Google News sitemap context. Passes variable $post_id; must return true or false.
 *  the_title_xmlsitemap  -> Filters the Google News publication name and title, plus
 *                           the Image title and caption tags.
 *  xmlsf_custom_urls     -> Filters the custom urls array
 *  xmlsf_custom_sitemaps -> Filters the custom sitemaps array
 *  xmlsf_post_language   -> Filters the post language tag used in the news sitemap.
 *                           Passes variable $post_id; must return a 2 or 3 letter
 *                           language ISO 639 code with the exception of zh-cn and zh-tw.
 *	xmlsf_post_types      -> Filters the post types array for the XML sitemaps index.
 *	xmlsf_post_priority   -> Filters a post priority value. Passes variables $priority and $post->ID.
 *							 Must return a float value between 0.1 and 1.0
 *	xmlsf_term_priority   -> Filters a taxonomy term priority value. Passes variables $priority and $term->slug.
 *							 Must return a float value between 0.1 and 1.0
 *	xmlsf_news_post_types -> Filters the post types array for the Google News sitemap.
 *
 *  ACTIONS
 *  xmlsf_news_tags_after -> Fired inside the Google News Sitemap loop at the end of the news
 *                           tags, just before each closing </news:news> is generated. Can be used to
 *                           echo custom tags or trigger another action in the background.
 *	xmlsf_news_settings_before -> Fired before the Google News Sitemap settings form
 *	xmlsf_news_settings_after  -> Fired after the Google News Sitemap settings form
 *
 * --------------------
 *  AVAILABLE FUNCTIONS
 * --------------------
 *
 *  is_sitemap() -> conditional, returns bolean, true if the request is for an xml sitemap
 *  is_news()    -> conditional, returns bolean, true if the request is for an xml news sitemap
 *
 *  Feel free to request, suggest or submit more :)
 */

if ( ! defined( 'WPINC' ) ) die;

define( 'XMLSF_DIR', dirname(__FILE__) );

define( 'XMLSF_BASENAME', plugin_basename(__FILE__) );

require XMLSF_DIR . '/models/global.php';

/* --------------------------
 *        INITIALIZE
 * -------------------------- */

function xmlsf_init() {

	xmlsf_maybe_upgrade();

	if ( is_admin() ) {
		require XMLSF_DIR . '/controllers/admin/main.php';
	}

	// include sitemaps if any enabled
	if ( get_option( 'xmlsf_sitemaps' ) ) {

		// include main controller functions
		require XMLSF_DIR . '/controllers/main.php';

		add_action( 'clean_post_cache', 'xmlsf_clean_post_cache', 99, 2 );

		// PINGING
		add_action( 'transition_post_status', 'xmlsf_do_pings', 10, 3 );

		// Update term meta lastmod date
		add_action( 'transition_post_status', 'update_term_modified_meta', 10, 3 );

		// add rewrite rules
		xmlsf_rewrite_rules();

		// include main model functions
		require XMLSF_DIR . '/models/main.php';

		// MAIN REQUEST filter
		add_filter( 'request', 'xmlsf_filter_request', 1 );

		// force remove url trailing slash
		add_filter( 'user_trailingslashit', 'xmlsf_untrailingslash' );

		// common sitemap element filters
		add_filter( 'the_title_xmlsitemap', 'strip_tags' );
		add_filter( 'the_title_xmlsitemap', 'ent2ncr', 8 );
		add_filter( 'the_title_xmlsitemap', 'esc_html' );

		add_filter( 'xmlsf_news_post_types', 'xmlsf_news_filter_post_types' );
		add_filter( 'xmlsf_post_types', 'xmlsf_filter_post_types' );

		// include and instantiate class
		xmlsf();
	}

	// add robots.txt filter
	add_filter( 'robots_txt', 'xmlsf_robots_txt', 9 );
}

add_action( 'init', 'xmlsf_init' );

/**
 * Upgrade/install, maybe...
 *
 * @since 5.0
 * @return void
 */
function xmlsf_maybe_upgrade() {
	$db_version = get_option( 'xmlsf_version', 0 );

	if ( version_compare( XMLSF_VERSION, $db_version, '=' ) ) {
		return;
	}

	// don't flush rules from init as Polylang chokes on that
	// just remove the db option and let WP regenerate them when ready...
	delete_option( 'rewrite_rules' );
	// ... but make sure rules are regenerated when admin is visited.
	set_transient( 'xmlsf_flush_rewrite_rules', '' );
	// static files checking
	set_transient( 'xmlsf_check_static_files', '' );

	// upgrade or install
	if ( $db_version ) :

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

		if ( version_compare( '5.0.2', $db_version, '>' ) ) {
			$defaults = xmlsf()->defaults();

			foreach ( $defaults as $option => $default ) {
				if ( get_option( 'xmlsf_'.$option ) ) continue;
				if ( in_array( $option, array('ping','robots') ) )
					add_option( 'xmlsf_'.$option, $default, null, false );
				else
					add_option( 'xmlsf_'.$option, $default );
			}

			delete_option( 'xmlsf_version' );
		}

		if ( defined('WP_DEBUG') && WP_DEBUG ) {
			error_log('XML Sitemap Feeds upgraded from '.$db_version.' to '.XMLSF_VERSION);
		};

	else :

		$defaults = xmlsf()->defaults();

		foreach ( $defaults as $option => $default ) {
			delete_option( 'xmlsf_'.$option );
			if ( in_array( $option, array('ping','robots') ) )
				add_option( 'xmlsf_'.$option, $default, null, false );
			else
				add_option( 'xmlsf_'.$option, $default );
		}

		if ( defined('WP_DEBUG') && WP_DEBUG ) {
			error_log('XML Sitemap Feeds version '.XMLSF_VERSION.' installed.');
		};

	endif;

	update_option( 'xmlsf_version', XMLSF_VERSION );
}

/**
 * Plugin activation
 *
 * @since 5.0
 * @return void
 */

function xmlsf_activate() {
	delete_option( 'rewrite_rules' );
	set_transient( 'xmlsf_flush_rewrite_rules', '' );
	set_transient( 'xmlsf_check_static_files', '' );
}

register_activation_hook( __FILE__, 'xmlsf_activate' );

/**
 * Plugin de-activation
 *
 * @since 5.0
 * @return void
 */

function xmlsf_deactivate() {
	delete_transient( 'xmlsf_flush_rewrite_rules' );
	delete_transient( 'xmlsf_check_static_files' );
	delete_transient( 'xmlsf_clear_settings' );
	flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'xmlsf_deactivate' );
