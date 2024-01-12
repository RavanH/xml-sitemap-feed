<?php
/**
 * XML Sitemap Feed Template for displaying an XML Sitemap feed.
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

defined( 'WPINC' ) || die;

// do xml tag via echo or SVN parser is going to freak out.
echo '<?xml version="1.0" encoding="' . esc_xml( esc_attr( get_bloginfo( 'charset' ) ) ) . '"?>
'; ?>
<?php xmlsf_xml_stylesheet( 'author' ); ?>
<?php do_action( 'xmlsf_generator' ); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" <?php do_action( 'xmlsf_urlset', 'home' ); ?>>
<?php
$users = get_users(
	apply_filters(
		'xmlsf_get_author_args',
		array(
			'orderby'             => 'post_count',
			'order'               => 'DESC',
			'number'              => '1000',
			'fields'              => array( 'ID', 'user_login', 'spam', 'deleted' ),
			'has_published_posts' => true, // Means all post types by default.
		)
	)
);
foreach ( $users as $user ) {
	$url = get_author_posts_url( $user->ID );

	// Check if we are dealing with an external URL. This can happen with multi-language plugins where each language has its own domain.
	if ( ! xmlsf_is_allowed_domain( $url ) ) {
		continue;
	}

	// Allow filtering of users.
	if ( apply_filters( 'xmlsf_skip_user', false, $user ) ) {
		continue;
	}

	echo '<url><loc>' . esc_xml( esc_url( $url ) ) . '</loc><priority>' . esc_xml( xmlsf_get_user_priority( $user ) ) . '</priority>';
	$lastmod = xmlsf_get_user_modified( $user );
	if ( $lastmod ) {
		echo '<lastmod>' . esc_xml( $lastmod ) . '</lastmod>';
	}
	do_action( 'xmlsf_tags_after', 'author' );
	echo '</url>';
	do_action( 'xmlsf_url_after', 'author' );
	echo PHP_EOL;
}
?>
</urlset>
