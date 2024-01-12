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
<?php xmlsf_xml_stylesheet( 'custom' ); ?>
<?php do_action( 'xmlsf_generator' ); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" <?php do_action( 'xmlsf_urlset', 'custom' ); ?>>
<?php

// Get our custom urls array.
$custom_urls = apply_filters( 'xmlsf_custom_urls', get_option( 'xmlsf_urls' ) );
if ( is_array( $custom_urls ) ) :
	// and loop away!
	foreach ( $custom_urls as $url ) {
		if ( empty( $url[0] ) ) {
			continue;
		}

		echo '<url><loc>' . esc_url( $url[0] ) . '</loc><priority>';
		echo ( isset( $url[1] ) && is_numeric( $url[1] ) ) ? esc_xml( $url[1] ) : '0.5';
		echo '</priority>';
		do_action( 'xmlsf_tags_after', 'custom' );
		echo '</url>';
		do_action( 'xmlsf_url_after', 'custom' );
		echo PHP_EOL;
	}
endif;
?>
</urlset>
