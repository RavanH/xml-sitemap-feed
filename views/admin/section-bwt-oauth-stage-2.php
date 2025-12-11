<?php
/**
 * GSC Oauth section stage 2
 *
 * @package XML Sitemap & Google News
 */

?>
<h3><?php esc_html_e( 'Stage II. Authorize the connection', 'xml-sitemap-feed' ); ?></h3>
<p>
	<a href="<?php echo esc_url( $oauth_url ); ?>" class="button button-primary">
		<?php esc_html_e( 'Connect to Bing Webmaster Tools', 'xml-sitemap-feed' ); ?>
	</a>
</p>
<p class="description">
	<?php esc_html_e( 'You will be redirected to Google to authorize your site.', 'xml-sitemap-feed' ); ?> <?php esc_html_e( 'Please use a Microsoft account that has as least Read-write access to the site property in Bing Webmaster Tools.', 'xml-sitemap-feed' ); ?>
	<br>
	<?php
	printf(
		/* translators: %s the URL */
		esc_html__( 'Reminder: The redirect URI for your Bing OAuth 2.0 client configuration should be %s', 'xml-sitemap-feed' ),
		'<code>' . esc_url( site_url( 'index.php?' . self::$query_var ) ) . '</code>'
	);
	?>
</p>
<hr>
<p>
	<?php printf( /* translators: %1$s Client ID, %2$s Save Changes */ esc_html__( 'To restart the setup process, clear the %1$s field and %2$s.', 'xml-sitemap-feed' ), esc_html__( 'Client ID', 'xml-sitemap-feed' ), esc_html( \translate( 'Save Changes' ) ) ); // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction ?>
</p>