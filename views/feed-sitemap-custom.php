<?php
/**
 * XML Sitemap Feed Template for displaying an XML Sitemap feed.
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

if ( ! defined( 'WPINC' ) ) die;

echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . '"?>
<?xml-stylesheet type="text/xsl" href="' . plugins_url('views/styles/sitemap-taxonomy.xsl',XMLSF_BASENAME) . '?ver=' . XMLSF_VERSION . '"?>
'; ?>
<?php xmlsf_generator(); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
		http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<?php

// get our custom urls array
$urls = apply_filters( 'xmlsf_custom_urls', get_option('xmlsf_urls') );
if ( is_array($urls) ) :
	// and loop away!
	foreach ( $urls as $url ) {
		if (empty($url[0]))
			continue;
	?>
	<url>
		<loc><?php echo esc_url( $url[0] ); ?></loc>
		<priority><?php echo ( isset($url[1]) && is_numeric($url[1]) ) ? $url[1] : '0.5'; ?></priority>
 	</url>
<?php
	};

endif;
?></urlset>
<?php xmlsf_usage(); ?>
