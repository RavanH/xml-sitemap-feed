<?php
/**
 * XML Sitemap Feed Template for displaying an XML Sitemap feed.
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

defined( 'WPINC' ) || die;

// Do xml tag via echo or SVN parser is going to freak out.
echo '<?xml version="1.0" encoding="' . esc_xml( esc_attr( get_bloginfo( 'charset' ) ) ) . '"?>' . PHP_EOL;
echo '<?xml-stylesheet type="text/xsl" href="' . \esc_url( \wp_make_link_relative( XMLSF\get_stylesheet_url( 'custom' ) ) ) . '?ver=' . \esc_xml( XMLSF_VERSION ) . '"?>' . PHP_EOL;
?>
<?php do_action( 'xmlsf_generator' ); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" <?php do_action( 'xmlsf_urlset', 'custom' ); ?>>
<?php

// Get our custom urls array.
$custom_urls = apply_filters( 'xmlsf_custom_urls', get_option( 'xmlsf_urls' ) );
if ( is_array( $custom_urls ) ) :
	// and loop away!
	foreach ( $custom_urls as $data ) {
		if ( empty( $data[0] ) ) {
			continue;
		}

		do_action( 'xmlsf_url', 'custom', $data );

		echo '<url><loc>' . esc_url( $data[0] ) . '</loc>';

		if ( ! empty( $data[1] ) && is_numeric( $data[1] ) ) {
			echo '<priority>' . esc_xml( $data[1] ) . '</priority>';
		}

		do_action( 'xmlsf_tags_after', 'custom', $data );

		echo '</url>';

		do_action( 'xmlsf_url_after', 'custom', $data );

		echo PHP_EOL;
	}
endif;
?>
</urlset>
