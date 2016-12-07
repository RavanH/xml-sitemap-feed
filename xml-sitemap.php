<?php
/*
Plugin Name: XML Sitemap & Google News feeds
Plugin URI: http://status301.net/wordpress-plugins/xml-sitemap-feed/
Description: Feed the  hungry spiders in compliance with the XML Sitemap and Google News protocols. Happy with the results? Please leave me a <strong><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=XML%20Sitemap%20Feed&item_number=4%2e0&no_shipping=0&tax=0&bn=PP%2dDonationsBF&charset=UTF%2d8&lc=us">tip</a></strong> for continued development and support. Thanks :)
Text Domain: xml-sitemap-feed
Version: 4.7.5
Author: RavanH
Author URI: http://status301.net/
*/

/*  Copyright 2016 RavanH
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
 * FILTERS
 * 		xmlsf_defaults				-> Filters the default array values for different option groups.
 * 		xmlsf_allowed_domain	-> Filters the response when checking the url against allowed domains.
 *					Passes variable $url; must return true or false.
 * 		xmlsf_excluded				-> Filters the response when checking the post for exclusion flags.
 *					Passes variable $post_id; must return true or false.
 * 		the_title_xmlsitemap	-> Filters the Google News publication name, title and keywords
 *					plus the Image title and caption tags
 * 		xmlsf_custom_urls			-> Filters the custom urls array
 * 		xmlsf_custom_sitemaps	-> Filters the custom sitemaps array
 *
 * ACTIONS
 * 		xmlsf_news_tags_after	-> Fired inside the Google News Sitemap loop at the end of the news
 * 					tags, just before each closing </news:news> is generated. Can be used to
 * 					echo custom tags or trigger another action in the background.
 *
 * Feel free to request, suggest or submit more :)
 */

if ( ! defined( 'WPINC' ) ) die;

/* --------------------
 *      CONSTANTS
 * -------------------- */

	define('XMLSF_VERSION', '4.7.5');

	define('XMLSF_PLUGIN_BASENAME', plugin_basename(__FILE__));

/*
 * The following constants can be used to change plugin defaults
 * by defining them in wp-config.php
 */

/*
 * XMLSF_NAME
 *
 * Pretty permalink name for the main sitemap (index)
 */

if ( !defined('XMLSF_NAME') )

	define('XMLSF_NAME', 'sitemap.xml');

/*
 * XMLSF_NEWS_NAME
 *
 * Pretty permalink name for the news sitemap
 */

if ( !defined('XMLSF_NEWS_NAME') )

	define('XMLSF_NEWS_NAME', 'sitemap-news.xml');

/*
 * XMLSF_MULTISITE_UNINSTALL
 *
 * Set this constant in wp-config.php if you want to allow looping over each site
 * in the network to run XMLSitemapFeed_Uninstall->uninstall() defined in uninstall.php
 *
 * Be careful: There is NO batch-processing so it does not scale on large networks!
 *
 * example:
 * define('XMLSF_MULTISITE_UNINSTALL', true);
 */


/* -------------------------------------
 *      INCLUDE HACKS & CLASS
 * ------------------------------------- */

$xmlsf_dir = dirname(__FILE__);

if ( file_exists ( $xmlsf_dir.'/xml-sitemap-feed' ) )
	$xmlsf_dir .= '/xml-sitemap-feed';

include_once( $xmlsf_dir.'/hacks.php' );
include_once( $xmlsf_dir.'/includes/class-xmlsitemapfeed.php' );

/* ----------------------
 *     INSTANTIATE
 * ---------------------- */

$xmlsf = new XMLSitemapFeed();
