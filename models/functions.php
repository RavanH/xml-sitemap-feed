<?php

/**
 * Get instantiated sitemap class
 *
 * @since 5.0
 * @global XMLSitemapFeed $xmlsf
 * @return XMLSitemapFeed object
 */
function xmlsf() {
	global $xmlsf;

	if ( ! isset( $xmlsf ) ) {
		if ( ! class_exists( 'XMLSitemapFeed' ) )
			require XMLSF_DIR . '/models/class-xmlsitemapfeed.php';

		$xmlsf = new XMLSitemapFeed();
	}

	return $xmlsf;
}

/**
 * Add sitemap rewrite rules
 *
 * @global object $wp_rewrite
 * @param array $rewrite_rules
 * @return array $rewrite_rules
 */
function xmlsf_rewrite_rules( $rewrite_rules ) {
	global $wp_rewrite;

	$sitemaps = get_option( 'xmlsf_sitemaps' );

	if ( isset($sitemaps['sitemap']) ) {
		/* One rule to ring them all */
		//add_rewrite_rule('sitemap(-[a-z0-9_\-]+)?\.([0-9]+\.)?xml$', $wp_rewrite->index . '?feed=sitemap$matches[1]&m=$matches[2]', 'top');
		return array_merge( array( 'sitemap(?:_index)?(\-[a-z0-9\-_]+)?(\.[0-9]+)?\.xml(\.gz)?$' => $wp_rewrite->index . '?feed=sitemap$matches[1]$matches[3]&m=$matches[2]' ), $rewrite_rules );
	} elseif ( isset($sitemaps['sitemap-news']) ) {
		//add_rewrite_rule('sitemap-news\.xml$', $wp_rewrite->index . '?feed=sitemap-news', 'top');
		return array_merge( array( 'sitemap-news\.xml(\.gz)?$' => $wp_rewrite->index . '?feed=sitemap-news$matches[1]' ), $rewrite_rules );
	}

	return $rewrite_rules;
}

/**
 * Filter robots.txt rules
 *
 * @param $output
 * @return string
 */
function xmlsf_robots_txt( $output ) {
	$url = trailingslashit( get_bloginfo('url') );

	$sitemaps = get_option( 'xmlsf_sitemaps' );

	// PRE
	$pre = '# XML Sitemap & Google News version ' . XMLSF_VERSION . ' - https://status301.net/wordpress-plugins/xml-sitemap-feed/' . PHP_EOL;
	if ( '1' != get_option('blog_public') )
		$pre .= '# XML Sitemaps are disabled because of this site\'s privacy settings.' . PHP_EOL;
	elseif( !is_array($sitemaps) || empty( $sitemaps ) )
		$pre .= '# No XML Sitemaps are enabled on this site.' . PHP_EOL;
	else
		foreach ( $sitemaps as $pretty )
			$pre .= 'Sitemap: ' . $url . $pretty . PHP_EOL;
	$pre .= PHP_EOL;

	// DEFAULT
	if ( substr($output, -1) !== PHP_EOL ) $output .= PHP_EOL;

	// POST
	$post = get_option('xmlsf_robots');
	if ( $post !== '' ) $post .= PHP_EOL;

	return $pre . $output . $post;
}

/* -------------------------------------
 *     CONDITIONAL FUNCTIONS
 * ------------------------------------- */

/**
 * Is the query for a sitemap?
 *
 * @since 4.8
 * @return bool
 */
function is_sitemap() {
	global $xmlsf;
	if ( ! is_object( $xmlsf ) || $xmlsf->request_filtered === false ) {
		_doing_it_wrong( __FUNCTION__, __( 'Conditional sitemap tags do not work before the sitemap request filter is run. Before then, they always return false.', 'xml-sitemap-feed' ), '4.8' );
		return false;
	}
	return $xmlsf->is_sitemap;
}

/**
 * Is the query for a news sitemap?
 *
 * @since 4.8
 * @return bool
 */
function is_news() {
	global $xmlsf;
	if ( ! is_object( $xmlsf ) || $xmlsf->request_filtered === false ) {
		_doing_it_wrong( __FUNCTION__, __( 'Conditional sitemap tags do not work before the sitemap request filter is run. Before then, they always return false.', 'xml-sitemap-feed' ), '4.8' );
		return false;
	}
	return $xmlsf->is_news;
}
