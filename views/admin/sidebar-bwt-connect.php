<?php
/**
 * Sidebar: BWT Connect
 *
 * @package XML Sitemap & Google News
 */

?>
<h3>
	<span class="dashicons dashicons-admin-site-alt3"></span>
	<?php esc_html_e( 'Bing Webmaster Tools', 'xml-sitemap-feed' ); ?>
</h3>
<?php
$bwt_options = (array) get_option( 'xmlsf_bwt_connect', array() );
if ( empty( $bwt_options['bing_api_key'] ) ) {
	// Initiate button.
	?>
	<p>
		<?php printf( /* translators: %s: Bing Webmaster Tools */ esc_html_x( 'Connect to %s for sitemap data retrieval and sitemap submissions.', 'Bing Webmaster Tools connection', 'xml-sitemap-feed' ), esc_html__( 'Bing Webmaster Tools', 'xml-sitemap-feed' ) ); ?>
	</p>
	<p>
		<a href="<?php echo esc_url( $settings_page_url ); ?>" class="button button-primary">
			<?php esc_html_e( 'Connect', 'xml-sitemap-feed' ); ?>
		</a>
	</p>
	<?php
} else {
	// Submit and Disconnect buttons.
	?>
	<form action="" method="post">
		<?php wp_nonce_field( XMLSF_BASENAME . '-bwt', '_xmlsf_bwt_nonce' ); ?>
		<p>
			<?php submit_button( sprintf( /* translators: %s: XML Sitemap Index */ __( 'Submit your %s now', 'xml-sitemap-feed' ), $sitemap_desc ), 'primary', 'xmlsf_bwt_manual_submit', false ); ?>
		</p>
		<p>
			<?php esc_html_e( 'Your site is connected to Bing Webmaster Tools. You can disconnect and reconnect if you encounter submission errors or wish to reset or transfer site connection ownership.', 'xml-sitemap-feed' ); ?>
		</p>
		<p>
			<input type="submit" name="xmlsf_bwt_disconnect" class="button button-small button-link-delete" value="<?php esc_attr_e( 'Disconnect', 'xml-sitemap-feed' ); ?>" onclick="javascript:return confirm( '<?php echo esc_js( __( 'You are about to DISCONNECT this site from Bing Webmaster Tools.', 'xml-sitemap-feed' ) ); ?>\n\n<?php echo esc_js( translate( 'Are you sure you want to do this?' ) ); // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction ?>' )" />
		</p>
	</form>
	<?php
}
