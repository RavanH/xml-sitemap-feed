<?php
/**
 * Admin notice: Jetpack sitemap
 *
 * @package XML Sitemap & Google News
 */

?>
<div class="notice notice-warning fade is-dismissible">
	<p>
		<?php
		printf( /* translators: Conflicting Plugn name, Plugin name */
			esc_html__( 'The %1$s XML Sitemap is not compatible with %2$s.', 'xml-sitemap-feed' ),
			esc_html__( 'XML Sitemaps Manager', 'xml-sitemaps-manager' ),
			esc_html__( 'XML Sitemap & Google News', 'xml-sitemap-feed' )
		);
		?>
		<?php
		printf( /* translators: XML Sitemaps Manager plugin and XML Sitemap & Google News plugin names (both linked to Active Plugins admin page) */
			esc_html__( 'Please either disable %1$s or %2$s.', 'xml-sitemap-feed' ),
			'<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '?plugin_status=active">' . esc_html__( 'XML Sitemaps Manager', 'xml-sitemaps-manager' ) . '</a>',
			'<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '?plugin_status=active">' . esc_html__( 'XML Sitemap & Google News', 'xml-sitemap-feed' ) . '</a>'
		);
		?>
	</p>
	<form action="" method="post">
		<?php wp_nonce_field( XMLSF_BASENAME . '-notice', '_xmlsf_notice_nonce' ); ?>
		<p>
			<input type="hidden" name="xmlsf-dismiss" value="xml_sitemaps_manager" />
			<input type="submit" class="button button-small" name="xmlsf-dismiss-submit" value="<?php echo esc_attr( translate( 'Dismiss' ) ); ?>" />
		</p>
	</form>
</div>
