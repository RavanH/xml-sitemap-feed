<?php

/* --------------------------
 *        INITIALIZE
 * -------------------------- */

function xmlsf_init() {

	// Upgrade/install, maybe...
	$db_version = get_option( 'xmlsf_version', 0 );
	if ( ! version_compare( XMLSF_VERSION, $db_version, '=' ) ) {
		require XMLSF_DIR . '/controllers/upgrade.php';
		new XMLSitemapFeed_Upgrade( $db_version );
	}

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
function xmlsf_rewrite_rules( $rewrite_rules ) {
	global $wp_rewrite;

	$sitemaps = get_option( 'xmlsf_sitemaps' );

	if ( isset($sitemaps['sitemap']) ) {
		/* One rule to ring them all */
		//add_rewrite_rule('sitemap(-[a-z0-9_\-]+)?\.([0-9]+\.)?xml$', $wp_rewrite->index . '?feed=sitemap$matches[1]&m=$matches[2]', 'top');
		return array_merge( array( 'sitemap(\-[a-z0-9_\-]+)?(\.[0-9]+)?\.xml(\.gz)?$' => $wp_rewrite->index . '?feed=sitemap$matches[1]$matches[3]&m=$matches[2]' ), $rewrite_rules );
	} elseif ( isset($sitemaps['sitemap-news']) ) {
		//add_rewrite_rule('sitemap-news\.xml$', $wp_rewrite->index . '?feed=sitemap-news', 'top');
		return array_merge( array( 'sitemap-news\.xml(\.gz)?$' => $wp_rewrite->index . '?feed=sitemap-news$matches[1]' ), $rewrite_rules );
	}

	return $rewrite_rules;
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
	// remove filter and flush rules
	remove_filter( 'rewrite_rules_array', 'xmlsf_rewrite_rules', 1, 1 );
	flush_rewrite_rules();
}
