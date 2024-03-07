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
</p>
<p>
	<?php esc_html_e( 'The Publication name must exactly match the name as it appears on your articles on news.google.com, omitting anything in parentheses.', 'xml-sitemap-feed' ); ?>
	<?php
	printf( /* translators: Google News Publisher Center (linked to https://publishercenter.google.com/) */
		esc_html__( 'If you wish to change your publication name on news.google.com, please go to %s and use the button "Request to change".', 'xml-sitemap-feed' ),
		'<a href="https://publishercenter.google.com/" target="_blank">' . esc_html__( 'Google News Publisher Center', 'xml-sitemap-feed' ) . '</a>'
	);
	?>
</p>
<?php
if ( ! \is_multisite() ) {
	echo '<p>';
	/* translators: folowed by an example code snippet */
	esc_html_e( 'To prevent any changes to the Publication name field you can set the XMLSF_GOOGLE_NEWS_NAME constant in your site\'s wp-config.php:', 'xml-sitemap-feed' );
	echo '<br><code>define( \'XMLSF_GOOGLE_NEWS_NAME\', \'Your Publication Name\' );</code>';
	echo '</p>';
}
?>
