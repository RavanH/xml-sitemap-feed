<?php
/**
 * XML Sitemap Feed Template for displaying an XML Sitemap feed.
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

if ( ! defined( 'WPINC' ) ) die;

extract ( xmlsf_do_tags( get_query_var('post_type') ) );

xmlsf_sitemap()->prefetch_posts_meta();

if ( !empty($image) ) {
	$image_xmlns = '	xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"'.PHP_EOL;
	$image_schema = '
		http://www.google.com/schemas/sitemap-image/1.1
		http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd';
} else {
	$image_xmlns = '';
	$image_schema = '';
}

// do xml tag via echo or SVN parser is going to freak out
echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . '"?>
<?xml-stylesheet type="text/xsl" href="' . wp_make_link_relative( plugins_url('assets/styles/sitemap.xsl',XMLSF_BASENAME) ) . '?ver=' . XMLSF_VERSION . '"?>
'; ?>
<?php xmlsf_generator(); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
<?php do_action('xmlsf_urlset', 'post_type'); ?>
<?php echo $image_xmlns; ?>
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
		http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd<?php echo $image_schema; ?>">
<?php
// loop away!
if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();

		// check if page is in the exclusion list (like front page or post meta)
		// or if we are dealing with an external URL :: Thanks to Francois Deschenes :)
		if ( $post->ID == get_option('page_on_front')
			|| apply_filters( 'xmlsf_excluded', get_post_meta( $post->ID, '_xmlsf_exclude', true ), $post->ID )
			|| !xmlsf_is_allowed_domain( get_permalink() )
		) continue;

	$did_posts = true;
	?>
	<url>
		<loc><?php echo esc_url( get_permalink() ); ?></loc>
		<priority><?php echo xmlsf_get_post_priority(); ?></priority>
<?php if ( $lastmod = xmlsf_get_post_modified() ) { ?>
		<lastmod><?php echo $lastmod; ?></lastmod>
<?php } ?>
<?php
		if ( !empty($image) ) :
			foreach ( get_post_meta( $post->ID, '_xmlsf_image_'.$image ) as $img_data ) {
				if ( empty($img_data['loc']) )
					continue;
	?>
		<image:image>
			<image:loc><?php echo utf8_uri_encode( $img_data['loc'] ); ?></image:loc>
<?php
			if ( !empty($img_data['title']) ) {
		?>
			<image:title><![CDATA[<?php echo str_replace(']]>', ']]&gt;', $img_data['title']); ?>]]></image:title>
<?php
			}
			if ( !empty($img_data['caption']) ) {
		?>
			<image:caption><![CDATA[<?php echo str_replace(']]>', ']]&gt;', $img_data['caption']); ?>]]></image:caption>
<?php
			}
		?>
		</image:image>
<?php
			}
		endif;

		do_action( 'xmlsf_tags_after', 'post_type' );
?>
 	</url>
<?php
		do_action( 'xmlsf_url_after', 'post_type' );
  endwhile;
endif;

if ( empty( $did_posts ) ) :
	// No posts done? Then do at least the homepage to prevent error message in GWT.
	?>
	<url>
		<loc><?php echo esc_url( home_url() ); ?></loc>
		<priority>1.0</priority>
	</url>
<?php
endif;
?></urlset>
<?php xmlsf_usage(); ?>
