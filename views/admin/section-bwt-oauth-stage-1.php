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
			/* translators: %1$s: Bing Webmaster Tools, %2$s: Settings */
			esc_html__( 'Sign in to your account on %1$s and open %2$s via the gear icon on the top right.', 'xml-sitemap-feed' ),
			'<strong><a href="https://www.bing.com/webmasters/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Bing Webmaster Tools', 'xml-sitemap-feed' ) . '</a></strong>',
			'<strong>' . esc_html__( 'Settings', 'xml-sitemap-feed' ) . '</strong>'
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %1$s: API Access, %2$s: OAuth client */
			esc_html__( 'Select %1$s (read and accept the Terms and Conditions if displayed) and go to %2$s. If you already have OAuth clients, follow %3$s to create a new one.', 'xml-sitemap-feed' ),
			'<strong>' . esc_html__( 'API Access', 'xml-sitemap-feed' ) . '</strong>',
			'<strong>' . esc_html__( 'OAuth client', 'xml-sitemap-feed' ) . '</strong>',
			'<strong>' . esc_html__( '+ Add', 'xml-sitemap-feed' ) . '</strong>'
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %1$s: Name, %2$s: The redirect URI to be registered in Bing Webmaster Tools */
			esc_html__( 'Give your OAuth client a reconizable %1$s and in the %2$s field, add the following exact URI: %3$s', 'xml-sitemap-feed' ),
			'<strong>' . esc_html__( 'Name', 'xml-sitemap-feed' ) . '</strong>',
			'<strong>' . esc_html__( 'Redirect URI', 'xml-sitemap-feed' ) . '</strong>',
			'<code>' . esc_url( site_url( 'index.php?' . self::$query_var ) ) . '</code>' // This is your plugin's custom endpoint URL.
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %1$s: Client ID, %2$s: Client secret */
			esc_html__( 'Select the created OAuth client name, then copy the %1$s and %2$s into their respective fields below.', 'xml-sitemap-feed' ),
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