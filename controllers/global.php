<?php

/* --------------------------
 *        INITIALIZE
 * -------------------------- */

function xmlsf_init() {

	if ( is_admin() ) {
		require XMLSF_DIR . '/controllers/admin/main.php';
	}

	// include sitemaps if any enabled
	if ( get_option( 'xmlsf_sitemaps' ) ) {

		// include main controller functions
		require XMLSF_DIR . '/controllers/main.php';

		add_action( 'clean_post_cache', 'xmlsf_clean_post_cache', 99, 2 );

		// PINGING
		add_action( 'transition_post_status', 'xmlsf_do_pings', 10, 3 );

		// Update term meta lastmod date
		add_action( 'transition_post_status', 'update_term_modified_meta', 10, 3 );

		// include main model functions
		require XMLSF_DIR . '/models/main.php';

		// MAIN REQUEST filter
		add_filter( 'request', 'xmlsf_filter_request', 1 );

		// force remove url trailing slash
		add_filter( 'user_trailingslashit', 'xmlsf_untrailingslash' );

		// common sitemap element filters
		add_filter( 'the_title_xmlsitemap', 'strip_tags' );
		add_filter( 'the_title_xmlsitemap', 'ent2ncr', 8 );
		add_filter( 'the_title_xmlsitemap', 'esc_html' );

		add_filter( 'xmlsf_news_post_types', 'xmlsf_news_filter_post_types' );
		add_filter( 'xmlsf_post_types', 'xmlsf_filter_post_types' );

		// include and instantiate class
		xmlsf();
	}

	// add robots.txt filter
	add_filter( 'robots_txt', 'xmlsf_robots_txt', 9 );
}

/**
 * Add sitemap rewrite rules
 *
 * @uses object $wp_rewrite
 *
 * @return void
 */
function xmlsf_rewrite_rules() {
	global $wp_rewrite;

	$sitemaps = get_option( 'xmlsf_sitemaps' );

	if ( isset($sitemaps['sitemap']) ) {
		/* One rule to ring them all */
		add_rewrite_rule('sitemap(-[a-z0-9_\-]+)?\.([0-9]+\.)?xml$', $wp_rewrite->index . '?feed=sitemap$matches[1]&m=$matches[2]', 'top');
	} elseif ( isset($sitemaps['sitemap-news']) ) {
		add_rewrite_rule('sitemap-news\.xml$', $wp_rewrite->index . '?feed=sitemap-news', 'top');
	}
}

/**
 * Upgrade/install, maybe...
 *
 * @since 5.0
 * @return void
 */
function xmlsf_maybe_upgrade() {
	$db_version = get_option( 'xmlsf_version', 0 );

	if ( version_compare( XMLSF_VERSION, $db_version, '=' ) ) {
		return;
	}

	require XMLSF_DIR . '/controllers/upgrade.php';

	new XMLSitemapFeed_Upgrade( $db_version );
}

/**
 * Plugin activation
 *
 * @since 5.0
 * @return void
 */

function xmlsf_activate() {
	delete_option( 'rewrite_rules' );
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
	flush_rewrite_rules();
}
