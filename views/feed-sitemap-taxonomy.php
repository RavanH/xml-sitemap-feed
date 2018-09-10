<?php
/**
 * Google News Sitemap Feed Template
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
$terms = get_terms( get_query_var('taxonomy') );

if ( is_array($terms) ) :
    foreach ( $terms as $term ) :
	?>
	<url>
		<loc><?php echo get_term_link( $term ); ?></loc>
	 	<priority><?php echo xmlsf_get_priority('taxonomy',$term); ?></priority>
<?php if ( $lastmod = xmlsf_get_lastmod('taxonomy',$term) ) { ?>
		<lastmod><?php echo $lastmod; ?></lastmod>
<?php } ?>
	</url>
<?php
    endforeach;
endif;

?></urlset>
<?php xmlsf_usage(); ?>
