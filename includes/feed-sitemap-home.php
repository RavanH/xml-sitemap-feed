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

$lastmodified = get_lastdate( 'gmt' ); // TODO take language into account !! Dont't use get_lastdate but pull one post for each language instead?
$lastactivityage = ( gmdate('U') - mysql2date( 'U', $lastmodified ) );
foreach ( $xmlsf->get_home_urls() as $url ) {
?>
	<url>
		<loc><?php echo esc_url( $url ); ?></loc>
		<lastmod><?php echo mysql2date('Y-m-d\TH:i:s+00:00', $lastmodified, false); ?></lastmod>
		<changefreq><?php
	 	if ( ($lastactivityage/86400) < 1 ) { // last activity less than 1 day old
	 		echo 'hourly';
	 	} else if ( ($lastactivityage/86400) < 7 ) { // last activity less than 1 week old
	 		echo 'daily';
	 	} else { // over a week old
	 		echo 'weekly';
	 	}
		?></changefreq>
		<priority>1.0</priority>
	</url>
<?php
}
?>
</urlset>
<?php $xmlsf->_e_usage();
