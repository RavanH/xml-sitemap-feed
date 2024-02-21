<?php
/**
 * XML Sitemap Index Feed Template
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

defined( 'WPINC' ) || die;

// Do xml tag via echo or SVN parser is going to freak out.
echo '<?xml version="1.0" encoding="' . esc_xml( esc_attr( get_bloginfo( 'charset' ) ) ) . '"?>'; ?>
<?php xmlsf_xml_stylesheet(); ?>
<?php do_action( 'xmlsf_generator' ); ?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<sitemap><loc><?php echo esc_xml( xmlsf_sitemap_url( 'root' ) ); ?></loc><lastmod><?php echo esc_xml( get_date_from_gmt( get_lastpostdate( 'GMT' ), DATE_W3C ) ); ?></lastmod></sitemap>
<?php
do_action( 'xmlsf_sitemap_index' );

$disabled = get_option( 'xmlsf_disabled_providers', xmlsf()->defaults( 'disabled_providers' ) );

// Public post types.
$post_types = xmlsf_get_post_types();
foreach ( $post_types as $the_post_type => $settings ) :
	$archive      = isset( $settings['archive'] ) ? $settings['archive'] : '';
	$archive_data = apply_filters( 'xmlsf_index_archive_data', array(), $the_post_type, $archive );

	foreach ( $archive_data as $url => $lastmod ) {
		echo '<sitemap><loc>' . esc_url( $url ) . '</loc>';
		if ( $lastmod ) {
			echo '<lastmod>' . esc_xml( $lastmod ) . '</lastmod>';
		}
		echo '</sitemap>' . PHP_EOL;
	}
endforeach;

// Public taxonomies.
if ( empty( $disabled ) || ! in_array( 'taxonomies', (array) $disabled, true ) ) {
	$taxonomies = xmlsf_get_taxonomies();
	foreach ( $taxonomies as $the_taxonomy ) :
		$url     = xmlsf_sitemap_url( 'taxonomy', array( 'type' => $the_taxonomy ) );
		$lastmod = xmlsf_get_taxonomy_modified( $the_taxonomy );
		echo '<sitemap><loc>' . esc_xml( $url ) . '</loc>';
		if ( $lastmod ) {
			echo '<lastmod>' . esc_xml( $lastmod ) . '</lastmod>';
		}
		echo '</sitemap>' . PHP_EOL;
	endforeach;
}

// Authors.
if ( empty( $disabled ) || ! in_array( 'users', (array) $disabled, true ) ) {
	echo '<sitemap><loc>' . esc_xml( xmlsf_sitemap_url( 'author' ) ) . '</loc><lastmod>' . esc_xml( get_date_from_gmt( get_lastpostdate( 'GMT' ), DATE_W3C ) ) . '</lastmod></sitemap>' . PHP_EOL;
}

// Custom URLs sitemap.
if ( apply_filters( 'xmlsf_custom_urls', get_option( 'xmlsf_urls' ) ) ) {
	echo '<sitemap><loc>' . esc_xml( xmlsf_sitemap_url( 'custom' ) ) . '</loc></sitemap>' . PHP_EOL;
}

// Custom sitemaps.
$custom_sitemaps = apply_filters( 'xmlsf_custom_sitemaps', get_option( 'xmlsf_custom_sitemaps', array() ) );
if ( is_array( $custom_sitemaps ) ) :
	foreach ( $custom_sitemaps as $url ) {
		if ( empty( $url ) ) {
			continue;
		}
		echo '<sitemap><loc>' . esc_url( $url ) . '</loc></sitemap>' . PHP_EOL;
	}
endif;

do_action( 'xmlsf_sitemap_index_after' );
?>
</sitemapindex>
