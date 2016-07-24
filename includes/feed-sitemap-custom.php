<?php
/**
 * XML Sitemap Feed Template for displaying an XML Sitemap feed.
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

if ( ! defined( 'WPINC' ) ) die;

global $xmlsf;

// start output
echo $xmlsf->headers();
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
		http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<?php

// get our custom urls array
$urls = $xmlsf->get_urls();

// and loop away!
foreach ( $urls as $url ) {
	if (empty($url[0]))
		continue;

	if ( $xmlsf->is_allowed_domain( $url[0] ) ) {
?>
	<url>
		<loc><?php echo esc_url( $url[0] ); ?></loc>
		<priority><?php echo ( isset($url[1]) && is_numeric($url[1]) ) ? $url[1] : '0.5'; ?></priority>
 	</url>
<?php
	} else {
?>
	<!-- URL <?php echo esc_url( $url[0] ); ?> skipped: Not within allowed domains. -->
<?php
	}
}
?></urlset>
<?php $xmlsf->_e_usage();
