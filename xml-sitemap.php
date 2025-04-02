<?php
/**
 * Plugin Name: XML Sitemap & Google News
 * Plugin URI: https://status301.net/wordpress-plugins/xml-sitemap-feed/
 * Description: Feed the hungry spiders in compliance with the XML Sitemap and Google News protocols. Happy with the results? Please leave me a <strong><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=XML%20Sitemap%20Feed">tip</a></strong> for continued development and support. Thanks :)
 * Version: 5.5.3
 * Text Domain: xml-sitemap-feed
 * Requires at least: 4.4
 * Requires PHP: 5.6
 * Author: RavanH
 * Author URI: https://status301.net/
 *
 * @package XML Sitemap & Google News
 */

define( 'XMLSF_VERSION', '5.5.3' );
define( 'XMLSF_ADV_MIN_VERSION', '0.1' );
define( 'XMLSF_NEWS_ADV_MIN_VERSION', '1.3.5' );

/**
 * Copyright 2025 RavanH
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

// Prepare hooks for debugging.
WP_DEBUG && require_once XMLSF_DIR . '/inc/functions-debugging.php';

// Fire it up at plugins_loaded.
add_action( 'plugins_loaded', 'xmlsf', 9 );

if ( is_admin() ) {
	add_action( 'plugins_loaded', array( 'XMLSF\Admin\Admin', 'init' ) );
}

/**
 * Get sitemap object.
 *
 * @since 5.0
 *
 * @static XMLSF\XMLSitemapFeed $xmlsf
 *
 * @return XMLSF\XMLSitemapFeed object by reference
 */
function &xmlsf() {
	global $xmlsf;

	if ( ! isset( $xmlsf ) ) {
		$xmlsf = new XMLSF\XMLSitemapFeed();
	}

	return $xmlsf;
}

/**
 * Plugin de-activation
 *
 * @since 5.0
 * @return void
 */
function xmlsf_deactivate() {
	// Clear all cache metadata.
	XMLSF\clear_metacache();

	// Remove old rules.
	if ( is_object( xmlsf()->sitemap ) && method_exists( xmlsf()->sitemap, 'unregister_rewrites' ) ) {
		xmlsf()->sitemap->unregister_rewrites();
	}
	if ( is_object( xmlsf()->sitemap_news ) && method_exists( xmlsf()->sitemap_news, 'unregister_rewrites' ) ) {
		xmlsf()->sitemap_news->unregister_rewrites();
	}

	// Re-add core rules.
	function_exists( 'wp_sitemaps_get_server' ) && wp_sitemaps_get_server();

	// Then flush.
	flush_rewrite_rules( false );
}

register_deactivation_hook( __FILE__, 'xmlsf_deactivate' );

/**
 * Plugin activation
 *
 * @since 5.4
 * @return void
 */
function xmlsf_activate() {
	flush_rewrite_rules( false );
}

register_activation_hook( __FILE__, 'xmlsf_activate' );


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

	// Build the filename and path.
	$class_name = str_replace( 'XMLSF', 'inc', $class_name );
	$class_name = strtolower( $class_name );
	$path_array = explode( '\\', $class_name );
	$class_name = array_pop( $path_array );
	$class_name = str_replace( '_', '-', $class_name );
	$file       = realpath( XMLSF_DIR ) . DIRECTORY_SEPARATOR . \implode( DIRECTORY_SEPARATOR, $path_array ) . DIRECTORY_SEPARATOR . 'class-' . $class_name . '.php';

	// If the file exists for the class name, load it.
	if ( file_exists( $file ) ) {
		include_once $file;
	}
}

spl_autoload_register( 'xmlsf_autoloader' );

/**
 * Init. That's deprecated, innit?
 *
 * Keep for backwards compatibility with Google News Advanced pre 1.3.6
 */
function xmlsf_init() {
	_deprecated_function( __FUNCTION__, '5.5', 'xmlsf' );
}
