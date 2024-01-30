<?php
/**
 * Google News Sitemap Feed Template
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

defined( 'WPINC' ) || die;

$options = get_option( 'xmlsf_news_tags' );

// Do xml tag via echo or SVN parser is going to freak out.
echo '<?xml version="1.0" encoding="' . esc_xml( esc_attr( get_bloginfo( 'charset' ) ) ) . '"?>
'; ?>
<?php xmlsf_xml_stylesheet( 'news' ); ?>
<?php do_action( 'xmlsf_generator' ); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" <?php do_action( 'xmlsf_urlset', 'news' ); ?>>
<?php
global $wp_query, $post;
// Loop away!
if ( have_posts() ) :
	$wp_query->in_the_loop = true;
	while ( have_posts() ) :
		// Not using the_post() to avoid expensive but useless setup_postdata().
		$post = $wp_query->next_post(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$url  = apply_filters( 'xmlsf_news_entry_url', get_permalink(), $post );

		// Use xmlsf_news_entry_url filter to return falsy value to exclude a specific URL.
		if ( empty( $url ) ) {
			continue;
		}

		// Or if post meta says "exclude me please".
		if ( apply_filters( 'xmlsf_news_excluded', get_post_meta( $post->ID, '_xmlsf_news_exclude', true ), $post->ID ) ) {
			continue;
		}

		$did_posts = true;

		do_action( 'xmlsf_news_url', $post );

		echo '<url><loc>' . esc_xml( esc_url( $url ) ) . '</loc>';

		// The news tags.
		echo '<news:news><news:publication><news:name>';
		echo esc_xml( apply_filters( 'xmlsf_news_publication_name', ( ! empty( $options['name'] ) ? $options['name'] : get_bloginfo( 'name' ) ) ) );
		echo '</news:name>';
		echo '<news:language>' . esc_xml( apply_filters( 'xmlsf_news_language', get_bloginfo( 'language' ), $post->ID, $post->post_type ) ) . '</news:language>';
		echo '</news:publication>';
		echo '<news:publication_date>' . esc_xml( get_date_from_gmt( $post->post_date_gmt, DATE_W3C ) ) . '</news:publication_date>';
		echo '<news:title>' . esc_xml( apply_filters( 'xmlsf_news_title', get_the_title() ) ) . '</news:title>';
		echo '<news:keywords>' . esc_xml( implode( ', ', (array) apply_filters( 'xmlsf_news_keywords', array(), $post->ID ) ) ) . '</news:keywords>';
		echo '<news:stock_tickers>' . esc_xml( implode( ', ', apply_filters( 'xmlsf_news_stock_tickers', array() ) ) ) . '</news:stock_tickers>';

		do_action( 'xmlsf_news_tags_inner', $post );

		echo '</news:news>';

		do_action( 'xmlsf_news_tags_after', $post );

		echo '</url>';

		do_action( 'xmlsf_news_url_after', $post );

		echo PHP_EOL;
	endwhile;
	$wp_query->in_the_loop = false;
endif;

if ( empty( $did_posts ) ) {
	// No posts done? Then do at least the homepage to prevent error message in Google Search Console.
	echo '<url><loc>' . esc_url( home_url() ) . '</loc></url>' . PHP_EOL;
}
?>
</urlset>
