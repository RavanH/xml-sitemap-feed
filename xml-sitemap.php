<?php
/**
 * Plugin Name: XML Sitemap & Google News
 * Plugin URI: https://status301.net/wordpress-plugins/xml-sitemap-feed/
 * Description: Feed the hungry spiders in compliance with the XML Sitemap and Google News protocols.
 * Version: 5.5.8
 * Text Domain: xml-sitemap-feed
 * Requires at least: 4.4
 * Requires PHP: 5.6
 * Author: RavanH
 * Author URI: https://status301.net/
 *
 * @package XML Sitemap & Google News
 */

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

define( 'XMLSF_VERSION', '5.5.8' );
define( 'XMLSF_ADV_MIN_VERSION', '0.1' );
define( 'XMLSF_NEWS_ADV_MIN_VERSION', '1.3.6' );
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
add_filter( 'robots_txt', 'XMLSF\robots_txt', 11 );
add_action( 'xmlsf_sitemap_loaded', 'XMLSF\sitemap_loaded' );
add_action( 'xmlsf_news_sitemap_loaded', 'XMLSF\sitemap_loaded' );

// Admin.
add_action( 'admin_menu', array( 'XMLSF\Admin\Main', 'add_options_pages' ) );
add_action( 'admin_init', array( 'XMLSF\Admin\Main', 'register_settings' ), 7 );
add_action( 'admin_init', array( 'XMLSF\Admin\Main', 'init' ), 9 );
add_action( 'admin_init', array( 'XMLSF\Admin\Main', 'compat' ) );

register_deactivation_hook( __FILE__, array( 'XMLSF\Admin\Main', 'deactivate' ) );
register_activation_hook( __FILE__, array( 'XMLSF\Admin\Main', 'activate' ) );

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
 * Register XMLSF autoloader
 * http://justintadlock.com/archives/2018/12/14/php-namespaces-for-wordpress-developers
 *
 * @since 5.5
 *
 * @param string $class_name Namespaced class name.
 */
spl_autoload_register(
	function ( $class_name ) {
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
);

/**
 * Deprecated, innit?
 *
 * Keep for backwards compatibility with Google News Advanced pre 1.3.6
 */
function xmlsf_init() {
	_deprecated_function( __FUNCTION__, '5.5', 'xmlsf' );
}
