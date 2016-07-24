<?php
/**
 * XML Sitemap Index Feed Template
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

if ( ! defined( 'WPINC' ) ) die;

global $xmlsf;

// start output
echo $xmlsf->headers('index');
?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
		http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd">
	<sitemap>
		<loc><?php echo $xmlsf->get_index_url('home'); ?></loc>
		<lastmod><?php echo mysql2date('Y-m-d\TH:i:s+00:00', get_lastdate( 'gmt' ), false); ?></lastmod>
	</sitemap>
<?php
// add rules for public post types
foreach ( $xmlsf->have_post_types() as $post_type ) {
	$archive = isset($post_type['archive']) ? $post_type['archive'] : '';

	foreach ( $xmlsf->get_archives($post_type['name'],$archive) as $m => $url ) {
?>
	<sitemap>
		<loc><?php echo $url; ?></loc>
		<lastmod><?php echo mysql2date('Y-m-d\TH:i:s+00:00', get_lastmodified( 'gmt', $post_type['name'], $m ), false); ?></lastmod>
	</sitemap>
<?php
	}
}

// add rules for public taxonomies
foreach ( $xmlsf->get_taxonomies() as $taxonomy ) {
	if ( wp_count_terms( $taxonomy, array('hide_empty'=>true) ) > 0 ) {
?>
	<sitemap>
		<loc><?php echo $xmlsf->get_index_url('taxonomy',$taxonomy); ?></loc>
	<?php echo $xmlsf->get_lastmod('taxonomy',$taxonomy); ?></sitemap>
<?php
	}
}

// custom URLs sitemap
$urls = $xmlsf->get_urls();
if ( !empty($urls) ) {
?>
	<sitemap>
		<loc><?php echo $xmlsf->get_index_url('custom'); ?></loc>
	</sitemap>
<?php
}

// custom sitemaps
$custom_sitemaps = $xmlsf->get_custom_sitemaps();
foreach ($custom_sitemaps as $url) {
	if (empty($url) || !$xmlsf->is_allowed_domain($url))
		continue;
?>
	<sitemap>
		<loc><?php echo esc_url($url); ?></loc>
	</sitemap>
<?php
}

?></sitemapindex>
<?php $xmlsf->_e_usage();
