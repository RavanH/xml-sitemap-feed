# XML Sitemap & Google News version <?php echo XMLSF_VERSION ?> - https://status301.net/wordpress-plugins/xml-sitemap-feed/
<?php switch ( $case ) {
	case 'private' : ?>
# XML Sitemaps are disabled because of this site's privacy settings.

<?php break;
	case 'disabled' : ?>
# No XML Sitemaps are enabled on this site.

<?php break;
	default :
		foreach ( $sitemaps as $pretty ) { ?>
Sitemap: <?php echo $url . $pretty; ?>

<?php 	} ?>

<?php break;
}
?>
