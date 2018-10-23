<?php
/**
 * Google News Sitemap Feed Template
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

if ( ! defined( 'WPINC' ) ) die;

$options = get_option('xmlsf_news_tags');
if ( !is_array($options) ) $options = array();

if ( !empty($options['image']) ) {
	$image_xmlns = '	xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"'.PHP_EOL;
	$image_schema = '
		http://www.google.com/schemas/sitemap-image/1.1
		http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd';
} else {
	$image_xmlns = '';
	$image_schema = '';
}

echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . '"?>
<?xml-stylesheet type="text/xsl" href="' . plugins_url('views/styles/sitemap-news.xsl',XMLSF_BASENAME) . '?ver=' . XMLSF_VERSION . '"?>
'; ?>
<?php xmlsf_generator(); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"
<?php echo $image_xmlns; ?>
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
		http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd
		http://www.google.com/schemas/sitemap-news/0.9
		http://www.google.com/schemas/sitemap-news/0.9/sitemap-news.xsd<?php echo $image_schema; ?>">
<?php

// set empty news sitemap flag
$have_posts = false;

// loop away!
if ( have_posts() ) :
    while ( have_posts() ) :
	the_post();

	// check if we are not dealing with an external URL :: Thanks to Francois Deschenes :)
	// or if post meta says "exclude me please"
	if ( apply_filters(
		 	'xmlsf_news_excluded',
		 	get_post_meta( $post->ID, '_xmlsf_news_exclude', true ),
		 	$post->ID
		 ) || !xmlsf_is_allowed_domain( get_permalink() ) )
		continue;

	$have_posts = true;
	?>
	<url>
		<loc><?php echo esc_url( get_permalink() ); ?></loc>
		<news:news>
			<news:publication>
				<news:name><?php
					if( !empty($options['name']) )
						echo apply_filters( 'the_title_xmlsitemap', $options['name'] );
					elseif(defined('XMLSF_GOOGLE_NEWS_NAME'))
						echo apply_filters( 'the_title_xmlsitemap', XMLSF_GOOGLE_NEWS_NAME );
					else
						echo apply_filters( 'the_title_xmlsitemap', get_bloginfo('name') ); ?></news:name>
				<news:language><?php echo xmlsf_get_language($post->ID); ?></news:language>
			</news:publication>
			<news:publication_date><?php
				echo mysql2date('Y-m-d\TH:i:s+00:00', $post->post_date_gmt, false); ?></news:publication_date>
			<news:title><?php echo apply_filters( 'the_title_xmlsitemap', get_the_title() ); ?></news:title>

<?php do_action( 'xmlsf_news_tags_after' ); ?>
		</news:news>
<?php
	if ( !empty($options['image']) ) :
		foreach ( xmlsf_get_images('news') as $image ) {
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
	</url>
<?php
endif;
?></urlset>
<?php xmlsf_usage(); ?>
