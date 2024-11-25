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
<?php XMLSF\xml_stylesheet( 'root' ); ?>
<?php do_action( 'xmlsf_generator' ); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" <?php do_action( 'xmlsf_urlset', 'home' ); ?>>
<?php
$settings    = (array) get_option( 'xmlsf_post_types' );
$do_priority = ! empty( $settings['page']['priority'] );

foreach ( XMLSF\get_root_data() as $url => $data ) {
	$url = apply_filters( 'xmlsf_entry_url', $url, 'home' );

	// Use xmlsf_entry_url filter to return falsy value to exclude a specific URL.
	if ( empty( $url ) ) {
		continue;
	}

	do_action( 'xmlsf_url', 'home', $data );

	echo '<url><loc>' . esc_url( $url ) . '</loc>';

	if ( $do_priority ) {
		echo '<priority>1.0</priority>';
	}

	if ( $data['lastmod'] ) {
		echo '<lastmod>' . esc_xml( $data['lastmod'] ) . '</lastmod>';
	}

	do_action( 'xmlsf_tags_after', 'home', $data );

	echo '</url>';

	do_action( 'xmlsf_url_after', 'home', $data );

	echo PHP_EOL;
}
?>
</urlset>
