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
'; ?>
<?php xmlsf_xml_stylesheet( 'news' ); ?>
<?php xmlsf_generator(); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
<?php do_action('xmlsf_urlset', 'news'); ?>
	xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
<?php
global $wp_query, $post;
// loop away!
if ( have_posts() ) :
	$wp_query->in_the_loop = true;
	while ( have_posts() ) :
		//the_post(); // disabled to avoid expensive but useless setup_postdata(), just do:
		// TODO : maybe make our own setup_postdata version?
		$post = $wp_query->next_post();

		// check if we are not dealing with an external URL :: Thanks to Francois Deschenes :)
		// or if post meta says "exclude me please"
		if ( apply_filters(
				'xmlsf_news_excluded',
				get_post_meta( $post->ID, '_xmlsf_news_exclude', true ),
				$post->ID
			) || !xmlsf_is_allowed_domain( get_permalink() )
		) continue;

		$did_posts = true;

		do_action( 'xmlsf_news_url' ); ?>
	<url>
		<loc><?php echo esc_url( get_permalink() ); ?></loc>
		<news:news>
			<news:publication>
				<news:name><?php
					if( !empty($options['name']) )
						echo apply_filters( 'xmlsf_news_publication_name', $options['name'] );
					elseif(defined('XMLSF_GOOGLE_NEWS_NAME'))
						echo apply_filters( 'xmlsf_news_publication_name', XMLSF_GOOGLE_NEWS_NAME );
					else
						echo apply_filters( 'xmlsf_news_publication_name', get_bloginfo('name') ); ?></news:name>
				<news:language><?php echo apply_filters( 'xmlsf_news_language', xmlsf()->blog_language(), $post->ID, $post->post_type ); ?></news:language>
			</news:publication>
			<news:publication_date><?php echo get_date_from_gmt( $post->post_date_gmt, DATE_W3C ); ?></news:publication_date>
			<news:title><?php echo apply_filters( 'xmlsf_news_title', get_the_title() ); ?></news:title>
			<news:keywords><?php echo implode( ', ', apply_filters( 'xmlsf_news_keywords', array() ) ); ?></news:keywords>
			<news:stock_tickers><?php echo implode( ', ', apply_filters( 'xmlsf_news_stock_tickers', array() ) ); ?></news:stock_tickers>
<?php do_action( 'xmlsf_news_tags_inner' ); ?>
		</news:news>
<?php do_action( 'xmlsf_news_tags_after' ); ?>
	</url>
<?php do_action( 'xmlsf_news_url_after' );
	endwhile;
	$wp_query->in_the_loop = false;
endif;

if ( empty( $did_posts ) ) :
	// No posts done? Then do at least the homepage to prevent error message in GWT.
	?>
	<url>
		<loc><?php echo esc_url( home_url() ); ?></loc>
	</url>
<?php
endif;
?></urlset>
<?php xmlsf_usage(); ?>
