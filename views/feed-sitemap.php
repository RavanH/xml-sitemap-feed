<?php
/**
 * XML Sitemap Index Feed Template
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

if ( ! defined( 'WPINC' ) ) die;

// do xml tag via echo or SVN parser is going to freak out
echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . '"?>
<?xml-stylesheet type="text/xsl" href="' . wp_make_link_relative( plugins_url('assets/styles/sitemap-index.xsl',XMLSF_BASENAME) ) . '?ver=' . XMLSF_VERSION . '"?>
'; ?>
<?php xmlsf_generator(); ?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
		http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd">
	<sitemap>
		<loc><?php echo xmlsf_get_index_url('home'); ?></loc>
		<lastmod><?php echo mysql2date( DATE_W3C, get_lastpostdate( 'blog' ) ); ?></lastmod>
	</sitemap>
<?php
// add rules for public post types
$post_types = apply_filters( 'xmlsf_post_types', (array) get_option( 'xmlsf_post_types', array() ) );
if ( is_array($post_types) ) :
	foreach ( $post_types as $post_type => $settings ) {
		if ( empty($settings['active']) || ! post_type_exists( $post_type ) )
			continue;

		$archive = isset($settings['archive']) ? $settings['archive'] : '';

		foreach ( xmlsf_get_archives($post_type,$archive) as $m => $url ) {
?>
	<sitemap>
		<loc><?php echo $url; ?></loc>
		<lastmod><?php echo mysql2date( DATE_W3C, get_lastmodified( 'blog', $post_type, $m ) ); ?></lastmod>
	</sitemap>
<?php
		}
	}
endif;

// add rules for public taxonomies
foreach ( xmlsf_get_taxonomies() as $taxonomy ) : ?>
	<sitemap>
		<loc><?php echo xmlsf_get_index_url('taxonomy',$taxonomy); ?></loc>
<?php if ( $lastmod = xmlsf_get_taxonomy_modified( $taxonomy ) ) { ?>
		<lastmod><?php echo $lastmod; ?></lastmod>
<?php } ?>
	</sitemap>
<?php
endforeach;

// custom URLs sitemap
if ( apply_filters( 'xmlsf_custom_urls', get_option('xmlsf_urls') ) ) :
?>
	<sitemap>
		<loc><?php echo xmlsf_get_index_url('custom'); ?></loc>
	</sitemap>
<?php
endif;

// custom sitemaps
$custom_sitemaps = apply_filters( 'xmlsf_custom_sitemaps', get_option('xmlsf_custom_sitemaps', array()) );
if ( is_array($custom_sitemaps) ) :
	foreach ( $custom_sitemaps as $url ) {
		if (empty($url)) continue;
?>
	<sitemap>
		<loc><?php echo esc_url($url); ?></loc>
	</sitemap>
<?php
	}
endif;
?></sitemapindex>
<?php xmlsf_usage(); ?>
