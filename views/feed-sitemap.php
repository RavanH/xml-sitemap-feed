<?php
/**
 * XML Sitemap Index Feed Template
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

if ( ! defined( 'WPINC' ) ) die;

// do xml tag via echo or SVN parser is going to freak out
echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . '"?>'; ?>
<?php xmlsf_xml_stylesheet(); ?>
<?php xmlsf_generator(); ?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
		http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd">
	<sitemap>
		<loc><?php echo xmlsf_get_index_url(); ?></loc>
		<lastmod><?php echo get_date_from_gmt( get_lastpostdate( 'GMT' ), DATE_W3C ); ?></lastmod>
	</sitemap>
<?php

// public post types
$post_types = (array) apply_filters( 'xmlsf_post_types', get_option( 'xmlsf_post_types', array() ) );
if ( ! empty( $post_types ) ) :
	foreach ( $post_types as $post_type => $settings ) {
		if ( empty( $settings['active'] ) || ! post_type_exists( $post_type ) )
			continue;

		$archive = isset( $settings['archive'] ) ? $settings['archive'] : '';

		foreach ( xmlsf_get_index_archive_data( $post_type, $archive ) as $url => $lastmod ) {
?>
	<sitemap>
		<loc><?php echo $url; ?></loc>
		<lastmod><?php echo $lastmod; ?></lastmod>
	</sitemap>
<?php
		}
	}
endif;

// public taxonomies
foreach ( xmlsf_get_taxonomies() as $taxonomy ) : ?>
	<sitemap>
		<loc><?php echo xmlsf_get_index_url( 'taxonomy', array( 'type' => $taxonomy ) ); ?></loc>
<?php if ( $lastmod = xmlsf_get_taxonomy_modified( $taxonomy ) ) { ?>
		<lastmod><?php echo $lastmod; ?></lastmod>
<?php } ?>
	</sitemap>
<?php
endforeach;

// authors
if ( xmlsf_do_authors() ) : ?>
	<sitemap>
		<loc><?php echo xmlsf_get_index_url( 'author' ); ?></loc>
		<lastmod><?php echo get_date_from_gmt( get_lastpostdate( 'GMT' ), DATE_W3C ); ?></lastmod>
	</sitemap>
<?php
endif;

// custom URLs sitemap
if ( apply_filters( 'xmlsf_custom_urls', get_option( 'xmlsf_urls' ) ) ) :
?>
	<sitemap>
		<loc><?php echo xmlsf_get_index_url( 'custom' ); ?></loc>
	</sitemap>
<?php
endif;

// custom sitemaps
$custom_sitemaps = apply_filters( 'xmlsf_custom_sitemaps', get_option( 'xmlsf_custom_sitemaps', array() ) );
if ( is_array( $custom_sitemaps ) ) :
	foreach ( $custom_sitemaps as $url ) {
		if ( empty( $url ) ) continue;
?>
	<sitemap>
		<loc><?php echo esc_url( $url ); ?></loc>
	</sitemap>
<?php
	}
endif;
?></sitemapindex>
<?php xmlsf_usage(); ?>
