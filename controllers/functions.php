<?php

/* --------------------------
 *        INITIALIZE
 * -------------------------- */

function xmlsf_init() {

	// Upgrade/install, maybe...
	$db_version = get_option( 'xmlsf_version', 0 );
	if ( ! version_compare( XMLSF_VERSION, $db_version, '=' ) ) {
		require_once XMLSF_DIR . '/upgrade.php';
		new XMLSitemapFeed_Upgrade( $db_version );
	}

	$sitemaps = get_option( 'xmlsf_sitemaps' );
	// include sitemaps if any enabled
	if ( $sitemaps ) {
		// main model functions
		require XMLSF_DIR . '/models/functions.shared.php';

		// force remove url trailing slash
		add_filter( 'user_trailingslashit', 'xmlsf_untrailingslash' );

		// MAIN REQUEST filter
		add_filter( 'request', 'xmlsf_filter_request', 1 );

		// NGINX HELPER PURGE URLS
		add_filter( 'rt_nginx_helper_purge_urls', 'xmlsf_nginx_helper_purge_urls', 10, 2 );

		// main controller functions
		require XMLSF_DIR . '/controllers/functions.shared.php';

		add_action( 'xmlsf_ping', 'xmlsf_debug_ping', 9, 4 );

		// include and instantiate class
		xmlsf();

		if ( ! empty( $sitemaps['sitemap-news'] ) ) {
			require XMLSF_DIR . '/models/functions.sitemap-news.php';
			add_filter( 'xmlsf_news_post_types', 'xmlsf_news_filter_post_types' );

			require XMLSF_DIR . '/controllers/class.xmlsf-sitemap-news.php';
			new XMLSF_Sitemap_News( $sitemaps['sitemap-news'] );

			// add feed type, news can now be accessed via /feed/sitemap-news too
			add_feed( 'sitemap-news', 'xmlsf_news_load_template' );
		}

		if ( ! empty( $sitemaps['sitemap'] ) ) {
			require XMLSF_DIR . '/models/functions.sitemap.php';
			add_filter( 'xmlsf_post_types', 'xmlsf_filter_post_types' );

			xmlsf_sitemap( $sitemaps['sitemap'] );
		}

		// common sitemap element filters
		add_filter( 'the_title_xmlsitemap', 'strip_tags' );
		add_filter( 'the_title_xmlsitemap', 'ent2ncr', 8 );
		add_filter( 'the_title_xmlsitemap', 'esc_html' );
	}

	// add robots.txt filter
	add_filter( 'robots_txt', 'xmlsf_robots_txt', 9 );
}

/**
 * Plugin activation
 *
 * @since 5.0
 * @return void
 */

function xmlsf_activate() {
	set_transient( 'xmlsf_flush_rewrite_rules', '' );
	set_transient( 'xmlsf_check_static_files', '' );
}

/**
 * Plugin de-activation
 *
 * @since 5.0
 * @return void
 */

function xmlsf_deactivate() {
	delete_transient( 'xmlsf_flush_rewrite_rules' );
	delete_transient( 'xmlsf_check_static_files' );

	// remove metadata
	global $wpdb;
	// posts meta
	$wpdb->delete( $wpdb->prefix.'postmeta', array( 'meta_key' => '_xmlsf_image_attached' ) );
	$wpdb->delete( $wpdb->prefix.'postmeta', array( 'meta_key' => '_xmlsf_image_featured' ) );
	$wpdb->delete( $wpdb->prefix.'postmeta', array( 'meta_key' => '_xmlsf_comment_date' ) );
	// terms meta
	$wpdb->delete( $wpdb->prefix.'termmeta', array( 'meta_key' => 'term_modified' ) );

	// remove filter and flush rules
	remove_filter( 'rewrite_rules_array', 'xmlsf_rewrite_rules', 99 );
	// how to unset add_feed() ?
	flush_rewrite_rules();
}
