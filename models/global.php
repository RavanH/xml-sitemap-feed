<?php

/**
 * Filter robots.txt rules
 *
 * @param $output
 * @return string
 */
function xmlsf_robots_txt( $output ) {
	$url = trailingslashit( get_bloginfo('url') );

	$sitemaps = get_option( 'xmlsf_sitemaps' );

	$pre = '# XML Sitemap & Google News version ' . XMLSF_VERSION . ' - https://status301.net/wordpress-plugins/xml-sitemap-feed/' . PHP_EOL;
	if ( '1' != get_option('blog_public') )
		$pre .= '# XML Sitemaps are disabled because of this site\'s privacy settings.' . PHP_EOL;
	elseif( !is_array($sitemaps) || empty( $sitemaps ) )
		$pre .= '# No XML Sitemaps are enabled on this site.' . PHP_EOL;
	else
		foreach ( $sitemaps as $pretty )
			$pre .= 'Sitemap: ' . $url . $pretty . PHP_EOL;
	$pre .= PHP_EOL;

	$post = get_option('xmlsf_robots');

	return $pre . $output . $post;
}

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
