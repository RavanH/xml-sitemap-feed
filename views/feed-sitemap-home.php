<?php
/**
 * XML Sitemap Feed Template for displaying an XML Sitemap feed.
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

if ( ! defined( 'WPINC' ) ) die;

echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . '"?>
<?xml-stylesheet type="text/xsl" href="' . plugins_url('views/styles/sitemap.xsl',XMLSF_BASENAME) . '?ver=' . XMLSF_VERSION . '"?>
'; ?>
<?php xmlsf_get_generator(); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
		http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<?php

$lastmodified = get_lastpostdate( 'gmt' ); // TODO take language into account !! Dont't use get_lastpostdate but pull one post for each language instead?
$lastactivityage = ( gmdate('U') - mysql2date( 'U', $lastmodified ) );
foreach ( xmlsf_get_home_urls() as $url ) {
?>
	<url>
		<loc><?php echo esc_url( $url ); ?></loc>
		<lastmod><?php echo mysql2date('Y-m-d\TH:i:s+00:00', $lastmodified, false); ?></lastmod>
		<priority>1.0</priority>
	</url>
<?php
}
?>
</urlset>
<?php xmlsf_get_usage(); ?>
