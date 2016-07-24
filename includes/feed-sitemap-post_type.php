<?php
/**
 * XML Sitemap Feed Template for displaying an XML Sitemap feed.
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

if ( ! defined( 'WPINC' ) ) die;

global $xmlsf;

foreach ( $xmlsf->do_tags( get_query_var('post_type') ) as $tag => $setting )
	${$tag} = $setting;

if ( !empty($image) ) {
	$image_xmlns = '	xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"'.PHP_EOL;
	$image_schema = '
		http://www.google.com/schemas/sitemap-image/1.1
		http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd';
} else {
	$image_xmlns = '';
	$image_schema = '';
}

// start output
echo $xmlsf->headers();
?>
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
	if ( $xmlsf->is_excluded($post->ID) || !$xmlsf->is_allowed_domain(get_permalink()) )
		continue;

	$have_posts = true;

	// TODO more image tags & video tags
	?>
	<url>
		<loc><?php echo esc_url( get_permalink() ); ?></loc>
		<?php echo $xmlsf->get_lastmod(); ?>
		<changefreq><?php echo $xmlsf->get_changefreq(); ?></changefreq>
	 	<priority><?php echo $xmlsf->get_priority(); ?></priority>
<?php
	if ( !empty($image) && $xmlsf->get_images() ) :
		foreach ( $xmlsf->get_images() as $image ) {
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
<?php $xmlsf->_e_usage();
