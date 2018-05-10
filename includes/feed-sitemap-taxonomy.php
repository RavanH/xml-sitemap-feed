<?php
/**
 * Google News Sitemap Feed Template
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

if ( ! defined( 'WPINC' ) ) die;

global $xmlsf;

// start output
echo $xmlsf->head();
?>
<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:xsi="https://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="https://www.sitemaps.org/schemas/sitemap/0.9
		https://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
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
	</url>
<?php
    endforeach;
endif;

?></urlset>
<?php $xmlsf->_e_usage();
