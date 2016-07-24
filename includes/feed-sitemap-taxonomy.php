<?php
/**
 * Google News Sitemap Feed Template
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

if ( ! defined( 'WPINC' ) ) die;

global $xmlsf;

// start output
echo $xmlsf->headers();
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
		http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<?php
$taxonomy = get_query_var('taxonomy');

$terms = get_terms( $taxonomy, array(
					'orderby' => 'count',
					'order' => 'DESC',
					'lang' => '',
					'hierachical' => 0,
					'pad_counts' => true, // count child term post count too...
					'number' => 50000 ) );

if ( $terms ) :
    foreach ( $terms as $term ) :
	?>
	<url>
		<loc><?php echo get_term_link( $term ); ?></loc>
	 	<priority><?php echo $xmlsf->get_priority('taxonomy',$term); ?></priority>
		<?php echo $xmlsf->get_lastmod('taxonomy',$term); ?>
		<changefreq><?php echo $xmlsf->get_changefreq('taxonomy',$term); ?></changefreq>
	</url>
<?php
    endforeach;
endif;

?></urlset>
<?php $xmlsf->_e_usage();
