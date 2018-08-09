<?php
/**
 * Google News Sitemap Feed Template
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

if ( ! defined( 'WPINC' ) ) die;

// start output
echo xmlsf()->head();
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
		http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<?php
$terms = get_terms( get_query_var('taxonomy') );

if ( is_array($terms) ) :
    foreach ( $terms as $term ) :
	?>
	<url>
		<loc><?php echo get_term_link( $term ); ?></loc>
	 	<priority><?php echo xmlsf()->get_priority('taxonomy',$term); ?></priority>
		<?php echo xmlsf()->get_lastmod('taxonomy',$term); ?>
	</url>
<?php
    endforeach;
endif;

?></urlset>
<?php XMLSitemapFeed_Controller::_e_usage(); ?>
