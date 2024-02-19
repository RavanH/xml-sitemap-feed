<?php
/**
 * XML Sitemap Feed Template for displaying an XML Sitemap feed.
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

defined( 'WPINC' ) || die;

global $xmlsf_sitemap;
$xmlsf_sitemap->prefetch_posts_meta();

// Do xml tag via echo or SVN parser is going to freak out.
echo '<?xml version="1.0" encoding="' . esc_xml( esc_attr( get_bloginfo( 'charset' ) ) ) . '"?>
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
		// Don't do the_post() here to avoid expensive setup_postdata(), just do the following.
		$post = $wp_query->next_post(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		// Check if page is front page.
		if ( (int) get_option( 'page_on_front' ) === $post->ID ) {
			continue;
		}

		$url = apply_filters( 'xmlsf_entry_url', get_permalink(), 'post_type', $post );

		// Use xmlsf_entry_url filter to return falsy value to exclude a specific URL.
		if ( empty( $url ) ) {
			continue;
		}

		// Or if post meta says "exclude me please".
		$excluded = apply_filters( 'xmlsf_excluded', get_post_meta( $post->ID, '_xmlsf_exclude', true ), $post->ID );
		if ( $excluded ) {
			continue;
		}

		$did_posts = true;

		do_action( 'xmlsf_url', 'post_type', $post );

		echo '<url>';
		echo '<loc>' . esc_xml( esc_url( $url ) ) . '</loc>';
		echo '<priority>' . esc_xml( xmlsf_get_post_priority( $post ) ) . '</priority>';

		$lastmod = xmlsf_get_post_modified( $post );
		if ( $lastmod ) {
			echo '<lastmod>' . esc_xml( $lastmod ) . '</lastmod>';
		}

		do_action( 'xmlsf_tags_after', 'post_type', $post );

		echo '</url>';

		do_action( 'xmlsf_url_after', 'post_type', $post );

		echo PHP_EOL;
	endwhile;
	$wp_query->in_the_loop = false;
endif;

if ( empty( $did_posts ) ) {
	// No posts done? Then do at least the homepage to prevent error message in GWT.
	echo '<url><loc>' . esc_url( home_url() ) . '</loc><priority>1.0</priority></url>' . PHP_EOL;
}
?>
</urlset>
