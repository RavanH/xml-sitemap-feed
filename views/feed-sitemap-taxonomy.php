<?php
/**
 * Google News Sitemap Feed Template
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

if ( ! defined( 'WPINC' ) ) die;

// do xml tag via echo or SVN parser is going to freak out
echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . '"?>
<?xml-stylesheet type="text/xsl" href="' . wp_make_link_relative( plugins_url('assets/styles/sitemap-taxonomy.xsl',XMLSF_BASENAME) ) . '?ver=' . XMLSF_VERSION . '"?>
'; ?>
<?php xmlsf_generator(); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
<?php do_action('xmlsf_urlset', 'taxonomy'); ?>
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
	 	<priority><?php echo xmlsf_get_term_priority( $term ); ?></priority>
<?php if ( $lastmod = xmlsf_get_term_modified( $term ) ) { ?>
		<lastmod><?php echo $lastmod; ?></lastmod>
<?php }
 		do_action( 'xmlsf_tags_after', 'taxonomy' );
?>
	</url>
<?php
  do_action( 'xmlsf_url_after', 'taxonomy' );
	endforeach;
endif;

?></urlset>
<?php xmlsf_usage(); ?>
