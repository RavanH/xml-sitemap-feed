<?php
/**
 * Google News Sitemap Feed Template
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

if ( ! defined( 'WPINC' ) ) die;

// do xml prolog via echo or plugin repository SVN parser is going to freak out
echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . '"?>
'; ?>
<?php xmlsf_xml_stylesheet( 'taxonomy' ); ?>
<?php do_action( 'xmlsf_generator' ); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" <?php do_action( 'xmlsf_urlset', 'taxonomy' ); ?>>
<?php
$terms = get_terms( array('taxonomy'=>get_query_var('taxonomy')) );

if ( is_array($terms) ) :
    foreach ( $terms as $term ) :
		$url = get_term_link( $term );
		// Check if we are dealing with an external URL. This can happen with multi-language plugins where each language has its own domain.
		if ( ! xmlsf_is_allowed_domain( $url ) ) continue;
		?>
	<url>
		<loc><?php echo esc_url( $url ); ?></loc>
	 	<priority><?php echo htmlspecialchars( xmlsf_get_term_priority( $term ), ENT_COMPAT, get_bloginfo('charset') ); ?></priority>
<?php if ( $lastmod = xmlsf_get_term_modified( $term ) ) { ?>
		<lastmod><?php echo htmlspecialchars( $lastmod, ENT_COMPAT, get_bloginfo('charset') ); ?></lastmod>
<?php }
 		do_action( 'xmlsf_tags_after', 'taxonomy' );
?>
	</url>
<?php
		do_action( 'xmlsf_url_after', 'taxonomy' );
	endforeach;
endif;

?></urlset>
<?php xmlsf_usage(); ?>
