<?php
/**
 * Google News Sitemap Feed Template
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

if ( ! defined( 'WPINC' ) ) die;

$options = get_option('xmlsf_news_tags');

// do xml tag via echo or SVN parser is going to freak out
echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . '"?>
<?xml-stylesheet type="text/xsl" href="' . wp_make_link_relative( plugins_url('assets/styles/sitemap-news.xsl',XMLSF_BASENAME) ) . '?ver=' . XMLSF_VERSION . '"?>
'; ?>
<?php xmlsf_generator(); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
<?php do_action('xmlsf_urlset', 'news'); ?>
	xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
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
				<news:language><?php echo xmlsf_get_language( $post->ID ); ?></news:language>
			</news:publication>
			<news:publication_date><?php echo mysql2date( DATE_W3C, $post->post_date ); ?></news:publication_date>
			<news:title><?php echo apply_filters( 'the_title_xmlsitemap', get_the_title() ); ?></news:title>
			<news:keywords><?php echo implode( ', ', apply_filters( 'xmlsf_news_keywords', array() ) ); ?></news:keywords>
			<news:stock_tickers><?php echo implode( ', ', apply_filters( 'xmlsf_news_stock_tickers', array() ) ); ?></news:stock_tickers>
<?php do_action( 'xmlsf_news_tags_after' ); ?>
		</news:news>
	</url>
<?php
			do_action( 'xmlsf_news_url_after' );
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
