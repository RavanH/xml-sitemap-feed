<?php
/**
 * GSC Oauth section stage 1
 *
 * @package XML Sitemap & Google News - Google News Advanced
 */

?>
<h3><?php esc_html_e( 'Stage I. Create a Google Cloud Console project', 'xml-sitemap-feed' ); ?></h3>
<p><?php esc_html_e( 'Follow the steps below to create a Google Cloud Console project and obtain your credentials.', 'xml-sitemap-feed' ); ?> <?php esc_html_e( 'Please use a Google account that has Full access to the site property in Google Search Console.', 'xml-sitemap-feed' ); ?></p>
<ol>
	<li>
		<?php
		printf(
			/* translators: %s: Link to Google Cloud Console */
			esc_html__( 'Go to the %s and either create a new project or select an existing one.', 'xml-sitemap-feed' ),
			'<strong><a href="https://console.cloud.google.com/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Google Cloud Console', 'xml-sitemap-feed' ) . '</a></strong>'
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %1$s: API & Services, %2$s: OAuth consent screen */
			esc_html__( 'If you created a new project, navigate to %1$s > %2$s.', 'xml-sitemap-feed' ),
			'<strong>' . esc_html__( 'APIs & Services', 'xml-sitemap-feed' ) . '</strong>',
			'<strong>' . esc_html__( 'OAuth consent screen', 'xml-sitemap-feed' ) . '</strong>'
		);
		?>
		<ul>
			<li>
				<?php
				printf(
					/* translators: %1$s: Get started, %2$s: Create */
					esc_html__( 'Click the %1$s button, give your App a name and follow the steps to finally reach %2$s.', 'xml-sitemap-feed' ),
					'<strong>' . esc_html__( 'Get started', 'xml-sitemap-feed' ) . '</strong>',
					'<strong>' . esc_html__( 'Create', 'xml-sitemap-feed' ) . '</strong>'
				);
				?>
			</li>
			<li>
				<?php
				printf(
					/* translators: %1$s: Audience, %2$s: Publish app */
					esc_html__( 'Then navigate to %1$s and, if available, click %2$s.', 'xml-sitemap-feed' ),
					'<strong>' . esc_html__( 'Audience', 'xml-sitemap-feed' ) . '</strong>',
					'<strong>' . esc_html__( 'Publish app', 'xml-sitemap-feed' ) . '</strong>'
				);
				?>
			</li>
		</ul>
	</li>
	<li>
		<?php
		printf(
			/* translators: %1$s: API & Services, %2$s: Library, %3$s Google Search Console API */
			esc_html__( 'Navigate to %1$s > %2$s. Search for %3$s and enable it for your project.', 'xml-sitemap-feed' ),
			'<strong>' . esc_html__( 'APIs & Services', 'xml-sitemap-feed' ) . '</strong>',
			'<strong>' . esc_html__( 'Library', 'xml-sitemap-feed' ) . '</strong>',
			'<strong>' . esc_html__( 'Google Search Console API', 'xml-sitemap-feed' ) . '</strong>'
		);
		?>
	</li>
</ol>

<h3><?php esc_html_e( 'Stage II. Create OAuth Credentials', 'xml-sitemap-feed' ); ?></h3>
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
		esc_html__( 'After filling the fields below and clicking %1$s a button %2$s will allow you to finalize the connection.', 'xml-sitemap-feed' ),
		'<strong>' . esc_html( \translate( 'Save Changes' ) ) . '</strong>', // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
		'<strong>' . esc_html__( 'Connect to Google Search Console', 'xml-sitemap-feed' ) . '</strong>'
	);
	?>
</p>
<hr>