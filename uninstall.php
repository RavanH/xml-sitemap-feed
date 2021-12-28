<?php

/**
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

// exit if uninstall not called from WordPress
defined('WP_UNINSTALL_PLUGIN') || exit();

/*
 * XML Sitemap Feed uninstallation
 *
 * @since 4.4
 */
class XMLSitemapFeed_Uninstall {

	/*
	 * constructor: manages uninstall for multisite
	 *
	 * @since 4.4
	 */
	function __construct()
	{
		global $wpdb;

		// check if it is a multisite and if XMLSF_MULTISITE_UNINSTALL constant is defined
		// if so, run the uninstall function for each blog id
		if ( is_multisite() && defined('XMLSF_MULTISITE_UNINSTALL') && XMLSF_MULTISITE_UNINSTALL ) {
			error_log('Clearing XML Sitemap Feeds settings from each site before uninstall:');
			$field = 'blog_id';
			$table = $wpdb->prefix.'blogs';
			foreach ( $wpdb->get_col("SELECT {$field} FROM {$table}") as $blog_id ) {
				switch_to_blog($blog_id);
				$this->uninstall($blog_id);
			}
			restore_current_blog();
		} else {
			$this->uninstall();
		}
	}

	/*
	 * remove plugin data
	 *
	 * @since 4.4
	 */
	function uninstall($blog_id = false)
	{
		// remove metadata
	  	global $wpdb;
		// posts meta
	  	$wpdb->delete( $wpdb->prefix.'postmeta', array( 'meta_key' => '_xmlsf_image_attached' ) );
	  	$wpdb->delete( $wpdb->prefix.'postmeta', array( 'meta_key' => '_xmlsf_image_featured' ) );
		$wpdb->delete( $wpdb->prefix.'postmeta', array( 'meta_key' => '_xmlsf_comment_date_gmt' ) );
		$wpdb->delete( $wpdb->prefix.'postmeta', array( 'meta_key' => '_xmlsf_priority' ) );
		$wpdb->delete( $wpdb->prefix.'postmeta', array( 'meta_key' => '_xmlsf_exclude' ) );
		$wpdb->delete( $wpdb->prefix.'postmeta', array( 'meta_key' => '_xmlsf_news_exclude' ) );
	  	// terms meta
	  	$wpdb->delete( $wpdb->prefix.'termmeta', array( 'meta_key' => 'term_modified' ) );

		// remove transients
		delete_transient( 'xmlsf_flush_rewrite_rules' );
	  	delete_transient( 'xmlsf_check_static_files' );
		delete_transient( 'xmlsf_prefetch_post_meta_failed' );

		// remove plugin settings
		delete_option('xmlsf_version');
		delete_option('xmlsf_sitemaps');
		delete_option('xmlsf_post_types');
		delete_option('xmlsf_taxonomies');
		delete_option('xmlsf_taxonomy_settings');
		delete_option('xmlsf_author_settings');
		delete_option('xmlsf_ping');
		delete_option('xmlsf_robots');
		delete_option('xmlsf_urls');
		delete_option('xmlsf_custom_sitemaps');
		delete_option('xmlsf_domains');
		delete_option('xmlsf_news_tags');
		delete_option('xmlsf_images_meta_primed');
		delete_option('xmlsf_comments_meta_primed');

		// flush rules
		flush_rewrite_rules();

		// Kilroy was here
		if ( defined('WP_DEBUG') && WP_DEBUG ) {
			if ($blog_id)
				error_log( $blog_id );
			else
				error_log('XML Sitemap Feeds settings cleared on uninstall.');
		}
	}
}

new XMLSitemapFeed_Uninstall();
