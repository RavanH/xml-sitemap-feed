<?php

/**
 * Nginx helper purge urls
 * adds sitemap urls to the purge array.
 *
 * @param $urls array
 * @param $redis bool|false
 *
 * @return $urls array
 */
function xmlsf_nginx_helper_purge_urls( $urls = array(), $redis = false ) {

	if ( $redis ) {
		// wildcard allowed, this makes everything simple
		$urls[] = '/sitemap*.xml';
	} else {
		// no wildcard, go through the motions
		$sitemaps = get_option( 'xmlsf_sitemaps' );

		if ( !empty( $sitemaps['sitemap-news'] ) ) {
			$urls[] = '/sitemap-news.xml';
		}

		if ( !empty( $sitemaps['sitemap'] ) ) {
			$urls[] = '/sitemap.xml';
			$urls[] = '/sitemap-home.xml';
			$urls[] = '/sitemap-custom.xml';

			include_once XMLSF_DIR . '/models/public/sitemap.php';

			// add public post types sitemaps
			$post_types = get_option( 'xmlsf_post_types' );
			if ( is_array($post_types) )
				foreach ( $post_types as $post_type => $settings ) {
					$archive = !empty($settings['archive']) ? $settings['archive'] : '';
					foreach ( xmlsf_get_archives($post_type,$archive) as $url )
						 $urls[] = parse_url( $url, PHP_URL_PATH);
				};

			// add public post taxonomies sitemaps
			$taxonomies = get_option('xmlsf_taxonomies');
			if ( is_array($taxonomies) )
				foreach ( $taxonomies as $taxonomy ) {
					$urls[] = parse_url( xmlsf_get_index_url('taxonomy',$taxonomy), PHP_URL_PATH);
				};
		}
	}

	return $urls;
}

// plugin action links

function xmlsf_add_action_link( $links ) {
	$settings_link = '<a href="' . admin_url('options-reading.php') . '#blog_public">' . translate('Settings') . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}

class XMLSF_Admin_Sanitize
{

	public static function sitemaps_settings( $new )
	{
		if  ( '1' !== get_option('blog_public') ) {
			return '';
		}

		$old = get_option( 'xmlsf_sitemaps' );
		$sanitized = array();

		if ( $old !== $new ) {
			// when sitemaps are added or removed, set transients
			set_transient('xmlsf_flush_rewrite_rules','');
			set_transient('xmlsf_check_static_files','');

			// switched on news sitemap
			if ( !empty($new['sitemap-news']) && empty($old['sitemap-news'] ) ) {
				// check news tag settings
				if ( !get_option( 'xmlsf_news_tags' ) ) {
					add_option( 'xmlsf_news_tags', array(
						'name' => '',
						'post_type' => array('post'),
						'categories' => '',
						'image' => 'featured'
					) );
				}
			}
		}

		if ( !empty($new['sitemap']) ) {
			$sanitized['sitemap'] = apply_filters( 'xmlsf_sitemap_filename', $new['sitemap'] );
		}

		if ( !empty($new['sitemap-news']) ) {
			$sanitized['sitemap-news'] = apply_filters( 'xmlsf_sitemap_news_filename', $new['sitemap-news'] );
		}

		return $sanitized;
	}

	public static function domains_settings( $new )
	{
		$default = parse_url( home_url(), PHP_URL_HOST );

		// clean up input
		if(is_array($new)) {
		  $new = array_filter($new);
		  $new = reset($new);
		}
		$input = $new ? explode( PHP_EOL, sanitize_textarea_field( $new ) ) : array();

		// build sanitized output
		$sanitized = array();
		foreach ($input as $line) {
			$line = trim($line);
			$parsed_url = parse_url( trim( filter_var( $line, FILTER_SANITIZE_URL ) ) );
			// Before PHP version 5.4.7, parse_url will return the domain as path when scheme is omitted so we do:
			if ( !empty($parsed_url['host']) ) {
				$domain = trim( $parsed_url['host'] );
			} else {
				$domain_arr = explode('/', $parsed_url['path']);
				$domain_arr = array_filter($domain_arr);
				$domain = array_shift( $domain_arr );
				$domain = trim( $domain );
			}

			// filter out empties and default domain
			if(!empty($domain) && $domain !== $default && strpos($domain,".".$default) === false)
				$sanitized[] = $domain;
		}

		return (!empty($sanitized)) ? $sanitized : '';
	}

	public static function ping_settings( $new )
	{
		return is_array($new) ? $new : array();
	}

	public static function robots_settings( $new )
	{
		$old = get_option('xmlsf_robots');

		// clean up input
		if ( is_array( $new ) ) {
		  $new = array_filter( $new );
		  $new = reset( $new );
		}

		if ( empty($old) && !empty($new) )
			set_transient('xmlsf_check_static_files','');

		return sanitize_textarea_field( $new );
	}
}
