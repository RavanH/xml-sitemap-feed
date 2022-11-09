<?php
/**
 * XML Sitemap Feed Template for displaying an XML Sitemap feed.
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

if ( ! defined( 'WPINC' ) ) die;

global $xmlsf_sitemap;
$xmlsf_sitemap->prefetch_posts_meta();

// Do xml tag via echo or SVN parser is going to freak out.
echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . '"?>
'; ?>
<?php xmlsf_xml_stylesheet( 'posttype' ); ?>
<?php do_action( 'xmlsf_generator' ); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" <?php do_action( 'xmlsf_urlset', 'post_type' ); ?>>
<?php
global $wp_query, $post;

// Loop away!
if ( have_posts() ) :
	$wp_query->in_the_loop = true;
	while ( have_posts() ) :
		// Don't do the_post() here to avoid expensive setup_postdata(), just do:
		$post = $wp_query->next_post();

		// Check if page is in the exclusion list (like front page or post meta)
		// or if we are dealing with an external URL :: Thanks to Francois Deschenes :)
		if (
			$post->ID == get_option('page_on_front') ||
			apply_filters( 'xmlsf_excluded', get_post_meta( $post->ID, '_xmlsf_exclude', true ), $post->ID ) ||
			! xmlsf_is_allowed_domain( get_permalink() )
		) {
			continue;
		}

		$did_posts = true;

		do_action( 'xmlsf_url', 'post_type' );
		?>
	<url>
		<loc><?php echo esc_url( get_permalink() ); ?></loc>
		<priority><?php echo htmlspecialchars( xmlsf_get_post_priority( $post ), ENT_COMPAT, get_bloginfo('charset') ); ?></priority>
<?php if ( $lastmod = xmlsf_get_post_modified( $post ) ) { ?>
		<lastmod><?php echo htmlspecialchars( $lastmod, ENT_COMPAT, get_bloginfo('charset') ); ?></lastmod>
<?php } ?>
<?php	do_action( 'xmlsf_tags_after', 'post_type', $post, $image ); ?>
 	</url>
<?php	do_action( 'xmlsf_url_after', 'post_type' );
	endwhile;
	$wp_query->in_the_loop = false;
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
