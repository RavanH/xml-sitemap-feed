<?php
/**
 * XML Sitemap Feed Template for displaying an XML Sitemap feed.
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

if ( ! defined( 'WPINC' ) ) die;

// do xml tag via echo or SVN parser is going to freak out
echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . '"?>
'; ?>
<?php xmlsf_xml_stylesheet( 'author' ); ?>
<?php do_action( 'xmlsf_generator' ); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
<?php do_action( 'xmlsf_urlset', 'home' ); ?>
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
		http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<?php
$users = get_users( apply_filters( 'get_users_args', array() ) );
foreach ( $users as $user ) {
	$url = get_author_posts_url( $user->ID );

	// Check if we are dealing with an external URL. This can happen with multi-language plugins where each language has its own domain.
	if ( ! xmlsf_is_allowed_domain( $url ) ) continue;

	// allow filtering of users
	if ( apply_filters( 'xmlsf_skip_user', false, $user ) ) continue;
?>
	<url>
		<loc><?php echo esc_url( $url ); ?></loc>
		<priority><?php echo xmlsf_get_user_priority( $user ); ?></priority>
<?php if ( $lastmod = xmlsf_get_user_modified( $user ) ) { ?>
		<lastmod><?php echo $lastmod; ?></lastmod>
<?php }
	do_action( 'xmlsf_tags_after', 'author' );
?>
	</url>
<?php
	do_action( 'xmlsf_url_after', 'author' );
}
?>
</urlset>
<?php xmlsf_usage(); ?>
