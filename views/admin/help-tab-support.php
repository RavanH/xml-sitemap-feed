<?php
/**
 * Help tab view, support links
 *
 * @package XML Sitemap & Google News
 */

?>
<hr />
<p class="description">
	<?php
	printf(
		/* translators: Plugin name, Support linked to forum on WordPress.org */
		esc_html__( 'These options are provided by %1$s. For help, please go to %2$s.', 'xml-sitemap-feed' ),
		'<strong>' . esc_html__( 'XML Sitemap & Google News', 'xml-sitemap-feed' ) . '</strong>',
		'<a href="https://wordpress.org/support/plugin/xml-sitemap-feed" target="_blank">' . esc_html__( 'Support', 'xml-sitemap-feed' ) . '</a>'
	);
	?>
	<?php
	printf(
		/* translators: Review page and Translation page on WordPress.org */
		esc_html__( 'If you would like to contribute and share with the rest of the WordPress community, please consider writing a quick %1$s or help out with %2$s!', 'xml-sitemap-feed' ),
		'<a href="https://wordpress.org/support/plugin/xml-sitemap-feed/reviews/?filter=5#new-post" target="_blank">' . esc_html__( 'Review', 'xml-sitemap-feed' ) . '</a>',
		'<a href="https://translate.wordpress.org/projects/wp-plugins/xml-sitemap-feed" target="_blank">' . esc_html__( 'Translating', 'xml-sitemap-feed' ) . '</a>'
	);
	?>
	<?php
	printf(
		/* translators: Github project page */
		esc_html__( 'For feature requests, reporting issues or contributing code, you can find and fork this plugin on %s.', 'xml-sitemap-feed' ),
		'<a href="https://github.com/RavanH/xml-sitemap-feed" target="_blank">' . esc_html__( 'Github', 'xml-sitemap-feed' ) . '</a>'
	);
	?>
</p>
