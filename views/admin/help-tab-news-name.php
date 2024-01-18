<?php
/**
 * Help tab: News publication name
 *
 * @package XML Sitemap & Google News
 */

?>
<p>
	<?php printf( /* translators: Site Title linked to Options > General */ esc_html__( 'By default, the general %s setting will be used.', 'xml-sitemap-feed' ), '<a href="options-general.php">' . esc_html__( 'Site Title' ) . '</a>' ); ?>
	<?php esc_html_e( 'The publication name should match the name submitted on the Google News Publisher Center. If you wish to change it, please read <a href="https://support.google.com/news/publisher/answer/40402" target="_blank">Updated publication name</a>.', 'xml-sitemap-feed' ); ?>
</p>
