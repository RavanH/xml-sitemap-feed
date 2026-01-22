<?php
/**
 * BWT Oauth section stage 2
 *
 * @package XML Sitemap & Google News
 */

$sitemap = xmlsf()->sitemap->get_sitemap_url();
$data    = \XMLSF\BWT_Connect::get( $sitemap );

?>
<h3><?php esc_html_e( 'Stage II. Test and activate the connection', 'xml-sitemap-feed' ); ?></h3>
<?php
if ( \is_wp_error( $data ) ) {
	// Display error message.
	?>
	<p>
		<strong style="color:#d63638">
			<?php esc_html_e( 'There was an error requesting sitemap data from Bing Webmaster Tools.', 'xml-sitemap-feed' ); ?>
		</strong>
		<br>
		<a href="https://premium.status301.com/knowledge-base/xml-sitemap-google-news/connect-your-site-to-bing-webmaster-tools/#errors" target="_blank" rel="noopener noreferrer">
			<?php echo esc_html( $data->get_error_message() ); ?>
		</a>
	</p>
	<p>
		<a href="" class="button button-primary"><?php echo esc_html( translate( 'Retry' ) ); // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction ?></a>
	</p>
	<p>
		<?php printf( /* translators: %1$s API Key, %2$s Save Changes */ esc_html__( 'To restart the setup process, clear the %1$s field and %2$s.', 'xml-sitemap-feed' ), esc_html__( 'API Key', 'xml-sitemap-feed' ), esc_html( \translate( 'Save Changes' ) ) ); // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction ?>
	</p>
	<?php
} else {
	// Update option with 'connected' status.
	\update_option( self::$option_group, array( 'bing_api_key' => self::$pw_placeholder, 'connected' => true ) );

	// Display success message and redirect.
	$redirect_url = \add_query_arg( array( 'page' => 'xmlsf', 'settings-updated' => 'true' ), \admin_url( 'options-general.php' ) );
	?>
	<p>
		<strong style="color:#00a32a">
			<?php esc_html_e( 'The connection test was successful and the connection was activated!', 'xml-sitemap-feed' ); ?>
		</strong>
		<br>
		<a href="<?php echo esc_url( $redirect_url ); ?>">
			<?php esc_html_e( 'You will now be redirected back to the plugin settings page.', 'xml-sitemap-feed' ); ?>
		</a>
	</p>
	<meta http-equiv="refresh" content="3;url=<?php echo esc_url( $redirect_url ); ?>">
	<style>
		#submit {
			display: none;
		}
	</style>
	<?php
}
?>
<hr>
