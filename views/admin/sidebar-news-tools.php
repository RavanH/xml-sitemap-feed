<?php
/**
 * Sidebar: News Tools
 *
 * @package XML Sitemap & Google News
 */

?>
<h3><span class="dashicons dashicons-admin-tools"></span> <?php echo esc_html( translate( 'Tools' ) ); // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction ?></h3>
<form action="" method="post">
	<?php wp_nonce_field( XMLSF_BASENAME . '-help', '_xmlsf_help_nonce' ); ?>
	<p>
		<input type="submit" name="xmlsf-flush-rewrite-rules" class="button button-small" value="<?php esc_html_e( 'Flush rewrite rules', 'xml-sitemap-feed' ); ?>" />
	</p>
	<p>
		<input type="submit" name="xmlsf-check-conflicts" class="button button-small" value="<?php esc_html_e( 'Check for conflicts', 'xml-sitemap-feed' ); ?>" />
	</p>
	<p>
		<input type="submit" name="xmlsf-clear-settings" class="button button-small button-link-delete" value="<?php esc_attr_e( 'Reset settings', 'xml-sitemap-feed' ); ?>" onclick="javascript:return confirm( '<?php echo esc_js( __( 'You are about to RESET ALL sitemap settings to the plugin defaults.', 'xml-sitemap-feed' ) ); ?>\n\n<?php echo esc_js( translate( 'Are you sure you want to do this?' ) ); // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction ?>' )" />
	</p>
</form>
