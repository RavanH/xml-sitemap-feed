<?php
/**
 * Google News Sitemap Feed Template
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

defined( 'WPINC' ) || die;

// Do xml prolog via echo or plugin repository SVN parser is going to freak out.
echo '<?xml version="1.0" encoding="' . esc_xml( esc_attr( get_bloginfo( 'charset' ) ) ) . '"?>
'; ?>
<?php xmlsf_xml_stylesheet( 'taxonomy' ); ?>
<?php do_action( 'xmlsf_generator' ); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" <?php do_action( 'xmlsf_urlset', 'taxonomy' ); ?>>
<?php
$terms = get_terms( array( 'taxonomy' => get_query_var( 'taxonomy' ) ) );

if ( is_array( $terms ) ) :
	foreach ( $terms as $tax_term ) :
		$url = get_term_link( $tax_term );
		// Check if we are dealing with an external URL. This can happen with multi-language plugins where each language has its own domain.
		if ( ! xmlsf_is_allowed_domain( $url ) ) {
			continue;
		}
		echo '<url><loc>' . esc_url( $url ) . '</loc><priority>' . esc_xml( xmlsf_get_term_priority( $tax_term ) ) . '</priority>';
		$lastmod = xmlsf_get_term_modified( $tax_term );
		if ( $lastmod ) {
			echo '<lastmod>' . esc_xml( $lastmod ) . '</lastmod>';
		}
		do_action( 'xmlsf_tags_after', 'taxonomy' );
		echo '</url>';
		do_action( 'xmlsf_url_after', 'taxonomy' );
		echo PHP_EOL;
	endforeach;
endif;
?>
</urlset>
