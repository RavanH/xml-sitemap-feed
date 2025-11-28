<?php
/**
 * Sidebar: Help
 *
 * @package XML Sitemap & Google News
 */

?>
<h3><span class="dashicons dashicons-sos" style="color:#d63638"></span> <?php echo esc_html( translate( 'Help' ) ); // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction ?></h3>
<p>
	<?php
	printf(
		/* translators: %s: Help tab */
		esc_html__( 'For help on the plugin options, open the %s above.', 'xml-sitemap-feed' ),
		'<a href="#wpbody-content" onclick="javascript:jQuery(\'#contextual-help-link\').trigger(\'click\');">' . esc_html__( 'Help tab', 'xml-sitemap-feed' ) . '</a>'
	);
	?>
	<?php
	printf(
		/* translators: %s Knowledge Base (linked to https://premium.status301.com/knowledge-base/xml-sitemap-google-news/) */
		esc_html__( 'Documentation on various related subjects can be found in our %s.', 'xml-sitemap-feed' ),
		'<a href="https://premium.status301.com/knowledge-base/xml-sitemap-google-news/" target="_blank">' . esc_html__( 'Knowledge Base', 'xml-sitemap-feed' ) . '</a>'
	);
	?>
	<?php
	printf(
		/* translators: %s Support forum (linked to https://wordpress.org/support/plugin/xml-sitemap-feed) */
		esc_html__( 'If you still have questions, please refer to the %s.', 'xml-sitemap-feed' ),
		'<a href="https://wordpress.org/support/plugin/xml-sitemap-feed" target="_blank">' . esc_html__( 'Support forum', 'xml-sitemap-feed' ) . '</a>'
	);
	?>
</p>
