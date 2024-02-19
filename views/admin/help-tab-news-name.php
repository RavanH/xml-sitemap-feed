<?php
/**
 * Help tab: News publication name
 *
 * @package XML Sitemap & Google News
 */

?>
<p>
	<?php
	printf( /* translators: Site Title linked to Options > General */
		esc_html__( 'By default, the general %s setting will be used.', 'xml-sitemap-feed' ),
		'<a href="options-general.php">' . esc_html( translate( 'Site Title' ) ) . '</a>'
	);
	?>
	<?php
	printf( /* translators:  Google News Publisher Center (linked to https://publishercenter.google.com/) */
		esc_html__( 'The publication name should match the name submitted on the Google News Publisher Center. If you wish to change it, please go to %s and use the button "Request to change".', 'xml-sitemap-feed' ),
		'<a href="https://publishercenter.google.com/" target="_blank">' . esc_html__( 'Google News Publisher Center', 'xml-sitemap-feed' ) . '</a>'
	);
	?>
</p>
