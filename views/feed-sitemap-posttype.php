<?php
/**
 * XML Sitemap Feed Template for displaying an XML Sitemap feed.
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

defined( 'WPINC' ) || die;

xmlsf()->sitemap->prefetch_posts_meta();

// Do xml tag via echo or SVN parser is going to freak out.
echo '<?xml version="1.0" encoding="' . esc_xml( esc_attr( get_bloginfo( 'charset' ) ) ) . '"?>
'; ?>
<?php XMLSF\xml_stylesheet( 'posttype' ); ?>
<?php do_action( 'xmlsf_generator' ); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" <?php do_action( 'xmlsf_urlset', 'post_type' ); ?>>
<?php
global $wp_query, $post;

if ( have_posts() ) :
	/**
	 * Add a URL for the homepage in the pages sitemap.
	 * Shows only on the first page if the reading settings are set to display latest posts.
	 */
	if ( 'page' === $post->post_type && 'posts' === get_option( 'show_on_front' ) ) {
		$home_pages = array(
			\trailingslashit( \home_url() ) => array(
				'lastmod' => get_lastpostdate( 'gmt', 'post' ),
			),
		);

		/**
		 * Developers
		 *
		 * Modify the root data array with: add_filter( 'xmlsf_root_data', 'your_filter_function' );
		 *
		 * Possible filters hooked here:
		 * XMLSF\Compat/Polylang->root_data - Polylang compatibility
		 * XMLSF\Compat\WPML->root_data - WPML compatibility
		 */
		$home_pages = \apply_filters( 'xmlsf_root_data', $home_pages );

		foreach ( $home_pages as $url => $data ) {
			$url = apply_filters( 'xmlsf_entry_url', $url, 'home' );

			// Use xmlsf_entry_url filter to return falsy value to exclude a specific URL.
			if ( empty( $url ) ) {
				continue;
			}

			do_action( 'xmlsf_url', 'home', $data );

			echo '<url><loc>' . esc_url( $url ) . '</loc>';

			$priority = XMLSF\get_home_priority();
			if ( ! empty( $priority ) ) {
				echo '<priority>' . esc_xml( $priority ) . '</priority>';
			}

			if ( ! empty( $data['lastmod'] ) ) {
				echo '<lastmod>' . esc_xml( get_date_from_gmt( $data['lastmod'], DATE_W3C ) ) . '</lastmod>';
			}

			do_action( 'xmlsf_tags_after', 'home', $data );

			echo '</url>';

			do_action( 'xmlsf_url_after', 'home', $data );

			echo PHP_EOL;
		}
	}

	// Loop away!
	$wp_query->in_the_loop = true;

	while ( have_posts() ) :
		// Don't do the_post() here to avoid expensive setup_postdata(), just do the following.
		$post = $wp_query->next_post(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$url  = apply_filters( 'xmlsf_entry_url', get_permalink(), 'post_type', $post );

		// Use xmlsf_entry_url filter to return falsy value to exclude a specific URL.
		if ( empty( $url ) ) {
			continue;
		}

		// Or if post meta says "exclude me please".
		if ( apply_filters( 'xmlsf_excluded', get_post_meta( $post->ID, '_xmlsf_exclude', true ), $post->ID ) ) {
			continue;
		}

		$did_posts = true;

		do_action( 'xmlsf_url', 'post_type', $post );

		echo '<url>';
		echo '<loc>' . esc_xml( esc_url( $url ) ) . '</loc>';

		$priority = XMLSF\get_post_priority( $post );
		if ( $priority ) {
			echo '<priority>' . esc_xml( $priority ) . '</priority>';
		}

		$lastmod = XMLSF\get_post_modified( $post );
		if ( $lastmod ) {
			echo '<lastmod>' . esc_xml( get_date_from_gmt( $lastmod, DATE_W3C ) ) . '</lastmod>';
		}

		do_action( 'xmlsf_tags_after', 'post_type', $post );

		echo '</url>';

		do_action( 'xmlsf_url_after', 'post_type', $post );

		echo PHP_EOL;
	endwhile;

	$wp_query->in_the_loop = false;

endif;

if ( empty( $did_posts ) ) {
	// No posts done? Then do at least the homepage to prevent error message in GSC.
	echo '<url><loc>' . esc_url( home_url() ) . '</loc><priority>1.0</priority></url>' . PHP_EOL;
}
?>
</urlset>
