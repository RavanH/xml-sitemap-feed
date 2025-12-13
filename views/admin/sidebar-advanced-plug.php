<?php
/**
 * Sidebar: Help
 *
 * @package XML Sitemap & Google News
 */

?>
<div style="border: 4px solid #2a8c41; padding: 0 10px; background-color:lemonchiffon">
	<h3><span class="dashicons dashicons-yes-alt" style="color:#2a8c41"></span> <?php esc_html_e( 'Sitemap notifier	', 'xml-sitemap-feed' ); ?></h3>
	<p>
		<strong>
			<?php esc_html_e( 'Instantly notify search engines by automatically resubmitting your sitemap index upon each new publication.', 'xml-sitemap-feed' ); ?>
			<?php
			printf(
				/* translators: %s: Advanced plugin name (linked to https://premium.status301.com/) */
				esc_html__( 'Available in %s.', 'xml-sitemap-feed' ),
				'<a href="https://premium.status301.com/downloads/xml-sitemap-advanced/" target="_blank">' . __( 'XML Sitemap Advanced', 'xml-sitemap-feed' ) . '</a>'
			);
			?>
		</strong>
	</p>
</div>