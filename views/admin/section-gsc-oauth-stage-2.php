<?php
/**
 * GSC Oauth section stage 2
 *
 * @package XML Sitemap & Google News - Google News Advanced
 */

?>
<h3><?php \esc_html_e( 'Stage 2/2. Authorize the connection', 'xml-sitemap-feed' ); ?></h3>
<p>
	<a href="<?php echo \esc_url( $oauth_url ); ?>" class="button button-primary">
		<?php \esc_html_e( 'Connect to Google Search Console', 'xml-sitemap-feed' ); ?>
	</a>
</p>
<p class="description">
	<?php \esc_html_e( 'You will be redirected to Google to authorize your site.', 'xml-sitemap-feed' ); ?> <?php \esc_html_e( 'Please use a Google account that has FULL access to the site property in Google Search Console.', 'xml-sitemap-feed' ); ?>
	<br>
	<?php
	\printf(
		/* translators: %s the URL */
		\esc_html__( 'Reminder: The redirect URI for your Google Cloud Console OAuth 2.0 client configuration should be %s', 'xml-sitemap-feed' ),
		'<code>' . \esc_url( \site_url( 'index.php?' . self::$query_var ) ) . '</code>'
	);
	?>
</p>
<p>
	<?php \esc_html_e( 'After successful authorization, you will be able to configure the plugin settings and start submitting your sitemap to Google Search Console manually or automatically on content updates.', 'xml-sitemap-feed' ); ?>
</p>
<hr>
<p>
	<?php \printf( /* translators: %1$s Client ID, %2$s Save Changes */ \esc_html__( 'To restart the setup process, clear the %1$s field and %2$s.', 'xml-sitemap-feed' ), \esc_html__( 'Client ID', 'xml-sitemap-feed' ), \esc_html( \translate( 'Save Changes' ) ) ); // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction ?>
</p>