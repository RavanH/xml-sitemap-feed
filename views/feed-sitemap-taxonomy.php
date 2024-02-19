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
		$url = apply_filters( 'xmlsf_entry_url', get_term_link( $tax_term ), 'taxonomy', $tax_term );

		// Use xmlsf_entry_url filter to return falsy value to exclude a specific URL.
		if ( empty( $url ) ) {
			continue;
		}

		do_action( 'xmlsf_url', 'taxonomy', $tax_term );

		echo '<url><loc>' . esc_xml( $url ) . '</loc><priority>' . esc_xml( xmlsf_get_term_priority( $tax_term ) ) . '</priority>';
		$lastmod = xmlsf_get_term_modified( $tax_term );
		if ( $lastmod ) {
			echo '<lastmod>' . esc_xml( $lastmod ) . '</lastmod>';
		}

		do_action( 'xmlsf_tags_after', 'taxonomy', $tax_term );

		echo '</url>';

		do_action( 'xmlsf_url_after', 'taxonomy', $tax_term );

		echo PHP_EOL;
	endforeach;
endif;
?>
</urlset>
