<?php
/**
 * Google News Sitemap Feed Template
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

if ( ! defined( 'WPINC' ) ) die;

?>
<?xml version="1.0" encoding="<?php echo get_bloginfo('charset'); ?>"?>
<?xml-stylesheet type="text/xsl" href="<?php echo plugins_url('views/styles/sitemap-taxonomy.xsl',XMLSF_BASENAME); ?>?ver=<?php echo XMLSF_VERSION; ?>"?>
<?php xmlsf_get_generator(); ?>
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
	 	<?php xmlsf_the_priority('taxonomy',$term); ?>
		<?php xmlsf_the_lastmod('taxonomy',$term); ?>
	</url>
<?php
    endforeach;
endif;

?></urlset>
<?php xmlsf_get_usage(); ?>
