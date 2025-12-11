<?php
/**
 * BWT Oauth section stage 1
 *
 * @package XML Sitemap & Google News
 */

?>
<h3><?php esc_html_e( 'Prerequisites', 'xml-sitemap-feed' ); ?></h3>
<ol>
	<li>
		<?php
		printf(
			/* translators: %s: Link to Google Cloud Console */
			esc_html__( 'Your site property needs to be set up in Bing Webmaster Tools. If you have not already done that, follow the instructions on %s.', 'xml-sitemap-feed' ),
			'<a href="https://www.bing.com/webmasters/help/add-and-verify-site-12184f8b" target="_blank" rel="noopener noreferrer">https://www.bing.com/webmasters/help/add-and-verify-site-12184f8b</a>'
		);
		?>
	</li>
</ol>
<p><?php esc_html_e( 'Follow the steps below to create a Bing Webmaster Tools credentials.', 'xml-sitemap-feed' ); ?> <?php esc_html_e( 'Please use a Microsoft account that has at least Read-write access to the site property in Bing Webmaster Tools.', 'xml-sitemap-feed' ); ?></p>
<h3><?php esc_html_e( 'Stage I. Create OAuth Credentials', 'xml-sitemap-feed' ); ?></h3>
<ol>
	<li>
		<?php
		printf(
			/* translators: %1$s: API & Services, %2$s: Credentials, %3$s + Create credentials */
			esc_html__( 'Go to %1$s > %2$s and click %3$s.', 'xml-sitemap-feed' ),
			'<strong>' . esc_html__( 'APIs & Services', 'xml-sitemap-feed' ) . '</strong>',
			'<strong>' . esc_html__( 'Credentials', 'xml-sitemap-feed' ) . '</strong>',
			'<strong>' . esc_html__( '+ Create credentials', 'xml-sitemap-feed' ) . '</strong>'
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %1$s: OAuth client ID, %2$s: Web application */
			esc_html__( 'Select %1$s and choose %2$s as the Application type.', 'xml-sitemap-feed' ),
			'<strong>' . esc_html__( 'OAuth client ID', 'xml-sitemap-feed' ) . '</strong>',
			'<strong>' . esc_html__( 'Web application', 'xml-sitemap-feed' ) . '</strong>'
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %1$s: Authorized redirect URIs, %2$s: The redirect URI to be registered in Google Cloud Console */
			esc_html__( 'In the %1$s field, add the following exact URI: %2$s', 'xml-sitemap-feed' ),
			'<strong>' . esc_html__( 'Authorized redirect URIs', 'xml-sitemap-feed' ) . '</strong>',
			'<code>' . esc_url( site_url( 'index.php?' . self::$query_var ) ) . '</code>' // This is your plugin's custom endpoint URL.
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %1$s: Create, %2$s: Client ID, %3$s: Client secret */
			esc_html__( 'Click %1$s button and a popup dialog will then display your %2$s and %3$s. Copy each of these and paste them into their respective fields below.', 'xml-sitemap-feed' ),
			'<strong>' . esc_html__( 'Create', 'xml-sitemap-feed' ) . '</strong>',
			'<strong>' . esc_html__( 'Client ID', 'xml-sitemap-feed' ) . '</strong>',
			'<strong>' . esc_html__( 'Client secret', 'xml-sitemap-feed' ) . '</strong>'
		);
		?>
	</li>
</ol>
<p><strong><?php esc_html_e( 'Important:', 'xml-sitemap-feed' ); ?></strong> <?php esc_html_e( 'Ensure the Redirect URI is copied and pasted exactly as shown.', 'xml-sitemap-feed' ); ?></p>
<p>
	<?php
	printf(
		/* translators: %s: Save Settings */
		esc_html__( 'After filling the fields below and clicking %1$s a button %2$s will allow you (or another site admin) to finalize the connection.', 'xml-sitemap-feed' ),
		'<strong>' . esc_html( \translate( 'Save Changes' ) ) . '</strong>', // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
		'<strong>' . esc_html__( 'Connect to Bing Webmaster Tools', 'xml-sitemap-feed' ) . '</strong>'
	);
	?>
</p>
<hr>