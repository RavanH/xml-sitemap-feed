<?php
/**
 * Plugin Name: XML Sitemap & Google News
 * Plugin URI: https://status301.net/wordpress-plugins/xml-sitemap-feed/
 * Description: Feed the hungry spiders in compliance with the XML Sitemap and Google News protocols. Happy with the results? Please leave me a <strong><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=XML%20Sitemap%20Feed">tip</a></strong> for continued development and support. Thanks :)
 * Version: 5.5-alpha19
 * Text Domain: xml-sitemap-feed
 * Requires at least: 4.4
 * Requires PHP: 5.6
 * Author: RavanH
 * Author URI: https://status301.net/
 *
 * @package XML Sitemap & Google News
 */

define( 'XMLSF_VERSION', '5.5-alpha19' );
define( 'XMLSF_ADV_MIN_VERSION', '0.1' );
define( 'XMLSF_NEWS_ADV_MIN_VERSION', '1.3.5' );

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

// Autoloader.
require_once XMLSF_DIR . '/inc/autoloader.php';

// Pluggable functions.
require_once XMLSF_DIR . '/inc/functions-pluggable.php';

// Shared functions.
require_once XMLSF_DIR . '/inc/functions.php';

// Prepare hooks for debugging.
WP_DEBUG && require_once XMLSF_DIR . '/inc/functions-debugging.php';

add_action( 'init', array( 'XMLSF\XMLSitemapFeed', 'maybe_upgrade' ), 8 );
add_action( 'init', array( 'XMLSF\XMLSitemapFeed', 'init' ), 9 );
add_action( 'admin_init', array( 'XMLSF\XMLSitemapFeed', 'admin_init' ), 9 );
add_filter( 'robots_txt', array( 'XMLSF\XMLSitemapFeed', 'robots_txt' ) );

/**
 * Get sitemap object
 *
 * @since 5.0
 *
 * @static XMLSF\XMLSitemapFeed $xmlsf
 * @return XMLSF\XMLSitemapFeed object by reference
 */
function xmlsf() {
	static $xmlsf;

	if ( ! isset( $xmlsf ) ) {
		$xmlsf = new XMLSF\XMLSF();
	}

	return $xmlsf;
}

/**
 * Get sitemap admin object
 *
 * @since 5.4
 *
 * @static XMLSF\Admin $xmlsf_admin
 * @return XMLSF\Admin object by reference
 */
function xmlsf_admin() {
	static $xmlsf_admin;

	if ( ! isset( $xmlsf_admin ) ) {
		$xmlsf_admin = new XMLSF\Admin();
	}

	return $xmlsf_admin;
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
	// TODO but how? remove_rewrite_rule() does not exist yet :/
	// Re-add core rules.
	function_exists( 'wp_sitemaps_get_server' ) && wp_sitemaps_get_server();
	// Then flush.
	flush_rewrite_rules( false );
}

register_deactivation_hook( __FILE__, 'xmlsf_deactivate' );
