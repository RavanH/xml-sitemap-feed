<?php
/**
 * Plugin Uninstallation
 *
 * @package XML Sitemap & Google News
 */

/**
 * XMLSF_MULTISITE_UNINSTALL
 *
 * Set this constant in wp-config.php if you want to allow looping over each site
 * in the network to run XMLSitemapFeed_Uninstall->uninstall() defined in uninstall.php
 *
 * There is NO batch-processing so it does not scale on large networks!
 * The constant is ignored on networks over 10k sites.
 *
 * Example:
 * define( 'XMLSF_MULTISITE_UNINSTALL', true);
 */

// Exit if uninstall not called from WordPress.
defined( 'WP_UNINSTALL_PLUGIN' ) || exit();

global $wpdb;

// Check if it is a multisite and if XMLSF_MULTISITE_UNINSTALL constant is defined
// if so, run the uninstall function for each blog id.
if ( is_multisite() && defined( 'XMLSF_MULTISITE_UNINSTALL' ) && XMLSF_MULTISITE_UNINSTALL && ! wp_is_large_network() ) {
	// Logging.
	WP_DEBUG_LOG && error_log( 'Clearing XML Sitemap Feeds settings from each site before uninstall:' );

	$blogs = $wpdb->get_col( $wpdb->prepare( 'SELECT %s FROM %s', array( 'blog_id', $wpdb->prefix . 'blogs' ) ) );

	foreach ( $blogs as $_id ) {
		switch_to_blog( $_id );
		xmlsf_uninstall();
		restore_current_blog();
		// Logging.
		WP_DEBUG_LOG && error_log( $_id );
	}
} else {
	xmlsf_uninstall();

	// Logging.
	WP_DEBUG_LOG && error_log( 'XML Sitemap Feeds settings cleared on uninstall.' );
}


/**
 * Remove plugin data.
 *
 * @since 4.4
 */
function xmlsf_uninstall() {
	// Remove cache metadata.
	// Should already have been done on plugin deactivation unless we're unstalling on multisite...
	include_once __DIR__ . '/inc/functions-sitemap.php';
	xmlsf_clear_metacache();

	// Remove post meta data.
	delete_metadata( 'post', 0, '_xmlsf_priority', '', true );
	delete_metadata( 'post', 0, '_xmlsf_exclude', '', true );
	delete_metadata( 'post', 0, '_xmlsf_news_exclude', '', true );

	// Remove plugin settings.
	delete_option( 'xmlsf_version' );
	delete_option( 'xmlsf_sitemaps' );
	delete_option( 'xmlsf_server' );
	delete_option( 'xmlsf_disabled_providers' );
	delete_option( 'xmlsf_post_types' );
	delete_option( 'xmlsf_taxonomies' );
	delete_option( 'xmlsf_taxonomy_settings' );
	delete_option( 'xmlsf_author_settings' );
	delete_option( 'xmlsf_ping' );
	delete_option( 'xmlsf_robots' );
	delete_option( 'xmlsf_urls' );
	delete_option( 'xmlsf_custom_sitemaps' );
	delete_option( 'xmlsf_domains' );
	delete_option( 'xmlsf_news_tags' );

	// Remove old transient.
	delete_transient( 'xmlsf_images_meta_primed' );
	delete_transient( 'xmlsf_comments_meta_primed' );
	delete_transient( 'xmlsf_static_files' );

	// Flush rules.
	flush_rewrite_rules( false );
}
