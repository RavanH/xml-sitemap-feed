<?php
/**
 * XML Sitemap Feed Template for displaying an XML Sitemap feed.
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

defined( 'WPINC' ) || die;

// Do xml tag via echo or SVN parser is going to freak out.
echo '<?xml version="1.0" encoding="' . esc_xml( esc_attr( get_bloginfo( 'charset' ) ) ) . '"?>
'; ?>
<?php xmlsf_xml_stylesheet( 'root' ); ?>
<?php do_action( 'xmlsf_generator' ); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" <?php do_action( 'xmlsf_urlset', 'home' ); ?>>
<?php
foreach ( xmlsf_get_root_data() as $url => $data ) {
	echo '<url><loc>' . esc_url( $url ) . '</loc><priority>' . esc_xml( $data['priority'] ) . '</priority><lastmod>' . esc_xml( $data['lastmod'] ) . '</lastmod>';
	do_action( 'xmlsf_tags_after', 'home' );
	echo '</url>';
	do_action( 'xmlsf_url_after', 'home' );
	echo PHP_EOL;
}
?>
</urlset>
