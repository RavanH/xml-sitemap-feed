<?php
/**
 * Sidebar: Help
 *
 * @package XML Sitemap & Google News
 */

?>
<h3><span class="dashicons dashicons-sos"></span> <?php echo esc_html( translate( 'Help' ) ); ?></h3>
<p>
	<?php
	printf(
		/* translators: %1$s Help tab, %2$s Support forum (linked to https://wordpress.org/support/plugin/xml-sitemap-feed) */
		esc_html__( 'You can find instructions on the %1$s above. If you still have questions, please go to the %2$s.', 'xml-sitemap-feed' ),
		'<strong>' . esc_html__( 'Help tab', 'xml-sitemap-feed' ) . '</strong>',
		'<a href="https://wordpress.org/support/plugin/xml-sitemap-feed" target="_blank">' . esc_html__( 'Support forum', 'xml-sitemap-feed' ) . '</a>'
	);
	?>
</p>
