<?php
/**
 * Sidebar: GSC Connect
 *
 * @package XML Sitemap & Google News
 */

?>
<h3>
	<span class="dashicons dashicons-google"></span>
	<?php esc_html_e( 'Google Search Console', 'xml-sitemap-feed' ); ?>
</h3>
<?php
$gsc_options = (array) get_option( 'xmlsf_gsc_connect', array() );
if ( empty( $gsc_options['google_refresh_token'] ) ) {
	// Initiate button.
	?>
	<p>
		<?php esc_html_e( 'Connect to Google Search Console to get a sitemap report in your site admin.', 'xml-sitemap-feed' ); ?>
	</p>
	<a href="<?php echo esc_url( $settings_page_url ); ?>" class="button button-primary">
		<?php esc_html_e( 'Connect', 'xml-sitemap-feed' ); ?>
	</a>
	<?php
	return;
}

// Submit and Disconnect buttons.
?>
<form action="" method="post">
	<?php wp_nonce_field( XMLSF_BASENAME . '-gsc', '_xmlsf_gsc_nonce' ); ?>
	<p>
		<?php submit_button( sprintf( /* translators: %s: Google News Sitemap or XML Sitemap Index depending on admin page */ __( 'Submit your %s now', 'xml-sitemap-feed' ), $sitemap_desc ), 'primary', 'xmlsf_gsc_manual_submit', false ); ?>
	</p>
	<p>
		<?php esc_html_e( 'Your site is connected to Google Search Console. You can disconnect and reconnect if you encounter submission errors or wish to reset or transfer site connection ownership.', 'xml-sitemap-feed' ); ?>
	</p>
	<p>
		<input type="submit" name="xmlsf_gsc_disconnect" class="button button-small button-link-delete" value="<?php esc_attr_e( 'Disconnect', 'xml-sitemap-feed' ); ?>" onclick="javascript:return confirm( '<?php echo esc_js( __( 'You are about to DISCONNECT this site from Google Search Console.', 'xml-sitemap-feed' ) ); ?>\n\n<?php echo esc_js( translate( 'Are you sure you want to do this?' ) ); // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction ?>' )" />
	</p>
</form>
