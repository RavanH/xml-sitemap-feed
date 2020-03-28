<?php
/**
 * XML Sitemap Feed Template for displaying an XML Sitemap feed.
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

if ( ! defined( 'WPINC' ) ) die;

// do xml tag via echo or SVN parser is going to freak out
echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . '"?>
'; ?>
<?php xmlsf_xml_stylesheet( 'custom' ); ?>
<?php xmlsf_generator(); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
<?php do_action('xmlsf_urlset', 'custom'); ?>
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
		http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<?php

// get our custom urls array
$urls = apply_filters( 'xmlsf_custom_urls', get_option('xmlsf_urls') );
if ( is_array($urls) ) :
	// and loop away!
	foreach ( $urls as $url ) {
		if (empty($url[0])) continue;
	?>
	<url>
		<loc><?php echo esc_url( $url[0] ); ?></loc>
		<priority><?php echo ( isset($url[1]) && is_numeric($url[1]) ) ? $url[1] : '0.5'; ?></priority>
<?php 	do_action( 'xmlsf_tags_after', 'custom' ); ?>
 	</url>
<?php
		do_action( 'xmlsf_url_after', 'custom' );
	};

endif;
?></urlset>
<?php xmlsf_usage(); ?>
