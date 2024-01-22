<?php
/**
 * Plugin Name: XML Sitemap & Google News
 * Plugin URI: https://status301.net/wordpress-plugins/xml-sitemap-feed/
 * Description: Feed the hungry spiders in compliance with the XML Sitemap and Google News protocols. Happy with the results? Please leave me a <strong><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=XML%20Sitemap%20Feed">tip</a></strong> for continued development and support. Thanks :)
 * Version: 5.4-beta31
 * Text Domain: xml-sitemap-feed
 * Requires at least: 5.5
 * Requires PHP: 5.6
 * Author: RavanH
 * Author URI: https://status301.net/
 *
 * @package XML Sitemap & Google News
 */

define( 'XMLSF_VERSION', '5.4-beta31' );

/**
 * Copyright 2024 RavanH
 * https://status301.net/
 * mailto: ravanhagen@gmail.com

 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as
 * published by the Free Software Foundation.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

/**
 * --------------------
 *  AVAILABLE HOOKS
 * --------------------
 *
 * Documented on https://premium.status301.com/knowledge-base/xml-sitemap-google-news/action-and-filter-hooks/
 *
 * ---------------------
 *  AVAILABLE FUNCTIONS
 * ---------------------
 *
 * Conditional tags https://premium.status301.com/knowledge-base/xml-sitemap-google-news/conditional-tags/
 *
 *  Feel free to request, suggest or submit more :)
 */

defined( 'WPINC' ) || die;

define( 'XMLSF_DIR', __DIR__ );

define( 'XMLSF_BASENAME', plugin_basename( __FILE__ ) );

// Main plugin init.
add_action( 'init', 'xmlsf_init', 9 );

register_activation_hook( __FILE__, 'xmlsf_activate' );

register_deactivation_hook( __FILE__, 'xmlsf_deactivate' );

/**
 * Plugin initialization
 *
 * @since 1.0
 * @return void
 */
function xmlsf_init() {
	// Prepare hooks for debugging.
	WP_DEBUG && require_once XMLSF_DIR . '/inc/functions-debugging.php';

	// Add robots.txt filter.
	add_filter( 'robots_txt', 'xmlsf_robots_txt', 0 );

	// If XML Sitemaps Manager is installed, remove its init and admin_init hooks.
	if ( function_exists( 'xmlsm_init' ) ) {
		remove_action( 'init', 'xmlsm_init', 9 );
		remove_action( 'admin_init', 'xmlsm_admin_init' );
	}

	// Upgrade/install, maybe...
	$db_version = get_option( 'xmlsf_version', 0 );
	if ( ! version_compare( XMLSF_VERSION, $db_version, '=' ) ) {
		require_once XMLSF_DIR . '/inc/class-xmlsf-upgrade.php';
		new XMLSF_Upgrade( $db_version );
	}

	if ( is_admin() ) {
		require XMLSF_DIR . '/inc/class-xmlsf-admin.php';
		new XMLSF_Admin();
	}

	$sitemaps = (array) get_option( 'xmlsf_sitemaps', array() );

	// If nothing enabled, just disable core sitemap and bail.
	if ( empty( $sitemaps ) ) {
		add_filter( 'wp_sitemaps_enabled', '__return_false' );
		return;
	}

	// Main functions.
	require XMLSF_DIR . '/inc/functions.php';

	// Include and instantiate main class.
	xmlsf();

	if ( ! empty( $sitemaps['sitemap'] ) ) {
		global $xmlsf_sitemap;

		require XMLSF_DIR . '/inc/class-xmlsf-sitemap.php';
		require XMLSF_DIR . '/inc/functions-sitemap.php';

		if ( xmlsf_uses_core_server() ) {
			// Extend core sitemap.
			require XMLSF_DIR . '/inc/class-xmlsf-sitemap-core.php';
			$xmlsf_sitemap = new XMLSF_Sitemap_Core( 'wp-sitemap.xml' );
		} else {
			// Replace core sitemap.
			remove_action( 'init', 'wp_sitemaps_get_server' );

			require XMLSF_DIR . '/inc/class-xmlsf-sitemap-plugin.php';
			$xmlsf_sitemap = new XMLSF_Sitemap_Plugin( $sitemaps['sitemap'] );
		}
	} else {
		// Disable core sitemap.
		add_filter( 'wp_sitemaps_enabled', '__return_false' );
	}

	if ( ! empty( $sitemaps['sitemap-news'] ) ) {
		require XMLSF_DIR . '/inc/class-xmlsf-sitemap-news.php';
		new XMLSF_Sitemap_News( $sitemaps['sitemap-news'] );
	}
}

/**
 * Plugin activation
 *
 * @since 5.0
 * @return void
 */
function xmlsf_activate() {
	// Remove rules so they will be REGENERATED either on the next page load (with old plugin settings) or on install/upgrade.
	delete_option( 'rewrite_rules' );
}

/**
 * Plugin de-activation
 *
 * @since 5.0
 * @return void
 */
function xmlsf_deactivate() {
	// Clear all cache metadata.
	xmlsf_clear_metacache();

	/*
	// global $wpdb;
	// Remove posts metadata.
	$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prefix . 'postmeta',
		array( 'meta_key' => '_xmlsf_image_attached' ) // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	);
	$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prefix . 'postmeta',
		array( 'meta_key' => '_xmlsf_image_featured' ) // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	);
	$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prefix . 'postmeta',
		array( 'meta_key' => '_xmlsf_comment_date_gmt' ) // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	);

	// Remove terms metadata.
	$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prefix . 'termmeta',
		array( 'meta_key' => 'term_modified' ) // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	);
	*/

	// Remove rules so they can be REGENERATED on the next page load (without this plugin active).
	delete_option( 'rewrite_rules' );
}

/**
 * Get instantiated sitemap class
 *
 * @since 5.0
 *
 * @global XMLSitemapFeed $xmlsf
 * @return XMLSitemapFeed object
 */
function xmlsf() {
	global $xmlsf;

	if ( ! isset( $xmlsf ) ) {
		if ( ! class_exists( 'XMLSitemapFeed' ) ) {
			require XMLSF_DIR . '/inc/class-xmlsitemapfeed.php';
		}

		$xmlsf = new XMLSitemapFeed();
	}

	return $xmlsf;
}

/**
 * Filter robots.txt rules
 *
 * @param string $output Default robots.txt content.
 *
 * @return string
 */
function xmlsf_robots_txt( $output ) {

	// CUSTOM ROBOTS.
	$robots_custom = get_option( 'xmlsf_robots' );
	$output       .= $robots_custom ? $robots_custom . PHP_EOL : '';

	// SITEMAPS.
	$sitemaps = (array) get_option( 'xmlsf_sitemaps', array() );

	$output .= PHP_EOL . '# XML Sitemap & Google News version ' . XMLSF_VERSION . ' - https://status301.net/wordpress-plugins/xml-sitemap-feed/' . PHP_EOL;
	if ( '1' !== get_option( 'blog_public' ) ) {
		$output .= '# XML Sitemaps are disabled because of this site\'s privacy settings.' . PHP_EOL;
	} elseif ( ! is_array( $sitemaps ) || empty( $sitemaps ) ) {
		$output .= '# No XML Sitemaps are enabled on this site.' . PHP_EOL;
	} else {
		$output .= ! empty( $sitemaps['sitemap'] ) && ! xmlsf_uses_core_server() ? 'Sitemap: ' . xmlsf_sitemap_url() . PHP_EOL : PHP_EOL;
		$output .= ! empty( $sitemaps['sitemap-news'] ) ? 'Sitemap: ' . xmlsf_sitemap_url( 'news' ) . PHP_EOL : '';
	}

	return $output;
}

/**
 * CONDITIONAL TAGS
 */

if ( ! function_exists( 'is_sitemap' ) ) {
	/**
	 * Is the query for a sitemap?
	 *
	 * @since 4.8
	 * @return bool
	 */
	function is_sitemap() {
		if ( function_exists( 'wp_sitemaps_loaded' ) ) {
			global $wp_query;
			if ( ! isset( $wp_query ) ) {
				_doing_it_wrong( __FUNCTION__, esc_html__( 'Conditional query tags do not work before the query is run. Before then, they always return false.' ), '3.1.0' );
				return false;
			}
			return property_exists( $wp_query, 'is_sitemap' ) ? $wp_query->is_sitemap : false;
		}
		global $xmlsf;
		if ( ! is_object( $xmlsf ) || false === $xmlsf->request_filtered ) {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Conditional sitemap tags do not work before the sitemap request filter is run. Before then, they always return false.', 'xml-sitemap-feed' ), '4.8' );
			return false;
		}
		return $xmlsf->is_sitemap;
	}
}

if ( ! function_exists( 'is_news' ) ) {
	/**
	 * Is the query for a news sitemap?
	 *
	 * @since 4.8
	 * @return bool
	 */
	function is_news() {
		global $xmlsf;
		if ( ! is_object( $xmlsf ) || false === $xmlsf->request_filtered_news ) {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Conditional sitemap tags do not work before the sitemap request filter is run. Before then, they always return false.', 'xml-sitemap-feed' ), '4.8' );
			return false;
		}
		return $xmlsf->is_news;
	}
}
