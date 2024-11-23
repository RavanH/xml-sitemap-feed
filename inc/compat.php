<?php
/**
 * Set up compatibility hooks
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF;

if ( ! \function_exists( '\is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( \is_plugin_active( 'polylang/polylang.php' ) ) {
	\add_filter( 'xmlsf_blogpages', array( __NAMESPACE__ . '\Compat\Polylang', 'get_translations' ) );
	\add_filter( 'xmlsf_frontpages', array( __NAMESPACE__ . '\Compat\Polylang', 'get_translations' ) );

	\add_filter( 'xmlsf_request', array( __NAMESPACE__ . '\Compat\Polylang', 'filter_request' ) );
	\add_filter( 'xmlsf_core_request', array( __NAMESPACE__ . '\Compat\Polylang', 'filter_request' ) );
	\add_filter( 'xmlsf_news_request', array( __NAMESPACE__ . '\Compat\Polylang', 'filter_request' ) );


	\add_filter( 'xmlsf_request', array( __NAMESPACE__ . '\Compat\Polylang', 'request_actions' ) );
	\add_filter( 'xmlsf_news_request', array( __NAMESPACE__ . '\Compat\Polylang', 'request_actions' ) );

	\add_filter( 'xmlsf_news_publication_name', array( __NAMESPACE__ . '\Compat\Polylang', 'news_name' ), 10, 2 );

	\add_filter( 'xmlsf_news_language', array( __NAMESPACE__ . '\Compat\Polylang', 'post_language_filter' ), 10, 2 );

	\add_action( 'xmlsf_register_news_sitemap_provider', array( __NAMESPACE__ . '\Compat\Polylang', 'pre_register_news_provider' ) );
	\add_action( 'xmlsf_register_news_sitemap_provider_after', array( __NAMESPACE__ . '\Compat\Polylang', 'post_register_news_provider' ) );

	\add_filter( 'xmlsf_root_data', array( __NAMESPACE__ . '\Compat\Polylang', 'root_data' ) );
}

if ( \is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
	\add_filter( 'xmlsf_blogpages', array( __NAMESPACE__ . '\Compat\WPML', 'get_translations' ) );
	\add_filter( 'xmlsf_frontpages', array( __NAMESPACE__ . '\Compat\WPML', 'get_translations' ) );

	\add_action( 'xmlsf_add_settings', array( __NAMESPACE__ . '\Compat\WPML', 'remove_home_url_filter' ) );
	\add_action( 'xmlsf_news_add_settings', array( __NAMESPACE__ . '\Compat\WPML', 'remove_home_url_filter' ) );

	\add_filter( 'xmlsf_request', array( __NAMESPACE__ . '\Compat\WPML', 'filter_request' ) );
	\add_filter( 'xmlsf_news_request', array( __NAMESPACE__ . '\Compat\WPML', 'filter_request' ) );

	\add_action( 'xmlsf_url', array( __NAMESPACE__ . '\Compat\WPML', 'language_switcher' ) );
	\add_action( 'xmlsf_news_url', array( __NAMESPACE__ . '\Compat\WPML', 'language_switcher' ) );

	\add_filter( 'xmlsf_root_data', array( __NAMESPACE__ . '\Compat\WPML', 'root_data' ) );
}

if ( \is_plugin_active( 'bbpress/bbpress.php' ) ) {
	\add_filter( 'xmlsf_request', array( __NAMESPACE__ . '\Compat\BBPress', 'filter_request' ) );
	\add_filter( 'xmlsf_news_request', array( __NAMESPACE__ . '\Compat\BBPress', 'filter_request' ) );
}
