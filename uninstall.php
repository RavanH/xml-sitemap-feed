<?php
// if uninstall not called from WordPress exit
if (!defined('WP_UNINSTALL_PLUGIN'))
    exit();

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
			error_log('Clearing XML Sitemap Feeds settings from each site brefore uninstall:');
			foreach ($wpdb->get_col("SELECT blog_id FROM $wpdb->blogs") as $blog_id) {
				switch_to_blog($blog_id);
				$this->uninstall($blog_id);
			}
			restore_current_blog();
			error_log('Done.');
		}
		else
			$this->uninstall();
	}

	/*
	 * remove plugin data
	 *
	 * @since 4.4
	 */
	function uninstall($blog_id = false)
	{
		// delete all taxonomy terms
		register_taxonomy( 'gn-genre', null );

		$terms = get_terms('gn-genre',array('hide_empty' => false));

		if ( is_array($terms) )
			foreach ( $terms as $term )
				wp_delete_term(	$term->term_id, 'gn-genre' );

		// remove plugin settings
		delete_option('xmlsf_version');
		delete_option('xmlsf_sitemaps');
		delete_option('xmlsf_post_types');
		delete_option('xmlsf_taxonomies');
		delete_option('xmlsf_news_sitemap');
		delete_option('xmlsf_ping');
		delete_option('xmlsf_robots');
		delete_option('xmlsf_urls');
		delete_option('xmlsf_custom_sitemaps');
		delete_option('xmlsf_domains');
		delete_option('xmlsf_news_tags');

		// make rewrite rules update at the appropriate time
		delete_option('rewrite_rules');

		// Kilroy was here
		if ($blog_id)
			error_log('XML Sitemap Feeds settings cleared from site '.$blog_id.'.');
		else
			error_log('XML Sitemap Feeds settings cleared before uninstall.');
	}
}

new XMLSitemapFeed_Uninstall();
