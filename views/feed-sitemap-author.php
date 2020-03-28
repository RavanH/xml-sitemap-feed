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
<?php xmlsf_generator(); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
<?php do_action('xmlsf_urlset', 'home'); ?>
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
		http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<?php
foreach ( xmlsf_get_author_data() as $url => $data ) {
?>
	<url>
		<loc><?php echo esc_url( $url ); ?></loc>
<?php if ( ! empty( $data['priority'] ) ) { ?>
		<priority><?php echo $data['priority']; ?></priority>
<?php } ?>
<?php if ( ! empty( $data['lastmod'] ) ) { ?>
		<lastmod><?php echo $data['lastmod']; ?></lastmod>
<?php } ?>
<?php do_action( 'xmlsf_tags_after', 'author' ); ?>
	</url>
<?php
	do_action( 'xmlsf_url_after', 'author' );
}
?>
</urlset>
<?php xmlsf_usage(); ?>
