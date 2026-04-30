<?php
/**
 * Admin for Sitemap
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Admin;

/**
 * Admin Sitemap Class
 */
class Sitemap_News_Fields {
	/**
	 * Post types field
	 */
	public static function post_types() {
		include XMLSF_DIR . '/views/admin/field-news-hierarchical.php';
	}

	/**
	 * Keywords field
	 */
	public static function keywords() {
		include XMLSF_DIR . '/views/admin/field-news-keywords.php';
	}

	/**
	 * Stock tickers field
	 */
	public static function stock_tickers() {
		include XMLSF_DIR . '/views/admin/field-news-stocktickers.php';
	}

	/**
	 * Sitemap notifier field
	 */
	public static function sitemap_notifier() {
		include XMLSF_DIR . '/views/admin/field-news-notifier.php';
	}

	/**
	 * GSC log section
	 *
	 * @since 5.7
	 */
	public static function sitemap_notifier_log() {
		$log = (array) get_option( 'xmlsf_news_notifier_log', array() );
		$advanced = apply_filters( 'xmlsf_news_advanced_enabled', false ) ? '' : sprintf( /* Translators: %s: Google News Advanced (with link) */ esc_html__( 'Available in %s.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/google-news-advanced/" target="_blank">' . esc_html__( 'Google News Advanced', 'xml-sitemap-feed' ) . '</a>' );

		include XMLSF_DIR . '/views/admin/section-ping-log.php';
	}

	/**
	 * News name
	 */
	public static function name() {
		include XMLSF_DIR . '/views/admin/field-news-name.php';
	}

	/**
	 * Post type
	 */
	public static function post_type() {
		include XMLSF_DIR . '/views/admin/field-news-post-type.php';
	}

	/**
	 * Categories
	 */
	public static function categories() {
		include XMLSF_DIR . '/views/admin/field-news-categories.php';
	}

	/**
	 * GSC Data
	 */
	public static function gsc_data() {
		include XMLSF_DIR . '/views/admin/section-gsc-data-news.php';
	}

	/**
	 * Sidebar GSC section
	 */
	public static function sidebar_gsc_connect() {
		$sitemap_desc      = __( 'Google News Sitemap', 'xml-sitemap-feed' );
		$settings_page_url = \add_query_arg( 'ref', 'xmlsf_news', GSC_Connect::get_settings_url() );

		include XMLSF_DIR . '/views/admin/sidebar-gsc-connect.php';
	}

	/**
	 * Sidbar tools
	 */
	public static function sidebar_tools() {
		include XMLSF_DIR . '/views/admin/sidebar-news-tools.php';
	}

	/**
	 * Sidebar links
	 */
	public static function sidebar_links() {
		include XMLSF_DIR . '/views/admin/sidebar-news-links.php';
	}

	/**
	 * Advanced plug
	 */
	public static function advanced_plug() {
		include XMLSF_DIR . '/views/admin/sidebar-news-advanced-plug.php';
	}

	/**
	 * Sidebar Priority Support section
	 */
	public static function sidebar_priority_support() {
		$adv_plugin_name = __( 'Google News Advanced', 'xml-sitemap-feed' );
		$adv_plugin_url  = 'https://premium.status301.com/downloads/google-news-advanced/';

		include XMLSF_DIR . '/views/admin/sidebar-priority-support.php';
	}
}
