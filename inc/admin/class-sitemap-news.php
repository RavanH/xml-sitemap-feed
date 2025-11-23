<?php
/**
 * XMLSF Admin Sitemap News CLASS
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Admin;

/**
 * XMLSF Admin Sitemap News CLASS
 */
class Sitemap_News {
	/**
	 * Initialize hooks and filters.
	 */
	public static function init() {
		\add_action( 'admin_notices', array( '\XMLSF\Admin\Sitemap_News', 'check_advanced' ), 0 );

		// META.
		\add_action( 'add_meta_boxes', array( '\XMLSF\Admin\Sitemap_News', 'add_meta_box' ) );
		\add_action( 'save_post', array( '\XMLSF\Admin\Sitemap_News', 'save_metadata' ) );
	}

	/**
	 * Plugin compatibility hooks and filters.
	 * Hooked on admin_init.
	 */
	public static function compat() {
		// Yoast SEO compatibility.
		if ( \is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			\add_action( 'admin_notices', array( '\XMLSF\Compat\WP_SEO', 'news_admin_notice' ) );
		}

		// Squirrly SEO compatibility.
		if ( \is_plugin_active( 'squirrly-seo/squirrly.php' ) ) {
			\add_action( 'admin_notices', array( '\XMLSF\Compat\Squirrly_SEO', 'news_admin_notices' ) );
		}
	}

	/**
	 * Compare versions to known compatibility.
	 */
	public static function compatible_with_advanced() {
		// Return true if plugin is not active.
		if ( ! \is_plugin_active( 'xml-sitemap-feed-advanced-news/xml-sitemap-advanced-news.php' ) ) {
			return true;
		}

		// Check version.
		\defined( 'XMLSF_NEWS_ADV_VERSION' ) || \define( 'XMLSF_NEWS_ADV_VERSION', '0' );

		return \version_compare( XMLSF_NEWS_ADV_MIN_VERSION, XMLSF_NEWS_ADV_VERSION, '<=' );
	}

	/**
	 * Check for conflicting themes and plugins
	 */
	public static function check_advanced() {
		if ( ! \current_user_can( 'manage_options' ) ) {
			return;
		}

		// Google News Advanced incompatibility notice.
		if ( ! self::compatible_with_advanced() && ! \in_array( 'xmlsf_advanced_news', (array) get_user_meta( get_current_user_id(), 'xmlsf_dismissed', false ), true ) ) {
			include XMLSF_DIR . '/views/admin/notice-xmlsf-advanced-news.php';
		}
	}

	/**
	 * Add a News Sitemap meta box to the side column
	 */
	public static function add_meta_box() {
		$news_tags       = \get_option( 'xmlsf_news_tags' );
		$news_post_types = ! empty( $news_tags['post_type'] ) && \is_array( $news_tags['post_type'] ) ? $news_tags['post_type'] : array( 'post' );

		// Only include metabox on post types that are included.
		foreach ( $news_post_types as $post_type ) {
			\add_meta_box(
				'xmlsf_news_section',
				__( 'Google News', 'xml-sitemap-feed' ),
				array( __CLASS__, 'meta_box' ),
				$post_type,
				'side'
			);
		}
	}

	/**
	 * Add a News Sitemap meta box to the side column
	 *
	 * @param obj $post The post object.
	 */
	public static function meta_box( $post ) {
		// Use nonce for verification.
		\wp_nonce_field( XMLSF_BASENAME, '_xmlsf_news_nonce' );

		// Use get_post_meta to retrieve an existing value from the database and use the value for the form.
		$exclude  = 'private' === $post->post_status || \get_post_meta( $post->ID, '_xmlsf_news_exclude', true );
		$disabled = 'private' === $post->post_status;

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-meta-box-news.php';
	}

	/**
	 * When the post is saved, save our meta data
	 *
	 * @param int $post_id The post ID.
	 */
	public static function save_metadata( $post_id ) {
		// Verify nonce and user privileges.
		if (
			! isset( $_POST['_xmlsf_news_nonce'] ) ||
			! \wp_verify_nonce( \wp_unslash( \sanitize_key( $_POST['_xmlsf_news_nonce'] ) ), XMLSF_BASENAME ) ||
			! \current_user_can( 'edit_post', $post_id )
		) {
			return;
		}

		// _xmlsf_news_exclude
		if ( empty( $_POST['xmlsf_news_exclude'] ) ) {
			\delete_post_meta( $post_id, '_xmlsf_news_exclude' );
		} else {
			\update_post_meta( $post_id, '_xmlsf_news_exclude', '1' );
		}
	}

	/**
	 * Add options page
	 */
	public static function add_options_page() {
		// This page will be under "Settings".
		$screen_id = \add_options_page(
			__( 'Google News Sitemap', 'xml-sitemap-feed' ),
			__( 'Google News', 'xml-sitemap-feed' ),
			'manage_options',
			'xmlsf_news',
			array( __NAMESPACE__ . '\Sitemap_News_Settings', 'settings_page' )
		);

		// Load settings.
		\add_action( 'load-' . $screen_id, array( __NAMESPACE__ . '\Sitemap_News_Settings', 'load' ) );
	}

	/**
	 * Register settings
	 */
	public static function register_settings() {
		\register_setting(
			'xmlsf_news_general',
			'xmlsf_news_tags',
			array( 'sanitize_callback' => array( __NAMESPACE__ . '\Sitemap_News_Settings', 'sanitize_news_tags' ) )
		);

		// Dummy register setting to prevent admin error on Save Settings from Advanced tab.
		\register_setting(
			'xmlsf_news_advanced',
			''
		);
	}
}
