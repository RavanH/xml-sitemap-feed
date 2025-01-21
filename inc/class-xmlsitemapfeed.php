<?php
/**
 * XMLSitemapFeed CLASS
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF;

/**
 * XMLSitemapFeed CLASS
 */
class XMLSitemapFeed {
	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {}

	/**
	 * Plugin compatibility hooks and filters.
	 */
	public static function compat() {
		//if ( ! \function_exists( '\is_plugin_active' ) ) {
		//	require_once ABSPATH . 'wp-admin/includes/plugin.php';
		//}

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
			\add_action( 'xmlsf_register_sitemap_provider', array( __NAMESPACE__ . '\Compat\Polylang', 'remove_replace_provider' ) );
			\add_action( 'xmlsf_register_sitemap_provider_after', array( __NAMESPACE__ . '\Compat\Polylang', 'add_replace_provider' ) );
			\add_filter( 'xmlsf_root_data', array( __NAMESPACE__ . '\Compat\Polylang', 'root_data' ) );
			\add_filter( 'xmlsf_url_after', array( __NAMESPACE__ . '\Compat\Polylang', 'author_archive_translations' ), 10, 3 );
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
	}

	/**
	 * Plugin upgrade.
	 */
	public static function maybe_upgrade() {
		// Upgrade/install, maybe...
		$db_version = \get_option( 'xmlsf_version', 0 );
		if ( ! \version_compare( XMLSF_VERSION, $db_version, '=' ) ) {
			require_once XMLSF_DIR . '/upgrade.php';
		}
	}

	/**
	 * Plugin admin init.
	 */
	public static function admin_init() {
		xmlsf_admin();
	}

	/**
	 * Plugin main init.
	 */
	public static function init() {
		// Load plugin core.
		xmlsf();

		self::compat();
	}

	/**
	 * Filter robots.txt rules
	 *
	 * @param string $output Default robots.txt content.
	 *
	 * @return string
	 */
	public static function robots_txt( $output ) {

		// CUSTOM ROBOTS.
		$robots_custom = \get_option( 'xmlsf_robots' );
		$output       .= $robots_custom ? $robots_custom . PHP_EOL : '';

		// SITEMAPS.

		$output .= PHP_EOL . '# XML Sitemap & Google News version ' . XMLSF_VERSION . ' - https://status301.net/wordpress-plugins/xml-sitemap-feed/' . PHP_EOL;
		if ( 1 !== (int) \get_option( 'blog_public' ) ) {
			$output .= '# XML Sitemaps are disabled because of this site\'s visibility settings.' . PHP_EOL;
		} elseif ( ! namespace\sitemaps_enabled() ) {
			$output .= '# No XML Sitemaps are enabled.' . PHP_EOL;
		} else {
			namespace\sitemaps_enabled( 'sitemap' ) && $output .= 'Sitemap: ' . namespace\sitemap_url() . PHP_EOL;
			namespace\sitemaps_enabled( 'news' ) && $output    .= 'Sitemap: ' . namespace\sitemap_url( 'news' ) . PHP_EOL;
		}

		return $output;
	}
}
