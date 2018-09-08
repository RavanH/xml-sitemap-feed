<?php
/**
 * XML Sitemap Feed Template for displaying an XML Sitemap feed.
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

if ( ! defined( 'WPINC' ) ) die;

extract ( xmlsf_do_tags( get_query_var('post_type') ) );

if ( !empty($image) ) {
	$image_xmlns = '	xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"'.PHP_EOL;
	$image_schema = '
		http://www.google.com/schemas/sitemap-image/1.1
		http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd';
} else {
	$image_xmlns = '';
	$image_schema = '';
}

?>
<?xml version="1.0" encoding="<?php echo get_bloginfo('charset'); ?>"?>
<?xml-stylesheet type="text/xsl" href="<?php echo plugins_url('views/styles/sitemap.xsl',XMLSF_BASENAME); ?>?ver=<?php echo XMLSF_VERSION; ?>"?>
<?php xmlsf_get_generator(); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
<?php echo $image_xmlns; ?>
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
		http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd<?php echo $image_schema; ?>">
<?php

// set empty sitemap flag
$have_posts = false;

// loop away!
if ( have_posts() ) :
    while ( have_posts() ) :
	the_post();

	// check if page is in the exclusion list (like front page or post meta)
	// or if we are not dealing with an external URL :: Thanks to Francois Deschenes :)
	if ( !empty(
			apply_filters(
				'xmlsf_excluded',
				get_post_meta( $post->ID, '_xmlsf_exclude', true ),
				$post->ID
			)
		 ) || !xmlsf_is_allowed_domain( get_permalink() ) ) {

		continue;
	}

	$have_posts = true;
	?>
	<url>
		<loc><?php echo esc_url( get_permalink() ); ?></loc>
		<?php xmlsf_the_priority(); ?>
		<?php xmlsf_the_lastmod(); ?>
<?php
	if ( !empty($image) ) :
		foreach ( xmlsf_get_images() as $image ) {
			if ( empty($image['loc']) )
				continue;
	?>
		<image:image>
			<image:loc><?php echo utf8_uri_encode( $image['loc'] ); ?></image:loc>
<?php
		if ( !empty($image['title']) ) {
		?>
			<image:title><![CDATA[<?php echo str_replace(']]>', ']]&gt;', $image['title']); ?>]]></image:title>
<?php
		}
		if ( !empty($image['caption']) ) {
		?>
			<image:caption><![CDATA[<?php echo str_replace(']]>', ']]&gt;', $image['caption']); ?>]]></image:caption>
<?php
		}
		?>
		</image:image>
<?php
		}
	endif;
?>
 	</url>
<?php
    endwhile;
endif;

if ( !$have_posts ) :
	// No posts done? Then do at least the homepage to prevent error message in GWT.
	?>
	<url>
		<loc><?php echo esc_url( home_url() ); ?></loc>
		<priority>1.0</priority>
	</url>
<?php
endif;
?></urlset>
<?php xmlsf_get_usage(); ?>
