<?php
/**
 * Plugin Name: XML Sitemap & Google News
 * Plugin URI: https://status301.net/wordpress-plugins/xml-sitemap-feed/
 * Description: Feed the hungry spiders in compliance with the XML Sitemap and Google News protocols. Happy with the results? Please leave me a <strong><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=XML%20Sitemap%20Feed">tip</a></strong> for continued development and support. Thanks :)
 * Version: 5.5-alpha1
 * Text Domain: xml-sitemap-feed
 * Requires at least: 4.4
 * Requires PHP: 5.6
 * Author: RavanH
 * Author URI: https://status301.net/
 *
 * @package XML Sitemap & Google News
 */

define( 'XMLSF_VERSION', '5.5-alpha1' );

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

// Pluggable functions.
require_once XMLSF_DIR . '/inc/functions-pluggable.php';

// Shared functions.
require_once XMLSF_DIR . '/inc/functions.php';

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
	add_filter( 'robots_txt', 'XMLSF\robots_txt' );

	// If XML Sitemaps Manager is installed, remove its init and admin_init hooks.
	if ( function_exists( 'xmlsm_init' ) ) {
		remove_action( 'init', 'xmlsm_init', 9 );
		remove_action( 'admin_init', 'xmlsm_admin_init' );
	}

	// Upgrade/install, maybe...
	$db_version = get_option( 'xmlsf_version', 0 );
	if ( ! version_compare( XMLSF_VERSION, $db_version, '=' ) ) {
		require_once XMLSF_DIR . '/upgrade.php';
	}

	// If sitemaps enabled, do our thing. Otherwise disable core.
	if ( XMLSF\sitemaps_enabled() ) {
		$sitemaps = (array) get_option( 'xmlsf_sitemaps', array() );

		// Google News sitemap?
		if ( ! empty( $sitemaps['sitemap-news'] ) ) {
			require XMLSF_DIR . '/inc/functions-sitemap-news.php';

			global $xmlsf_sitemap_news;
			$xmlsf_sitemap_news = new XMLSF\Sitemap_News();
		}

		// XML Sitemap?
		if ( ! empty( $sitemaps['sitemap'] ) ) {
			require XMLSF_DIR . '/inc/functions-sitemap.php';

			global $xmlsf_sitemap;
			if ( XMLSF\uses_core_server() ) {
				// Extend core sitemap.
				$xmlsf_sitemap = new XMLSF\Sitemap_Core();
			} else {
				// Replace core sitemap.
				remove_action( 'init', 'wp_sitemaps_get_server' );

				$xmlsf_sitemap = new XMLSF\Sitemap_Plugin();
			}
		} else {
			// Disable core sitemap.
			add_filter( 'wp_sitemaps_enabled', '__return_false' );
		}

		// Include and instantiate main class.
		xmlsf();
	} else {
		add_filter( 'wp_sitemaps_enabled', '__return_false' );
	}

	if ( is_admin() ) {
		xmlsf_admin();
	}

	// Flush rewrite rules?
	global $wp_rewrite;
	$wp_rewrite->wp_rewrite_rules(); // Recreates rewrite rules only when needed.
}

add_action( 'init', 'xmlsf_init', 9 );

/**
 * Plugin activation
 *
 * @since 5.0
 * @return void
 */
function xmlsf_activate() {
	// Flush rewrite rules on next init.
	delete_option( 'rewrite_rules' );
}

register_activation_hook( __FILE__, 'xmlsf_activate' );

/**
 * Plugin de-activation
 *
 * @since 5.0
 * @return void
 */
function xmlsf_deactivate() {
	// Clear all cache metadata.
	if ( ! function_exists( 'XMLSF\clear_metacache' ) ) {
		// Needed for wp-cli.
		include_once XMLSF_DIR . '/inc/functions-sitemap.php';
	}
	XMLSF\clear_metacache();

	// Remove old rules.
	// TODO but how? remove_rewrite_rule() does not exist yet :/
	// Re-add core rules.
	function_exists( 'wp_sitemaps_get_server' ) && wp_sitemaps_get_server();
	// Then flush.
	flush_rewrite_rules( false );
}

register_deactivation_hook( __FILE__, 'xmlsf_deactivate' );

/**
 * Get instantiated sitemap class
 *
 * @since 5.0
 *
 * @global XMLSitemapFeed $xmlsf
 * @return XMLSitemapFeed object by reference
 */
function &xmlsf() {
	global $xmlsf;

	if ( ! isset( $xmlsf ) ) {
		$xmlsf = new XMLSF\XMLSitemapFeed();
	}

	return $xmlsf;
}

/**
 * Get instantiated sitemap admin class
 *
 * @since 5.4
 *
 * @global XMLSF\Admin $xmlsf_admin
 * @return XMLSF\Admin object by reference
 */
function &xmlsf_admin() {
	global $xmlsf_admin;

	if ( ! isset( $xmlsf_admin ) ) {
		$xmlsf_admin = new XMLSF\Admin();
	}

	return $xmlsf_admin;
}

/**
 * Register XMLSF autoloader
 * http://justintadlock.com/archives/2018/12/14/php-namespaces-for-wordpress-developers
 *
 * @since 5.5
 *
 * @param string $class_name Namespaced class name.
 */
function xmlsf_autoloader( $class_name ) {
	// Bail if the class is not in our namespace.
	if ( 0 !== strpos( $class_name, 'XMLSF\\' ) ) {
		return;
	}

	// Build the filename.
	$class_name = str_replace( 'XMLSF\\', '', $class_name );
	$class_name = strtolower( $class_name );
	$class_name = str_replace( '_', '-', $class_name );
	$file       = realpath( __DIR__ ) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'class-' . $class_name . '.php';

	// If the file exists for the class name, load it.
	if ( file_exists( $file ) ) {
		include $file;
	}
}

spl_autoload_register( 'xmlsf_autoloader' );
