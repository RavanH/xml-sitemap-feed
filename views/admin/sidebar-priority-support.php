<?php
/**
 * Sidebar: Help
 *
 * @package XML Sitemap & Google News
 */

?>
<h3><span class="dashicons dashicons-yes-alt" style="color:#2a8c41"></span> <?php esc_html_e( 'Priority support', 'xml-sitemap-feed' ); ?></h3>
<p>
	<?php
	printf(
		/* translators: %s: Advanced plugin name (linked to https://premium.status301.com/) */
		esc_html__( 'For priority support and advanced features, please refer to %s.', 'xml-sitemap-feed' ),
		'<a href="' . esc_url( $adv_plugin_url ) . '" target="_blank">' . esc_html( $adv_plugin_name ) . '</a>'
	);
	?>
</p>
